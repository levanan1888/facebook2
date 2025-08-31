<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\FacebookDataFilterRequest;
use App\Services\FacebookDataService;
use App\Http\Resources\FacebookPostResource;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FacebookDataController extends Controller
{
    public function __construct(
        private readonly FacebookDataService $facebookDataService
    ) {
        //
    }

    /**
     * Hiển thị màn hình quản lý dữ liệu Facebook
     */
    public function index(FacebookDataFilterRequest $request): View
    {
        $filters = $request->validated();
        
        // Đảm bảo tất cả keys đều có, ngay cả khi null
        $filters = array_merge([
            'page_id' => null,
            'date_from' => null,
            'date_to' => null,
            'post_type' => null,
            'status' => null,
            'search' => null,
        ], $filters);
        
        $data = $this->facebookDataService->getFacebookData($filters);
        
        return view('facebook.data-management.index', compact('data', 'filters'));
    }

    /**
     * API endpoint để lấy danh sách posts theo page
     */
    public function getPostsByPage(Request $request)
    {
        $pageId = $request->input('page_id');
        $filters = $request->only(['date_from', 'date_to', 'post_type', 'status', 'search']);
        
        $posts = $this->facebookDataService->getPostsByPage($pageId, $filters);
        
        return FacebookPostResource::collection($posts);
    }

    /**
     * API endpoint để lấy data cho page (posts + stats)
     */
    public function getPageData(Request $request)
    {
        $pageId = $request->input('page_id');
        $filters = $request->only(['date_from', 'date_to', 'post_type', 'status', 'search']);
        
        $data = [
            'posts' => $this->facebookDataService->getPostsByPage($pageId, $filters),
            'spending_stats' => $this->facebookDataService->getPostSpendingStats($pageId, $filters['date_from'] ?? null, $filters['date_to'] ?? null),
            'page_summary' => $this->facebookDataService->getPageSummary($pageId),
            'breakdowns' => [
                'device' => $this->facebookDataService->getDeviceBreakdown($pageId, $filters['date_from'] ?? null, $filters['date_to'] ?? null),
                'region' => $this->facebookDataService->getRegionBreakdown($pageId, $filters['date_from'] ?? null, $filters['date_to'] ?? null),
                'gender_age' => $this->facebookDataService->getGenderAgeBreakdown($pageId, $filters['date_from'] ?? null, $filters['date_to'] ?? null),
                'correlation' => $this->facebookDataService->getBudgetResultConversionCorrelation($pageId, $filters['date_from'] ?? null, $filters['date_to'] ?? null),
            ],
        ];
        
        return response()->json($data);
    }

    /**
     * API: Lấy tóm tắt AI theo góc nhìn marketing quản lý
     */
    public function getAiSummary(Request $request)
    {
        $pageId = (string) $request->input('page_id');
        $postId = (string) $request->input('post_id');
        $since = $request->input('date_from');
        $until = $request->input('date_to');
        $frontendMetrics = $request->input('metrics', []);

        try {
            // 1) Lấy khoảng ngày hợp lệ theo dữ liệu có thật của post
            $dateBounds = \App\Models\FacebookAdInsight::query()
                ->when($pageId, fn($q) => $q->where('page_id', $pageId))
                ->when($postId, fn($q) => $q->where('post_id', $postId))
                ->selectRaw('MIN(date) as min_date, MAX(date) as max_date')
                ->first();
            $since = $since ?: ($dateBounds->min_date ?? null);
            $until = $until ?: ($dateBounds->max_date ?? null);

            // 2) Tổng hợp số liệu chính từ facebook_ad_insights cho post
            $agg = \App\Models\FacebookAdInsight::query()
                ->when($pageId, fn($q) => $q->where('page_id', $pageId))
                ->when($postId, fn($q) => $q->where('post_id', $postId))
                ->when($since && $until, fn($q) => $q->whereBetween('date', [$since, $until]))
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

            // 3) Lấy breakdowns/insights/actions giống màn chi tiết
            $service = $this->facebookDataService;
            $breakdowns = $service->getPostBreakdowns($postId, $pageId);
            $detailedBreakdowns = $service->getPostDetailedBreakdowns($postId, $pageId);
            $insights = $service->getPostInsights($postId, $pageId);
            $actions = $service->getPostActions($postId, $pageId);

            // Chuẩn hoá breakdowns giống overview để AI hiểu – và rút gọn (tránh quá tải)
            $summarize = function(array $data) {
                $result = [];
                foreach ($data as $type => $items) {
                    if (!is_array($items)) continue;
                    $list = [];
                    foreach ($items as $name => $m) {
                        if (!is_array($m)) continue;
                        $list[] = [
                            'name' => (string) $name,
                            'spend' => (float) ($m['spend'] ?? 0),
                            'impressions' => (int) ($m['impressions'] ?? 0),
                            'clicks' => (int) ($m['clicks'] ?? 0),
                            'video_views' => (int) ($m['video_views'] ?? 0),
                            'conversions' => (int) ($m['conversions'] ?? 0),
                        ];
                    }
                    usort($list, fn($a,$b) => ($b['spend'] <=> $a['spend']));
                    $topSpend = array_slice($list, 0, 5);
                    usort($list, fn($a,$b) => ($b['video_views'] <=> $a['video_views']));
                    $topVideo = array_slice($list, 0, 5);
                    $result[$type] = [
                        'top_by_spend' => $topSpend,
                        'top_by_video_views' => $topVideo,
                        'count' => count($list),
                    ];
                }
                return $result;
            };
            $breakdownsSummary = $summarize($breakdowns);

            // Gom nhóm thiết bị/khu vực/giới tính-độ tuổi/placement như overview
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
                        'video_views' => 0,
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
                if (!empty($breakdowns[$k]) && is_array($breakdowns[$k])) {
                    foreach ($breakdowns[$k] as $value => $m) { $sumInto($devices, (string)($value ?: 'unknown'), (array)$m); }
                }
            }

            $regions = [];
            if (!empty($breakdowns['region'])) {
                foreach ($breakdowns['region'] as $value => $m) { $sumInto($regions, (string)($value ?: 'unknown'), (array)$m); }
            }
            $countries = [];
            if (!empty($breakdowns['country'])) {
                foreach ($breakdowns['country'] as $value => $m) { $sumInto($countries, (string)($value ?: 'unknown'), (array)$m); }
            }
            $ages = [];
            if (!empty($breakdowns['age'])) {
                foreach ($breakdowns['age'] as $value => $m) { $sumInto($ages, (string)($value ?: 'unknown'), (array)$m); }
            }
            $genders = [];
            if (!empty($breakdowns['gender'])) {
                foreach ($breakdowns['gender'] as $value => $m) { $sumInto($genders, (string)($value ?: 'unknown'), (array)$m); }
            }

            $placements = [ 'publisher_platform' => [], 'platform_position' => [], 'impression_device' => [] ];
            foreach (['publisher_platform','platform_position','impression_device'] as $k) {
                if (!empty($breakdowns[$k]) && is_array($breakdowns[$k])) {
                    foreach ($breakdowns[$k] as $value => $m) { $sumInto($placements[$k], (string)($value ?: 'unknown'), (array)$m); }
                }
            }

            $computeTopWorst = function(array $bucket, string $by = 'spend', int $limit = 5) {
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

            // 4) Chuẩn bị payload phong phú gửi AI
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
                // Chuẩn hoá 'breakdowns' theo đúng cấu trúc mà prompt của AI mong đợi (giống overview)
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
                'detailedBreakdowns' => $detailedBreakdowns,
                'breakdowns_summary' => $breakdownsSummary,
                // Lưu bản thô để debug
                'breakdowns_raw' => $breakdowns,
                'insights' => $insights,
                'actions' => $actions,
                // Chỉ giữ phần cần thiết từ frontend để tránh quá tải prompt
                'frontend_breakdowns' => [
                    'summary' => $frontendMetrics['summary'] ?? [],
                    'video' => $frontendMetrics['video'] ?? [],
                ],
            ];

            $summary = app(\App\Services\GeminiService::class)
                ->generateMarketingSummary($pageId ?: 'post-detail', $since, $until, $metrics);

            if ($request->boolean('debug')) {
                return response()->json([
                    'ok' => true,
                    'summary' => $summary,
                    'ai_raw' => $summary, // raw text currently same as summary
                    'metrics_sent' => $metrics,
                    'since' => $since,
                    'until' => $until,
                    'env_key_present' => (bool) (env('GEMINI_API_KEY') ?: config('services.gemini.api_key')),
                    'has_video' => (bool) (($metrics['video']['views'] ?? 0) || ($metrics['video']['plays'] ?? 0)),
                    'has_breakdowns' => !empty($breakdowns),
                ]);
            }

            return response()->json(['summary' => $summary, 'since' => $since, 'until' => $until]);
        } catch (\Throwable $e) {
            return response()->json(['summary' => 'Không tạo được nhận định AI lúc này.'], 200);
        }
    }

    /**
     * API endpoint để lấy thống kê chi phí theo bài viết
     */
    public function getPostSpendingStats(Request $request)
    {
        $pageId = $request->input('page_id');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        
        $stats = $this->facebookDataService->getPostSpendingStats($pageId, $dateFrom, $dateTo);
        
        return response()->json($stats);
    }

    /**
     * API endpoint để lấy chi tiết chiến dịch quảng cáo
     */
    public function getAdCampaigns(Request $request)
    {
        $postId = $request->get('post_id');
        $pageId = $request->get('page_id');
        
        if (!$postId || !$pageId) {
            return response()->json(['error' => 'Missing post_id or page_id'], 400);
        }
        
        $data = $this->facebookDataService->getAdCampaigns($postId, $pageId);
        return response()->json($data);
    }

    public function getAdBreakdowns(Request $request)
    {
        $adId = $request->get('ad_id');
        
        if (!$adId) {
            return response()->json(['error' => 'Missing ad_id'], 400);
        }
        
        $data = $this->facebookDataService->getAdBreakdowns($adId);
        return response()->json($data);
    }

    public function getAdInsights(Request $request)
    {
        $adId = $request->get('ad_id');
        
        if (!$adId) {
            return response()->json(['error' => 'Missing ad_id'], 400);
        }
        
        $data = $this->facebookDataService->getAdInsights($adId);
        return response()->json($data);
    }

    public function showPostDetail(string $postId, string $pageId)
    {
        try {
            // Lấy thông tin post
            $post = $this->facebookDataService->getPostById($postId, $pageId);
            
            if (!$post) {
                abort(404, 'Post không tồn tại');
            }
            
            // Lấy breakdown data
            $breakdowns = $this->facebookDataService->getPostBreakdowns($postId, $pageId);
            $detailedBreakdowns = $this->facebookDataService->getPostDetailedBreakdowns($postId, $pageId);
            
            // Lấy insights data
            $insights = $this->facebookDataService->getPostInsights($postId, $pageId);
          
            
            // Lấy actions data
            $actions = $this->facebookDataService->getPostActions($postId, $pageId);
            
            return view('facebook.data-management.post-detail', [
                'post' => $post,
                'breakdowns' => $breakdowns,
                'detailedBreakdowns' => $detailedBreakdowns,
                'insights' => $insights,
                'actions' => $actions,
                'pageId' => $pageId
            ]);
            
        } catch (\Exception $e) {
            abort(500, 'Lỗi khi tải dữ liệu post: ' . $e->getMessage());
        }
    }
} 