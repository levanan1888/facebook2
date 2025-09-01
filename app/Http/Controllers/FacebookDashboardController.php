<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\FacebookAd;
use App\Models\FacebookAdAccount;
use App\Models\FacebookAdSet;
use App\Models\FacebookBusiness;
use App\Models\FacebookCampaign;
use App\Models\FacebookAdInsight;
use App\Models\FacebookPost;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class FacebookDashboardController extends Controller
{
    public function overview(Request $request): View
    {
        $data = $this->getOverviewData($request);
       
        // Debug nhanh: dd breakdowns trực tiếp nếu cần kiểm tra dữ liệu phân khúc
        if ($request->boolean('dd_breakdowns') || $request->get('dd') === 'breakdowns') {
            dd($data['breakdowns'] ?? []);
        }
        return view('facebook.dashboard.overview', compact('data'));
    }

    public function hierarchy(Request $request): View
    {
        $data = $this->getHierarchyData();
        return view('facebook.dashboard.hierarchy', compact('data'));
    }

    public function analytics(Request $request): View
    {
        $data = $this->getAnalyticsData();
        return view('facebook.dashboard.analytics', compact('data'));
    }

    public function dataRaw(Request $request): View
    {
        $data = $this->getRawData();
        return view('facebook.dashboard.data-raw', compact('data'));
    }

    private function getOverviewData(Request $request = null): array
    {
        $request = $request ?? request();
        $from = $request->get('from');
        $to = $request->get('to');
        if (!$from || !$to) {
            // Mặc định lấy 36 tháng gần nhất để tránh thiếu số liệu khi AI tổng hợp
            $to = now()->toDateString();
            $from = now()->subMonthsNoOverflow(36)->toDateString();
        }
        $selectedBusinessId = $request->get('business_id');
        $selectedAccountId = $request->get('account_id');
        $selectedCampaignId = $request->get('campaign_id');
        $selectedPageId = $request->get('page_id');

        // Tính totals theo filter đã chọn
        $totals = $this->calculateFilteredTotals($selectedBusinessId, $selectedAccountId, $selectedCampaignId, $selectedPageId, $from, $to);

        // Lấy dữ liệu từ facebook_ad_insights
        $insightsQuery = FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id');
        
        // Join với facebook_ad_accounts nếu cần filter theo business
        if ($selectedBusinessId) {
            $insightsQuery->join('facebook_ad_accounts', 'facebook_ads.account_id', '=', 'facebook_ad_accounts.id')
                          ->where('facebook_ad_accounts.business_id', $selectedBusinessId);
        }
        
        if ($selectedAccountId) {
            $insightsQuery->where('facebook_ads.account_id', $selectedAccountId);
        }
        if ($selectedCampaignId) {
            $insightsQuery->where('facebook_ads.campaign_id', $selectedCampaignId);
        }
        if ($selectedPageId) {
            $insightsQuery->where('facebook_ad_insights.page_id', $selectedPageId);
        }
        
        $insightsData = $insightsQuery->whereBetween('facebook_ad_insights.date', [$from, $to])->get();

        // Chuỗi hoạt động từ trước tới nay (theo dải from/to đã xác định ở trên)
        $activityAll = [];
        if ($insightsData->count() > 0) {
            $grouped = $insightsData->groupBy('date')->sortKeys();
            foreach ($grouped as $d => $dayData) {
                $activityAll[] = [
                    'date' => $d instanceof \Carbon\Carbon ? $d->toDateString() : (string) $d,
                    'ads' => $dayData->count(),
                    'posts' => $dayData->whereNotNull('post_id')->count(),
                    'campaigns' => $dayData->pluck('campaign_id')->unique()->count(),
                    'spend' => $dayData->sum('spend'),
                ];
            }
        }

        // Tính toán stats tổng hợp
        $stats = [
            'total_spend' => $insightsData->sum('spend'),
            'total_impressions' => $insightsData->sum('impressions'),
            'total_clicks' => $insightsData->sum('clicks'),
            'total_reach' => $insightsData->sum('reach'),
            'total_conversions' => $insightsData->sum('conversions'),
            'total_conversion_values' => $insightsData->sum('conversion_values'),
            'avg_ctr' => $insightsData->avg('ctr'),
            'avg_cpc' => $insightsData->avg('cpc'),
            'avg_cpm' => $insightsData->avg('cpm'),
        ];

        // Top performing ads - áp dụng filter
        $topAds = $this->getFilteredTopAds($selectedBusinessId, $selectedAccountId, $selectedCampaignId, $selectedPageId, $from, $to);

        // Top performing posts - áp dụng filter
        $facebookDataService = new \App\Services\FacebookDataService();
        $topPosts = $this->getFilteredTopPosts($selectedBusinessId, $selectedAccountId, $selectedCampaignId, $selectedPageId, $from, $to);
        
        // Lấy breakdown data cho overview - áp dụng filter
        $breakdowns = $this->getFilteredBreakdowns($selectedBusinessId, $selectedAccountId, $selectedCampaignId, $selectedPageId, $from, $to);
        
        // Lấy actions data cho overview - áp dụng filter  
        $actions = $this->getFilteredActions($selectedBusinessId, $selectedAccountId, $selectedCampaignId, $selectedPageId, $from, $to);

        // Lấy accounts và campaigns cho filter
        $accounts = FacebookAdAccount::select('id', 'name', 'account_id', 'business_id')->get();
        $campaigns = FacebookCampaign::select('id', 'name', 'ad_account_id')->get();
        
        // Lấy Business Managers cho filter
        $businesses = FacebookBusiness::select('id', 'name')->get();
        
        // Lấy Facebook Pages từ facebook_ad_insights thay vì bảng facebook_pages
        $pages = FacebookAdInsight::select('page_id as id', 'page_id')
            ->whereNotNull('page_id')
            ->distinct()
            ->get()
            ->map(function($item) {
                return (object) [
                    'id' => $item->page_id,
                    'name' => 'Page ' . $item->page_id
                ];
            });

        // Thống kê trạng thái cho biểu đồ donut - áp dụng filter
        $statusStats = $this->calculateFilteredStatusStats($selectedBusinessId, $selectedAccountId, $selectedCampaignId, $selectedPageId, $from, $to);

        // Hiệu suất tổng hợp để hiển thị phụ chú
        $performanceStats = [
            'totalSpend' => (float) $insightsData->sum('spend'),
            'totalImpressions' => (int) $insightsData->sum('impressions'),
            'totalClicks' => (int) $insightsData->sum('clicks'),
            'totalReach' => (int) $insightsData->sum('reach'),
            'totalConversions' => (int) $insightsData->sum('conversions'),
            'totalConversionValues' => (float) $insightsData->sum('conversion_values'),
            'avgCTR' => (float) $insightsData->avg('ctr'),
            'avgCPC' => (float) $insightsData->avg('cpc'),
            'avgCPM' => (float) $insightsData->avg('cpm'),
        ];

        return [
            'totals' => $totals,
            'stats' => $stats,
            'last7Days' => $activityAll, // dùng key cũ cho biểu đồ – nay là all-time
            'topAds' => $topAds,
            'topPosts' => $topPosts,
            'breakdowns' => $breakdowns,
            'actions' => $actions,
            'statusStats' => $statusStats,
            'performanceStats' => $performanceStats,
            'filters' => [
                'from' => $from,
                'to' => $to,
                'account_id' => $selectedAccountId,
                'campaign_id' => $selectedCampaignId,
                'business_id' => $request->get('business_id'),
                'page_id' => $selectedPageId,
                'accounts' => $accounts,
                'campaigns' => $campaigns,
                'businesses' => $businesses,
                'pages' => $pages,
            ]
        ];
    }

    /**
     * Tính toán totals theo filter đã chọn
     */
    private function calculateFilteredTotals($selectedBusinessId, $selectedAccountId, $selectedCampaignId, $selectedPageId, $from, $to): array
    {
        // Base query cho insights trong khoảng thời gian
        $insightsQuery = FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
            ->whereBetween('facebook_ad_insights.date', [$from, $to]);
        
        // Apply filters
        if ($selectedBusinessId) {
            $insightsQuery->join('facebook_ad_accounts', 'facebook_ads.account_id', '=', 'facebook_ad_accounts.id')
                          ->where('facebook_ad_accounts.business_id', $selectedBusinessId);
        }
        
        if ($selectedAccountId) {
            $insightsQuery->where('facebook_ads.account_id', $selectedAccountId);
        }
        
        if ($selectedCampaignId) {
            $insightsQuery->where('facebook_ads.campaign_id', $selectedCampaignId);
        }
        
        if ($selectedPageId) {
            $insightsQuery->where('facebook_ad_insights.page_id', $selectedPageId);
        }
        
        $insightsData = $insightsQuery->get();
        
        // Tính totals từ insights data đã filter
        $totals = [
            'businesses' => 0,
            'accounts' => 0,
            'campaigns' => 0,
            'adsets' => 0,
            'ads' => 0,
            'pages' => 0,
            'posts' => 0,
            'ad_insights' => $insightsData->count(),
        ];
        
        if ($insightsData->count() > 0) {
            // Lấy unique IDs từ insights data
            $uniqueAdIds = $insightsData->pluck('ad_id')->unique();
            $uniquePageIds = $insightsData->whereNotNull('page_id')->pluck('page_id')->unique();
            $uniquePostIds = $insightsData->whereNotNull('post_id')->pluck('post_id')->unique();
            
            // Lấy thông tin campaign và account từ ads
            $adsData = FacebookAd::whereIn('id', $uniqueAdIds)->get();
            $uniqueCampaignIds = $adsData->pluck('campaign_id')->unique();
            $uniqueAccountIds = $adsData->pluck('account_id')->unique();
            $uniqueAdsetIds = $adsData->pluck('adset_id')->unique();
            
            // Đếm từ các bảng chính
            $totals['ads'] = $uniqueAdIds->count();
            $totals['campaigns'] = $uniqueCampaignIds->count();
            $totals['accounts'] = $uniqueAccountIds->count();
            $totals['adsets'] = $uniqueAdsetIds->count();
            $totals['pages'] = $uniquePageIds->count();
            $totals['posts'] = $uniquePostIds->count();
            
            // Đếm businesses từ accounts
            if ($uniqueAccountIds->count() > 0) {
                $totals['businesses'] = FacebookAdAccount::whereIn('id', $uniqueAccountIds)
                    ->distinct('business_id')
                    ->count('business_id');
            }
        }
        
        return $totals;
    }

    /**
     * Tính toán status stats theo filter đã chọn
     */
    private function calculateFilteredStatusStats($selectedBusinessId, $selectedAccountId, $selectedCampaignId, $selectedPageId, $from, $to): array
    {
        // Base query cho insights trong khoảng thời gian
        $insightsQuery = FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
            ->whereBetween('facebook_ad_insights.date', [$from, $to]);
        
        // Apply filters
        if ($selectedBusinessId) {
            $insightsQuery->join('facebook_ad_accounts', 'facebook_ads.account_id', '=', 'facebook_ad_accounts.id')
                          ->where('facebook_ad_accounts.business_id', $selectedBusinessId);
        }
        
        if ($selectedAccountId) {
            $insightsQuery->where('facebook_ads.account_id', $selectedAccountId);
        }
        
        if ($selectedCampaignId) {
            $insightsQuery->where('facebook_ads.campaign_id', $selectedCampaignId);
        }
        
        if ($selectedPageId) {
            $insightsQuery->where('facebook_ad_insights.page_id', $selectedPageId);
        }
        
        $insightsData = $insightsQuery->get();
        
        $statusStats = [
            'campaigns' => [],
            'ads' => [],
        ];
        
        if ($insightsData->count() > 0) {
            // Lấy unique ad IDs và campaign IDs
            $uniqueAdIds = $insightsData->pluck('ad_id')->unique();
            $adsData = FacebookAd::whereIn('id', $uniqueAdIds)->get();
            $uniqueCampaignIds = $adsData->pluck('campaign_id')->unique();
            
            // Thống kê status của campaigns
            if ($uniqueCampaignIds->count() > 0) {
                $statusStats['campaigns'] = FacebookCampaign::whereIn('id', $uniqueCampaignIds)
                    ->select('status', DB::raw('count(*) as count'))
                    ->groupBy('status')
                    ->pluck('count', 'status')
                    ->toArray();
            }
            
            // Thống kê status của ads
            if ($uniqueAdIds->count() > 0) {
                $statusStats['ads'] = FacebookAd::whereIn('id', $uniqueAdIds)
                    ->select('status', DB::raw('count(*) as count'))
                    ->groupBy('status')
                    ->pluck('count', 'status')
                    ->toArray();
            }
        }
        
        return $statusStats;
    }

    /**
     * Lấy top ads theo filter đã chọn
     */
    private function getFilteredTopAds($selectedBusinessId, $selectedAccountId, $selectedCampaignId, $selectedPageId, $from, $to)
    {
        // Base query cho insights trong khoảng thời gian
        $insightsQuery = FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
            ->whereBetween('facebook_ad_insights.date', [$from, $to]);
        
        // Apply filters
        if ($selectedBusinessId) {
            $insightsQuery->join('facebook_ad_accounts', 'facebook_ads.account_id', '=', 'facebook_ad_accounts.id')
                          ->where('facebook_ad_accounts.business_id', $selectedBusinessId);
        }
        
        if ($selectedAccountId) {
            $insightsQuery->where('facebook_ads.account_id', $selectedAccountId);
        }
        
        if ($selectedCampaignId) {
            $insightsQuery->where('facebook_ads.campaign_id', $selectedCampaignId);
        }
        
        if ($selectedPageId) {
            $insightsQuery->where('facebook_ad_insights.page_id', $selectedPageId);
        }
        
        $insightsData = $insightsQuery->get();
        
        if ($insightsData->count() === 0) {
            return collect();
        }
        
        // Lấy unique ad IDs
        $uniqueAdIds = $insightsData->pluck('ad_id')->unique();
        
        // Lấy top ads với insights data
        return FacebookAd::with(['campaign', 'adSet'])
            ->whereIn('id', $uniqueAdIds)
            ->withSum(['insights as total_spend' => function ($query) use ($from, $to) {
                $query->whereBetween('date', [$from, $to]);
            }], 'spend')
            ->withSum(['insights as total_impressions' => function ($query) use ($from, $to) {
                $query->whereBetween('date', [$from, $to]);
            }], 'impressions')
            ->withSum(['insights as total_clicks' => function ($query) use ($from, $to) {
                $query->whereBetween('date', [$from, $to]);
            }], 'clicks')
            ->orderByDesc('total_spend')
            ->limit(5)
            ->get();
    }

    /**
     * Lấy top posts theo filter đã chọn
     */
    private function getFilteredTopPosts($selectedBusinessId, $selectedAccountId, $selectedCampaignId, $selectedPageId, $from, $to)
    {
        // Base query cho insights trong khoảng thời gian
        $insightsQuery = FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
            ->whereBetween('facebook_ad_insights.date', [$from, $to])
            ->whereNotNull('facebook_ad_insights.post_id');
        
        // Apply filters
        if ($selectedBusinessId) {
            $insightsQuery->join('facebook_ad_accounts', 'facebook_ads.account_id', '=', 'facebook_ad_accounts.id')
                          ->where('facebook_ad_accounts.business_id', $selectedBusinessId);
        }
        
        if ($selectedAccountId) {
            $insightsQuery->where('facebook_ads.account_id', $selectedAccountId);
        }
        
        if ($selectedCampaignId) {
            $insightsQuery->where('facebook_ads.campaign_id', $selectedCampaignId);
        }
        
        if ($selectedPageId) {
            $insightsQuery->where('facebook_ad_insights.page_id', $selectedPageId);
        }
        
        $insightsData = $insightsQuery->get();
        
        if ($insightsData->count() === 0) {
            return collect();
        }
        
        // Group by post_id và tính tổng metrics
        $postMetrics = $insightsData->groupBy('post_id')->map(function ($postInsights) {
            return [
                'post_id' => $postInsights->first()->post_id,
                'page_id' => $postInsights->first()->page_id,
                'total_spend' => $postInsights->sum('spend'),
                'total_impressions' => $postInsights->sum('impressions'),
                'total_clicks' => $postInsights->sum('clicks'),
                'total_reach' => $postInsights->sum('reach'),
                'total_conversions' => $postInsights->sum('conversions'),
                'total_conversion_values' => $postInsights->sum('conversion_values'),
                'total_video_views' => $postInsights->sum('video_views'),
                'avg_ctr' => $postInsights->avg('ctr'),
                'avg_cpc' => $postInsights->avg('cpc'),
                'avg_cpm' => $postInsights->avg('cpm'),
            ];
        });
        
        // Sort by total_spend và lấy top 5
        return $postMetrics->sortByDesc('total_spend')->take(5)->values();
    }

    /**
     * Lấy breakdowns theo filter đã chọn
     */
    private function getFilteredBreakdowns($selectedBusinessId, $selectedAccountId, $selectedCampaignId, $selectedPageId, $from, $to): array
    {
        try {
            // Base query cho insights trong khoảng thời gian
            $insightsQuery = FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
                ->whereBetween('facebook_ad_insights.date', [$from, $to]);
            
            // Apply filters
            if ($selectedBusinessId) {
                $insightsQuery->join('facebook_ad_accounts', 'facebook_ads.account_id', '=', 'facebook_ad_accounts.id')
                              ->where('facebook_ad_accounts.business_id', $selectedBusinessId);
            }
            
            if ($selectedAccountId) {
                $insightsQuery->where('facebook_ads.account_id', $selectedAccountId);
            }
            
            if ($selectedCampaignId) {
                $insightsQuery->where('facebook_ads.campaign_id', $selectedCampaignId);
            }
            
            if ($selectedPageId) {
                $insightsQuery->where('facebook_ad_insights.page_id', $selectedPageId);
            }
            
            // Lấy danh sách insight IDs
            $insightIds = $insightsQuery->pluck('facebook_ad_insights.id');
            
            if ($insightIds->isEmpty()) {
                return [];
            }
            
            // Lấy breakdowns từ facebook_breakdowns
            $breakdowns = \App\Models\FacebookBreakdown::whereIn('ad_insight_id', $insightIds->all())
                ->orderBy('breakdown_type')
                ->orderBy('breakdown_value')
                ->get()
                ->groupBy('breakdown_type');
            
            $result = [];
            foreach ($breakdowns as $breakdownType => $items) {
                $result[$breakdownType] = [];
                foreach ($items as $item) {
                    $breakdownValue = $item->breakdown_value;
                    $metrics = $item->metrics ?? [];
                    
                    if (!isset($result[$breakdownType][$breakdownValue])) {
                        $result[$breakdownType][$breakdownValue] = [
                            'spend' => 0,
                            'impressions' => 0,
                            'clicks' => 0,
                            'reach' => 0,
                            'conversions' => 0,
                            'conversion_values' => 0,
                            'video_views' => 0,
                        ];
                    }
                    
                    $result[$breakdownType][$breakdownValue]['spend'] += $metrics['spend'] ?? 0;
                    $result[$breakdownType][$breakdownValue]['impressions'] += $metrics['impressions'] ?? 0;
                    $result[$breakdownType][$breakdownValue]['clicks'] += $metrics['clicks'] ?? 0;
                    $result[$breakdownType][$breakdownValue]['reach'] += $metrics['reach'] ?? 0;
                    $result[$breakdownType][$breakdownValue]['conversions'] += $metrics['conversions'] ?? 0;
                    $result[$breakdownType][$breakdownValue]['conversion_values'] += $metrics['conversion_values'] ?? 0;
                    $result[$breakdownType][$breakdownValue]['video_views'] += $metrics['video_views'] ?? 0;
                }
            }
            
            return $result;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in getFilteredBreakdowns', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }

    /**
     * Lấy actions theo filter đã chọn
     */
    private function getFilteredActions($selectedBusinessId, $selectedAccountId, $selectedCampaignId, $selectedPageId, $from, $to): array
    {
        try {
            // Base query cho insights trong khoảng thời gian
            $insightsQuery = FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
                ->whereBetween('facebook_ad_insights.date', [$from, $to]);
            
            // Apply filters
            if ($selectedBusinessId) {
                $insightsQuery->join('facebook_ad_accounts', 'facebook_ads.account_id', '=', 'facebook_ad_accounts.id')
                              ->where('facebook_ad_accounts.business_id', $selectedBusinessId);
            }
            
            if ($selectedAccountId) {
                $insightsQuery->where('facebook_ads.account_id', $selectedAccountId);
            }
            
            if ($selectedCampaignId) {
                $insightsQuery->where('facebook_ads.campaign_id', $selectedCampaignId);
            }
            
            if ($selectedPageId) {
                $insightsQuery->where('facebook_ad_insights.page_id', $selectedPageId);
            }
            
            $insightsData = $insightsQuery->get();
            
            $result = [
                'daily_actions' => [],
                'summary' => []
            ];
            
            foreach ($insightsData as $insight) {
                $date = $insight->date;
                $dateString = $date instanceof \Carbon\Carbon ? $date->toDateString() : (string) $date;
                $actions = $insight->actions ?? [];
                
                if (!isset($result['daily_actions'][$dateString])) {
                    $result['daily_actions'][$dateString] = [];
                }
                
                foreach ($actions as $action) {
                    if (!is_array($action)) {
                        continue;
                    }
                    
                    $actionType = $action['action_type'] ?? 'unknown';
                    $value = $action['value'] ?? 0;
                    
                    // Thêm vào daily actions
                    if (!isset($result['daily_actions'][$dateString][$actionType])) {
                        $result['daily_actions'][$dateString][$actionType] = 0;
                    }
                    $result['daily_actions'][$dateString][$actionType] += $value;
                    
                    // Thêm vào summary
                    if (!isset($result['summary'][$actionType])) {
                        $result['summary'][$actionType] = 0;
                    }
                    $result['summary'][$actionType] += $value;
                }
            }
            
            return $result;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in getFilteredActions', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [
                'daily_actions' => [],
                'summary' => []
            ];
        }
    }

    /**
     * Gọi AI tóm tắt tổng quan dashboard
     */
    public function overviewAiSummary(Request $request)
    {
        $data = $this->getOverviewData($request);
       
        // Nhận data breakdowns từ frontend nếu có
        $frontendBreakdowns = $request->input('breakdowns_data', []);
        
        // Ưu tiên khoảng thời gian người dùng chọn; nếu thiếu, mặc định 36 tháng
        $since = $request->get('from') ?: ($data['filters']['from'] ?? now()->subMonthsNoOverflow(36)->toDateString());
        $until = $request->get('to') ?: ($data['filters']['to'] ?? now()->toDateString());

        // Tổng hợp dữ liệu chi tiết từ database theo khoảng ngày
        $agg = FacebookAdInsight::query()
            ->whereBetween('date', [$since, $until])
            ->selectRaw('COALESCE(SUM(spend),0) as spend')
            ->selectRaw('COALESCE(SUM(impressions),0) as impressions')
            ->selectRaw('COALESCE(SUM(clicks),0) as clicks')
            ->selectRaw('COALESCE(SUM(reach),0) as reach')
            ->selectRaw('COALESCE(SUM(conversions),0) as conversions')
            ->selectRaw('COALESCE(SUM(conversion_values),0) as conversion_values')
            ->selectRaw('COALESCE(AVG(ctr),0) as avg_ctr')
            ->selectRaw('COALESCE(AVG(cpc),0) as avg_cpc')
            ->selectRaw('COALESCE(AVG(cpm),0) as avg_cpm')
            ->selectRaw('COALESCE(SUM(video_views),0) as video_views')
            ->selectRaw('COALESCE(SUM(video_view_time),0) as video_view_time')
            ->selectRaw('COALESCE(AVG(video_avg_time_watched),0) as video_avg_time_watched')
            ->selectRaw('COALESCE(SUM(video_plays),0) as video_plays')
            ->selectRaw('COALESCE(SUM(thruplays),0) as thruplays')
            ->selectRaw('COALESCE(SUM(video_30_sec_watched),0) as video_30s')
            ->selectRaw('COALESCE(SUM(video_p25_watched_actions),0) as v_p25')
            ->selectRaw('COALESCE(SUM(video_p50_watched_actions),0) as v_p50')
            ->selectRaw('COALESCE(SUM(video_p75_watched_actions),0) as v_p75')
            ->selectRaw('COALESCE(SUM(video_p95_watched_actions),0) as v_p95')
            ->selectRaw('COALESCE(SUM(video_p100_watched_actions),0) as v_p100')
            ->first();

        $spend = (float) ($agg->spend ?? 0);
        $impr = (int) ($agg->impressions ?? 0);
        $clicks = (int) ($agg->clicks ?? 0);
        $conversions = (int) ($agg->conversions ?? 0);
        $convValues = (float) ($agg->conversion_values ?? 0);
        $roas = $spend > 0 ? ($convValues / $spend) : 0.0;

        // Breakdown theo thiết bị/khu vực/giới tính/độ tuổi/vị trí/nền tảng – tái sử dụng service đang dùng ở Post Detail/Overview
        $breakdownsService = new \App\Services\FacebookDataService();
        $breakdownsAgg = $breakdownsService->getOverviewBreakdowns(null, $since, $until);
        // Debug optional: kiểm tra trực tiếp bảng facebook_breakdowns nếu số liệu bất thường
        if ($request->boolean('debug_breakdowns')) {
            $insightIds = FacebookAdInsight::query()
                ->whereBetween('date', [$since, $until])
                ->pluck('id');
            $raw = \App\Models\FacebookBreakdown::whereIn('ad_insight_id', $insightIds->all())
                ->limit(50)
                ->get(['ad_insight_id','breakdown_type','breakdown_value','metrics']);
            return response()->json(['raw_breakdowns_samples' => $raw, 'ids_count' => $insightIds->count(), 'since' => $since, 'until' => $until]);
        }
      
        // Fallback: nếu service không trả về, dùng breakdowns từ frontend gửi lên (giống view)
        if (empty($breakdownsAgg) && !empty($frontendBreakdowns['breakdowns'])) {
            $breakdownsAgg = $frontendBreakdowns['breakdowns'];
        }

        // Chuẩn hoá breakdowns cho AI: gom thành các nhóm dễ hiểu
        $normalizeNumber = function ($v) { return is_numeric($v) ? $v + 0 : 0; };
        $sumInto = function (&$bucket, $key, array $metrics) use ($normalizeNumber) {
            if (!isset($bucket[$key])) {
                $bucket[$key] = [
                    'spend' => 0.0,
                    'impressions' => 0,
                    'reach' => 0,
                    'clicks' => 0,
                    'conversions' => 0,
                    'conversion_values' => 0.0,
                    'video_views' => 0
                ];
            }
            $bucket[$key]['spend'] += (float) $normalizeNumber($metrics['spend'] ?? 0);
            $bucket[$key]['impressions'] += (int) $normalizeNumber($metrics['impressions'] ?? 0);
            $bucket[$key]['reach'] += (int) $normalizeNumber($metrics['reach'] ?? 0);
            $bucket[$key]['clicks'] += (int) $normalizeNumber($metrics['clicks'] ?? 0);
            $bucket[$key]['conversions'] += (int) $normalizeNumber($metrics['conversions'] ?? 0);
            $bucket[$key]['conversion_values'] += (float) $normalizeNumber($metrics['conversion_values'] ?? 0);
            $bucket[$key]['video_views'] += (int) $normalizeNumber($metrics['video_views'] ?? 0);
        };

        $devices = [];
        foreach (['action_device','device_platform','impression_device'] as $k) {
            if (!empty($breakdownsAgg[$k]) && is_array($breakdownsAgg[$k])) {
                foreach ($breakdownsAgg[$k] as $value => $metrics) {
                    $label = (string) ($value ?: 'unknown');
                    $sumInto($devices, $label, is_array($metrics) ? $metrics : []);
                }
            }
        }
      
        $regions = [];
        if (!empty($breakdownsAgg['region'])) {
            foreach ($breakdownsAgg['region'] as $value => $metrics) {
                $sumInto($regions, (string) ($value ?: 'unknown'), is_array($metrics) ? $metrics : []);
            }
        }
        $countries = [];
        if (!empty($breakdownsAgg['country'])) {
            foreach ($breakdownsAgg['country'] as $value => $metrics) {
                $sumInto($countries, (string) ($value ?: 'unknown'), is_array($metrics) ? $metrics : []);
            }
        }

        $ages = [];
        if (!empty($breakdownsAgg['age'])) {
            foreach ($breakdownsAgg['age'] as $value => $metrics) {
                $sumInto($ages, (string) ($value ?: 'unknown'), is_array($metrics) ? $metrics : []);
            }
        }
        $genders = [];
        if (!empty($breakdownsAgg['gender'])) {
            foreach ($breakdownsAgg['gender'] as $value => $metrics) {
                $sumInto($genders, (string) ($value ?: 'unknown'), is_array($metrics) ? $metrics : []);
            }
        }

        $placements = [
            'publisher_platform' => [],
            'platform_position' => [],
            'impression_device' => [],
        ];
        foreach (['publisher_platform','platform_position','impression_device'] as $k) {
            if (!empty($breakdownsAgg[$k]) && is_array($breakdownsAgg[$k])) {
                foreach ($breakdownsAgg[$k] as $value => $metrics) {
                    $sumInto($placements[$k], (string) ($value ?: 'unknown'), is_array($metrics) ? $metrics : []);
                }
            }
        }

        // Tính toán top/worst cho từng nhóm để AI có thể suy luận trực tiếp
        $computeTopWorst = function(array $bucket, string $by = 'spend', int $limit = 5) {
            // Chuẩn hoá thành mảng [name => metrics]
            $list = [];
            foreach ($bucket as $name => $m) {
                if (!is_array($m)) continue;
                $list[$name] = [
                    'spend' => (float) ($m['spend'] ?? 0),
                    'impressions' => (int) ($m['impressions'] ?? 0),
                    'reach' => (int) ($m['reach'] ?? 0),
                    'clicks' => (int) ($m['clicks'] ?? 0),
                    'conversions' => (int) ($m['conversions'] ?? 0),
                    'conversion_values' => (float) ($m['conversion_values'] ?? 0),
                    'video_views' => (int) ($m['video_views'] ?? 0),
                ];
            }
            uasort($list, function($a,$b) use ($by){ return ($b[$by] ?? 0) <=> ($a[$by] ?? 0); });
            $top = array_slice($list, 0, $limit, true);
            $worst = array_slice(array_reverse($list, true), 0, $limit, true);
            return ['top' => $top, 'worst' => $worst];
        };

        $metrics = [
            'summary' => [
                'total_spend' => $spend,
                'total_impressions' => $impr,
                'total_clicks' => $clicks,
                'total_reach' => (int) ($agg->reach ?? 0),
                'avg_ctr' => (float) ($agg->avg_ctr ?? 0),
                'avg_cpc' => (float) ($agg->avg_cpc ?? 0),
                'avg_cpm' => (float) ($agg->avg_cpm ?? 0),
                'total_conversions' => $conversions,
                'conversion_values' => $convValues,
                'roas' => $roas,
            ],
            'video' => [
                'views' => (int) ($agg->video_views ?? 0),
                'view_time' => (int) ($agg->video_view_time ?? 0),
                'avg_time' => (float) ($agg->video_avg_time_watched ?? 0),
                'plays' => (int) ($agg->video_plays ?? 0),
                'p25' => (int) ($agg->v_p25 ?? 0),
                'p50' => (int) ($agg->v_p50 ?? 0),
                'p75' => (int) ($agg->v_p75 ?? 0),
                'p95' => (int) ($agg->v_p95 ?? 0),
                'p100' => (int) ($agg->v_p100 ?? 0),
                'thruplays' => (int) ($agg->thruplays ?? 0),
                'video_30s' => (int) ($agg->video_30s ?? 0),
            ],
            'last7Days' => $data['last7Days'] ?? [],
            'status' => $data['statusStats'] ?? [],
            // Dữ liệu phân khúc đã chuẩn hoá cho AI
            'breakdowns' => [
                'devices' => $devices,
                'regions' => $regions,
                'countries' => $countries,
                'ages' => $ages,
                'genders' => $genders,
                'placements' => $placements,
                'highlights' => [
                    'devices' => $computeTopWorst($devices, 'spend'),
                    'regions' => $computeTopWorst($regions, 'spend'),
                    'countries' => $computeTopWorst($countries, 'spend'),
                    'ages' => $computeTopWorst($ages, 'spend'),
                    'genders' => $computeTopWorst($genders, 'spend'),
                    'publisher_platform' => $computeTopWorst($placements['publisher_platform'] ?? [], 'spend'),
                    'platform_position' => $computeTopWorst($placements['platform_position'] ?? [], 'spend'),
                    'impression_device' => $computeTopWorst($placements['impression_device'] ?? [], 'spend'),
                ],
            ],
            // Dữ liệu breakdowns từ frontend (nếu có)
            'frontend_breakdowns' => $frontendBreakdowns,
            // Raw để debug khi cần
            'breakdowns_raw' => $breakdownsAgg,
        ];

        $gemini = new \App\Services\GeminiService();
        // Debug optional: nếu ?debug=1 sẽ trả ra dữ liệu tổng hợp thay vì gọi AI
        if ($request->boolean('debug')) {
            return response()->json([
                'ok' => true,
                'debug' => true,
                'metrics' => $metrics,
            ]);
        }

        $summary = $gemini->generateMarketingSummary('facebook-dashboard', $since, $until, $metrics);
        return response()->json([
            'ok' => true, 
            'summary' => $summary, 
            'hasBreakdowns' => !empty($metrics['breakdowns']),
            'hasFrontendBreakdowns' => !empty($frontendBreakdowns),
            'breakdownsCount' => count($frontendBreakdowns)
        ]);
    }

    private function getHierarchyData(): array
    {
        // Sử dụng cấu trúc mới với relationships
        $businesses = FacebookBusiness::withCount(['adAccounts'])
            ->with(['adAccounts' => function ($query) {
                $query->withCount(['campaigns']);
            }])
            ->get();

        $campaigns = FacebookCampaign::withCount(['adSets', 'ads'])
            ->with(['adSets' => function ($query) {
                $query->withCount('ads');
            }])
            ->get();

        $adSets = FacebookAdSet::withCount('ads')
            ->with(['ads' => function ($query) {
                $query->with('insights');
            }])
            ->get();

        return [
            'businesses' => $businesses,
            'campaigns' => $campaigns,
            'adsets' => $adSets,
        ];
    }

    private function getAnalyticsData(): array
    {
        // Sử dụng facebook_ad_insights cho analytics
        $insightsData = FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
            ->whereBetween('facebook_ad_insights.date', [
                now()->subDays(30)->toDateString(),
                now()->toDateString()
            ])
            ->get();

        $dailyStats = $insightsData->groupBy('date')->map(function ($dayData) {
            $firstInsight = $dayData->first();
            $date = $firstInsight->date;
            return [
                'date' => $date instanceof \Carbon\Carbon ? $date->toDateString() : (string) $date,
                'total_spend' => $dayData->sum('spend'),
                'total_impressions' => $dayData->sum('impressions'),
                'total_clicks' => $dayData->sum('clicks'),
                'total_reach' => $dayData->sum('reach'),
                'avg_ctr' => $dayData->avg('ctr'),
                'avg_cpc' => $dayData->avg('cpc'),
                'avg_cpm' => $dayData->avg('cpm'),
            ];
        })->values();

        // Performance by campaign
        $campaignPerformance = FacebookCampaign::with(['ads.insights'])
            ->withSum(['ads.insights as total_spend' => function ($query) {
                $query->whereBetween('date', [now()->subDays(30)->toDateString(), now()->toDateString()]);
            }], 'spend')
            ->withSum(['ads.insights as total_impressions' => function ($query) {
                $query->whereBetween('date', [now()->subDays(30)->toDateString(), now()->toDateString()]);
            }], 'impressions')
            ->withSum(['ads.insights as total_clicks' => function ($query) {
                $query->whereBetween('date', [now()->subDays(30)->toDateString(), now()->toDateString()]);
            }], 'clicks')
            ->orderByDesc('total_spend')
            ->limit(20)
            ->get();

        // Performance by post type - sử dụng dữ liệu từ facebook_ads
        $postTypePerformance = FacebookAd::whereNotNull('post_id')
            ->whereHas('insights', function ($query) {
                $query->whereBetween('date', [now()->subDays(30)->toDateString(), now()->toDateString()]);
            })
            ->withSum(['insights as total_spend' => function ($query) {
                $query->whereBetween('date', [now()->subDays(30)->toDateString(), now()->toDateString()]);
            }], 'spend')
            ->groupBy('post_id')
            ->select('post_id', DB::raw('count(*) as ad_count'), DB::raw('sum(total_spend) as total_spend'))
            ->get();

        return [
            'dailyStats' => $dailyStats,
            'campaignPerformance' => $campaignPerformance,
            'postTypePerformance' => $postTypePerformance,
        ];
    }

    private function getRawData(): array
    {
        // Sử dụng cấu trúc mới với pagination
        $ads = FacebookAd::with(['campaign', 'adSet', 'insights'])
            ->orderBy('created_time', 'desc')
            ->paginate(50);

        // Lấy posts từ facebook_ad_insights thay vì facebook_posts
        $posts = FacebookAdInsight::whereNotNull('post_id')
            ->select('post_id', 'page_id', DB::raw('MAX(date) as last_date'))
            ->groupBy('post_id', 'page_id')
            ->orderBy('last_date', 'desc')
            ->paginate(50);

        $insights = FacebookAdInsight::with(['ad'])
            ->orderBy('date', 'desc')
            ->paginate(50);

        return [
            'ads' => $ads,
            'posts' => $posts,
            'insights' => $insights,
        ];
    }
}