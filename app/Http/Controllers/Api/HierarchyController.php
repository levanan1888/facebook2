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
    public function getBusinesses(): JsonResponse
    {
        try {
            // Hiển thị ngày đồng bộ (created_at) và đếm số tài khoản quảng cáo
            $businesses = FacebookBusiness::withCount('adAccounts')
                ->orderBy('created_at', 'desc')
                ->get(['id', 'name', 'verification_status', 'created_at']);

            return response()->json($businesses);
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
            $month = $request->get('month'); // YYYY-MM to aggregate monthly KPIs
            
            if (!$businessId) {
                return response()->json([
                    'error' => 'Thiếu businessId parameter'
                ], 400);
            }

            $accounts = FacebookAdAccount::where('business_id', $businessId)
                ->withCount('campaigns')
                ->orderBy('created_at', 'desc')
                ->get(['id', 'name', 'account_id', 'account_status', 'created_at']);

            if ($month) {
                [$y, $m] = explode('-', $month) + [null, null];
                if ($y && $m) {
                    $start = "$y-$m-01";
                    $end = date('Y-m-t', strtotime($start));
                    
                    // Lấy insights từ bảng facebook_ad_insights
                    $insights = FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
                        ->whereIn('facebook_ads.account_id', $accounts->pluck('id'))
                        ->whereBetween('facebook_ad_insights.date', [$start, $end])
                        ->whereNotNull('facebook_ad_insights.spend')
                        ->get(['facebook_ads.account_id', 'facebook_ad_insights.spend', 'facebook_ad_insights.impressions', 'facebook_ad_insights.clicks', 'facebook_ad_insights.reach']);
                    
                    $byAcc = [];
                    foreach ($insights as $in) {
                        $accId = $in->account_id;
                        if (!$accId) continue;
                        if (!isset($byAcc[$accId])) {
                            $byAcc[$accId] = ['spend'=>0,'impressions'=>0,'clicks'=>0,'reach'=>0];
                        }
                        $byAcc[$accId]['spend'] += (float) ($in->spend ?? 0);
                        $byAcc[$accId]['impressions'] += (int) ($in->impressions ?? 0);
                        $byAcc[$accId]['clicks'] += (int) ($in->clicks ?? 0);
                        $byAcc[$accId]['reach'] += (int) ($in->reach ?? 0);
                    }
                    // attach KPI
                    $accounts->transform(function ($a) use ($byAcc) {
                        $a->kpi = $byAcc[$a->id] ?? ['spend'=>0,'impressions'=>0,'clicks'=>0,'reach'=>0];
                        return $a;
                    });
                }
            }

            return response()->json($accounts);
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
            
            if (!$accountId) {
                return response()->json([
                    'error' => 'Thiếu accountId parameter'
                ], 400);
            }

            $campaigns = FacebookCampaign::where('ad_account_id', $accountId)
                ->withCount(['adSets', 'ads'])
                ->orderBy('created_at', 'desc')
                ->get(['id', 'name', 'status', 'objective', 'created_at']);

            if ($month) {
                [$y, $m] = explode('-', $month) + [null, null];
                if ($y && $m) {
                    $start = "$y-$m-01";
                    $end = date('Y-m-t', strtotime($start));
                    
                    // Lấy insights từ bảng facebook_ad_insights
                    $insights = FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
                        ->whereIn('facebook_ads.campaign_id', $campaigns->pluck('id'))
                        ->whereBetween('facebook_ad_insights.date', [$start, $end])
                        ->whereNotNull('facebook_ad_insights.spend')
                        ->get(['facebook_ads.campaign_id', 'facebook_ad_insights.spend', 'facebook_ad_insights.impressions', 'facebook_ad_insights.clicks', 'facebook_ad_insights.reach']);
                    
                    $byCampaign = [];
                    foreach ($insights as $in) {
                        $campaignId = $in->campaign_id;
                        if (!$campaignId) continue;
                        if (!isset($byCampaign[$campaignId])) {
                            $byCampaign[$campaignId] = ['spend'=>0,'impressions'=>0,'clicks'=>0,'reach'=>0];
                        }
                        $byCampaign[$campaignId]['spend'] += (float) ($in->spend ?? 0);
                        $byCampaign[$campaignId]['impressions'] += (int) ($in->impressions ?? 0);
                        $byCampaign[$campaignId]['clicks'] += (int) ($in->clicks ?? 0);
                        $byCampaign[$campaignId]['reach'] += (int) ($in->reach ?? 0);
                    }
                    // attach KPI
                    $campaigns->transform(function ($c) use ($byCampaign) {
                        $c->kpi = $byCampaign[$c->id] ?? ['spend'=>0,'impressions'=>0,'clicks'=>0,'reach'=>0];
                        return $c;
                    });
                }
            }

            return response()->json($campaigns);
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
            
            if (!$campaignId) {
                return response()->json([
                    'error' => 'Thiếu campaignId parameter'
                ], 400);
            }

            $adSets = FacebookAdSet::where('campaign_id', $campaignId)
                ->withCount('ads')
                ->orderBy('created_at', 'desc')
                ->get(['id', 'name', 'status', 'optimization_goal', 'created_at']);

            if ($month) {
                [$y, $m] = explode('-', $month) + [null, null];
                if ($y && $m) {
                    $start = "$y-$m-01";
                    $end = date('Y-m-t', strtotime($start));
                    
                    // Lấy insights từ bảng facebook_ad_insights
                    $insights = FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
                        ->whereIn('facebook_ads.adset_id', $adSets->pluck('id'))
                        ->whereBetween('facebook_ad_insights.date', [$start, $end])
                        ->whereNotNull('facebook_ad_insights.spend')
                        ->get(['facebook_ads.adset_id', 'facebook_ad_insights.spend', 'facebook_ad_insights.impressions', 'facebook_ad_insights.clicks', 'facebook_ad_insights.reach']);
                    
                    $byAdSet = [];
                    foreach ($insights as $in) {
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
                    // attach KPI
                    $adSets->transform(function ($as) use ($byAdSet) {
                        $as->kpi = $byAdSet[$as->id] ?? ['spend'=>0,'impressions'=>0,'clicks'=>0,'reach'=>0];
                        return $as;
                    });
                }
            }

            return response()->json($adSets);
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
                ->with(['post', 'insights'])
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
            $pageId = $request->get('pageId');
            $month = $request->get('month'); // YYYY-MM to aggregate monthly KPIs
            
            // Bảng posts đã loại bỏ; trả rỗng hoặc chuyển sang đọc từ FacebookAd nếu cần
            $postsQuery = collect();
            
            if ($pageId) {
                $postsQuery->where('page_id', $pageId);
            }
            
            $posts = collect();

            if ($month) {
                [$y, $m] = explode('-', $month) + [null, null];
                if ($y && $m) {
                    $start = "$y-$m-01";
                    $end = date('Y-m-t', strtotime($start));
                    
                    // Lấy insights từ bảng facebook_post_insights
                    $insights = collect();
                    
                    $byPost = [];
                    foreach ($insights as $in) {
                        $postId = $in->post_id;
                        if (!$postId) continue;
                        if (!isset($byPost[$postId])) {
                            $byPost[$postId] = ['likes'=>0,'shares'=>0,'comments'=>0,'impressions'=>0,'reach'=>0];
                        }
                        $byPost[$postId]['likes'] += (int) ($in->likes ?? 0);
                        $byPost[$postId]['shares'] += (int) ($in->shares ?? 0);
                        $byPost[$postId]['comments'] += (int) ($in->comments ?? 0);
                        $byPost[$postId]['impressions'] += (int) ($in->impressions ?? 0);
                        $byPost[$postId]['reach'] += (int) ($in->reach ?? 0);
                    }
                    // attach KPI
                    // Không còn dữ liệu posts để transform
                }
            }

            return response()->json($posts);
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

            // Sử dụng summary table cho hiệu suất tốt hơn
            $summaryQuery = FacebookReportSummary::whereBetween('date', [$from, $to]);
            
            if ($accountId) {
                $summaryQuery->where('account_id', $accountId);
            }
            if ($campaignId) {
                $summaryQuery->where('campaign_id', $campaignId);
            }

            $summaryData = $summaryQuery->get();

            $kpi = [
                'total_spend' => $summaryData->sum('total_spend'),
                'total_impressions' => $summaryData->sum('total_impressions'),
                'total_clicks' => $summaryData->sum('total_clicks'),
                'total_reach' => $summaryData->sum('total_reach'),
                'total_conversions' => $summaryData->sum('total_conversions'),
                'total_conversion_values' => $summaryData->sum('total_conversion_values'),
                'avg_ctr' => $summaryData->avg('avg_ctr'),
                'avg_cpc' => $summaryData->avg('avg_cpc'),
                'avg_cpm' => $summaryData->avg('avg_cpm'),
                'avg_frequency' => $summaryData->avg('avg_frequency'),
                'roas' => $summaryData->sum('total_spend') > 0 ? $summaryData->sum('total_conversion_values') / $summaryData->sum('total_spend') : 0,
            ];

            return response()->json($kpi);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Lỗi khi tải KPI: ' . $e->getMessage()
            ], 500);
        }
    }
}
