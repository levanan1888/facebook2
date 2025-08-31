<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\FacebookAdAccount;
use App\Models\FacebookBusiness;
use App\Models\FacebookAd;
use App\Models\FacebookAdInsight;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        // Mặc định: thống kê theo tháng hiện tại để đồng bộ tất cả số liệu
        $from = now()->startOfMonth()->toDateString();
        $to = now()->toDateString();
        $dateFrom = $request->get('dateFrom', $from);
        $dateTo = $request->get('dateTo', $to);
        $rangeLabel = $dateFrom . ' → ' . $dateTo;

        $spendByBusiness = FacebookBusiness::query()
            ->with(['adAccounts' => function ($q) use ($dateFrom, $dateTo) {
                $q->withSum(['ads as spend_sum' => function ($q2) use ($dateFrom, $dateTo) {
                    $q2->whereBetween('last_insights_sync', [$dateFrom, $dateTo]);
                }], 'id');
            }])
            ->get()
            ->map(function (FacebookBusiness $b) {
                $sum = $b->adAccounts->sum('spend_sum');
                // Tính tổng spend từ insights
                $totalSpend = \App\Models\FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
                    ->join('facebook_ad_accounts', 'facebook_ads.account_id', '=', 'facebook_ad_accounts.id')
                    ->join('facebook_businesses', 'facebook_ad_accounts.business_id', '=', 'facebook_businesses.id')
                    ->where('facebook_businesses.id', $b->id)
                    ->whereBetween('facebook_ad_insights.date', [$dateFrom, $dateTo])
                    ->sum('facebook_ad_insights.spend');
                
                return [
                    'id' => $b->id,
                    'name' => $b->name,
                    'spend' => (float) $totalSpend,
                    'accounts' => $b->adAccounts->count(),
                ];
            })
            ->sortByDesc('spend')
            ->values();

        $topAccounts = FacebookAdAccount::query()
            ->get(['id','account_id','name'])
            ->map(function (FacebookAdAccount $a) use ($dateFrom, $dateTo) {
                // Tính tổng spend từ insights
                $totalSpend = \App\Models\FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
                    ->where('facebook_ads.account_id', $a->id)
                    ->whereBetween('facebook_ad_insights.date', [$dateFrom, $dateTo])
                    ->sum('facebook_ad_insights.spend');
                
                return [
                    'id' => $a->id,
                    'account_id' => $a->account_id,
                    'name' => $a->name,
                    'spend' => (float) $totalSpend,
                ];
            })
            ->sortByDesc('spend')
            ->values()
            ->take(20);

        $base = FacebookAd::query()->whereBetween('last_insights_sync', [$dateFrom, $dateTo]);
        $spend = (float) \App\Models\FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
            ->whereBetween('facebook_ads.last_insights_sync', [$dateFrom, $dateTo])
            ->sum('facebook_ad_insights.spend');
        $impressions = (int) \App\Models\FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
            ->whereBetween('facebook_ads.last_insights_sync', [$dateFrom, $dateTo])
            ->sum('facebook_ad_insights.impressions');
        $clicks = (int) \App\Models\FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
            ->whereBetween('facebook_ads.last_insights_sync', [$dateFrom, $dateTo])
            ->sum('facebook_ad_insights.clicks');
        $ctr = $impressions > 0 ? ($clicks / $impressions) * 100.0 : 0.0;
        $cpc = $clicks > 0 ? ($spend / $clicks) : 0.0;
        $cpm = $impressions > 0 ? ($spend / $impressions) * 1000.0 : 0.0;
        // ROAS: tổng purchase value / spend (nếu có action_values.purchase)
        $purchaseValue = (float) \App\Models\FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
            ->whereBetween('facebook_ads.last_insights_sync', [$dateFrom, $dateTo])
            ->get(['facebook_ad_insights.action_values'])
            ->sum(function ($row) {
                $vals = is_array($row->action_values) ? $row->action_values : [];
                foreach ($vals as $v) {
                    if (($v['action_type'] ?? '') === 'purchase') {
                        return (float) ($v['value'] ?? 0);
                    }
                }
                return 0;
            });
        $roas = $spend > 0 ? ($purchaseValue / $spend) : 0.0;

        $totals = [
            'spend_period' => $spend,
            'impressions_period' => $impressions,
            'clicks_period' => $clicks,
            'ctr_period' => $ctr,
            'cpc_period' => $cpc,
            'cpm_period' => $cpm,
            'roas_period' => $roas,
        ];

        return view('analytics.index', [
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'rangeLabel' => $rangeLabel,
            'spendByBusiness' => $spendByBusiness,
            'topAccounts' => $topAccounts,
            'totals' => $totals,
        ]);
    }

    /**
     * API: trả về options cho bộ lọc
     */
    public function options(AnalyticsService $service)
    {
        return response()->json($service->getOptions());
    }

    /**
     * API: tổng quan theo filters
     */
    public function summary(Request $request, AnalyticsService $service)
    {
        $filters = $this->extractFilters($request);
        return response()->json($service->getSummary($filters));
    }

    /**
     * API: breakdown theo business/account
     */
    public function breakdown(Request $request, AnalyticsService $service)
    {
        $filters = $this->extractFilters($request);
        return response()->json($service->getBreakdown($filters));
    }

    /**
     * API: time-series các metric
     */
    public function series(Request $request, AnalyticsService $service)
    {
        $filters = $this->extractFilters($request);
        $metrics = $request->input('metrics', ['spend']);
        if (is_string($metrics)) {
            $metrics = array_filter(array_map('trim', explode(',', $metrics)));
        }
        return response()->json($service->getTimeSeries($filters, $metrics));
    }

    /**
     * API: chi tiết ads (post) trong ad set/campaign/account
     */
    public function adDetails(Request $request, AnalyticsService $service)
    {
        $filters = $this->extractFilters($request);
        $filters['adsetId'] = $request->input('adsetId');
        return response()->json($service->getAdDetails($filters));
    }

    /**
     * Gom bộ lọc từ request (an toàn, rõ ràng)
     */
    protected function extractFilters(Request $request): array
    {
        return [
            'preset' => $request->input('preset'),
            'dateFrom' => $request->input('dateFrom'),
            'dateTo' => $request->input('dateTo'),
            'level' => $request->input('level', 'account'),
            'businessId' => $request->input('businessId'),
            'accountId' => $request->input('accountId'),
            'campaignId' => $request->input('campaignId'),
            'by' => $request->input('by', 'business'),
            'sortBy' => $request->input('sortBy', 'spend'),
            'sortDir' => $request->input('sortDir', 'desc'),
            'limit' => (int) $request->input('limit', 10),
            'minCpc' => $request->input('minCpc'),
            'maxCpc' => $request->input('maxCpc'),
            'minCtr' => $request->input('minCtr'),
            'maxCtr' => $request->input('maxCtr'),
            'minCpm' => $request->input('minCpm'),
            'maxCpm' => $request->input('maxCpm'),
            'minRoas' => $request->input('minRoas'),
            'maxRoas' => $request->input('maxRoas'),
        ];
    }
}


