<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\FacebookAd;
use App\Models\FacebookAdAccount;
use App\Models\FacebookAdSet;
use App\Models\FacebookBusiness;
use App\Models\FacebookCampaign;
use App\Models\FacebookPost;
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

        // Top posts - Sử dụng dữ liệu từ facebook_posts thay vì các cột không tồn tại
        $topPosts = \App\Models\FacebookPost::select([
                'facebook_posts.id as post_id', 
                'facebook_posts.message as post_message', 
                'facebook_posts.type as post_type',
                'facebook_posts.likes_count as post_likes',
                'facebook_posts.shares_count as post_shares',
                'facebook_posts.comments_count as post_comments'
            ])
            ->orderByRaw('(facebook_posts.likes_count + facebook_posts.shares_count + facebook_posts.comments_count) DESC')
            ->limit(10)
            ->get();

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
            'impressions' => \App\Models\FacebookAdInsight::join('facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
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
        ];
    }

    public function getDailyStats(string $date): array
    {
        $insights = \App\Models\FacebookAdInsight::whereDate('date', $date)->first();
        $posts = \App\Models\FacebookPost::whereDate('created_time', $date)->get();

        return [
            'date' => $date,
            'insights' => $insights ? [
                'spend' => $insights->spend ?? 0,
                'impressions' => $insights->impressions ?? 0,
                'clicks' => $insights->clicks ?? 0,
                'reach' => $insights->reach ?? 0,
            ] : null,
            'posts' => $posts->map(function ($post) {
                return [
                    'post_id' => $post->id,
                    'message' => $post->message,
                    'type' => $post->type,
                    'likes' => $post->likes_count ?? 0,
                    'shares' => $post->shares_count ?? 0,
                    'comments' => $post->comments_count ?? 0,
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
            'total_posts' => \App\Models\FacebookPost::where('created_time', '>=', $startDate)->count(),
            'total_engagement' => \App\Models\FacebookPost::where('created_time', '>=', $startDate)
                ->sum(\Illuminate\Support\Facades\DB::raw('likes_count + shares_count + comments_count')),
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
