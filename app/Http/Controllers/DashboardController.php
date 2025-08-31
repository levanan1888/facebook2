<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\FacebookAd;
use App\Models\FacebookAdAccount;
use App\Models\FacebookAdSet;
use App\Models\FacebookBusiness;
use App\Models\FacebookCampaign;
use App\Models\FacebookAdInsight;
use App\Models\DashboardReport;
use App\Models\DashboardCache;
use App\Services\UnifiedDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use App\Services\FacebookAdsService;
use App\Services\FacebookAdsSyncService;

class DashboardController extends Controller
{

    public function index(Request $request): View
    {
        $tab = $request->get('tab', 'overview');
        
        // Tạm thời sử dụng dữ liệu trực tiếp thay vì service
        $data = match ($tab) {
            'overview' => $this->getCrossPlatformOverviewData($request),
            'fb-overview' => $this->getFacebookOverviewData($request),
            'unified-data' => $this->getUnifiedData(),
            'data-raw' => $this->getRawData(),
            'hierarchy' => $this->getHierarchyData(),
            'analytics' => $this->getAnalyticsData(),
            'comparison' => $this->getComparisonData(),
            default => $this->getCrossPlatformOverviewData($request),
        };

        return view('dashboard', compact('data', 'tab'));
    }

    private function getFacebookOverviewData(Request $request): array
    {
        $from = $request->get('from', now()->subDays(29)->toDateString());
        $to = $request->get('to', now()->toDateString());

        // Tổng quan tổng hợp
        $totals = [
            'businesses' => FacebookBusiness::count(),
            'accounts' => FacebookAdAccount::count(),
            'campaigns' => FacebookCampaign::count(),
            'adsets' => FacebookAdSet::count(),
            'ads' => FacebookAd::count(),
            // Đếm từ FacebookAd để không phụ thuộc bảng pages/posts
            'pages' => FacebookAd::whereNotNull('page_id')->distinct('page_id')->count(),
            'posts' => FacebookAd::whereNotNull('post_id')->distinct('post_id')->count(),
            'insights' => FacebookAdInsight::count(),
        ];

        // Lấy Ad IDs đã filter
        $filteredAdIds = collect();
        if ($request->has('account_id') || $request->has('campaign_id')) {
            $adQuery = FacebookAd::query();
            
            if ($request->has('account_id')) {
                $adQuery->whereHas('campaign', function ($q) use ($request) {
                    $q->where('ad_account_id', $request->get('account_id'));
                });
            }
            
            if ($request->has('campaign_id')) {
                $adQuery->where('campaign_id', $request->get('campaign_id'));
            }
            
            $filteredAdIds = $adQuery->pluck('id');
        }

        // Thống kê theo ngày
        $last7Days = collect(range(6, 0))->map(function ($daysAgo) use ($from, $to, $filteredAdIds) {
            $date = now()->subDays($daysAgo)->toDateString();
            
            $adQuery = FacebookAd::query()
                ->whereDate('last_insights_sync', $date);
            
            if ($filteredAdIds->isNotEmpty()) {
                $adQuery->whereIn('id', $filteredAdIds);
            }
            
            // Lấy spend từ facebook_ad_insights thay vì ad_spend
            $spend = FacebookAdInsight::whereDate('date', $date)
                ->when($filteredAdIds->isNotEmpty(), function ($query) use ($filteredAdIds) {
                    $query->whereIn('ad_id', $filteredAdIds);
                })
                ->sum('spend');
            
            return [
                'date' => $date,
                'ads' => $adQuery->count(),
                'posts' => $adQuery->whereNotNull('post_id')->count(),
                'campaigns' => $adQuery->distinct('campaign_id')->count(),
                'spend' => $spend,
            ];
        });

        // Thống kê tổng hợp từ facebook_ad_insights
        $totalStats = FacebookAdInsight::query()
            ->when($filteredAdIds->isNotEmpty(), function ($query) use ($filteredAdIds) {
                $query->whereIn('ad_id', $filteredAdIds);
            })
            ->whereBetween('date', [$from, $to])
            ->selectRaw('
                COUNT(DISTINCT ad_id) as total_ads,
                SUM(spend) as total_spend,
                SUM(impressions) as total_impressions,
                SUM(clicks) as total_clicks,
                SUM(reach) as total_reach,
                AVG(ctr) as avg_ctr,
                AVG(cpc) as avg_cpc,
                AVG(cpm) as avg_cpm
            ')
            ->first();

        // Đếm posts và campaigns từ FacebookAd
        $adQuery = FacebookAd::query()
            ->when($filteredAdIds->isNotEmpty(), function ($query) use ($filteredAdIds) {
                $query->whereIn('id', $filteredAdIds);
            })
            ->whereBetween('last_insights_sync', [$from, $to]);

        $totalPosts = $adQuery->whereNotNull('post_id')->distinct('post_id')->count();
        $totalCampaigns = $adQuery->distinct('campaign_id')->count();

        return [
            'filters' => ['from' => $from, 'to' => $to],
            'totals' => $totals,
            'last7Days' => $last7Days,
            'stats' => [
                'total_ads' => $totalStats->total_ads ?? 0,
                'total_posts' => $totalPosts,
                'total_campaigns' => $totalCampaigns,
                'total_spend' => $totalStats->total_spend ?? 0,
                'total_impressions' => $totalStats->total_impressions ?? 0,
                'total_clicks' => $totalStats->total_clicks ?? 0,
                'total_reach' => $totalStats->total_reach ?? 0,
                'avg_ctr' => $totalStats->avg_ctr ?? 0,
                'avg_cpc' => $totalStats->avg_cpc ?? 0,
                'avg_cpm' => $totalStats->avg_cpm ?? 0,
            ],
        ];
    }

    private function getCrossPlatformOverviewData(Request $request): array
    {
        $from = $request->get('from', now()->subDays(29)->toDateString());
        $to = $request->get('to', now()->toDateString());

        // Facebook data từ bảng facebook_ad_insights
        $fb = FacebookAdInsight::query()
            ->whereBetween('date', [$from, $to])
            ->selectRaw('
                SUM(spend) as spend,
                SUM(impressions) as impressions,
                SUM(clicks) as clicks,
                SUM(reach) as reach,
                AVG(ctr) as ctr,
                AVG(cpc) as cpc,
                AVG(cpm) as cpm
            ')
            ->first();

        $fb = [
            'spend' => $fb->spend ?? 0,
            'impressions' => $fb->impressions ?? 0,
            'clicks' => $fb->clicks ?? 0,
            'reach' => $fb->reach ?? 0,
            'ctr' => $fb->ctr ?? 0,
            'cpc' => $fb->cpc ?? 0,
            'cpm' => $fb->cpm ?? 0,
        ];

        // Placeholder for Google/TikTok (0 nếu chưa kết nối)
        $google = ['spend' => 0, 'impressions' => 0, 'clicks' => 0, 'reach' => 0, 'ctr' => 0];
        $tiktok = ['spend' => 0, 'impressions' => 0, 'clicks' => 0, 'reach' => 0, 'ctr' => 0];

        $totals = [
            'spend' => $fb['spend'] + $google['spend'] + $tiktok['spend'],
            'impressions' => $fb['impressions'] + $google['impressions'] + $tiktok['impressions'],
            'clicks' => $fb['clicks'] + $google['clicks'] + $tiktok['clicks'],
            'reach' => $fb['reach'] + $google['reach'] + $tiktok['reach'],
        ];

        $series = collect(range(6, 0))->map(function ($d) use ($from, $to) {
            $date = now()->subDays($d)->toDateString();
            $spend = ($date >= $from && $date <= $to) ? 
                (float) FacebookAdInsight::whereDate('date', $date)->sum('spend') : 0;
            return ['date' => $date, 'spend' => $spend];
        });

        return [
            'filters' => ['from' => $from, 'to' => $to],
            'platforms' => ['facebook' => $fb, 'google' => $google, 'tiktok' => $tiktok],
            'totals' => $totals,
            'series' => $series,
        ];
    }

    private function getRawData(): array
    {
        // Tổng số từ bảng FacebookAd làm trung tâm
        $totalsRaw = [
            'businesses' => FacebookBusiness::count(),
            'accounts' => FacebookAdAccount::count(),
            'campaigns' => FacebookCampaign::count(),
            'adsets' => FacebookAdSet::count(),
            'ads' => FacebookAd::count(),
            'pages' => FacebookAd::whereNotNull('page_id')->distinct('page_id')->count(),
            'posts' => FacebookAd::whereNotNull('post_id')->distinct('post_id')->count(),
            'insights' => FacebookAd::whereNotNull('last_insights_sync')->count(),
        ];

        // Dữ liệu từ bảng FacebookAd với các thông tin liên quan
        $rawData = [
            'businesses' => FacebookBusiness::withCount('adAccounts')->latest('created_at')->limit(10)->get(),
            'accounts' => FacebookAdAccount::withCount('campaigns')->latest('created_at')->limit(20)->get(),
            'campaigns' => FacebookCampaign::with(['adAccount:id,name'])->latest('created_at')->limit(50)->get(),
            'adsets' => FacebookAdSet::with(['campaign:id,name'])->latest('created_at')->limit(50)->get(),
            'ads' => FacebookAd::with(['campaign:id,name', 'adSet:id,name'])
                ->whereNotNull('post_id') // Chỉ lấy ads có post
                ->latest('last_insights_sync')
                ->limit(100)
                ->get(),
            'pages' => FacebookAd::select('page_id', 'post_message as name', 'post_type as category')
                ->whereNotNull('page_id')
                ->distinct('page_id')
                ->limit(50)
                ->get(),
            'posts' => FacebookAd::select(
                'post_id',
                'post_message',
                'post_type',
                'post_created_time',
                'post_likes',
                'post_shares',
                'post_comments',
                'post_reactions',
                'post_impressions',
                'post_reach',
                'post_clicks'
            )
                ->whereNotNull('post_id')
                ->latest('post_created_time')
                ->limit(100)
                ->get(),
        ];

        return [
            'totals' => $totalsRaw,
            'data' => $rawData,
        ];
    }

    private function getHierarchyData(): array
    {
        return [
            'businesses' => FacebookBusiness::withCount('adAccounts')->get(),
            'totalAccounts' => FacebookAdAccount::count(),
            'totalCampaigns' => FacebookCampaign::count(),
            'totalAdSets' => FacebookAdSet::count(),
            'totalAds' => FacebookAd::count(),
            'totalPosts' => FacebookAd::whereNotNull('post_id')->distinct('post_id')->count(),
        ];
    }

    private function getAnalyticsData(): array
    {
        // Dữ liệu analytics từ bảng FacebookAdInsight
        $stats = FacebookAdInsight::selectRaw('
            SUM(spend) as total_spend,
            SUM(impressions) as total_impressions,
            SUM(clicks) as total_clicks,
            SUM(reach) as total_reach,
            AVG(ctr) as avg_ctr,
            AVG(cpc) as avg_cpc,
            AVG(cpm) as avg_cpm
        ')->first();

        return [
            'totalSpend' => $stats->total_spend ?? 0,
            'totalImpressions' => $stats->total_impressions ?? 0,
            'totalClicks' => $stats->total_clicks ?? 0,
            'totalReach' => $stats->total_reach ?? 0,
            'avgCTR' => $stats->avg_ctr ?? 0,
            'avgCPC' => $stats->avg_cpc ?? 0,
            'avgCPM' => $stats->avg_cpm ?? 0,
        ];
    }

    /**
     * Debug method để kiểm tra dữ liệu campaigns
     */
    public function debugCampaigns(): \Illuminate\Http\JsonResponse
    {
        try {
            $campaigns = FacebookCampaign::with(['adAccount:id,name'])
                ->latest('created_at')
                ->limit(10)
                ->get();

            $totalCampaigns = FacebookCampaign::count();
            $totalAdAccounts = FacebookAdAccount::count();

            return response()->json([
                'success' => true,
                'campaigns' => $campaigns,
                'totalCampaigns' => $totalCampaigns,
                'totalAdAccounts' => $totalAdAccounts,
            ]);
        } catch (\Exception $e) {
            Log::error('Debug campaigns error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function getUnifiedData(): array
    {
        // Dữ liệu tổng hợp từ bảng FacebookAd và FacebookAdInsight
        $adStats = FacebookAd::selectRaw('
            COUNT(*) as total_ads,
            COUNT(DISTINCT post_id) as total_posts,
            COUNT(DISTINCT page_id) as total_pages,
            COUNT(DISTINCT campaign_id) as total_campaigns,
            COUNT(DISTINCT adset_id) as total_adsets,
            COUNT(DISTINCT account_id) as total_accounts
        ')->first();

        $insightStats = FacebookAdInsight::selectRaw('
            SUM(spend) as total_spend,
            SUM(impressions) as total_impressions,
            SUM(clicks) as total_clicks,
            SUM(reach) as total_reach,
            AVG(ctr) as avg_ctr,
            AVG(cpc) as avg_cpc,
            AVG(cpm) as avg_cpm
        ')->first();

        return [
            'totals' => [
                'ads' => $adStats->total_ads ?? 0,
                'posts' => $adStats->total_posts ?? 0,
                'pages' => $adStats->total_pages ?? 0,
                'campaigns' => $adStats->total_campaigns ?? 0,
                'adsets' => $adStats->total_adsets ?? 0,
                'accounts' => $adStats->total_accounts ?? 0,
            ],
            'metrics' => [
                'spend' => $insightStats->total_spend ?? 0,
                'impressions' => $insightStats->total_impressions ?? 0,
                'clicks' => $insightStats->total_clicks ?? 0,
                'reach' => $insightStats->total_reach ?? 0,
                'ctr' => $insightStats->avg_ctr ?? 0,
                'cpc' => $insightStats->avg_cpc ?? 0,
                'cpm' => $insightStats->avg_cpm ?? 0,
            ],
        ];
    }

    private function getComparisonData(): array
    {
        // Dữ liệu so sánh từ bảng FacebookAdInsight
        $last7Days = collect(range(6, 0))->map(function ($daysAgo) {
            $date = now()->subDays($daysAgo)->toDateString();
            $stats = FacebookAdInsight::whereDate('date', $date)
                ->selectRaw('
                    COUNT(DISTINCT ad_id) as ads_count,
                    SUM(spend) as spend,
                    SUM(impressions) as impressions,
                    SUM(clicks) as clicks,
                    SUM(reach) as reach
                ')
                ->first();

            // Đếm posts từ FacebookAd
            $postsCount = FacebookAd::whereDate('last_insights_sync', $date)
                ->whereNotNull('post_id')
                ->distinct('post_id')
                ->count();

            return [
                'date' => $date,
                'ads' => $stats->ads_count ?? 0,
                'posts' => $postsCount,
                'spend' => $stats->spend ?? 0,
                'impressions' => $stats->impressions ?? 0,
                'clicks' => $stats->clicks ?? 0,
                'reach' => $stats->reach ?? 0,
            ];
        });

        return [
            'last7Days' => $last7Days,
            'summary' => [
                'total_ads' => $last7Days->sum('ads'),
                'total_posts' => $last7Days->sum('posts'),
                'total_spend' => $last7Days->sum('spend'),
                'total_impressions' => $last7Days->sum('impressions'),
                'total_clicks' => $last7Days->sum('clicks'),
                'total_reach' => $last7Days->sum('reach'),
            ],
        ];
    }

    public function syncFacebook(Request $request)
    {
        if (empty(config('services.facebook.ads_token'))) {
            return redirect()->route('dashboard', ['tab' => 'data-raw'])
                ->with('error', 'Vui lòng cấu hình FACEBOOK_ADS_TOKEN trong .env');
        }

        $api = new FacebookAdsService();
        $sync = new FacebookAdsSyncService($api);
        $result = $sync->syncYesterday();

        return redirect()->route('dashboard', ['tab' => 'data-raw'])
            ->with('success', 'Đồng bộ thành công')
            ->with('sync_result', $result);
    }
} 