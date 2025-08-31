<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\FacebookAdInsight;
use App\Models\FacebookAd;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class FacebookDataService
{
    /**
     * Lấy dữ liệu Facebook theo filter
     */
    public function getFacebookData(array $filters): array
    {
        $pages = $this->getAvailablePages();
        $selectedPageId = $filters['page_id'] ?? null;
        
        $data = [
            'pages' => $pages,
            'selected_page' => null,
            'posts' => collect(),
            'spending_stats' => [],
        ];
        
        if ($selectedPageId) {
            $data['selected_page'] = $pages->firstWhere('id', $selectedPageId);
            $data['posts'] = $this->getPostsByPage($selectedPageId, $filters);
            $data['spending_stats'] = $this->getPostSpendingStats(
                $selectedPageId, 
                $filters['date_from'] ?? null, 
                $filters['date_to'] ?? null
            );
        }
        
        return $data;
    }

    /**
     * Lấy danh sách các Page có sẵn từ facebook_ad_insights
     */
    public function getAvailablePages(): Collection
    {
        $pages = FacebookAdInsight::select('page_id as id')
            ->whereNotNull('page_id')
            ->groupBy('page_id')
            ->orderBy('page_id')
            ->get();
            
        // Tạo Eloquent Collection thay vì Support Collection
        return new \Illuminate\Database\Eloquent\Collection(
            $pages->map(function ($insight) {
                // Đếm số insights cho page này
                $insightsCount = FacebookAdInsight::where('page_id', $insight->id)->count();
                
                // Lấy thông tin page từ Facebook API nếu có thể
                $pageName = $this->getPageNameFromApi((string) $insight->id);
                
                return (object) [
                    'id' => $insight->id,
                    'name' => $pageName ?: 'Page ' . $insight->id,
                    'category' => 'Unknown',
                    'fan_count' => 0,
                    'ads_count' => $insightsCount
                ];
            })
        );
    }

    /**
     * Lấy tên page từ Facebook API
     */
    private function getPageNameFromApi(string $pageId): ?string
    {
        try {
            // Thử lấy thông tin page từ Facebook API
            $apiService = new \App\Services\FacebookAdsService();
            $pageInfo = $apiService->getPageInfo($pageId);
            
            if ($pageInfo && !isset($pageInfo['error']) && isset($pageInfo['name'])) {
                return $pageInfo['name'];
            }
            
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Helper method để validate và convert array key
     */
    private function validateArrayKey($key, string $default = 'unknown'): string
    {
        if (empty($key) || (!is_string($key) && !is_numeric($key))) {
            return $default;
        }
        return (string) $key;
    }

    /**
     * Lấy danh sách bài viết theo Page từ facebook_ad_insights
     * Tổng hợp các bản ghi có cùng page_id và post_id thành một bài viết duy nhất
     */
    public function getPostsByPage(string $pageId, array $filters = []): Collection
    {
        $query = FacebookAdInsight::where('page_id', $pageId)
            ->whereNotNull('post_id')
            ->select([
                'post_id',
                'page_id',
                DB::raw('COUNT(DISTINCT ad_id) as ad_count'),
                DB::raw('SUM(spend) as total_spend'),
                DB::raw('SUM(impressions) as total_impressions'),
                DB::raw('SUM(reach) as total_reach'),
                DB::raw('SUM(clicks) as total_clicks'),
                DB::raw('SUM(unique_clicks) as total_unique_clicks'),
                DB::raw('AVG(ctr) as avg_ctr'),
                DB::raw('AVG(cpc) as avg_cpc'),
                DB::raw('AVG(cpm) as avg_cpm'),
                DB::raw('SUM(conversions) as total_conversions'),
                DB::raw('SUM(conversion_values) as total_conversion_values'),
                DB::raw('SUM(cost_per_conversion) as total_cost_per_conversion'),
                DB::raw('SUM(purchase_roas) as total_purchase_roas'),
                DB::raw('SUM(outbound_clicks) as total_outbound_clicks'),
                DB::raw('SUM(unique_outbound_clicks) as total_unique_outbound_clicks'),
                DB::raw('SUM(inline_link_clicks) as total_inline_link_clicks'),
                DB::raw('SUM(unique_inline_link_clicks) as total_unique_inline_link_clicks'),
                DB::raw('SUM(website_clicks) as total_website_clicks'),
                // Video metrics
                DB::raw('SUM(video_views) as total_video_views'),
                DB::raw('SUM(video_plays) as total_video_plays'),
                DB::raw('SUM(video_p25_watched_actions) as total_video_p25_watched_actions'),
                DB::raw('SUM(video_p50_watched_actions) as total_video_p50_watched_actions'),
                DB::raw('SUM(video_p75_watched_actions) as total_video_p75_watched_actions'),
                DB::raw('SUM(video_p95_watched_actions) as total_video_p95_watched_actions'),
                DB::raw('SUM(video_p100_watched_actions) as total_video_p100_watched_actions'),
                DB::raw('SUM(thruplays) as total_thruplays'),
                DB::raw('SUM(video_30_sec_watched) as total_video_30_sec_watched'),
                DB::raw('AVG(video_avg_time_watched) as avg_video_avg_time_watched'),
                DB::raw('SUM(video_view_time) as total_video_view_time'),
                // Actions từ JSON fields
                DB::raw('MIN(date) as first_date'),
                DB::raw('MAX(date) as last_date'),
            ])
            ->groupBy('post_id', 'page_id');

        // Lọc theo ngày
        if (!empty($filters['date_from'])) {
            $query->where('date', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->where('date', '<=', $filters['date_to']);
        }

        // Tìm kiếm
        if (!empty($filters['search'])) {
            $query->whereHas('ad', function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%');
            });
        }

        $posts = $query->orderBy('last_date', 'desc')->get();

        // Chuyển đổi thành Eloquent Collection với thông tin bổ sung
        return new \Illuminate\Database\Eloquent\Collection(
            $posts->map(function ($post) {
                // Lấy thông tin từ ad để có message và các thông tin khác
                $adInfo = FacebookAd::where('post_id', $post->post_id)
                    ->where('page_id', $post->page_id)
                    ->first();

                return (object) [
                    'id' => $post->post_id,
                    'page_id' => $post->page_id,
                    'type' => $adInfo->type ?? 'post',
                    'message' => $adInfo->name ?? 'Không có nội dung',
                    'created_time' => $adInfo->created_time ?? $post->first_date,
                    'permalink_url' => $adInfo->permalink_url ?? null,
                    'status' => $adInfo->status ?? null,
                    // Engagement stats (từ post insights nếu có)
                    'likes_count' => 0,
                    'shares_count' => 0,
                    'comments_count' => 0,
                    'reactions_count' => 0,
                    // Ad performance stats
                    'ad_count' => $post->ad_count,
                    'total_spend' => $post->total_spend,
                    'total_impressions' => $post->total_impressions,
                    'total_reach' => $post->total_reach,
                    'total_clicks' => $post->total_clicks,
                    'total_unique_clicks' => $post->total_unique_clicks,
                    'avg_ctr' => $post->avg_ctr,
                    'avg_cpc' => $post->avg_cpc,
                    'avg_cpm' => $post->avg_cpm,
                    'total_conversions' => $post->total_conversions,
                    'total_conversion_values' => $post->total_conversion_values,
                    'total_cost_per_conversion' => $post->total_cost_per_conversion,
                    'total_purchase_roas' => $post->total_purchase_roas,
                    'total_outbound_clicks' => $post->total_outbound_clicks,
                    'total_unique_outbound_clicks' => $post->total_unique_outbound_clicks,
                    'total_inline_link_clicks' => $post->total_inline_link_clicks,
                    'total_unique_inline_link_clicks' => $post->total_unique_inline_link_clicks,
                    'total_website_clicks' => $post->total_website_clicks,
                    // Video metrics
                    'total_video_views' => $post->total_video_views,
                    'total_video_plays' => $post->total_video_plays,
                    'total_video_p25_watched_actions' => $post->total_video_p25_watched_actions,
                    'total_video_p50_watched_actions' => $post->total_video_p50_watched_actions,
                    'total_video_p75_watched_actions' => $post->total_video_p75_watched_actions,
                    'total_video_p95_watched_actions' => $post->total_video_p95_watched_actions,
                    'total_video_p100_watched_actions' => $post->total_video_p100_watched_actions,
                    'total_thruplays' => $post->total_thruplays,
                    'total_video_30_sec_watched' => $post->total_video_30_sec_watched,
                    'avg_video_avg_time_watched' => $post->avg_video_avg_time_watched,
                    'total_video_view_time' => $post->total_video_view_time,
                    // Date range
                    'first_date' => $post->first_date,
                    'last_date' => $post->last_date,
                ];
            })->toArray()
        );
    }

    /**
     * Lấy thống kê chi phí theo bài viết từ facebook_ad_insights
     */
    public function getPostSpendingStats(string $pageId, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $query = FacebookAdInsight::where('page_id', $pageId)
            ->whereNotNull('post_id');

        if ($dateFrom) {
            $query->where('date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->where('date', '<=', $dateTo);
        }

        $stats = $query->select(
                'post_id',
                DB::raw('COUNT(DISTINCT ad_id) as ad_count'),
                DB::raw('SUM(spend) as total_spend'),
                DB::raw('SUM(impressions) as total_impressions'),
                DB::raw('SUM(reach) as total_reach'),
                DB::raw('SUM(clicks) as total_clicks'),
                DB::raw('SUM(unique_clicks) as total_unique_clicks'),
                DB::raw('AVG(ctr) as avg_ctr'),
                DB::raw('AVG(cpc) as avg_cpc'),
                DB::raw('AVG(cpm) as avg_cpm'),
                DB::raw('SUM(conversions) as total_conversions'),
                DB::raw('SUM(conversion_values) as total_conversion_values'),
                DB::raw('SUM(cost_per_conversion) as total_cost_per_conversion'),
                DB::raw('SUM(purchase_roas) as total_purchase_roas'),
                // Video metrics
                DB::raw('SUM(video_views) as total_video_views'),
                DB::raw('SUM(video_plays) as total_video_plays'),
                DB::raw('SUM(video_p25_watched_actions) as total_video_p25_watched_actions'),
                DB::raw('SUM(video_p50_watched_actions) as total_video_p50_watched_actions'),
                DB::raw('SUM(video_p75_watched_actions) as total_video_p75_watched_actions'),
                DB::raw('SUM(video_p95_watched_actions) as total_video_p95_watched_actions'),
                DB::raw('SUM(video_p100_watched_actions) as total_video_p100_watched_actions'),
                DB::raw('SUM(thruplays) as total_thruplays'),
                DB::raw('SUM(video_30_sec_watched) as total_video_30_sec_watched'),
                DB::raw('AVG(video_avg_time_watched) as avg_video_avg_time_watched'),
                DB::raw('SUM(video_view_time) as total_video_view_time'),
            )
            ->groupBy('post_id')
            ->orderBy('total_spend', 'desc')
            ->get();

        // Lấy thông tin message từ ads
        $stats = $stats->map(function ($stat) use ($pageId) {
            $adInfo = FacebookAd::where('post_id', $stat->post_id)
                ->where('page_id', $pageId)
                ->first();
            
            $stat->message = $adInfo->name ?? 'Không có nội dung';
            $stat->created_time = $adInfo->created_time ?? now();
            $stat->permalink_url = $adInfo->permalink_url ?? null;
            
            return $stat;
        });

        return [
            'posts' => $stats,
            'summary' => [
                'total_spend' => $stats->sum('total_spend'),
                'total_impressions' => $stats->sum('total_impressions'),
                'total_reach' => $stats->sum('total_reach'),
                'total_clicks' => $stats->sum('total_clicks'),
                'total_unique_clicks' => $stats->sum('total_unique_clicks'),
                'total_conversions' => $stats->sum('total_conversions'),
                'total_conversion_values' => $stats->sum('total_conversion_values'),
                'total_cost_per_conversion' => $stats->sum('total_cost_per_conversion'),
                'total_purchase_roas' => $stats->sum('total_purchase_roas'),
                'avg_cpc' => $stats->avg('avg_cpc'),
                'avg_cpm' => $stats->avg('avg_cpm'),
                'avg_ctr' => $stats->avg('avg_ctr'),
                // Video metrics summary
                'total_video_views' => $stats->sum('total_video_views'),
                'total_video_plays' => $stats->sum('total_video_plays'),
                'total_video_p25_watched_actions' => $stats->sum('total_video_p25_watched_actions'),
                'total_video_p50_watched_actions' => $stats->sum('total_video_p50_watched_actions'),
                'total_video_p75_watched_actions' => $stats->sum('total_video_p75_watched_actions'),
                'total_video_p95_watched_actions' => $stats->sum('total_video_p95_watched_actions'),
                'total_video_p100_watched_actions' => $stats->sum('total_video_p100_watched_actions'),
                'total_thruplays' => $stats->sum('total_thruplays'),
                'total_video_30_sec_watched' => $stats->sum('total_video_30_sec_watched'),
                'avg_video_avg_time_watched' => $stats->avg('avg_video_avg_time_watched'),
                'total_video_view_time' => $stats->sum('total_video_view_time'),
            ]
        ];
    }

    /**
     * Lấy thống kê tổng quan theo Page
     */
    public function getPageOverview(string $pageId): array
    {
        $insights = FacebookAdInsight::where('page_id', $pageId)
            ->select(
                DB::raw('SUM(spend) as total_spend'),
                DB::raw('SUM(impressions) as total_impressions'),
                DB::raw('SUM(reach) as total_reach'),
                DB::raw('SUM(clicks) as total_clicks'),
                DB::raw('SUM(unique_clicks) as total_unique_clicks'),
                DB::raw('SUM(conversions) as total_conversions'),
                DB::raw('SUM(conversion_values) as total_conversion_values'),
                DB::raw('SUM(cost_per_conversion) as total_cost_per_conversion'),
                DB::raw('SUM(purchase_roas) as total_purchase_roas'),
                DB::raw('AVG(cpc) as avg_cpc'),
                DB::raw('AVG(cpm) as avg_cpm'),
                DB::raw('AVG(ctr) as avg_ctr'),
                // Video metrics
                DB::raw('SUM(video_views) as total_video_views'),
                DB::raw('SUM(video_plays) as total_video_plays'),
                DB::raw('SUM(video_p25_watched_actions) as total_video_p25_watched_actions'),
                DB::raw('SUM(video_p50_watched_actions) as total_video_p50_watched_actions'),
                DB::raw('SUM(video_p75_watched_actions) as total_video_p75_watched_actions'),
                DB::raw('SUM(video_p95_watched_actions) as total_video_p95_watched_actions'),
                DB::raw('SUM(video_p100_watched_actions) as total_video_p100_watched_actions'),
                DB::raw('SUM(thruplays) as total_thruplays'),
                DB::raw('SUM(video_30_sec_watched) as total_video_30_sec_watched'),
                DB::raw('AVG(video_avg_time_watched) as avg_video_avg_time_watched'),
                DB::raw('SUM(video_view_time) as total_video_view_time'),
            )
            ->first();

        return [
            'total_spend' => $insights->total_spend ?? 0,
            'total_impressions' => $insights->total_impressions ?? 0,
            'total_reach' => $insights->total_reach ?? 0,
            'total_clicks' => $insights->total_clicks ?? 0,
            'total_unique_clicks' => $insights->total_unique_clicks ?? 0,
            'total_conversions' => $insights->total_conversions ?? 0,
            'total_conversion_values' => $insights->total_conversion_values ?? 0,
            'total_cost_per_conversion' => $insights->total_cost_per_conversion ?? 0,
            'total_purchase_roas' => $insights->total_purchase_roas ?? 0,
            'avg_cpc' => $insights->avg_cpc ?? 0,
            'avg_cpm' => $insights->avg_cpm ?? 0,
            'avg_ctr' => $insights->avg_ctr ?? 0,
            // Video metrics
            'total_video_views' => $insights->total_video_views ?? 0,
            'total_video_plays' => $insights->total_video_plays ?? 0,
            'total_video_p25_watched_actions' => $insights->total_video_p25_watched_actions ?? 0,
            'total_video_p50_watched_actions' => $insights->total_video_p50_watched_actions ?? 0,
            'total_video_p75_watched_actions' => $insights->total_video_p75_watched_actions ?? 0,
            'total_video_p95_watched_actions' => $insights->total_video_p95_watched_actions ?? 0,
            'total_video_p100_watched_actions' => $insights->total_video_p100_watched_actions ?? 0,
            'total_thruplays' => $insights->total_thruplays ?? 0,
            'total_video_30_sec_watched' => $insights->total_video_30_sec_watched ?? 0,
            'avg_video_avg_time_watched' => $insights->avg_video_avg_time_watched ?? 0,
            'total_video_view_time' => $insights->total_video_view_time ?? 0,
        ];
    }

    /**
     * Breakdown: Thiết bị (mobile/desktop/tablet...)
     */
    public function getDeviceBreakdown(string $pageId, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $query = \App\Models\FacebookBreakdown::query()
            ->where('breakdown_type', 'action_device')
            ->whereHas('adInsight', function ($q) use ($pageId, $dateFrom, $dateTo) {
                $q->where('page_id', $pageId);
                if ($dateFrom) $q->where('date', '>=', $dateFrom);
                if ($dateTo) $q->where('date', '<=', $dateTo);
            });

        $data = [];
        foreach ($query->get() as $row) {
            $device = $this->validateArrayKey($row->breakdown_value);
            $metrics = is_string($row->metrics) ? (json_decode($row->metrics, true) ?: []) : ($row->metrics ?: []);

            $spend = (float) ($metrics['spend'] ?? 0);
            $impressions = (int) ($metrics['impressions'] ?? 0);
            $clicks = (int) ($metrics['clicks'] ?? 0);
            $conversions = (int) ($metrics['conversions'] ?? 0);

            if (!isset($data[$device])) {
                $data[$device] = [
                    'spend' => 0,
                    'impressions' => 0,
                    'clicks' => 0,
                    'conversions' => 0,
                ];
            }
            $data[$device]['spend'] += $spend;
            $data[$device]['impressions'] += $impressions;
            $data[$device]['clicks'] += $clicks;
            $data[$device]['conversions'] += $conversions;
        }

        return $data;
    }

    /**
     * Breakdown: Tỉnh/Thành phố Việt Nam
     */
    public function getRegionBreakdown(string $pageId, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $query = \App\Models\FacebookBreakdown::query()
            ->where('breakdown_type', 'region')
            ->whereHas('adInsight', function ($q) use ($pageId, $dateFrom, $dateTo) {
                $q->where('page_id', $pageId);
                if ($dateFrom) $q->where('date', '>=', $dateFrom);
                if ($dateTo) $q->where('date', '<=', $dateTo);
            });

        $data = [];
        foreach ($query->get() as $row) {
            $region = $this->validateArrayKey($row->breakdown_value);
            $metrics = is_string($row->metrics) ? (json_decode($row->metrics, true) ?: []) : ($row->metrics ?: []);
            $data[$region] = ($data[$region] ?? 0) + (int) ($metrics['reach'] ?? 0);
        }
        return $data;
    }

    /**
     * Breakdown: Giới tính - Độ tuổi
     */
    public function getGenderAgeBreakdown(string $pageId, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $query = \App\Models\FacebookBreakdown::query()
            ->where('breakdown_type', 'age_gender')
            ->whereHas('adInsight', function ($q) use ($pageId, $dateFrom, $dateTo) {
                $q->where('page_id', $pageId);
                if ($dateFrom) $q->where('date', '>=', $dateFrom);
                if ($dateTo) $q->where('date', '<=', $dateTo);
            });

        // Trả về dạng: [age_bucket => ['male' => n, 'female'=>n, 'unknown'=>n]] theo clicks
        $result = [];
        foreach ($query->get() as $row) {
            $raw = (string) $this->validateArrayKey($row->breakdown_value);
            $metrics = is_string($row->metrics) ? (json_decode($row->metrics, true) ?: []) : ($row->metrics ?: []);
            $clicks = (int) ($metrics['clicks'] ?? 0);

            // Expect pattern like "25-34_male" or "18-24_female"
            $ageBucket = $raw;
            $gender = 'unknown';
            if (strpos($raw, '_') !== false) {
                [$ageBucket, $gender] = explode('_', $raw, 2);
            }
            $ageBucket = trim((string) $ageBucket);
            $gender = trim((string) $gender);

            if (!isset($result[$ageBucket])) {
                $result[$ageBucket] = ['male' => 0, 'female' => 0, 'unknown' => 0];
            }
            if (!isset($result[$ageBucket][$gender])) {
                $gender = 'unknown';
            }
            $result[$ageBucket][$gender] += $clicks;
        }
        ksort($result);
        return $result;
    }

    /**
     * Tương quan ngân sách – kết quả – giá trị chuyển đổi
     */
    public function getBudgetResultConversionCorrelation(string $pageId, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $query = FacebookAdInsight::where('page_id', $pageId);
        if ($dateFrom) $query->where('date', '>=', $dateFrom);
        if ($dateTo) $query->where('date', '<=', $dateTo);

        $rows = $query->select(['date', 'spend', 'impressions', 'clicks', 'conversions', 'conversion_values'])
            ->orderBy('date')
            ->get();

        $points = [];
        foreach ($rows as $r) {
            $points[] = [
                'date' => $r->date,
                'spend' => (float)$r->spend,
                'impressions' => (int)$r->impressions,
                'clicks' => (int)$r->clicks,
                'conversions' => (int)$r->conversions,
                'conversion_values' => (float)$r->conversion_values,
            ];
        }

        return ['points' => $points];
    }

    /**
     * Lấy chi tiết chiến dịch quảng cáo cho một post
     */
    public function getAdCampaigns(string $postId, string $pageId): array
    {
        try {
            // Lấy danh sách ads cho post này
            $ads = FacebookAd::where('post_id', $postId)
                ->where('page_id', $pageId)
                ->with(['insights' => function ($query) {
                    $query->orderBy('date', 'desc');
                }])
                ->get();

            $campaigns = [];
            $performanceData = [
                'labels' => [],
                'impressions' => [],
                'clicks' => []
            ];
            $spendData = [
                'labels' => [],
                'values' => []
            ];

            foreach ($ads as $ad) {
                $totalSpend = $ad->insights->sum('spend');
                $totalImpressions = $ad->insights->sum('impressions');
                $totalClicks = $ad->insights->sum('clicks');
                $avgCtr = $ad->insights->avg('ctr');

                $campaigns[] = [
                    'id' => $ad->id,
                    'name' => $ad->name ?? 'Ad ' . $ad->id,
                    'status' => $ad->status ?? 'Unknown',
                    'spend' => $totalSpend,
                    'impressions' => $totalImpressions,
                    'clicks' => $totalClicks,
                    'ctr' => $avgCtr,
                    'created_time' => $ad->created_time,
                ];

                // Thêm vào performance data
                $performanceData['labels'][] = $ad->name ?? 'Ad ' . $ad->id;
                $performanceData['impressions'][] = $totalImpressions;
                $performanceData['clicks'][] = $totalClicks;

                // Thêm vào spend data
                $spendData['labels'][] = $ad->name ?? 'Ad ' . $ad->id;
                $spendData['values'][] = $totalSpend;
            }

            return [
                'campaigns' => $campaigns,
                'performance_data' => $performanceData,
                'spend_data' => $spendData,
                'summary' => [
                    'total_campaigns' => count($campaigns),
                    'total_spend' => array_sum(array_column($campaigns, 'spend')),
                    'total_impressions' => array_sum(array_column($campaigns, 'impressions')),
                    'total_clicks' => array_sum(array_column($campaigns, 'clicks')),
                ]
            ];

        } catch (\Exception $e) {
            return [
                'campaigns' => [],
                'performance_data' => ['labels' => [], 'impressions' => [], 'clicks' => []],
                'spend_data' => ['labels' => [], 'values' => []],
                'summary' => ['total_campaigns' => 0, 'total_spend' => 0, 'total_impressions' => 0, 'total_clicks' => 0],
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Lấy breakdown data cho một ad campaign
     */
    public function getAdBreakdowns(string $adId): array
    {
        try {
            // Lấy breakdown data từ bảng facebook_breakdowns
            $breakdowns = \App\Models\FacebookBreakdown::whereHas('adInsight', function($query) use ($adId) {
                $query->where('ad_id', $adId);
            })
            ->orderBy('breakdown_type')
            ->orderBy('breakdown_value')
            ->get()
            ->groupBy('breakdown_type');

            $result = [];
            foreach ($breakdowns as $breakdownType => $breakdownData) {
                $result[$breakdownType] = [];
                foreach ($breakdownData as $breakdown) {
                    $metrics = $breakdown->metrics ?? [];
                    $result[$breakdownType][] = [
                        'breakdown_value' => $breakdown->breakdown_value,
                        'spend' => $metrics['spend'] ?? 0,
                        'impressions' => $metrics['impressions'] ?? 0,
                        'clicks' => $metrics['clicks'] ?? 0,
                        'ctr' => $metrics['ctr'] ?? 0,
                        'cpc' => $metrics['cpc'] ?? 0,
                        'cpm' => $metrics['cpm'] ?? 0,
                        'reach' => $metrics['reach'] ?? 0,
                        'frequency' => $metrics['frequency'] ?? 0,
                        // Video metrics
                        'video_views' => $metrics['video_views'] ?? 0,
                        'video_plays' => $metrics['video_plays'] ?? 0,
                        'video_p25_watched_actions' => $metrics['video_p25_watched_actions'] ?? 0,
                        'video_p50_watched_actions' => $metrics['video_p50_watched_actions'] ?? 0,
                        'video_p75_watched_actions' => $metrics['video_p75_watched_actions'] ?? 0,
                        'video_p95_watched_actions' => $metrics['video_p95_watched_actions'] ?? 0,
                        'video_p100_watched_actions' => $metrics['video_p100_watched_actions'] ?? 0,
                        'thruplays' => $metrics['thruplays'] ?? 0,
                        'video_30_sec_watched' => $metrics['video_30_sec_watched'] ?? 0,
                        'video_avg_time_watched' => $metrics['video_avg_time_watched'] ?? 0,
                        'video_view_time' => $metrics['video_view_time'] ?? 0,
                    ];
                }
            }

            return $result;

        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Lấy detailed insights cho một ad campaign
     */
    public function getAdInsights(string $adId): array
    {
        try {
            // Lấy insights data từ bảng facebook_ad_insights
            $insights = \App\Models\FacebookAdInsight::where('ad_id', $adId)
                ->orderBy('date')
                ->get();

            $result = [
                'daily_data' => [],
                'summary' => [
                    'total_spend' => 0,
                    'total_impressions' => 0,
                    'total_clicks' => 0,
                    'total_reach' => 0,
                    'avg_ctr' => 0,
                    'avg_cpc' => 0,
                    'avg_cpm' => 0,
                    'total_video_views' => 0,
                    'total_video_plays' => 0,
                    'total_video_p75_watched_actions' => 0,
                    'total_video_p100_watched_actions' => 0,
                ]
            ];

            foreach ($insights as $insight) {
                $dailyData = [
                    'date' => $insight->date,
                    'spend' => $insight->spend,
                    'impressions' => $insight->impressions,
                    'clicks' => $insight->clicks,
                    'reach' => $insight->reach,
                    'ctr' => $insight->ctr,
                    'cpc' => $insight->cpc,
                    'cpm' => $insight->cpm,
                    'video_views' => $insight->video_views,
                    'video_plays' => $insight->video_plays,
                    'video_p75_watched_actions' => $insight->video_p75_watched_actions,
                    'video_p100_watched_actions' => $insight->video_p100_watched_actions,
                ];

                $result['daily_data'][] = $dailyData;

                // Cộng dồn vào summary
                $result['summary']['total_spend'] += $insight->spend;
                $result['summary']['total_impressions'] += $insight->impressions;
                $result['summary']['total_clicks'] += $insight->clicks;
                $result['summary']['total_reach'] += $insight->reach;
                $result['summary']['total_video_views'] += $insight->video_views;
                $result['summary']['total_video_plays'] += $insight->video_plays;
                $result['summary']['total_video_p75_watched_actions'] += $insight->video_p75_watched_actions;
                $result['summary']['total_video_p100_watched_actions'] += $insight->video_p100_watched_actions;
            }

            // Tính trung bình
            if (count($insights) > 0) {
                $result['summary']['avg_ctr'] = $result['summary']['total_impressions'] > 0 ? 
                    ($result['summary']['total_clicks'] / $result['summary']['total_impressions']) * 100 : 0;
                $result['summary']['avg_cpc'] = $result['summary']['total_clicks'] > 0 ? 
                    $result['summary']['total_spend'] / $result['summary']['total_clicks'] : 0;
                $result['summary']['avg_cpm'] = $result['summary']['total_impressions'] > 0 ? 
                    ($result['summary']['total_spend'] / $result['summary']['total_impressions']) * 1000 : 0;
            }

            return $result;

        } catch (\Exception $e) {
            return [
                'daily_data' => [],
                'summary' => [
                    'total_spend' => 0,
                    'total_impressions' => 0,
                    'total_clicks' => 0,
                    'total_reach' => 0,
                    'avg_ctr' => 0,
                    'avg_cpc' => 0,
                    'avg_cpm' => 0,
                    'total_video_views' => 0,
                    'total_video_plays' => 0,
                    'total_video_p75_watched_actions' => 0,
                    'total_video_p100_watched_actions' => 0,
                ]
            ];
        }
    }

    /**
     * Lấy thông tin chi tiết của một post
     */
    public function getPostById(string $postId, string $pageId): ?object
    {
        try {
            // Thử lấy từ FacebookAdInsight trước
            $post = FacebookAdInsight::where('post_id', $postId)
                ->where('page_id', $pageId)
                ->select([
                    'post_id as id',
                    'page_id',
                    DB::raw('SUM(spend) as total_spend'),
                    DB::raw('SUM(impressions) as total_impressions'),
                    DB::raw('SUM(clicks) as total_clicks'),
                    DB::raw('SUM(video_views) as total_video_views'),
                    DB::raw('SUM(video_p75_watched_actions) as total_video_p75_watched_actions'),
                    DB::raw('SUM(video_p100_watched_actions) as total_video_p100_watched_actions'),
                ])
                ->groupBy('post_id', 'page_id')
                ->first();

            // Lấy thông tin bổ sung từ FacebookAd
            if ($post) {
                $adInfo = FacebookAd::where('post_id', $postId)
                    ->where('page_id', $pageId)
                    ->first();
                
                if ($adInfo) {
                    $post->message = $adInfo->name ?? 'Không có nội dung';
                    $post->type = $adInfo->type ?? 'post';
                    $post->created_time = $adInfo->created_time ?? now();
                    $post->permalink_url = $adInfo->permalink_url ?? null;
                } else {
                    $post->message = 'Không có nội dung';
                    $post->type = 'post';
                    $post->created_time = now();
                    $post->permalink_url = null;
                }
            }

            // Nếu không có data, thử lấy từ FacebookPost
            if (!$post) {
                $post = \App\Models\FacebookPost::where('id', $postId)
                    ->where('page_id', $pageId)
                    ->select([
                        'id',
                        'page_id',
                        'message',
                        'type',
                        'created_time',
                        'permalink_url',
                        DB::raw('0 as total_spend'),
                        DB::raw('0 as total_impressions'),
                        DB::raw('0 as total_clicks'),
                        DB::raw('0 as total_video_views'),
                        DB::raw('0 as total_video_p75_watched_actions'),
                        DB::raw('0 as total_video_p100_watched_actions'),
                    ])
                    ->first();
            }

            return $post;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in getPostById', [
                'post_id' => $postId,
                'page_id' => $pageId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Lấy breakdown data cho một post
     */
    public function getPostBreakdowns(string $postId, string $pageId): array
    {
        try {
            // Lấy tất cả ad_insight_ids của post này
            $adInsightIds = FacebookAdInsight::where('post_id', $postId)
                ->where('page_id', $pageId)
                ->pluck('id')
                ->unique();

            $result = [];
            foreach ($adInsightIds as $adInsightId) {
                // Truy vấn trực tiếp breakdowns cho ad_insight_id này
                $breakdowns = \App\Models\FacebookBreakdown::where('ad_insight_id', $adInsightId)
                    ->orderBy('breakdown_type')
                    ->orderBy('breakdown_value')
                    ->get()
                    ->groupBy('breakdown_type');

                foreach ($breakdowns as $breakdownType => $breakdownData) {
                    // Kiểm tra breakdownType có hợp lệ không
                    if (empty($breakdownType) || !is_string($breakdownType) && !is_numeric($breakdownType)) {
                        continue;
                    }
                    
                    // Đảm bảo breakdownType là string
                    $breakdownType = (string) $breakdownType;
                    
                    if (!isset($result[$breakdownType])) {
                        $result[$breakdownType] = [];
                    }
                    
                    foreach ($breakdownData as $breakdown) {
                        // Xử lý metrics - có thể là JSON string hoặc array
                        $metrics = $breakdown->metrics ?? [];
                        if (is_string($metrics)) {
                            $metrics = json_decode($metrics, true) ?? [];
                        }
                        
                        $result[$breakdownType][] = [
                            'breakdown_value' => $breakdown->breakdown_value ?? 'Unknown',
                            'spend' => (float)($metrics['spend'] ?? 0),
                            'impressions' => (int)($metrics['impressions'] ?? 0),
                            'clicks' => (int)($metrics['clicks'] ?? 0),
                            'ctr' => (float)($metrics['ctr'] ?? 0),
                            'cpc' => (float)($metrics['cpc'] ?? 0),
                            'cpm' => (float)($metrics['cpm'] ?? 0),
                            'reach' => (int)($metrics['reach'] ?? 0),
                            'frequency' => (float)($metrics['frequency'] ?? 0),
                            'video_views' => (int)($metrics['video_views'] ?? 0),
                            'video_plays' => (int)($metrics['video_plays'] ?? 0),
                            'video_p25_watched_actions' => (int)($metrics['video_p25_watched_actions'] ?? 0),
                            'video_p50_watched_actions' => (int)($metrics['video_p50_watched_actions'] ?? 0),
                            'video_p75_watched_actions' => (int)($metrics['video_p75_watched_actions'] ?? 0),
                            'video_p95_watched_actions' => (int)($metrics['video_p95_watched_actions'] ?? 0),
                            'video_p100_watched_actions' => (int)($metrics['video_p100_watched_actions'] ?? 0),
                            'thruplays' => (int)($metrics['thruplays'] ?? 0),
                            'video_30_sec_watched' => (int)($metrics['video_30_sec_watched'] ?? 0),
                            'video_avg_time_watched' => (float)($metrics['video_avg_time_watched'] ?? 0),
                            'video_view_time' => (int)($metrics['video_view_time'] ?? 0),
                        ];
                    }
                }
            }

            return $result;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in getPostBreakdowns', [
                'post_id' => $postId,
                'page_id' => $pageId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }

    /**
     * Lấy detailed breakdown data cho một post với tổng hợp
     */
    public function getPostDetailedBreakdowns(string $postId, string $pageId): array
    {
        try {
            // Lấy tất cả ad_insight_ids của post này
            $adInsightIds = FacebookAdInsight::where('post_id', $postId)
                ->where('page_id', $pageId)
                ->pluck('id')
                ->unique();

            $result = [];
            foreach ($adInsightIds as $adInsightId) {
                // Truy vấn breakdowns cho ad_insight_id này
                $breakdowns = \App\Models\FacebookBreakdown::where('ad_insight_id', $adInsightId)
                    ->orderBy('breakdown_type')
                    ->orderBy('breakdown_value')
                    ->get()
                    ->groupBy('breakdown_type');

                foreach ($breakdowns as $breakdownType => $breakdownData) {
                    if (empty($breakdownType)) continue;
                    
                    $breakdownType = (string) $breakdownType;
                    
                    if (!isset($result[$breakdownType])) {
                        $result[$breakdownType] = [];
                    }
                    
                    foreach ($breakdownData as $breakdown) {
                        // Xử lý metrics - có thể là JSON string hoặc array
                        $metrics = $breakdown->metrics ?? [];
                        if (is_string($metrics)) {
                            $metrics = json_decode($metrics, true) ?? [];
                        }
                        
                        $breakdownValue = $breakdown->breakdown_value ?? 'Unknown';
                        
                        if (!isset($result[$breakdownType][$breakdownValue])) {
                            $result[$breakdownType][$breakdownValue] = [
                                'spend' => 0,
                                'impressions' => 0,
                                'clicks' => 0,
                                'reach' => 0,
                                'ctr' => 0,
                                'cpc' => 0,
                                'cpm' => 0,
                                'frequency' => 0,
                                'video_views' => 0,
                                'video_plays' => 0,
                                'video_p25_watched_actions' => 0,
                                'video_p50_watched_actions' => 0,
                                'video_p75_watched_actions' => 0,
                                'video_p95_watched_actions' => 0,
                                'video_p100_watched_actions' => 0,
                                'thruplays' => 0,
                                'video_30_sec_watched' => 0,
                                'video_avg_time_watched' => 0,
                                'video_view_time' => 0,
                            ];
                        }
                        
                        // Cộng dồn metrics cơ bản
                        $result[$breakdownType][$breakdownValue]['spend'] += (float)($metrics['spend'] ?? 0);
                        $result[$breakdownType][$breakdownValue]['impressions'] += (int)($metrics['impressions'] ?? 0);
                        $result[$breakdownType][$breakdownValue]['clicks'] += (int)($metrics['clicks'] ?? 0);
                        $result[$breakdownType][$breakdownValue]['reach'] += (int)($metrics['reach'] ?? 0);
                        
                        // Xử lý video metrics từ actions
                        $videoViews = 0;
                        $videoPlays = 0;
                        $videoP25 = 0;
                        $videoP50 = 0;
                        $videoP75 = 0;
                        $videoP95 = 0;
                        $videoP100 = 0;
                        $video30Sec = 0;
                        $videoAvgTime = 0;
                        
                        // Lấy video_views từ actions
                        if (isset($metrics['actions']) && is_array($metrics['actions'])) {
                            foreach ($metrics['actions'] as $action) {
                                if (isset($action['action_type']) && $action['action_type'] === 'video_view') {
                                    $videoViews += (int)($action['value'] ?? 0);
                                }
                            }
                        }
                        
                        // Lấy video_play_actions
                        if (isset($metrics['video_play_actions']) && is_array($metrics['video_play_actions'])) {
                            foreach ($metrics['video_play_actions'] as $action) {
                                if (isset($action['action_type']) && $action['action_type'] === 'video_view') {
                                    $videoPlays += (int)($action['value'] ?? 0);
                                }
                            }
                        }
                        
                        // Lấy các metrics video khác
                        if (isset($metrics['video_p25_watched_actions']) && is_array($metrics['video_p25_watched_actions'])) {
                            foreach ($metrics['video_p25_watched_actions'] as $action) {
                                if (isset($action['action_type']) && $action['action_type'] === 'video_view') {
                                    $videoP25 += (int)($action['value'] ?? 0);
                                }
                            }
                        }
                        
                        if (isset($metrics['video_p50_watched_actions']) && is_array($metrics['video_p50_watched_actions'])) {
                            foreach ($metrics['video_p50_watched_actions'] as $action) {
                                if (isset($action['action_type']) && $action['action_type'] === 'video_view') {
                                    $videoP50 += (int)($action['value'] ?? 0);
                                }
                            }
                        }
                        
                        if (isset($metrics['video_p75_watched_actions']) && is_array($metrics['video_p75_watched_actions'])) {
                            foreach ($metrics['video_p75_watched_actions'] as $action) {
                                if (isset($action['action_type']) && $action['action_type'] === 'video_view') {
                                    $videoP75 += (int)($action['value'] ?? 0);
                                }
                            }
                        }
                        
                        if (isset($metrics['video_p95_watched_actions']) && is_array($metrics['video_p95_watched_actions'])) {
                            foreach ($metrics['video_p95_watched_actions'] as $action) {
                                if (isset($action['action_type']) && $action['action_type'] === 'video_view') {
                                    $videoP95 += (int)($action['value'] ?? 0);
                                }
                            }
                        }
                        
                        if (isset($metrics['video_p100_watched_actions']) && is_array($metrics['video_p100_watched_actions'])) {
                            foreach ($metrics['video_p100_watched_actions'] as $action) {
                                if (isset($action['action_type']) && $action['action_type'] === 'video_view') {
                                    $videoP100 += (int)($action['value'] ?? 0);
                                }
                            }
                        }
                        
                        if (isset($metrics['video_30_sec_watched_actions']) && is_array($metrics['video_30_sec_watched_actions'])) {
                            foreach ($metrics['video_30_sec_watched_actions'] as $action) {
                                if (isset($action['action_type']) && $action['action_type'] === 'video_view') {
                                    $video30Sec += (int)($action['value'] ?? 0);
                                }
                            }
                        }
                        
                        if (isset($metrics['video_avg_time_watched_actions']) && is_array($metrics['video_avg_time_watched_actions'])) {
                            foreach ($metrics['video_avg_time_watched_actions'] as $action) {
                                if (isset($action['action_type']) && $action['action_type'] === 'video_view') {
                                    $videoAvgTime += (int)($action['value'] ?? 0);
                                }
                            }
                        }
                        
                        // Cộng dồn vào kết quả
                        $result[$breakdownType][$breakdownValue]['video_views'] += $videoViews;
                        $result[$breakdownType][$breakdownValue]['video_plays'] += $videoPlays;
                        $result[$breakdownType][$breakdownValue]['video_p25_watched_actions'] += $videoP25;
                        $result[$breakdownType][$breakdownValue]['video_p50_watched_actions'] += $videoP50;
                        $result[$breakdownType][$breakdownValue]['video_p75_watched_actions'] += $videoP75;
                        $result[$breakdownType][$breakdownValue]['video_p95_watched_actions'] += $videoP95;
                        $result[$breakdownType][$breakdownValue]['video_p100_watched_actions'] += $videoP100;
                        $result[$breakdownType][$breakdownValue]['video_30_sec_watched'] += $video30Sec;
                        
                        // video_avg_time_watched cần được tính trung bình, không cộng dồn
                        // Lưu giá trị cuối cùng để tính trung bình sau
                        if ($videoAvgTime > 0) {
                            $result[$breakdownType][$breakdownValue]['video_avg_time_watched'] = $videoAvgTime;
                        }
                        
                        // Tính toán video_view_time từ video_avg_time_watched và video_views
                        if ($videoAvgTime > 0 && $videoViews > 0) {
                            $result[$breakdownType][$breakdownValue]['video_view_time'] += ($videoAvgTime * $videoViews);
                        }
                    }
                }
            }

            // Tính toán CTR, CPC, CPM trung bình
            foreach ($result as $breakdownType => $breakdownData) {
                foreach ($breakdownData as $value => $metrics) {
                    if ($metrics['impressions'] > 0) {
                        $result[$breakdownType][$value]['ctr'] = ($metrics['clicks'] / $metrics['impressions']) * 100;
                        $result[$breakdownType][$value]['cpm'] = ($metrics['spend'] / $metrics['impressions']) * 1000;
                    }
                    if ($metrics['clicks'] > 0) {
                        $result[$breakdownType][$value]['cpc'] = $metrics['spend'] / $metrics['clicks'];
                    }
                    if ($metrics['reach'] > 0) {
                        $result[$breakdownType][$value]['frequency'] = $metrics['impressions'] / $metrics['reach'];
                    }
                }
            }

            return $result;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in getPostDetailedBreakdowns', [
                'post_id' => $postId,
                'page_id' => $pageId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }

    /**
     * Lấy breakdowns data cho overview
     */
    public function getOverviewBreakdowns(string $pageId = null, string $dateFrom = null, string $dateTo = null): array
    {
        try {
            $query = FacebookAdInsight::query();
            if ($pageId) {
                $query->where('page_id', $pageId);
            }
            if ($dateFrom && $dateTo) {
                $query->whereBetween('date', [$dateFrom, $dateTo]);
            }

            // Lấy danh sách insight IDs (đúng khoá tham chiếu tới facebook_breakdowns)
            $insightIds = $query->pluck('id');
            $result = [];
            if ($insightIds->isEmpty()) {
                return $result;
            }

            $breakdowns = \App\Models\FacebookBreakdown::whereIn('ad_insight_id', $insightIds->all())
                ->orderBy('breakdown_type')
                ->orderBy('breakdown_value')
                ->get()
                ->groupBy('breakdown_type');

            foreach ($breakdowns as $breakdownType => $items) {
                if (empty($breakdownType)) { continue; }
                $breakdownType = (string) $breakdownType;
                if (!isset($result[$breakdownType])) {
                    $result[$breakdownType] = [];
                }
                foreach ($items as $row) {
                    $breakdownValue = $row->breakdown_value ?? 'Unknown';
                    if ($breakdownValue === '' || $breakdownValue === null) { $breakdownValue = 'Unknown'; }
                    $breakdownValue = (string) $breakdownValue;
                    // Robust decode: metrics có thể được lưu dạng JSON string (double-encoded)
                    $metrics = [];
                    if (is_array($row->metrics)) {
                        $metrics = $row->metrics;
                    } elseif (is_string($row->metrics)) {
                        $decoded = json_decode($row->metrics, true);
                        if (is_string($decoded)) {
                            $decoded = json_decode($decoded, true);
                        }
                        if (is_array($decoded)) {
                            $metrics = $decoded;
                        }
                    }
                    $spend = (float) ($metrics['spend'] ?? 0);
                    $impressions = (int) ($metrics['impressions'] ?? 0);
                    $clicks = (int) ($metrics['clicks'] ?? 0);
                    $reach = (int) ($metrics['reach'] ?? 0);
                    $conversions = (int) ($metrics['conversions'] ?? 0);
                    $conversionValues = (float) ($metrics['conversion_values'] ?? 0.0);
                    $videoViews = (int) ($metrics['video_views'] ?? 0);

                                            if (!isset($result[$breakdownType][$breakdownValue])) {
                            $result[$breakdownType][$breakdownValue] = [
                                'spend' => 0.0,
                                'impressions' => 0,
                                'clicks' => 0,
                                'reach' => 0,
                                'conversions' => 0,
                                'conversion_values' => 0.0,
                                'video_views' => 0,
                                'video_view_time' => 0,
                                'video_avg_time_watched' => 0,
                            ];
                        }

                    $result[$breakdownType][$breakdownValue]['spend'] += $spend;
                    $result[$breakdownType][$breakdownValue]['impressions'] += $impressions;
                    $result[$breakdownType][$breakdownValue]['clicks'] += $clicks;
                    $result[$breakdownType][$breakdownValue]['reach'] += $reach;
                    $result[$breakdownType][$breakdownValue]['conversions'] += $conversions;
                    $result[$breakdownType][$breakdownValue]['conversion_values'] += $conversionValues;
                    $result[$breakdownType][$breakdownValue]['video_views'] += $videoViews;
                    
                    // Xử lý video metrics
                    $videoAvgTime = (int) ($metrics['video_avg_time_watched'] ?? 0);
                    if ($videoAvgTime > 0) {
                        $result[$breakdownType][$breakdownValue]['video_avg_time_watched'] = $videoAvgTime;
                    }
                    
                    // Tính toán video_view_time
                    if ($videoAvgTime > 0 && $videoViews > 0) {
                        $result[$breakdownType][$breakdownValue]['video_view_time'] += ($videoAvgTime * $videoViews);
                    }
                }
            }

            // Fallback: Nếu bảng facebook_breakdowns chưa có dữ liệu (hoặc rỗng) trong phạm vi ngày, cố gắng tổng hợp từ cột breakdowns của facebook_ad_insights
            if (empty($result)) {
                $insights = FacebookAdInsight::query()
                    ->whereIn('id', $insightIds->all())
                    ->whereNotNull('breakdowns')
                    ->get(['id', 'breakdowns']);

                foreach ($insights as $insight) {
                    $rows = $insight->breakdowns;
                    if (is_string($rows)) {
                        $tmp = json_decode($rows, true);
                        if (is_array($tmp)) { $rows = $tmp; }
                    }
                    if (!is_array($rows)) { continue; }

                    foreach ($rows as $row) {
                        // Kỳ vọng cấu trúc { breakdown_type, breakdown_value, metrics }
                        $type = isset($row['breakdown_type']) ? (string) $row['breakdown_type'] : null;
                        $value = $row['breakdown_value'] ?? null;
                        $metrics = $row['metrics'] ?? [];

                        if (!$type) { continue; }
                        if ($value === '' || $value === null) { $value = 'Unknown'; }
                        $value = (string) $value;

                        if (!isset($result[$type])) { $result[$type] = []; }
                        if (!isset($result[$type][$value])) {
                            $result[$type][$value] = [
                                'spend' => 0.0,
                                'impressions' => 0,
                                'clicks' => 0,
                                'reach' => 0,
                                'conversions' => 0,
                                'conversion_values' => 0.0,
                                'video_views' => 0,
                                'video_view_time' => 0,
                                'video_avg_time_watched' => 0,
                            ];
                        }

                        // Nếu không có 'metrics', thử lấy trực tiếp từ các trường cấp 1 của row
                        $spend = $metrics['spend'] ?? ($row['spend'] ?? 0);
                        $impr = $metrics['impressions'] ?? ($row['impressions'] ?? 0);
                        $clicks = $metrics['clicks'] ?? ($row['clicks'] ?? 0);
                        $reach = $metrics['reach'] ?? ($row['reach'] ?? 0);
                        $vviews = $metrics['video_views'] ?? ($row['video_views'] ?? 0);
                        $conversions = $metrics['conversions'] ?? ($row['conversions'] ?? 0);
                        $conversionValues = $metrics['conversion_values'] ?? ($row['conversion_values'] ?? 0);

                        $result[$type][$value]['spend'] += (float) $spend;
                        $result[$type][$value]['impressions'] += (int) $impr;
                        $result[$type][$value]['clicks'] += (int) $clicks;
                        $result[$type][$value]['reach'] += (int) $reach;
                        $result[$type][$value]['conversions'] += (int) $conversions;
                        $result[$type][$value]['conversion_values'] += (float) $conversionValues;
                        $result[$type][$value]['video_views'] += (int) $vviews;
                        
                        // Xử lý video metrics trong fallback
                        $videoAvgTime = (int) ($metrics['video_avg_time_watched'] ?? 0);
                        if ($videoAvgTime > 0) {
                            $result[$type][$value]['video_avg_time_watched'] = $videoAvgTime;
                        }
                        
                        // Tính toán video_view_time
                        if ($videoAvgTime > 0 && $vviews > 0) {
                            $result[$type][$value]['video_view_time'] += ($videoAvgTime * $vviews);
                        }
                    }
                }
            }

            return $result;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in getOverviewBreakdowns', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }

    /**
     * Lấy top 5 bài viết theo hiệu suất
     */
    public function getTop5Posts(string $pageId = null, string $dateFrom = null, string $dateTo = null): array
    {
        try {
            $query = FacebookAdInsight::whereNotNull('post_id');
            
            if ($pageId) {
                $query->where('page_id', $pageId);
            }
            
            if ($dateFrom && $dateTo) {
                $query->whereBetween('date', [$dateFrom, $dateTo]);
            }
            
            $posts = $query->select([
                'post_id',
                'page_id',
                DB::raw('SUM(spend) as total_spend'),
                DB::raw('SUM(impressions) as total_impressions'),
                DB::raw('SUM(clicks) as total_clicks'),
                DB::raw('SUM(reach) as total_reach'),
                DB::raw('AVG(ctr) as avg_ctr'),
                DB::raw('AVG(cpc) as avg_cpc'),
                DB::raw('AVG(cpm) as avg_cpm'),
                DB::raw('SUM(video_views) as total_video_views'),
                DB::raw('SUM(video_p75_watched_actions) as total_video_p75_watched_actions'),
                DB::raw('SUM(video_p100_watched_actions) as total_video_p100_watched_actions'),
            ])
            ->groupBy('post_id', 'page_id')
            ->orderBy('total_spend', 'desc')
            ->limit(5) // Giới hạn chỉ 5 bài viết
            ->get();

            // Lấy thông tin bổ sung từ FacebookAd
            $posts = $posts->map(function ($post) {
                $adInfo = FacebookAd::where('post_id', $post->post_id)
                    ->where('page_id', $post->page_id)
                    ->first();
                
                return (object) [
                    'id' => $post->post_id,
                    'page_id' => $post->page_id,
                    'message' => $adInfo->name ?? 'Không có nội dung',
                    'type' => $adInfo->type ?? 'post',
                    'created_time' => $adInfo->created_time ?? now(),
                    'permalink_url' => $adInfo->permalink_url ?? null,
                    'total_spend' => $post->total_spend,
                    'total_impressions' => $post->total_impressions,
                    'total_clicks' => $post->total_clicks,
                    'total_reach' => $post->total_reach,
                    'avg_ctr' => $post->avg_ctr,
                    'avg_cpc' => $post->avg_cpc,
                    'avg_cpm' => $post->avg_cpm,
                    'total_video_views' => $post->total_video_views,
                    'total_video_p75_watched_actions' => $post->total_video_p75_watched_actions,
                    'total_video_p100_watched_actions' => $post->total_video_p100_watched_actions,
                ];
            });

            return $posts->toArray();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in getTop5Posts', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }

    /**
     * Lấy actions data cho overview
     */
    public function getOverviewActions(string $pageId = null, string $dateFrom = null, string $dateTo = null): array
    {
        try {
            $query = FacebookAdInsight::whereNotNull('actions');
            
            if ($pageId) {
                $query->where('page_id', $pageId);
            }
            
            if ($dateFrom && $dateTo) {
                $query->whereBetween('date', [$dateFrom, $dateTo]);
            }
            
            $insights = $query->get();
            
            $result = [
                'daily_actions' => [],
                'summary' => []
            ];

            foreach ($insights as $insight) {
                $actions = $insight->actions ?? [];
                
                // Actions đã là array, không cần decode
                if (!is_array($actions)) {
                    continue;
                }

                $date = $insight->date;
                
                // Kiểm tra date có hợp lệ không
                if (empty($date) || !is_string($date) && !is_numeric($date)) {
                    continue;
                }
                
                // Đảm bảo date là string
                $date = (string) $date;
                
                if (!isset($result['daily_actions'][$date])) {
                    $result['daily_actions'][$date] = [];
                }

                foreach ($actions as $action) {
                    // Kiểm tra action có phải là array không
                    if (!is_array($action)) {
                        continue;
                    }

                    $actionType = $action['action_type'] ?? 'unknown';
                    $value = $action['value'] ?? 0;

                    // Thêm vào daily actions
                    if (!isset($result['daily_actions'][$date][$actionType])) {
                        $result['daily_actions'][$date][$actionType] = 0;
                    }
                    $result['daily_actions'][$date][$actionType] += $value;

                    // Thêm vào summary
                    if (!isset($result['summary'][$actionType])) {
                        $result['summary'][$actionType] = 0;
                    }
                    $result['summary'][$actionType] += $value;
                }
            }

            return $result;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in getOverviewActions', [
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
     * Lấy tổng hợp data của page
     */
    public function getPageSummary(string $pageId): array
    {
        try {
            $insights = FacebookAdInsight::where('page_id', $pageId)
                ->select([
                    DB::raw('COUNT(DISTINCT post_id) as total_posts'),
                    DB::raw('COUNT(DISTINCT ad_id) as total_ads'),
                    DB::raw('SUM(spend) as total_spend'),
                    DB::raw('SUM(impressions) as total_impressions'),
                    DB::raw('SUM(clicks) as total_clicks'),
                    DB::raw('SUM(reach) as total_reach'),
                    DB::raw('AVG(ctr) as avg_ctr'),
                    DB::raw('AVG(cpc) as avg_cpc'),
                    DB::raw('AVG(cpm) as avg_cpm'),
                    DB::raw('SUM(video_views) as total_video_views'),
                    DB::raw('SUM(video_plays) as total_video_plays'),
                    DB::raw('SUM(video_p75_watched_actions) as total_video_p75_watched_actions'),
                    DB::raw('SUM(video_p100_watched_actions) as total_video_p100_watched_actions'),
                ])
                ->first();

            return [
                'total_posts' => $insights->total_posts ?? 0,
                'total_ads' => $insights->total_ads ?? 0,
                'total_spend' => $insights->total_spend ?? 0,
                'total_impressions' => $insights->total_impressions ?? 0,
                'total_clicks' => $insights->total_clicks ?? 0,
                'total_reach' => $insights->total_reach ?? 0,
                'avg_ctr' => $insights->avg_ctr ?? 0,
                'avg_cpc' => $insights->avg_cpc ?? 0,
                'avg_cpm' => $insights->avg_cpm ?? 0,
                'total_video_views' => $insights->total_video_views ?? 0,
                'total_video_plays' => $insights->total_video_plays ?? 0,
                'total_video_p75_watched_actions' => $insights->total_video_p75_watched_actions ?? 0,
                'total_video_p100_watched_actions' => $insights->total_video_p100_watched_actions ?? 0,
            ];
        } catch (\Exception $e) {
            return [
                'total_posts' => 0,
                'total_ads' => 0,
                'total_spend' => 0,
                'total_impressions' => 0,
                'total_clicks' => 0,
                'total_reach' => 0,
                'avg_ctr' => 0,
                'avg_cpc' => 0,
                'avg_cpm' => 0,
                'total_video_views' => 0,
                'total_video_plays' => 0,
                'total_video_p75_watched_actions' => 0,
                'total_video_p100_watched_actions' => 0,
            ];
        }
    }

    /**
     * Lấy actions data cho một post
     */
    public function getPostActions(string $postId, string $pageId): array
    {
        try {
            $insights = FacebookAdInsight::where('post_id', $postId)
                ->where('page_id', $pageId)
                ->whereNotNull('actions')
                ->get();

            $result = [
                'daily_actions' => [],
                'summary' => [],
                'detailed_actions' => []
            ];

            foreach ($insights as $insight) {
                $actions = $insight->actions ?? [];
                
                // Actions đã là array, không cần decode
                if (!is_array($actions)) {
                    continue;
                }

                $date = $insight->date;
                
                // Kiểm tra date có hợp lệ không
                if (empty($date) || !is_string($date) && !is_numeric($date)) {
                    continue;
                }
                
                // Đảm bảo date là string
                $date = (string) $date;
                
                if (!isset($result['daily_actions'][$date])) {
                    $result['daily_actions'][$date] = [];
                }

                foreach ($actions as $action) {
                    // Kiểm tra action có phải là array không
                    if (!is_array($action)) {
                        continue;
                    }

                    $actionType = $action['action_type'] ?? 'unknown';
                    $value = (int)($action['value'] ?? 0);

                    // Thêm vào daily actions
                    if (!isset($result['daily_actions'][$date][$actionType])) {
                        $result['daily_actions'][$date][$actionType] = 0;
                    }
                    $result['daily_actions'][$date][$actionType] += $value;

                    // Thêm vào summary
                    if (!isset($result['summary'][$actionType])) {
                        $result['summary'][$actionType] = 0;
                    }
                    $result['summary'][$actionType] += $value;

                    // Thêm vào detailed actions
                    if (!isset($result['detailed_actions'][$actionType])) {
                        $result['detailed_actions'][$actionType] = [
                            'total_value' => 0,
                            'occurrences' => 0,
                            'dates' => []
                        ];
                    }
                    $result['detailed_actions'][$actionType]['total_value'] += $value;
                    $result['detailed_actions'][$actionType]['occurrences']++;
                    $result['detailed_actions'][$actionType]['dates'][] = $date;
                }
            }

            // Sắp xếp summary theo giá trị giảm dần
            arsort($result['summary']);

            return $result;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in getPostActions', [
                'post_id' => $postId,
                'page_id' => $pageId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [
                'daily_actions' => [],
                'summary' => [],
                'detailed_actions' => []
            ];
        }
    }

    /**
     * Lấy insights data cho một post
     */
    public function getPostInsights(string $postId, string $pageId): array
    {
        try {
            $insights = FacebookAdInsight::where('post_id', $postId)
                ->where('page_id', $pageId)
                ->orderBy('date')
                ->get();

            $result = [
                'daily_data' => [],
                'summary' => [
                    'total_spend' => 0,
                    'total_impressions' => 0,
                    'total_clicks' => 0,
                    'total_reach' => 0,
                    'avg_ctr' => 0,
                    'avg_cpc' => 0,
                    'avg_cpm' => 0,
                    'total_video_views' => 0,
                    'total_video_plays' => 0,
                    'total_video_p75_watched_actions' => 0,
                    'total_video_p100_watched_actions' => 0,
                ]
            ];

            foreach ($insights as $insight) {
                $dailyData = [
                    'date' => $insight->date,
                    'spend' => $insight->spend,
                    'impressions' => $insight->impressions,
                    'clicks' => $insight->clicks,
                    'reach' => $insight->reach,
                    'ctr' => $insight->ctr,
                    'cpc' => $insight->cpc,
                    'cpm' => $insight->cpm,
                    'video_views' => $insight->video_views,
                    'video_plays' => $insight->video_plays,
                    'video_p75_watched_actions' => $insight->video_p75_watched_actions,
                    'video_p100_watched_actions' => $insight->video_p100_watched_actions,
                ];

                $result['daily_data'][] = $dailyData;

                // Cộng dồn vào summary
                $result['summary']['total_spend'] += $insight->spend;
                $result['summary']['total_impressions'] += $insight->impressions;
                $result['summary']['total_clicks'] += $insight->clicks;
                $result['summary']['total_reach'] += $insight->reach;
                $result['summary']['total_video_views'] += $insight->video_views;
                $result['summary']['total_video_plays'] += $insight->video_plays;
                $result['summary']['total_video_p75_watched_actions'] += $insight->video_p75_watched_actions;
                $result['summary']['total_video_p100_watched_actions'] += $insight->video_p100_watched_actions;
            }

            // Tính trung bình
            if (count($insights) > 0) {
                $result['summary']['avg_ctr'] = $result['summary']['total_impressions'] > 0 ? 
                    ($result['summary']['total_clicks'] / $result['summary']['total_impressions']) * 100 : 0;
                $result['summary']['avg_cpc'] = $result['summary']['total_clicks'] > 0 ? 
                    $result['summary']['total_spend'] / $result['summary']['total_clicks'] : 0;
                $result['summary']['avg_cpm'] = $result['summary']['total_impressions'] > 0 ? 
                    ($result['summary']['total_spend'] / $result['summary']['total_impressions']) * 1000 : 0;
            }

            return $result;
        } catch (\Exception $e) {
            return [
                'daily_data' => [],
                'summary' => [
                    'total_spend' => 0,
                    'total_impressions' => 0,
                    'total_clicks' => 0,
                    'total_reach' => 0,
                    'avg_ctr' => 0,
                    'avg_cpc' => 0,
                    'avg_cpm' => 0,
                    'total_video_views' => 0,
                    'total_video_plays' => 0,
                    'total_video_p75_watched_actions' => 0,
                    'total_video_p100_watched_actions' => 0,
                ]
            ];
        }
    }
} 