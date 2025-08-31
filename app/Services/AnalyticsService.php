<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\FacebookAdAccount;
use App\Models\FacebookBusiness;
use App\Models\FacebookCampaign;
use App\Models\FacebookInsight;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\Database\Query\Builder as QueryBuilderContract;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Collection;

/**
 * Dịch vụ tính toán báo cáo/analytics từ bảng facebook_insights
 */
class AnalyticsService
{
    /**
     * Danh sách options cho bộ lọc
     */
    public function getOptions(): array
    {
        $businesses = FacebookBusiness::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (FacebookBusiness $b) => [
                'id' => (string) $b->id,
                'name' => (string) ($b->name ?? $b->id),
            ])->values()->all();

        $adAccounts = FacebookAdAccount::query()
            ->orderBy('name')
            ->get(['id', 'name', 'business_id', 'account_id'])
            ->map(fn (FacebookAdAccount $a) => [
                'id' => (string) $a->id,
                'accountId' => (string) ($a->account_id ?? $a->id),
                'name' => (string) ($a->name ?? $a->account_id ?? $a->id),
                'businessId' => (string) ($a->business_id ?? ''),
            ])->values()->all();

        $campaigns = FacebookCampaign::query()
            ->orderBy('name')
            ->get(['id', 'name', 'ad_account_id'])
            ->map(fn (FacebookCampaign $c) => [
                'id' => (string) $c->id,
                'name' => (string) ($c->name ?? $c->id),
                'adAccountId' => (string) ($c->ad_account_id ?? ''),
            ])->values()->all();

