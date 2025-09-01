<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\FacebookAd;
use App\Models\FacebookAdAccount;
use App\Models\FacebookAdSet;
use App\Models\FacebookBusiness;
use App\Models\FacebookCampaign;
use App\Models\FacebookAdInsight;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class UnifiedDataService
{
    public function getUnifiedData(): array
    {
        $startDate = now()->subDays(30)->toDateString();
        
        // Sử dụng FacebookAd làm trung tâm
        $totals = [
            'businesses' => FacebookBusiness::count(),
            'accounts' => FacebookAdAccount::count(),
            'campaigns' => FacebookCampaign::count(),
            'adsets' => FacebookAdSet::count(),
            'ads' => FacebookAd::count(),
            'pages' => FacebookAd::whereNotNull('page_id')->distinct('page_id')->count(),
            'posts' => FacebookAd::whereNotNull('post_id')->distinct('post_id')->count(),
            'insights' => FacebookAd::whereNotNull('last_insights_sync')->count(),
            'spend' => \App\Models\FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
                ->where('facebook_ads.last_insights_sync', '>=', $startDate)
                ->sum('facebook_ad_insights.spend') ?? 0,
            'impressions' => \App\Models\FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
                ->where('facebook_ads.last_insights_sync', '>=', $startDate)
                ->sum('facebook_ad_insights.impressions') ?? 0,
            'clicks' => \App\Models\FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
                ->where('facebook_ads.last_insights_sync', '>=', $startDate)
                ->sum('facebook_ad_insights.clicks') ?? 0,
            'reach' => \App\Models\FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
                ->where('facebook_ads.last_insights_sync', '>=', $startDate)
                ->sum('facebook_ad_insights.reach') ?? 0,
            'ctr' => \App\Models\FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
                ->where('facebook_ads.last_insights_sync', '>=', $startDate)
                ->avg('facebook_ad_insights.ctr') ?? 0,
            'cpc' => \App\Models\FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
                ->where('facebook_ads.last_insights_sync', '>=', $startDate)
                ->avg('facebook_ad_insights.cpc') ?? 0,
            'cpm' => \App\Models\FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
                ->where('facebook_ads.last_insights_sync', '>=', $startDate)
                ->avg('facebook_ad_insights.cpm') ?? 0,
        ];

        // Time series data
        $timeSeries = collect(range(6, 0))->map(function ($daysAgo) {
            $date = now()->subDays($daysAgo)->toDateString();
            return [
                'date' => $date,
                'spend' => \App\Models\FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
                    ->whereDate('facebook_ad_insights.date', $date)
                    ->sum('facebook_ad_insights.spend') ?? 0,
                'impressions' => \App\Models\FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
                    ->whereDate('facebook_ad_insights.date', $date)
                    ->sum('facebook_ad_insights.impressions') ?? 0,
                'clicks' => \App\Models\FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
                    ->whereDate('facebook_ad_insights.date', $date)
                    ->sum('facebook_ad_insights.clicks') ?? 0,
                'reach' => \App\Models\FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
                    ->whereDate('facebook_ad_insights.date', $date)
                    ->sum('facebook_ad_insights.reach') ?? 0,
                'posts' => FacebookAd::whereDate('last_insights_sync', $date)->whereNotNull('post_id')->count(),
            ];
        });

        // Top posts - Sử dụng dữ liệu từ facebook_ads với cấu trúc bảng thực tế
        $topPosts = \App\Models\FacebookAd::select([
                'facebook_ads.id as ad_id', 
                'facebook_ads.name as ad_name',
                'facebook_ads.post_id',
                'facebook_ads.post_meta',
                'facebook_ads.page_id',
                'facebook_ads.page_meta',
                'facebook_ads.creative_json',
                'facebook_ads.last_insights_sync'
            ])
            ->whereNotNull('post_id')
            ->whereNotNull('post_meta')
            ->orderBy('facebook_ads.last_insights_sync', 'DESC')
            ->limit(10)
            ->get()
            ->map(function ($ad) {
                // Parse post_meta để lấy thông tin post
                $postMeta = json_decode($ad->post_meta, true) ?: [];
                $creativeData = json_decode($ad->creative_json, true) ?: [];
                
                return [
                    'ad_id' => $ad->ad_id,
                    'ad_name' => $ad->ad_name,
                    'post_id' => $ad->post_id,
                    'post_message' => $postMeta['message'] ?? $creativeData['message'] ?? 'Không có nội dung',
                    'post_type' => $postMeta['type'] ?? $creativeData['type'] ?? 'unknown',
                    'post_likes' => $postMeta['likes_count'] ?? 0,
                    'post_shares' => $postMeta['shares_count'] ?? 0,
                    'post_comments' => $postMeta['comments_count'] ?? 0,
                    'page_id' => $ad->page_id,
                    'last_sync' => $ad->last_insights_sync
                ];
            });

        return [
            'totals' => $totals,
            'timeSeries' => $timeSeries,
            'topPosts' => $topPosts,
        ];
    }

    public function getComparisonData(): array
    {
        $startDate = now()->subDays(30)->toDateString();
        $previousStartDate = now()->subDays(60)->toDateString();
        
        // Current period
        $current = [
            'spend' => \App\Models\FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
                ->where('facebook_ads.last_insights_sync', '>=', $startDate)
                ->sum('facebook_ad_insights.spend') ?? 0,
            'impressions' => \App\Models\FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
                ->where('facebook_ads.last_insights_sync', '>=', $startDate)
                ->sum('facebook_ad_insights.impressions') ?? 0,
            'clicks' => \App\Models\FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
                ->where('facebook_ads.last_insights_sync', '>=', $startDate)
                ->sum('facebook_ad_insights.clicks') ?? 0,
            'reach' => \App\Models\FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
                ->where('facebook_ads.last_insights_sync', '>=', $startDate)
                ->sum('facebook_ad_insights.reach') ?? 0,
        ];

        // Previous period
        $previous = [
            'spend' => \App\Models\FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
                ->where('facebook_ads.last_insights_sync', '>=', $previousStartDate)
                ->where('facebook_ads.last_insights_sync', '<', $startDate)
                ->sum('facebook_ad_insights.spend') ?? 0,
            'impressions' => \App\Models\FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
                ->where('facebook_ads.last_insights_sync', '>=', $previousStartDate)
                ->where('facebook_ads.last_insights_sync', '<', $startDate)
                ->sum('facebook_ad_insights.impressions') ?? 0,
            'clicks' => \App\Models\FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
                ->where('facebook_ads.last_insights_sync', '>=', $previousStartDate)
                ->where('facebook_ads.last_insights_sync', '<', $startDate)
                ->sum('facebook_ad_insights.clicks') ?? 0,
            'reach' => \App\Models\FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
                ->where('facebook_ads.last_insights_sync', '>=', $previousStartDate)
                ->where('facebook_ads.last_insights_sync', '<', $startDate)
                ->sum('facebook_ad_insights.reach') ?? 0,
        ];

        return [
            'current' => $current,
            'previous' => $previous,
        ];
    }

    public function getFilteredData(array $filters): array
    {
        $query = FacebookAd::query();

        if (isset($filters['date_from'])) {
            $query->where('last_insights_sync', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('last_insights_sync', '<=', $filters['date_to']);
        }

        if (isset($filters['account_id'])) {
            $query->where('account_id', $filters['account_id']);
        }

        if (isset($filters['campaign_id'])) {
            $query->where('campaign_id', $filters['campaign_id']);
        }

        $data = $query->get();

        return [
            'total_ads' => $data->count(),
            'total_posts' => $data->whereNotNull('post_id')->count(),
            'total_spend' => \App\Models\FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
                ->whereIn('facebook_ads.id', $data->pluck('id'))
                ->sum('facebook_ad_insights.spend'),
            'total_impressions' => \App\Models\FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
                ->whereIn('facebook_ads.id', $data->pluck('id'))
                ->sum('facebook_ad_insights.impressions'),
            'avg_ctr' => \App\Models\FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
                ->whereIn('facebook_ads.id', $data->pluck('id'))
                ->avg('facebook_ad_insights.ctr'),
            'data' => $data,
        ];
    }

    public function getDataSourcesStatus(): array
    {
        return [
            'sources' => [
                'facebook' => [
                    'connected' => true,
                    'last_sync' => FacebookAd::max('last_insights_sync'),
                    'data_count' => FacebookAd::count(),
                ],
                'google' => [
                    'connected' => false,
                    'last_sync' => null,
                    'data_count' => 0,
                ],
                'tiktok' => [
                    'connected' => false,
                    'last_sync' => null,
                    'data_count' => 0,
                ],
            ]
        ];
    }

    public function getDailyStats(string $date): array
    {
        $insights = \App\Models\FacebookAdInsight::whereDate('date', $date)->first();
        $posts = \App\Models\FacebookAd::whereDate('last_insights_sync', $date)
            ->whereNotNull('post_id')
            ->get();

        return [
            'date' => $date,
            'insights' => $insights ? [
                'spend' => $insights->spend ?? 0,
                'impressions' => $insights->impressions ?? 0,
                'clicks' => $insights->clicks ?? 0,
                'reach' => $insights->reach ?? 0,
            ] : null,
            'posts' => $posts->map(function ($post) {
                $postMeta = json_decode($post->post_meta, true) ?: [];
                $creativeData = json_decode($post->creative_json, true) ?: [];
                
                return [
                    'post_id' => $post->post_id,
                    'message' => $postMeta['message'] ?? $creativeData['message'] ?? 'Không có nội dung',
                    'type' => $postMeta['type'] ?? $creativeData['type'] ?? 'unknown',
                    'likes' => $postMeta['likes_count'] ?? 0,
                    'shares' => $postMeta['shares_count'] ?? 0,
                    'comments' => $postMeta['comments_count'] ?? 0,
                ];
            }),
        ];
    }

    public function getAnalyticsSummary(): array
    {
        $startDate = now()->subDays(30)->toDateString();
        
        return [
            'total_spend' => \App\Models\FacebookAdInsight::where('date', '>=', $startDate)->sum('spend') ?? 0,
            'total_impressions' => \App\Models\FacebookAdInsight::where('date', '>=', $startDate)->sum('impressions') ?? 0,
            'avg_ctr' => \App\Models\FacebookAdInsight::where('date', '>=', $startDate)->avg('ctr') ?? 0,
            'total_posts' => \App\Models\FacebookAd::where('last_insights_sync', '>=', $startDate)
                ->whereNotNull('post_id')
                ->count(),
            'total_engagement' => \App\Models\FacebookAd::where('last_insights_sync', '>=', $startDate)
                ->whereNotNull('post_id')
                ->get()
                ->sum(function ($ad) {
                    $postMeta = json_decode($ad->post_meta, true) ?: [];
                    return ($postMeta['likes_count'] ?? 0) + ($postMeta['shares_count'] ?? 0) + ($postMeta['comments_count'] ?? 0);
                }),
        ];
    }

    public function getInsightsData(array $filters = []): array
    {
        $query = FacebookAd::query();

        if (isset($filters['date_from'])) {
            $query->where('last_insights_sync', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('last_insights_sync', '<=', $filters['date_to']);
        }

        return $query->get()->toArray();
    }
}
