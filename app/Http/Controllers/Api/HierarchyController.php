<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FacebookBusiness;
use App\Models\FacebookAdAccount;
use App\Models\FacebookCampaign;
use App\Models\FacebookAdSet;
use App\Models\FacebookAd;
use App\Models\FacebookAdInsight;
use App\Models\FacebookReportSummary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class HierarchyController extends Controller
{
    /**
     * Lấy danh sách Business Managers
     */
    public function getBusinesses(Request $request): JsonResponse
    {
        try {
            $from = $request->get('from');
            $to = $request->get('to');
            // Hiển thị ngày đồng bộ (created_at) và đếm số tài khoản quảng cáo
            $businesses = FacebookBusiness::withCount('adAccounts')
                ->orderBy('created_at', 'desc')
                ->get(['id', 'name', 'verification_status', 'created_at']);

            // Calculate overall totals across all businesses
            $totalAccounts = FacebookAdAccount::count();
            $totalCampaigns = FacebookCampaign::count();
            $totalAdSets = FacebookAdSet::count();
            $totalAds = FacebookAd::count();
            $totalPosts = FacebookAd::whereNotNull('post_id')->count();
            $totalPages = FacebookAd::whereNotNull('page_id')->distinct('page_id')->count('page_id');

            // Totals from insights in date range (spend, impressions, clicks, reach)
            $insights = FacebookAdInsight::query();
            if (!empty($from) && !empty($to)) {
                $insights->whereBetween('date', [$from, $to]);
            }
            $insTotals = $insights->selectRaw('
                    COALESCE(SUM(spend), 0) as total_spend,
                    COALESCE(SUM(impressions), 0) as total_impressions,
                    COALESCE(SUM(clicks), 0) as total_clicks,
                    COALESCE(SUM(reach), 0) as total_reach
                ')->first();

            return response()->json([
                'success' => true,
                'data' => $businesses,
                'pagination' => [
                    'total_accounts' => $totalAccounts,
                    'total_campaigns' => $totalCampaigns,
                    'total_adsets' => $totalAdSets,
                    'total_ads' => $totalAds,
                    'total_posts' => $totalPosts,
                    'total_pages' => $totalPages,
                    'total_spend' => (float) ($insTotals->total_spend ?? 0),
                    'total_impressions' => (int) ($insTotals->total_impressions ?? 0),
                    'total_clicks' => (int) ($insTotals->total_clicks ?? 0),
                    'total_reach' => (int) ($insTotals->total_reach ?? 0),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Lỗi khi tải Business Managers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy danh sách Ad Accounts theo Business Manager
     */
    public function getAccounts(Request $request): JsonResponse
    {
        try {
            $businessId = $request->get('businessId');
            $page = $request->get('page', 1);
            $perPage = $request->get('per_page', 10);
            $search = $request->get('search', '');
            $status = $request->get('status', '');
            $sort = $request->get('sort', 'created_at_desc');
            $groupBy = $request->get('group_by', '');
            $from = $request->get('from');
            $to = $request->get('to');
            
            if (!$businessId) {
                return response()->json([
                    'error' => 'Thiếu businessId parameter'
                ], 400);
            }

            $query = FacebookAdAccount::where('business_id', $businessId);
            
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('account_id', 'LIKE', "%{$search}%");
                });
            }
            
            if (!empty($status)) {
                $query->where('account_status', $status);
            }
            
            if (!empty($from) && !empty($to)) {
                $query->whereExists(function($q) use ($from, $to) {
                    $q->select(DB::raw(1))
                      ->from('facebook_ads')
                      ->join('facebook_ad_insights', 'facebook_ads.id', '=', 'facebook_ad_insights.ad_id')
                      ->whereColumn('facebook_ads.account_id', 'facebook_ad_accounts.id')
                      ->whereBetween('facebook_ad_insights.date', [$from, $to]);
                });
            }
            
            $sortParts = explode('_', $sort);
            $sortField = $sortParts[0];
            $sortDirection = isset($sortParts[1]) && in_array($sortParts[1], ['asc', 'desc']) ? $sortParts[1] : 'desc';
            $allowedSortFields = ['created_at', 'name', 'account_status', 'spend', 'impressions', 'clicks', 'ctr'];
            if (!in_array($sortField, $allowedSortFields)) {
                $sortField = 'created_at';
                $sortDirection = 'desc';
            }
            
            if (in_array($sortField, ['spend', 'impressions', 'clicks', 'ctr'])) {
                // For metrics sorting, we'll need to join with insights
                $query->leftJoin('facebook_ads', 'facebook_ad_accounts.id', '=', 'facebook_ads.account_id')
                      ->leftJoin('facebook_ad_insights', 'facebook_ads.id', '=', 'facebook_ad_insights.ad_id')
                      ->select('facebook_ad_accounts.*')
                      ->selectRaw('COALESCE(SUM(facebook_ad_insights.spend), 0) as total_spend')
                      ->selectRaw('COALESCE(SUM(facebook_ad_insights.impressions), 0) as total_impressions')
                      ->selectRaw('COALESCE(SUM(facebook_ad_insights.clicks), 0) as total_clicks')
                      ->selectRaw('CASE WHEN SUM(facebook_ad_insights.impressions) > 0 THEN (SUM(facebook_ad_insights.clicks) / SUM(facebook_ad_insights.impressions)) * 100 ELSE 0 END as ctr')
                      ->groupBy('facebook_ad_accounts.id');
                
                if ($sortField === 'ctr') {
                    $query->orderBy('ctr', $sortDirection);
                } else {
                    $query->orderBy('total_' . $sortField, $sortDirection);
                }
            } else {
                $query->orderBy('facebook_ad_accounts.' . $sortField, $sortDirection);
            }
            
            $accounts = $query->paginate($perPage, ['id', 'name', 'account_id', 'account_status', 'created_at', 'updated_at'], 'page', $page);

            // per-account counts + metrics (metrics via campaigns join to avoid account_id mismatch)
            $accounts->transform(function ($account) use ($from, $to) {
                // counts via insights (kept)
                $insightsCampaigns = FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
                    ->join('facebook_campaigns', 'facebook_ads.campaign_id', '=', 'facebook_campaigns.id')
                    ->where('facebook_campaigns.ad_account_id', $account->id);
                if (!empty($from) && !empty($to)) {
                    $insightsCampaigns->whereBetween('facebook_ad_insights.date', [$from, $to]);
                }
                $insightsCampaignsCount = (clone $insightsCampaigns)
                    ->distinct('facebook_campaigns.id')
                    ->count('facebook_campaigns.id');

                $insightsAdSets = FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
                    ->join('facebook_ad_sets', 'facebook_ads.adset_id', '=', 'facebook_ad_sets.id')
                    ->join('facebook_campaigns', 'facebook_ad_sets.campaign_id', '=', 'facebook_campaigns.id')
                    ->where('facebook_campaigns.ad_account_id', $account->id);
                if (!empty($from) && !empty($to)) {
                    $insightsAdSets->whereBetween('facebook_ad_insights.date', [$from, $to]);
                }
                $insightsAdSetsCount = (clone $insightsAdSets)
                    ->distinct('facebook_ad_sets.id')
                    ->count('facebook_ad_sets.id');

                $insightsAds = FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
                    ->join('facebook_campaigns', 'facebook_ads.campaign_id', '=', 'facebook_campaigns.id')
                    ->where('facebook_campaigns.ad_account_id', $account->id);
                if (!empty($from) && !empty($to)) {
                    $insightsAds->whereBetween('facebook_ad_insights.date', [$from, $to]);
                }
                $insightsAdsCount = (clone $insightsAds)
                    ->distinct('facebook_ads.id')
                    ->count('facebook_ads.id');

                $insightsPosts = FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
                    ->join('facebook_campaigns', 'facebook_ads.campaign_id', '=', 'facebook_campaigns.id')
                    ->where('facebook_campaigns.ad_account_id', $account->id)
                    ->whereNotNull('facebook_ads.post_id');
                if (!empty($from) && !empty($to)) {
                    $insightsPosts->whereBetween('facebook_ad_insights.date', [$from, $to]);
                }
                $insightsPostsCount = (clone $insightsPosts)
                    ->distinct('facebook_ads.post_id')
                    ->count('facebook_ads.post_id');

                $insightsPages = FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
                    ->join('facebook_campaigns', 'facebook_ads.campaign_id', '=', 'facebook_campaigns.id')
                    ->where('facebook_campaigns.ad_account_id', $account->id)
                    ->whereNotNull('facebook_ads.page_id');
                if (!empty($from) && !empty($to)) {
                    $insightsPages->whereBetween('facebook_ad_insights.date', [$from, $to]);
                }
                $insightsPagesCount = (clone $insightsPages)
                    ->distinct('facebook_ads.page_id')
                    ->count('facebook_ads.page_id');

                $account->campaigns_count = $insightsCampaignsCount > 0 ? $insightsCampaignsCount : FacebookCampaign::where('ad_account_id', $account->id)->count();
                $account->adsets_count = $insightsAdSetsCount > 0 ? $insightsAdSetsCount : FacebookAdSet::join('facebook_campaigns', 'facebook_ad_sets.campaign_id', '=', 'facebook_campaigns.id')->where('facebook_campaigns.ad_account_id', $account->id)->count();
                $account->ads_count = $insightsAdsCount > 0 ? $insightsAdsCount : FacebookAd::join('facebook_campaigns', 'facebook_ads.campaign_id', '=', 'facebook_campaigns.id')->where('facebook_campaigns.ad_account_id', $account->id)->count();
                $account->posts_count = $insightsPostsCount > 0 ? $insightsPostsCount : FacebookAd::join('facebook_campaigns', 'facebook_ads.campaign_id', '=', 'facebook_campaigns.id')->where('facebook_campaigns.ad_account_id', $account->id)->whereNotNull('post_id')->count();
                $account->pages_count = $insightsPagesCount > 0 ? $insightsPagesCount : FacebookAd::join('facebook_campaigns', 'facebook_ads.campaign_id', '=', 'facebook_campaigns.id')->where('facebook_campaigns.ad_account_id', $account->id)->whereNotNull('page_id')->distinct('page_id')->count('page_id');

                // metrics via campaigns join (sums by range)
                $metrics = FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
                    ->join('facebook_campaigns', 'facebook_ads.campaign_id', '=', 'facebook_campaigns.id')
                    ->where('facebook_campaigns.ad_account_id', $account->id);
                if (!empty($from) && !empty($to)) {
                    $metrics->whereBetween('facebook_ad_insights.date', [$from, $to]);
                }
                $metrics = $metrics->selectRaw('
                        COALESCE(SUM(facebook_ad_insights.spend), 0) as total_spend,
                        COALESCE(SUM(facebook_ad_insights.impressions), 0) as total_impressions,
                        COALESCE(SUM(facebook_ad_insights.clicks), 0) as total_clicks
                    ')->first();

                $account->total_spend = (float) ($metrics->total_spend ?? 0);
                $account->total_impressions = (int) ($metrics->total_impressions ?? 0);
                $account->total_clicks = (int) ($metrics->total_clicks ?? 0);
                $account->ctr = $account->total_impressions > 0 ? round(($account->total_clicks / $account->total_impressions) * 100, 2) : 0;

                // sync date
                $account->sync_date = $account->updated_at;
                
                return $account;
            });

            // overall totals (metrics) in selected range for the business
            $totalsQuery = FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
                ->join('facebook_ad_accounts', 'facebook_ads.account_id', '=', 'facebook_ad_accounts.id')
                ->where('facebook_ad_accounts.business_id', $businessId);
            if (!empty($from) && !empty($to)) {
                $totalsQuery->whereBetween('facebook_ad_insights.date', [$from, $to]);
            }
            $totals = $totalsQuery->selectRaw('
                    COALESCE(SUM(facebook_ad_insights.spend), 0) as total_spend,
                    COALESCE(SUM(facebook_ad_insights.impressions), 0) as total_impressions,
                    COALESCE(SUM(facebook_ad_insights.clicks), 0) as total_clicks
                ')->first();
            $overallSpend = (float) ($totals->total_spend ?? 0);
            $overallImpr = (int) ($totals->total_impressions ?? 0);
            $overallClicks = (int) ($totals->total_clicks ?? 0);
            $overallCtr = $overallImpr > 0 ? round(($overallClicks / $overallImpr) * 100, 2) : 0;

            // totals pages and account counts
            $pagesQuery = FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
                ->join('facebook_ad_accounts', 'facebook_ads.account_id', '=', 'facebook_ad_accounts.id')
                ->where('facebook_ad_accounts.business_id', $businessId)
                ->whereNotNull('facebook_ads.page_id');
            if (!empty($from) && !empty($to)) {
                $pagesQuery->whereBetween('facebook_ad_insights.date', [$from, $to]);
            }
            $overallPages = (int) $pagesQuery->distinct('facebook_ads.page_id')->count('facebook_ads.page_id');
            $overallAccounts = (int) $query->count();
            
            // Calculate total campaigns for this business
            $totalCampaigns = FacebookCampaign::join('facebook_ad_accounts', 'facebook_campaigns.ad_account_id', '=', 'facebook_ad_accounts.id')
                ->where('facebook_ad_accounts.business_id', $businessId)
                ->count();
            
            // Calculate total ad sets for this business
            $totalAdSets = FacebookAdSet::join('facebook_campaigns', 'facebook_ad_sets.campaign_id', '=', 'facebook_campaigns.id')
                ->join('facebook_ad_accounts', 'facebook_campaigns.ad_account_id', '=', 'facebook_ad_accounts.id')
                ->where('facebook_ad_accounts.business_id', $businessId)
                ->count();
            
            // Calculate total ads for this business
            $totalAds = FacebookAd::join('facebook_campaigns', 'facebook_ads.campaign_id', '=', 'facebook_campaigns.id')
                ->join('facebook_ad_accounts', 'facebook_campaigns.ad_account_id', '=', 'facebook_ad_accounts.id')
                ->where('facebook_ad_accounts.business_id', $businessId)
                ->count();
            
            // Calculate total posts for this business
            $totalPosts = FacebookAd::join('facebook_campaigns', 'facebook_ads.campaign_id', '=', 'facebook_campaigns.id')
                ->join('facebook_ad_accounts', 'facebook_campaigns.ad_account_id', '=', 'facebook_ad_accounts.id')
                ->where('facebook_ad_accounts.business_id', $businessId)
                ->whereNotNull('facebook_ads.post_id')
                ->count();

            return response()->json([
                'success' => true,
                'data' => $accounts->items(),
                'pagination' => [
                    'current_page' => $accounts->currentPage(),
                    'last_page' => $accounts->lastPage(),
                    'total' => $accounts->total(),
                    'from' => $accounts->firstItem(),
                    'to' => $accounts->lastItem(),
                    'has_more_pages' => $accounts->hasMorePages(),
                    'total_spend' => $overallSpend,
                    'total_impressions' => $overallImpr,
                    'total_clicks' => $overallClicks,
                    'total_ctr' => $overallCtr,
                    'total_pages' => $overallPages,
                    'total_accounts' => $overallAccounts,
                    'total_campaigns' => $totalCampaigns,
                    'total_adsets' => $totalAdSets,
                    'total_ads' => $totalAds,
                    'total_posts' => $totalPosts,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Lỗi khi tải Ad Accounts: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy danh sách Campaigns theo Ad Account
     */
    public function getCampaigns(Request $request): JsonResponse
    {
        try {
            $accountId = $request->get('accountId');
            $month = $request->get('month'); // YYYY-MM to aggregate monthly KPIs
            $page = $request->get('page', 1);
            $perPage = $request->get('per_page', 10);
            $search = $request->get('search', '');
            $status = $request->get('status', '');
            $objective = $request->get('objective', '');
            $sort = $request->get('sort', 'created_at_desc');
            $groupBy = $request->get('group_by', '');
            $from = $request->get('from');
            $to = $request->get('to');
            
            if (!$accountId) {
                return response()->json([
                    'error' => 'Thiếu accountId parameter'
                ], 400);
            }

            $query = FacebookCampaign::where('ad_account_id', $accountId)
                ->withCount(['adSets', 'ads']);
            
            // Apply search filter
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('id', 'LIKE', "%{$search}%");
                });
            }
            
            // Apply status filter
            if (!empty($status)) {
                $query->where('effective_status', $status);
            }
            
            // Apply objective filter
            if (!empty($objective)) {
                $query->where('objective', $objective);
            }
            
            // Apply date range filter based on facebook_ad_insights.date
            if (!empty($from) && !empty($to)) {
                $query->whereExists(function($q) use ($from, $to) {
                    $q->select(DB::raw(1))
                      ->from('facebook_ads')
                      ->join('facebook_ad_insights', 'facebook_ads.id', '=', 'facebook_ad_insights.ad_id')
                      ->whereColumn('facebook_ads.campaign_id', 'facebook_campaigns.id')
                      ->whereBetween('facebook_ad_insights.date', [$from, $to]);
                });
            }
            
            // Apply sorting
            $sortParts = explode('_', $sort);
            $sortField = $sortParts[0];
            $sortDirection = isset($sortParts[1]) && in_array($sortParts[1], ['asc', 'desc']) ? $sortParts[1] : 'desc';
            
            // Validate sort field
            $allowedSortFields = ['created_at', 'name', 'status', 'effective_status', 'objective', 'spend'];
            if (!in_array($sortField, $allowedSortFields)) {
                $sortField = 'created_at';
                $sortDirection = 'desc';
            }
            
            if ($sortField === 'spend') {
                // For spend sorting, we'll need to join with insights
                $query->leftJoin('facebook_ads', 'facebook_campaigns.id', '=', 'facebook_ads.campaign_id')
                      ->leftJoin('facebook_ad_insights', 'facebook_ads.id', '=', 'facebook_ad_insights.ad_id')
                      ->select('facebook_campaigns.*')
                      ->selectRaw('COALESCE(SUM(facebook_ad_insights.spend), 0) as total_spend')
                      ->groupBy('facebook_campaigns.id')
                      ->orderBy('total_spend', $sortDirection);
            } else {
                $query->orderBy('facebook_campaigns.' . $sortField, $sortDirection);
            }
            
            $campaigns = $query->paginate($perPage, ['id', 'name', 'status', 'effective_status', 'objective', 'start_time', 'created_at'], 'page', $page);

            // Add counts for breakdown from facebook_ad_insights
            $campaigns->transform(function ($campaign) use ($from, $to) {
                $campaign->ad_sets_count = FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
                    ->join('facebook_ad_sets', 'facebook_ads.adset_id', '=', 'facebook_ad_sets.id')
                    ->where('facebook_ad_sets.campaign_id', $campaign->id)
                    ->distinct('facebook_ad_sets.id')
                    ->count('facebook_ad_sets.id');
                    
                $campaign->ads_count = FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
                    ->where('facebook_ads.campaign_id', $campaign->id)
                    ->distinct('facebook_ads.id')
                    ->count('facebook_ads.id');
                    
                $campaign->posts_count = FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
                    ->where('facebook_ads.campaign_id', $campaign->id)
                    ->whereNotNull('facebook_ads.post_id')
                    ->distinct('facebook_ads.post_id')
                    ->count('facebook_ads.post_id');
                    
                $campaign->pages_count = FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
                    ->where('facebook_ads.campaign_id', $campaign->id)
                    ->whereNotNull('facebook_ads.page_id')
                    ->distinct('facebook_ads.page_id')
                    ->count('facebook_ads.page_id');

                // Include KPI per campaign in the selected date range (or all-time if no range)
                $kpiQuery = FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
                    ->where('facebook_ads.campaign_id', $campaign->id);
                if (!empty($from) && !empty($to)) {
                    $kpiQuery->whereBetween('facebook_ad_insights.date', [$from, $to]);
                }
                $kpi = $kpiQuery->selectRaw('
                        COALESCE(SUM(facebook_ad_insights.spend), 0) as total_spend,
                        COALESCE(SUM(facebook_ad_insights.impressions), 0) as total_impressions,
                        COALESCE(SUM(facebook_ad_insights.clicks), 0) as total_clicks,
                        COALESCE(SUM(facebook_ad_insights.reach), 0) as total_reach
                    ')->first();
                $campaign->total_spend = (float) ($kpi->total_spend ?? 0);
                $campaign->total_impressions = (int) ($kpi->total_impressions ?? 0);
                $campaign->total_clicks = (int) ($kpi->total_clicks ?? 0);
                $campaign->total_reach = (int) ($kpi->total_reach ?? 0);
                $campaign->ctr = ($campaign->total_impressions > 0)
                    ? round(($campaign->total_clicks / $campaign->total_impressions) * 100, 2)
                    : 0;
                    
                return $campaign;
            });

            // Calculate total counts from facebook_ad_insights
            $totalAdSets = FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
                ->join('facebook_ad_sets', 'facebook_ads.adset_id', '=', 'facebook_ad_sets.id')
                ->join('facebook_campaigns', 'facebook_ad_sets.campaign_id', '=', 'facebook_campaigns.id')
                ->where('facebook_campaigns.ad_account_id', $accountId)
                ->distinct('facebook_ad_sets.id')
                ->count('facebook_ad_sets.id');
                
            $totalAds = FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
                ->join('facebook_campaigns', 'facebook_ads.campaign_id', '=', 'facebook_campaigns.id')
                ->where('facebook_campaigns.ad_account_id', $accountId)
                ->distinct('facebook_ads.id')
                ->count('facebook_ads.id');
                
            $totalPosts = FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
                ->join('facebook_campaigns', 'facebook_ads.campaign_id', '=', 'facebook_campaigns.id')
                ->where('facebook_campaigns.ad_account_id', $accountId)
                ->whereNotNull('facebook_ads.post_id')
                ->distinct('facebook_ads.post_id')
                ->count('facebook_ads.post_id');
                
            $totalPages = FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
                ->join('facebook_campaigns', 'facebook_ads.campaign_id', '=', 'facebook_campaigns.id')
                ->where('facebook_campaigns.ad_account_id', $accountId)
                ->whereNotNull('facebook_ads.page_id')
                ->distinct('facebook_ads.page_id')
                ->count('facebook_ads.page_id');
            
            // Calculate total campaigns and accounts for this account
            $totalCampaigns = FacebookCampaign::where('ad_account_id', $accountId)->count();
            $totalAccounts = 1; // Since we're viewing campaigns for a specific account

            // Totals: KPI in selected range for this account (all-time if no range)
            $spendTotalsQuery = FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
                ->join('facebook_campaigns', 'facebook_ads.campaign_id', '=', 'facebook_campaigns.id')
                ->where('facebook_campaigns.ad_account_id', $accountId);
            if (!empty($from) && !empty($to)) {
                $spendTotalsQuery->whereBetween('facebook_ad_insights.date', [$from, $to]);
            }
            $spendTotals = $spendTotalsQuery->selectRaw('
                    COALESCE(SUM(facebook_ad_insights.spend), 0) as total_spend,
                    COALESCE(SUM(facebook_ad_insights.impressions), 0) as total_impressions,
                    COALESCE(SUM(facebook_ad_insights.clicks), 0) as total_clicks,
                    COALESCE(SUM(facebook_ad_insights.reach), 0) as total_reach
                ')->first();
            $totalSpend = (float) ($spendTotals->total_spend ?? 0);
            $totalImpr = (int) ($spendTotals->total_impressions ?? 0);
            $totalClicks = (int) ($spendTotals->total_clicks ?? 0);
            $totalReach = (int) ($spendTotals->total_reach ?? 0);
            $totalCtr = $totalImpr > 0 ? round(($totalClicks / $totalImpr) * 100, 2) : 0;

            return response()->json([
                'success' => true,
                'data' => $campaigns->items(),
                'pagination' => [
                    'current_page' => $campaigns->currentPage(),
                    'last_page' => $campaigns->lastPage(),
                    'per_page' => $campaigns->perPage(),
                    'total' => $campaigns->total(),
                    'from' => $campaigns->firstItem(),
                    'to' => $campaigns->lastItem(),
                    'has_more_pages' => $campaigns->hasMorePages(),
                    'total_spend' => $totalSpend,
                    'total_impressions' => $totalImpr,
                    'total_clicks' => $totalClicks,
                    'total_reach' => $totalReach,
                    'total_ctr' => $totalCtr,
                    'total_adsets' => $totalAdSets,
                    'total_ads' => $totalAds,
                    'total_posts' => $totalPosts,
                    'total_pages' => $totalPages,
                    'total_campaigns' => $totalCampaigns,
                    'total_accounts' => $totalAccounts
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Lỗi khi tải Campaigns: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy danh sách Ad Sets theo Campaign
     */
    public function getAdSets(Request $request): JsonResponse
    {
        try {
            $campaignId = $request->get('campaignId');
            $month = $request->get('month'); // YYYY-MM to aggregate monthly KPIs
            $page = $request->get('page', 1);
            $perPage = $request->get('per_page', 10);
            $search = $request->get('search', '');
            $status = $request->get('status', '');
            $sort = $request->get('sort', 'created_at_desc');
            $groupBy = $request->get('group_by', '');
            $from = $request->get('from');
            $to = $request->get('to');
            
            if (!$campaignId) {
                return response()->json([
                    'error' => 'Thiếu campaignId parameter'
                ], 400);
            }

            $query = FacebookAdSet::where('campaign_id', $campaignId)
                ->withCount('ads');
            
            // Apply search filter
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('id', 'LIKE', "%{$search}%");
                });
            }
            
            // Apply status filter
            if (!empty($status)) {
                $query->where('status', $status);
            }
            
            // Apply date range filter based on facebook_ad_insights.date
            if (!empty($from) && !empty($to)) {
                $query->whereExists(function($q) use ($from, $to) {
                    $q->select(DB::raw(1))
                      ->from('facebook_ads')
                      ->join('facebook_ad_insights', 'facebook_ads.id', '=', 'facebook_ad_insights.ad_id')
                      ->whereColumn('facebook_ads.adset_id', 'facebook_ad_sets.id')
                      ->whereBetween('facebook_ad_insights.date', [$from, $to]);
                });
            }
            
            // Apply sorting
            $sortParts = explode('_', $sort);
            $sortField = $sortParts[0];
            $sortDirection = isset($sortParts[1]) && in_array($sortParts[1], ['asc', 'desc']) ? $sortParts[1] : 'desc';
            
            // Validate sort field
            $allowedSortFields = ['created_at', 'name', 'status', 'optimization_goal', 'spend'];
            if (!in_array($sortField, $allowedSortFields)) {
                $sortField = 'created_at';
                $sortDirection = 'desc';
            }
            
            if ($sortField === 'spend') {
                // For spend sorting, we'll need to join with insights
                $query->leftJoin('facebook_ads', 'facebook_ad_sets.id', '=', 'facebook_ads.adset_id')
                      ->leftJoin('facebook_ad_insights', 'facebook_ads.id', '=', 'facebook_ad_insights.ad_id')
                      ->select('facebook_ad_sets.*')
                      ->selectRaw('COALESCE(SUM(facebook_ad_insights.spend), 0) as total_spend')
                      ->groupBy('facebook_ad_sets.id')
                      ->orderBy('total_spend', $sortDirection);
            } else {
                $query->orderBy('facebook_ad_sets.' . $sortField, $sortDirection);
            }
            
            $adSets = $query->paginate($perPage, ['id', 'name', 'status', 'optimization_goal', 'created_at'], 'page', $page);

            // Always calculate KPI from insights data
            $insights = FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
                ->whereIn('facebook_ads.adset_id', $adSets->pluck('id'));
            
            // Apply date filter if provided
            if ($month) {
                [$y, $m] = explode('-', $month) + [null, null];
                if ($y && $m) {
                    $start = "$y-$m-01";
                    $end = date('Y-m-t', strtotime($start));
                    $insights->whereBetween('facebook_ad_insights.date', [$start, $end]);
                }
            } elseif (!empty($from) && !empty($to)) {
                $insights->whereBetween('facebook_ad_insights.date', [$from, $to]);
            }
            
            $insightsData = $insights->get(['facebook_ads.adset_id', 'facebook_ad_insights.spend', 'facebook_ad_insights.impressions', 'facebook_ad_insights.clicks', 'facebook_ad_insights.reach']);
                    
                    $byAdSet = [];
            foreach ($insightsData as $in) {
                        $adsetId = $in->adset_id;
                        if (!$adsetId) continue;
                        if (!isset($byAdSet[$adsetId])) {
                            $byAdSet[$adsetId] = ['spend'=>0,'impressions'=>0,'clicks'=>0,'reach'=>0];
                        }
                        $byAdSet[$adsetId]['spend'] += (float) ($in->spend ?? 0);
                        $byAdSet[$adsetId]['impressions'] += (int) ($in->impressions ?? 0);
                        $byAdSet[$adsetId]['clicks'] += (int) ($in->clicks ?? 0);
                        $byAdSet[$adsetId]['reach'] += (int) ($in->reach ?? 0);
                    }
            
            // Attach KPI to adSets
                    $adSets->transform(function ($as) use ($byAdSet) {
                        $kpi = $byAdSet[$as->id] ?? ['spend'=>0,'impressions'=>0,'clicks'=>0,'reach'=>0];
                        $as->total_spend = $kpi['spend'];
                        $as->total_impressions = $kpi['impressions'];
                        $as->total_clicks = $kpi['clicks'];
                        $as->total_reach = $kpi['reach'];
                        $as->avg_ctr = $kpi['impressions'] > 0 ? ($kpi['clicks'] / $kpi['impressions']) * 100 : 0;
                        $as->avg_cpc = $kpi['clicks'] > 0 ? $kpi['spend'] / $kpi['clicks'] : 0;
                        $as->avg_cpm = $kpi['impressions'] > 0 ? ($kpi['spend'] / $kpi['impressions']) * 1000 : 0;
                        return $as;
                    });

            // Totals for summary cards
            $totalsForAdsets = [
                'total_spend' => array_sum(array_map(fn($a) => (float) ($a['spend'] ?? 0), $byAdSet)),
                'total_impressions' => array_sum(array_map(fn($a) => (int) ($a['impressions'] ?? 0), $byAdSet)),
                'total_clicks' => array_sum(array_map(fn($a) => (int) ($a['clicks'] ?? 0), $byAdSet)),
                'total_reach' => array_sum(array_map(fn($a) => (int) ($a['reach'] ?? 0), $byAdSet)),
                'total_adsets' => (int) $adSets->total(),
            ];

            return response()->json([
                'success' => true,
                'data' => $adSets->items(),
                'pagination' => [
                    'current_page' => $adSets->currentPage(),
                    'last_page' => $adSets->lastPage(),
                    'per_page' => $adSets->perPage(),
                    'total' => $adSets->total(),
                    'from' => $adSets->firstItem(),
                    'to' => $adSets->lastItem(),
                    'has_more_pages' => $adSets->hasMorePages(),
                    'total_spend' => (float) ($totalsForAdsets['total_spend'] ?? 0),
                    'total_impressions' => (int) ($totalsForAdsets['total_impressions'] ?? 0),
                    'total_clicks' => (int) ($totalsForAdsets['total_clicks'] ?? 0),
                    'total_reach' => (int) ($totalsForAdsets['total_reach'] ?? 0),
                    'total_ctr' => ($totalsForAdsets['total_impressions'] ?? 0) > 0 ? round((($totalsForAdsets['total_clicks'] ?? 0) / ($totalsForAdsets['total_impressions'] ?? 0)) * 100, 2) : 0,
                    'total_adsets' => (int) ($totalsForAdsets['total_adsets'] ?? 0)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Lỗi khi tải Ad Sets: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy danh sách Ads theo Ad Set
     */
    public function getAds(Request $request): JsonResponse
    {
        try {
            $adsetId = $request->get('adsetId');
            $month = $request->get('month'); // YYYY-MM to aggregate monthly KPIs
            
            if (!$adsetId) {
                return response()->json([
                    'error' => 'Thiếu adsetId parameter'
                ], 400);
            }

            $ads = FacebookAd::where('adset_id', $adsetId)
                ->with(['insights'])
                ->orderBy('created_time', 'desc')
                ->get(['id', 'name', 'status', 'effective_status', 'post_id', 'created_at']);

            if ($month) {
                [$y, $m] = explode('-', $month) + [null, null];
                if ($y && $m) {
                    $start = "$y-$m-01";
                    $end = date('Y-m-t', strtotime($start));
                    
                    // Lấy insights từ bảng facebook_ad_insights
                    $insights = FacebookAdInsight::whereIn('ad_id', $ads->pluck('id'))
                        ->whereBetween('date', [$start, $end])
                        ->whereNotNull('spend')
                        ->get(['ad_id', 'spend', 'impressions', 'clicks', 'reach']);
                    
                    $byAd = [];
                    foreach ($insights as $in) {
                        $adId = $in->ad_id;
                        if (!$adId) continue;
                        if (!isset($byAd[$adId])) {
                            $byAd[$adId] = ['spend'=>0,'impressions'=>0,'clicks'=>0,'reach'=>0];
                        }
                        $byAd[$adId]['spend'] += (float) ($in->spend ?? 0);
                        $byAd[$adId]['impressions'] += (int) ($in->impressions ?? 0);
                        $byAd[$adId]['clicks'] += (int) ($in->clicks ?? 0);
                        $byAd[$adId]['reach'] += (int) ($in->reach ?? 0);
                    }
                    // attach KPI
                    $ads->transform(function ($a) use ($byAd) {
                        $a->kpi = $byAd[$a->id] ?? ['spend'=>0,'impressions'=>0,'clicks'=>0,'reach'=>0];
                        return $a;
                    });
                }
            } elseif (!empty($from) && !empty($to)) {
                // Aggregate KPI using from/to range
                $insights = FacebookAdInsight::whereIn('ad_id', $ads->pluck('id'))
                    ->whereBetween('date', [$from, $to])
                    ->get(['ad_id', 'spend', 'impressions', 'clicks', 'reach']);
                $byAd = [];
                foreach ($insights as $in) {
                    $adId = $in->ad_id;
                    if (!$adId) continue;
                    if (!isset($byAd[$adId])) {
                        $byAd[$adId] = ['spend'=>0,'impressions'=>0,'clicks'=>0,'reach'=>0];
                    }
                    $byAd[$adId]['spend'] += (float) ($in->spend ?? 0);
                    $byAd[$adId]['impressions'] += (int) ($in->impressions ?? 0);
                    $byAd[$adId]['clicks'] += (int) ($in->clicks ?? 0);
                    $byAd[$adId]['reach'] += (int) ($in->reach ?? 0);
                }
                $ads->transform(function ($a) use ($byAd) {
                    $a->kpi = $byAd[$a->id] ?? ['spend'=>0,'impressions'=>0,'clicks'=>0,'reach'=>0];
                    return $a;
                });
            }

            return response()->json($ads);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Lỗi khi tải Ads: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy danh sách Posts
     */
    public function getPosts(Request $request): JsonResponse
    {
        try {
            $adsetId = $request->get('adsetId');
            $campaignId = $request->get('campaignId');
            $accountId = $request->get('accountId');
            $month = $request->get('month'); // YYYY-MM to aggregate monthly KPIs
            $page = $request->get('page', 1);
            $perPage = $request->get('per_page', 10);
            $search = $request->get('search', '');
            $status = $request->get('status', '');
            $sort = $request->get('sort', 'created_time_desc');
            $groupBy = $request->get('group_by', '');
            $from = $request->get('from');
            $to = $request->get('to');
            
            // Lấy posts từ FacebookAd có post_id
            $adsQuery = FacebookAd::whereNotNull('post_id')
                ->with(['insights']);
            
            if ($adsetId) {
                $adsQuery->where('adset_id', $adsetId);
            } elseif ($campaignId) {
                $adsQuery->where('campaign_id', $campaignId);
            } elseif ($accountId) {
                $adsQuery->where('account_id', $accountId);
            }
            
            // Apply search filter
            if (!empty($search)) {
                $adsQuery->where(function($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('post_id', 'LIKE', "%{$search}%")
                      ->orWhere('page_id', 'LIKE', "%{$search}%");
                });
            }
            
            // Apply status filter
            if (!empty($status)) {
                $adsQuery->where('effective_status', $status);
            }
            
            // Apply date range filter based on facebook_ad_insights.date
            if (!empty($from) && !empty($to)) {
                $adsQuery->whereExists(function($q) use ($from, $to) {
                    $q->select(DB::raw(1))
                      ->from('facebook_ad_insights')
                      ->whereColumn('facebook_ad_insights.ad_id', 'facebook_ads.id')
                      ->whereBetween('facebook_ad_insights.date', [$from, $to]);
                });
            }
            
            // Apply sorting
            $sortParts = explode('_', $sort);
            $sortField = $sortParts[0];
            $sortDirection = isset($sortParts[1]) && in_array($sortParts[1], ['asc', 'desc']) ? $sortParts[1] : 'desc';
            
            // Validate sort field
            $allowedSortFields = ['created_time', 'name', 'status', 'effective_status', 'post_id', 'page_id', 'spend'];
            if (!in_array($sortField, $allowedSortFields)) {
                $sortField = 'created_time';
                $sortDirection = 'desc';
            }
            
            if ($sortField === 'spend') {
                // For spend sorting, we'll need to join with insights
                $adsQuery->leftJoin('facebook_ad_insights', 'facebook_ads.id', '=', 'facebook_ad_insights.ad_id')
                      ->select('facebook_ads.*')
                      ->selectRaw('COALESCE(SUM(facebook_ad_insights.spend), 0) as total_spend')
                      ->groupBy('facebook_ads.id')
                      ->orderBy('total_spend', $sortDirection);
            } else {
                $adsQuery->orderBy('facebook_ads.' . $sortField, $sortDirection);
            }
            
            $ads = $adsQuery->paginate($perPage, ['id', 'name', 'status', 'effective_status', 'post_id', 'page_id', 'created_time'], 'page', $page);
            
            // Transform ads thành posts format (không phụ thuộc bảng facebook_posts)
            $posts = $ads->getCollection()->map(function ($ad) {
                return [
                    'id' => $ad->id,
                    'name' => $ad->name,
                    'status' => $ad->effective_status ?? $ad->status,
                    'post_id' => $ad->post_id,
                    'page_id' => $ad->page_id,
                    'post_message' => null,
                    'post_created_time' => $ad->created_time ?? $ad->created_at,
                    'post_permalink_url' => null,
                    'post_likes' => 0,
                    'post_shares' => 0,
                    'post_comments' => 0,
                    'creative_link_url' => null,
                    'creative_link_name' => null,
                    'creative_link_message' => null,
                    'ad_impressions' => 0,
                    'ad_reach' => 0,
                    'ad_clicks' => 0,
                ];
            });

            if ($month) {
                [$y, $m] = explode('-', $month) + [null, null];
                if ($y && $m) {
                    $start = "$y-$m-01";
                    $end = date('Y-m-t', strtotime($start));
                    
                    // Lấy insights từ bảng facebook_ad_insights
                    $insights = FacebookAdInsight::whereIn('ad_id', $ads->getCollection()->pluck('id'))
                        ->whereBetween('date', [$start, $end])
                        ->get(['ad_id', 'spend', 'impressions', 'clicks', 'reach', 'actions']);
                    
                    $byAd = [];
                    foreach ($insights as $in) {
                        $adId = $in->ad_id;
                        if (!$adId) continue;
                        if (!isset($byAd[$adId])) {
                            $byAd[$adId] = ['spend'=>0,'impressions'=>0,'reach'=>0,'clicks'=>0,'likes'=>0,'shares'=>0,'comments'=>0];
                        }
                        $byAd[$adId]['spend'] += (float) ($in->spend ?? 0);
                        $byAd[$adId]['impressions'] += (int) ($in->impressions ?? 0);
                        $byAd[$adId]['reach'] += (int) ($in->reach ?? 0);
                        $byAd[$adId]['clicks'] += (int) ($in->clicks ?? 0);
                        // actions có thể là json string hoặc array
                        $actions = $in->actions ?? [];
                        if (is_string($actions)) {
                            $decoded = json_decode($actions, true);
                            if (json_last_error() === JSON_ERROR_NONE) { $actions = $decoded; }
                        }
                        if (is_array($actions)) {
                            foreach ($actions as $a) {
                                $type = $a['action_type'] ?? '';
                                $val = (int) ($a['value'] ?? 0);
                                if (str_contains($type, 'like')) $byAd[$adId]['likes'] += $val;
                                if (str_contains($type, 'comment')) $byAd[$adId]['comments'] += $val;
                                if (str_contains($type, 'share')) $byAd[$adId]['shares'] += $val;
                            }
                        }
                    }
                    
                    // Attach KPI to posts
                    $posts = $posts->map(function ($post) use ($byAd) {
                        $kpi = $byAd[$post['id']] ?? ['spend'=>0,'impressions'=>0,'reach'=>0,'clicks'=>0,'likes'=>0,'shares'=>0,'comments'=>0];
                        $post['ad_impressions'] = $kpi['impressions'];
                        $post['ad_reach'] = $kpi['reach'];
                        $post['ad_clicks'] = $kpi['clicks'];
                        $post['ad_spend'] = $kpi['spend'];
                        $post['post_likes'] = $kpi['likes'];
                        $post['post_shares'] = $kpi['shares'];
                        $post['post_comments'] = $kpi['comments'];
                        return $post;
                    });
                }
            } elseif (!empty($from) && !empty($to)) {
                $insights = FacebookAdInsight::whereIn('ad_id', $ads->getCollection()->pluck('id'))
                    ->whereBetween('date', [$from, $to])
                    ->get(['ad_id', 'spend', 'impressions', 'clicks', 'reach', 'actions']);
                $byAd = [];
                foreach ($insights as $in) {
                    $adId = $in->ad_id;
                    if (!$adId) continue;
                    if (!isset($byAd[$adId])) {
                        $byAd[$adId] = ['spend'=>0,'impressions'=>0,'reach'=>0,'clicks'=>0,'likes'=>0,'shares'=>0,'comments'=>0];
                    }
                    $byAd[$adId]['spend'] += (float) ($in->spend ?? 0);
                    $byAd[$adId]['impressions'] += (int) ($in->impressions ?? 0);
                    $byAd[$adId]['reach'] += (int) ($in->reach ?? 0);
                    $byAd[$adId]['clicks'] += (int) ($in->clicks ?? 0);
                    $actions = $in->actions ?? [];
                    if (is_string($actions)) { $decoded = json_decode($actions, true); if (json_last_error() === JSON_ERROR_NONE) { $actions = $decoded; } }
                    if (is_array($actions)) {
                        foreach ($actions as $a) {
                            $type = $a['action_type'] ?? '';
                            $val = (int) ($a['value'] ?? 0);
                            if (str_contains($type, 'like')) $byAd[$adId]['likes'] += $val;
                            if (str_contains($type, 'comment')) $byAd[$adId]['comments'] += $val;
                            if (str_contains($type, 'share')) $byAd[$adId]['shares'] += $val;
                        }
                    }
                }
                $posts = $posts->map(function ($post) use ($byAd) {
                    $kpi = $byAd[$post['id']] ?? ['spend'=>0,'impressions'=>0,'reach'=>0,'clicks'=>0,'likes'=>0,'shares'=>0,'comments'=>0];
                    $post['ad_impressions'] = $kpi['impressions'];
                    $post['ad_reach'] = $kpi['reach'];
                    $post['ad_clicks'] = $kpi['clicks'];
                    $post['ad_spend'] = $kpi['spend'];
                    $post['post_likes'] = $kpi['likes'];
                    $post['post_shares'] = $kpi['shares'];
                    $post['post_comments'] = $kpi['comments'];
                    return $post;
                });
            } else {
                // Aggregate all-time KPI if no date filter provided
                $insights = FacebookAdInsight::whereIn('ad_id', $ads->getCollection()->pluck('id'))
                    ->get(['ad_id', 'spend', 'impressions', 'clicks', 'reach', 'actions']);
                $byAd = [];
                foreach ($insights as $in) {
                    $adId = $in->ad_id;
                    if (!$adId) continue;
                    if (!isset($byAd[$adId])) {
                        $byAd[$adId] = ['spend'=>0,'impressions'=>0,'reach'=>0,'clicks'=>0,'likes'=>0,'shares'=>0,'comments'=>0];
                    }
                    $byAd[$adId]['spend'] += (float) ($in->spend ?? 0);
                    $byAd[$adId]['impressions'] += (int) ($in->impressions ?? 0);
                    $byAd[$adId]['reach'] += (int) ($in->reach ?? 0);
                    $byAd[$adId]['clicks'] += (int) ($in->clicks ?? 0);
                    $actions = $in->actions ?? [];
                    if (is_string($actions)) { $decoded = json_decode($actions, true); if (json_last_error() === JSON_ERROR_NONE) { $actions = $decoded; } }
                    if (is_array($actions)) {
                        foreach ($actions as $a) {
                            $type = $a['action_type'] ?? '';
                            $val = (int) ($a['value'] ?? 0);
                            if (str_contains($type, 'like')) $byAd[$adId]['likes'] += $val;
                            if (str_contains($type, 'comment')) $byAd[$adId]['comments'] += $val;
                            if (str_contains($type, 'share')) $byAd[$adId]['shares'] += $val;
                        }
                    }
                }
                $posts = $posts->map(function ($post) use ($byAd) {
                    $kpi = $byAd[$post['id']] ?? ['spend'=>0,'impressions'=>0,'reach'=>0,'clicks'=>0,'likes'=>0,'shares'=>0,'comments'=>0];
                    $post['ad_impressions'] = $kpi['impressions'];
                    $post['ad_reach'] = $kpi['reach'];
                    $post['ad_clicks'] = $kpi['clicks'];
                    $post['ad_spend'] = $kpi['spend'];
                    $post['post_likes'] = $kpi['likes'];
                    $post['post_shares'] = $kpi['shares'];
                    $post['post_comments'] = $kpi['comments'];
                    return $post;
                });
            }

            // Compute totals for posts in current result set
            $postsArr = $posts->values()->toArray();
            $totalSpendForPosts = array_sum(array_map(fn($p) => (float) ($p['ad_spend'] ?? 0), $postsArr));
            $totalImprForPosts = array_sum(array_map(fn($p) => (int) ($p['ad_impressions'] ?? 0), $postsArr));
            $totalClicksForPosts = array_sum(array_map(fn($p) => (int) ($p['ad_clicks'] ?? 0), $postsArr));
            $totalReachForPosts = array_sum(array_map(fn($p) => (int) ($p['ad_reach'] ?? 0), $postsArr));

            return response()->json([
                'success' => true,
                'data' => $posts->values()->toArray(),
                'pagination' => [
                    'current_page' => $ads->currentPage(),
                    'last_page' => $ads->lastPage(),
                    'per_page' => $ads->perPage(),
                    'total' => $ads->total(),
                    'from' => $ads->firstItem(),
                    'to' => $ads->lastItem(),
                    'has_more_pages' => $ads->hasMorePages(),
                    'total_spend' => (float) $totalSpendForPosts,
                    'total_impressions' => (int) $totalImprForPosts,
                    'total_clicks' => (int) $totalClicksForPosts,
                    'total_reach' => (int) $totalReachForPosts,
                    'total_ctr' => $totalImprForPosts > 0 ? round(($totalClicksForPosts / $totalImprForPosts) * 100, 2) : 0,
                    'total_posts' => (int) $ads->total()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Lỗi khi tải Posts: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy tổng quan KPI cho dashboard
     */
    public function getDashboardKPI(Request $request): JsonResponse
    {
        try {
            $from = $request->get('from', now()->subDays(30)->toDateString());
            $to = $request->get('to', now()->toDateString());
            $accountId = $request->get('accountId');
            $campaignId = $request->get('campaignId');

            // Tổng quan dựa hoàn toàn vào facebook_ad_insights
            $insights = FacebookAdInsight::whereBetween('date', [$from, $to]);
            if ($accountId) {
                $insights->join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
                    ->where('facebook_ads.account_id', $accountId);
            }
            if ($campaignId) {
                $insights->join('facebook_ads as fa2', 'facebook_ad_insights.ad_id', '=', 'fa2.id')
                    ->where('fa2.campaign_id', $campaignId);
            }

            $totals = $insights->selectRaw('
                    COALESCE(SUM(facebook_ad_insights.spend), 0) as total_spend,
                    COALESCE(SUM(facebook_ad_insights.impressions), 0) as total_impressions,
                    COALESCE(SUM(facebook_ad_insights.clicks), 0) as total_clicks,
                    COALESCE(SUM(facebook_ad_insights.reach), 0) as total_reach
                ')->first();

            return response()->json([
                'total_spend' => (float) ($totals->total_spend ?? 0),
                'total_impressions' => (int) ($totals->total_impressions ?? 0),
                'total_clicks' => (int) ($totals->total_clicks ?? 0),
                'total_reach' => (int) ($totals->total_reach ?? 0),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Lỗi khi tải KPI: ' . $e->getMessage()
            ], 500);
        }
    }
}