        return compact('businesses', 'adAccounts', 'campaigns');
    }

    /**
     * Tính tổng quan theo bộ lọc
     */
    public function getSummary(array $filters): array
    {
        [$from, $to] = $this->resolveDateRange($filters);

        $base = $this->buildBaseQuery($filters)
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()]);

        $spend = (float) $base->clone()->sum('spend');
        $impressions = (int) $base->clone()->sum('impressions');
        $clicks = (int) $base->clone()->sum('clicks');
        $ctr = $impressions > 0 ? ($clicks / $impressions) * 100.0 : 0.0;
        $cpc = $clicks > 0 ? ($spend / $clicks) : 0.0;
        $cpm = $impressions > 0 ? ($spend / $impressions) * 1000.0 : 0.0;

        $purchaseValue = $this->sumPurchaseValue($base->clone()->get(['action_values']));
        $roas = $spend > 0 ? ($purchaseValue / $spend) : 0.0;

        return [
            'spend' => $spend,
            'impressions' => $impressions,
            'clicks' => $clicks,
            'ctr' => $ctr,
            'cpc' => $cpc,
            'cpm' => $cpm,
            'roas' => $roas,
            'dateFrom' => $from->toDateString(),
            'dateTo' => $to->toDateString(),
        ];
    }

    /**
     * Chuỗi thời gian theo ngày cho các metric yêu cầu
     * @param array $metrics vd: ['spend','impressions','clicks','ctr','cpc','cpm','roas']
     */
    public function getTimeSeries(array $filters, array $metrics): array
    {
        [$from, $to] = $this->resolveDateRange($filters);

        $base = $this->buildBaseQuery($filters)
            ->selectRaw('date as d')
            ->selectRaw('COALESCE(SUM(spend),0) as spend')
            ->selectRaw('COALESCE(SUM(impressions),0) as impressions')
            ->selectRaw('COALESCE(SUM(clicks),0) as clicks')
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->groupBy('date')
            ->orderBy('date');

        /** @var Collection<int, array{id:string,spend:string,impressions:string,clicks:string}> $rows */
        $rows = collect($base->get())->map(function ($r) {
            return [
                'date' => (string) $r->d,
                'spend' => (float) $r->spend,
                'impressions' => (int) $r->impressions,
                'clicks' => (int) $r->clicks,
            ];
        });

        // Bổ sung các ngày trống
        $period = CarbonPeriod::create($from, $to);
        $byDate = $rows->keyBy('date');
        $series = [];
        foreach ($period as $day) {
            $dateStr = $day->toDateString();
            $series[$dateStr] = $byDate[$dateStr] ?? [
                'date' => $dateStr,
                'spend' => 0.0,
                'impressions' => 0,
                'clicks' => 0,
            ];
        }

        // Tính các metric dẫn xuất
        $data = array_values(array_map(function ($row) use ($metrics) {
            $row['ctr'] = $row['impressions'] > 0 ? ($row['clicks'] / $row['impressions']) * 100.0 : 0.0;
            $row['cpc'] = $row['clicks'] > 0 ? ($row['spend'] / $row['clicks']) : 0.0;
            $row['cpm'] = $row['impressions'] > 0 ? ($row['spend'] / $row['impressions']) * 1000.0 : 0.0;
            // ROAS theo ngày: ước lượng từ action_values, nếu cần chính xác cần truy vấn riêng
            $row['roas'] = 0.0; // có thể mở rộng sau khi có dữ liệu theo ngày
            return array_intersect_key($row, array_flip(array_merge(['date'], $metrics)));
        }, $series));

        return [
            'dateFrom' => $from->toDateString(),
            'dateTo' => $to->toDateString(),
            'rows' => $data,
        ];
    }

    /**
     * Phân rã theo business|account với sắp xếp và giới hạn
     */
    public function getBreakdown(array $filters): array
    {
        [$from, $to] = $this->resolveDateRange($filters);
        $by = $filters['by'] ?? 'business'; // business|account|campaign
        $sortBy = $filters['sortBy'] ?? 'spend';
        $sortDir = strtolower((string) ($filters['sortDir'] ?? 'desc')) === 'asc' ? 'asc' : 'desc';
        $limit = (int) ($filters['limit'] ?? 10);

        if ($by === 'account') {
            $query = $this->buildBaseQuery($filters)
                ->join('facebook_ad_accounts as a', 'a.id', '=', 'fi.ref_id')
                ->leftJoin('facebook_businesses as b', 'b.id', '=', 'a.business_id')
                ->where('fi.level', '=', 'account')
                ->whereBetween('fi.date', [$from->toDateString(), $to->toDateString()])
                ->groupBy('fi.ref_id', 'a.name', 'a.account_id', 'a.business_id', 'b.name')
                ->selectRaw('fi.ref_id as id, COALESCE(a.name, a.account_id) as name, a.account_id, a.business_id, b.name as business_name')
                ->selectRaw('COALESCE(SUM(fi.spend),0) as spend')
                ->selectRaw('COALESCE(SUM(fi.impressions),0) as impressions')
                ->selectRaw('COALESCE(SUM(fi.clicks),0) as clicks');

            $rows = collect($query->get())->map(function ($r) {
                $spend = (float) $r->spend;
                $impressions = (int) $r->impressions;
                $clicks = (int) $r->clicks;
                return [
                    'id' => (string) $r->id,
                    'name' => (string) ($r->name ?? $r->account_id ?? $r->id),
                    'businessId' => (string) ($r->business_id ?? ''),
                    'businessName' => (string) ($r->business_name ?? ''),
                    'spend' => $spend,
                    'impressions' => $impressions,
                    'clicks' => $clicks,
                    'ctr' => $impressions > 0 ? ($clicks / $impressions) * 100.0 : 0.0,
                    'cpc' => $clicks > 0 ? ($spend / $clicks) : 0.0,
                    'cpm' => $impressions > 0 ? ($spend / $impressions) * 1000.0 : 0.0,
                ];
            });
        } elseif ($by === 'campaign') {
            // Breakdown theo campaign
            $query = FacebookInsight::query()->from('facebook_insights as fi')
                ->join('facebook_campaigns as c', 'c.id', '=', 'fi.ref_id')
                ->where('fi.level', '=', 'campaign')
                ->whereBetween('fi.date', [$from->toDateString(), $to->toDateString()])
                ->groupBy('c.id', 'c.name', 'c.ad_account_id')
                ->selectRaw('c.id as id, COALESCE(c.name, c.id) as name, c.ad_account_id')
                ->selectRaw('COALESCE(SUM(fi.spend),0) as spend')
                ->selectRaw('COALESCE(SUM(fi.impressions),0) as impressions')
                ->selectRaw('COALESCE(SUM(fi.clicks),0) as clicks');

            // Lọc theo account/business nếu có
            if (!empty($filters['accountId'])) {
                $query->where('c.ad_account_id', '=', $filters['accountId']);
            }
            if (!empty($filters['businessId'])) {
                $query->join('facebook_ad_accounts as a', 'a.id', '=', 'c.ad_account_id')
                    ->where('a.business_id', '=', $filters['businessId']);
            }

            $rows = collect($query->get())->map(function ($r) {
                $spend = (float) $r->spend;
                $impressions = (int) $r->impressions;
                $clicks = (int) $r->clicks;
                return [
                    'id' => (string) $r->id,
                    'name' => (string) ($r->name ?? $r->id),
                    'adAccountId' => (string) ($r->ad_account_id ?? ''),
                    'spend' => $spend,
                    'impressions' => $impressions,
                    'clicks' => $clicks,
                    'ctr' => $impressions > 0 ? ($clicks / $impressions) * 100.0 : 0.0,
                    'cpc' => $clicks > 0 ? ($spend / $clicks) : 0.0,
                    'cpm' => $impressions > 0 ? ($spend / $impressions) * 1000.0 : 0.0,
                ];
            });
        } elseif ($by === 'adset') {
            // Breakdown theo ad set
            $query = FacebookInsight::query()->from('facebook_insights as fi')
                ->join('facebook_ad_sets as s', 's.id', '=', 'fi.ref_id')
                ->where('fi.level', '=', 'adset')
                ->whereBetween('fi.date', [$from->toDateString(), $to->toDateString()])
                ->groupBy('s.id', 's.name', 's.campaign_id')
                ->selectRaw('s.id as id, COALESCE(s.name, s.id) as name, s.campaign_id')
                ->selectRaw('COALESCE(SUM(fi.spend),0) as spend')
                ->selectRaw('COALESCE(SUM(fi.impressions),0) as impressions')
                ->selectRaw('COALESCE(SUM(fi.clicks),0) as clicks');

            // Lọc theo cấp trên nếu có
            if (!empty($filters['campaignId'])) {
                $query->where('s.campaign_id', '=', $filters['campaignId']);
            }
            if (!empty($filters['accountId'])) {
                $query->join('facebook_campaigns as c_f', 'c_f.id', '=', 's.campaign_id')
                    ->where('c_f.ad_account_id', '=', $filters['accountId']);
            }

            $rows = collect($query->get())->map(function ($r) {
                $spend = (float) $r->spend;
                $impressions = (int) $r->impressions;
                $clicks = (int) $r->clicks;
                return [
                    'id' => (string) $r->id,
                    'name' => (string) ($r->name ?? $r->id),
                    'campaignId' => (string) ($r->campaign_id ?? ''),
                    'spend' => $spend,
                    'impressions' => $impressions,
                    'clicks' => $clicks,
                    'ctr' => $impressions > 0 ? ($clicks / $impressions) * 100.0 : 0.0,
                    'cpc' => $clicks > 0 ? ($spend / $clicks) : 0.0,
                    'cpm' => $impressions > 0 ? ($spend / $impressions) * 1000.0 : 0.0,
                ];
            });
        } else {
            // business
            $query = FacebookInsight::query()->from('facebook_insights as fi')
                ->join('facebook_ad_accounts as a', 'a.id', '=', 'fi.ref_id')
                ->join('facebook_businesses as b', 'b.id', '=', 'a.business_id')
                ->where('fi.level', '=', 'account')
                ->whereBetween('fi.date', [$from->toDateString(), $to->toDateString()])
                ->groupBy('b.id', 'b.name')
                ->selectRaw('b.id as id, b.name as name')
                ->selectRaw('COALESCE(SUM(fi.spend),0) as spend')
                ->selectRaw('COALESCE(SUM(fi.impressions),0) as impressions')
                ->selectRaw('COALESCE(SUM(fi.clicks),0) as clicks');

            $rows = collect($query->get())->map(function ($r) {
                $spend = (float) $r->spend;
                $impressions = (int) $r->impressions;
                $clicks = (int) $r->clicks;
                return [
                    'id' => (string) $r->id,
                    'name' => (string) ($r->name ?? $r->id),
                    'spend' => $spend,
                    'impressions' => $impressions,
                    'clicks' => $clicks,
                    'ctr' => $impressions > 0 ? ($clicks / $impressions) * 100.0 : 0.0,
                    'cpc' => $clicks > 0 ? ($spend / $clicks) : 0.0,
                    'cpm' => $impressions > 0 ? ($spend / $impressions) * 1000.0 : 0.0,
                ];
            });
        }

        // Lọc theo ngưỡng hiệu suất nếu có
        $rows = $this->applyPerformanceFilters($rows, $filters);

        // Sắp xếp và giới hạn
        $sorted = $rows->sortBy($sortBy, SORT_REGULAR, $sortDir === 'desc')->values();
        $limited = $limit > 0 ? $sorted->take($limit)->values() : $sorted;

        return [
            'by' => $by,
            'sortBy' => $sortBy,
            'sortDir' => $sortDir,
            'limit' => $limit,
            'rows' => $limited->all(),
        ];
    }

    /**
     * Xây dựng query base từ bộ lọc
     * - Mặc định lấy level=account (ổn định nhất)
     */
    protected function buildBaseQuery(array $filters): EloquentBuilder|QueryBuilderContract
    {
        $q = FacebookInsight::query()->from('facebook_insights as fi');

        // Level mặc định
        $level = (string) ($filters['level'] ?? 'account');
        $q->where('fi.level', $level);

        $businessId = $filters['businessId'] ?? null;
        $accountId = $filters['accountId'] ?? null;
        $campaignId = $filters['campaignId'] ?? null;

        if ($level === 'account') {
            // fi.ref_id = account id
            if ($businessId) {
                $q->join('facebook_ad_accounts as a_f', 'a_f.id', '=', 'fi.ref_id')
                    ->where('a_f.business_id', '=', $businessId);
            }
            if ($accountId) {
                $q->where('fi.ref_id', '=', $accountId);
            }
        } elseif ($level === 'campaign') {
            // fi.ref_id = campaign id → join để lọc theo business/account
            $q->join('facebook_campaigns as c_f', 'c_f.id', '=', 'fi.ref_id');
            if ($accountId) {
                $q->where('c_f.ad_account_id', '=', $accountId);
            }
            if ($businessId) {
                $q->join('facebook_ad_accounts as a_f', 'a_f.id', '=', 'c_f.ad_account_id')
                    ->where('a_f.business_id', '=', $businessId);
            }
            if ($campaignId) {
                $q->where('fi.ref_id', '=', $campaignId);
            }
        } elseif ($level === 'adset') {
            // fi.ref_id = adset id → join campaign + account
            $q->join('facebook_ad_sets as s_f', 's_f.id', '=', 'fi.ref_id')
              ->leftJoin('facebook_campaigns as c_f', 'c_f.id', '=', 's_f.campaign_id');
            if ($campaignId) {
                $q->where('s_f.campaign_id', '=', $campaignId);
            }
            if ($accountId) {
                $q->where('c_f.ad_account_id', '=', $accountId);
            }
            if ($businessId) {
                $q->join('facebook_ad_accounts as a_f', 'a_f.id', '=', 'c_f.ad_account_id')
                    ->where('a_f.business_id', '=', $businessId);
            }
        } else { // ad
            // fi.ref_id = ad id → join account + campaign
            $q->join('facebook_ads as ad_f', 'ad_f.id', '=', 'fi.ref_id')
              ->leftJoin('facebook_campaigns as c_f', 'c_f.id', '=', 'ad_f.campaign_id')
              ->leftJoin('facebook_ad_accounts as a_f', 'a_f.id', '=', 'ad_f.account_id');
            if ($campaignId) {
                $q->where('ad_f.campaign_id', '=', $campaignId);
            }
            if ($accountId) {
                $q->where('ad_f.account_id', '=', $accountId);
            }
            if ($businessId) {
                $q->where('a_f.business_id', '=', $businessId);
            }
        }

        return $q;
    }

    /**
     * Chuẩn hoá dải ngày từ filters: preset day|week|month hoặc dateFrom/dateTo
     * Mặc định: hôm qua
     */
    protected function resolveDateRange(array $filters): array
    {
        $preset = strtolower((string) ($filters['preset'] ?? 'month'));
        $today = Carbon::today();
        if (!empty($filters['dateFrom']) && !empty($filters['dateTo'])) {
            $from = Carbon::parse((string) $filters['dateFrom']);
            $to = Carbon::parse((string) $filters['dateTo']);
            return [$from, $to];
        }

        switch ($preset) {
            case 'week':
                $from = $today->copy()->subDays(6);
                $to = $today;
                break;
            case 'month':
                $from = $today->copy()->startOfMonth();
                $to = $today;
                break;
            case 'day':
            default:
                $from = $today->copy()->subDay();
                $to = $today->copy()->subDay();
                break;
        }

        return [$from, $to];
    }

    /**
     * Tính tổng purchase value từ cột action_values (mảng JSON)
     */
    protected function sumPurchaseValue($rows): float
    {
        return (float) collect($rows)->sum(function ($row) {
            $vals = is_array($row->action_values) ? $row->action_values : [];
            foreach ($vals as $v) {
                if (($v['action_type'] ?? '') === 'purchase') {
                    return (float) ($v['value'] ?? 0);
                }
            }
            return 0;
        });
    }

    /**
     * Lọc theo các ngưỡng hiệu suất từ filters: min/max cho CPC, CTR, CPM, ROAS
     */
    protected function applyPerformanceFilters(Collection $rows, array $filters): Collection
    {
        $minCpc = isset($filters['minCpc']) ? (float) $filters['minCpc'] : null;
        $maxCpc = isset($filters['maxCpc']) ? (float) $filters['maxCpc'] : null;
        $minCtr = isset($filters['minCtr']) ? (float) $filters['minCtr'] : null;
        $maxCtr = isset($filters['maxCtr']) ? (float) $filters['maxCtr'] : null;
        $minCpm = isset($filters['minCpm']) ? (float) $filters['minCpm'] : null;
        $maxCpm = isset($filters['maxCpm']) ? (float) $filters['maxCpm'] : null;
        $minRoas = isset($filters['minRoas']) ? (float) $filters['minRoas'] : null;
        $maxRoas = isset($filters['maxRoas']) ? (float) $filters['maxRoas'] : null;

        // Lưu ý: ROAS hiện chưa tính ở breakdown; có thể bổ sung khi cần
        return $rows->filter(function ($r) use ($minCpc, $maxCpc, $minCtr, $maxCtr, $minCpm, $maxCpm, $minRoas, $maxRoas) {
            if ($minCpc !== null && $r['cpc'] < $minCpc) return false;
            if ($maxCpc !== null && $r['cpc'] > $maxCpc) return false;
            if ($minCtr !== null && $r['ctr'] < $minCtr) return false;
            if ($maxCtr !== null && $r['ctr'] > $maxCtr) return false;
            if ($minCpm !== null && $r['cpm'] < $minCpm) return false;
            if ($maxCpm !== null && $r['cpm'] > $maxCpm) return false;
            if ($minRoas !== null && (($r['roas'] ?? 0) < $minRoas)) return false;
            if ($maxRoas !== null && (($r['roas'] ?? 0) > $maxRoas)) return false;
            return true;
        })->values();
    }

    /**
     * Chi tiết ads (post) trong ad set/campaign/account theo khoảng ngày.
     * Trả về theo từng ad (post) với tổng hợp metric.
     */
    public function getAdDetails(array $filters): array
    {
        [$from, $to] = $this->resolveDateRange($filters);

        $query = FacebookInsight::query()->from('facebook_insights as fi')
            ->join('facebook_ads as ad', 'ad.id', '=', 'fi.ref_id')
            ->leftJoin('facebook_campaigns as c', 'c.id', '=', 'ad.campaign_id')
            ->leftJoin('facebook_ad_accounts as a', 'a.id', '=', 'ad.account_id')
            ->where('fi.level', '=', 'ad')
            ->whereBetween('fi.date', [$from->toDateString(), $to->toDateString()])
            ->selectRaw('ad.id as id, COALESCE(ad.name, ad.id) as name, ad.adset_id, ad.campaign_id, ad.account_id, c.name as campaign_name, a.name as account_name, a.account_id as account_code')
            ->selectRaw('COALESCE(SUM(fi.spend),0) as spend')
            ->selectRaw('COALESCE(SUM(fi.impressions),0) as impressions')
            ->selectRaw('COALESCE(SUM(fi.clicks),0) as clicks')
            ->groupBy('ad.id', 'ad.name', 'ad.adset_id', 'ad.campaign_id', 'ad.account_id', 'c.name', 'a.name', 'a.account_id')
            ->orderByDesc('spend');

        if (!empty($filters['adsetId'])) {
            $query->where('ad.adset_id', '=', $filters['adsetId']);
        }
        if (!empty($filters['campaignId'])) {
            $query->where('ad.campaign_id', '=', $filters['campaignId']);
        }
        if (!empty($filters['accountId'])) {
            $query->where('ad.account_id', '=', $filters['accountId']);
        }
        if (!empty($filters['businessId'])) {
            $query->where('a.business_id', '=', $filters['businessId']);
        }

        $rows = collect($query->get())->map(function ($r) {
            $spend = (float) $r->spend;
            $impressions = (int) $r->impressions;
            $clicks = (int) $r->clicks;
            return [
                'id' => (string) $r->id,
                'name' => (string) ($r->name ?? $r->id),
                'adsetId' => (string) ($r->adset_id ?? ''),
                'campaignId' => (string) ($r->campaign_id ?? ''),
                'campaignName' => (string) ($r->campaign_name ?? ''),
                'accountId' => (string) ($r->account_id ?? ''),
                'accountName' => (string) ($r->account_name ?? ''),
                'accountCode' => (string) ($r->account_code ?? ''),
                'spend' => $spend,
                'impressions' => $impressions,
                'clicks' => $clicks,
                'ctr' => $impressions > 0 ? ($clicks / $impressions) * 100.0 : 0.0,
                'cpc' => $clicks > 0 ? ($spend / $clicks) : 0.0,
                'cpm' => $impressions > 0 ? ($spend / $impressions) * 1000.0 : 0.0,
            ];
        })->values();

        return [
            'dateFrom' => $from->toDateString(),
            'dateTo' => $to->toDateString(),
            'rows' => $rows->all(),
        ];
    }
}


