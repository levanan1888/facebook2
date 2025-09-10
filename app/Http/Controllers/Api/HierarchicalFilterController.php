<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FacebookBusiness;
use App\Models\FacebookAdAccount;
use App\Models\FacebookCampaign;
use App\Models\FacebookAd;
use App\Models\FacebookAdInsight;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class HierarchicalFilterController extends Controller
{
    /**
     * Lấy danh sách Business Managers
     */
    public function getBusinessManagers(): JsonResponse
    {
        try {
            $cacheKey = "business_managers_all";
            
            $businesses = Cache::remember($cacheKey, 600, function() { // Cache 10 phút
                return FacebookBusiness::select('id', 'name', 'verification_status')
                    ->whereNotNull('name')
                    ->orderBy('name')
                    ->get()
                    ->map(function($business) {
                        return [
                            'id' => $business->id,
                            'name' => $business->name ?: 'Business ' . $business->id,
                            'verification_status' => $business->verification_status
                        ];
                    });
            });

            return response()->json([
                'success' => true,
                'data' => $businesses,
                'total' => $businesses->count(),
                'cached' => Cache::has($cacheKey)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách Business Managers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy danh sách Ad Accounts theo Business Manager
     */
    public function getAdAccountsByBusiness($businessId): JsonResponse
    {
        try {
            $cacheKey = "ad_accounts_business_{$businessId}";
            
            // Kiểm tra cache trước
            $adAccounts = Cache::remember($cacheKey, 300, function() use ($businessId) { // Cache 5 phút
                return FacebookAdAccount::select('id', 'name', 'account_id', 'business_id')
                    ->where('business_id', $businessId)
                    ->whereNotNull('name') // Chỉ lấy accounts có tên
                    ->orderBy('name')
                    ->limit(100) // Giới hạn số lượng
                    ->get()
                    ->map(function($account) {
                        return [
                            'id' => $account->id,
                            'name' => $account->name ?: 'Account ' . $account->account_id,
                            'account_id' => $account->account_id,
                            'business_id' => $account->business_id
                        ];
                    });
            });

            return response()->json([
                'success' => true,
                'data' => $adAccounts,
                'total' => $adAccounts->count(),
                'cached' => Cache::has($cacheKey)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách Ad Accounts: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy danh sách Campaigns theo Ad Account
     */
    public function getCampaignsByAccount($accountId): JsonResponse
    {
        try {
            $cacheKey = "campaigns_account_{$accountId}";
            
            $campaigns = Cache::remember($cacheKey, 300, function() use ($accountId) {
                return FacebookCampaign::select('id', 'name', 'ad_account_id', 'status', 'objective')
                    ->where('ad_account_id', $accountId)
                    ->whereNotNull('name')
                    ->orderBy('name')
                    ->limit(200)
                    ->get()
                    ->map(function($campaign) {
                        return [
                            'id' => $campaign->id,
                            'name' => $campaign->name ?: 'Campaign ' . $campaign->id,
                            'ad_account_id' => $campaign->ad_account_id,
                            'status' => $campaign->status,
                            'objective' => $campaign->objective
                        ];
                    });
            });

            return response()->json([
                'success' => true,
                'data' => $campaigns,
                'total' => $campaigns->count(),
                'cached' => Cache::has($cacheKey)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách Campaigns: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy danh sách Ads theo Campaign
     */
    public function getAdsByCampaign($campaignId): JsonResponse
    {
        try {
            $cacheKey = "ads_campaign_{$campaignId}";
            
            $ads = Cache::remember($cacheKey, 300, function() use ($campaignId) {
                return FacebookAd::select('id', 'name', 'campaign_id', 'status', 'effective_status')
                    ->where('campaign_id', $campaignId)
                    ->whereNotNull('name')
                    ->orderBy('name')
                    ->limit(500)
                    ->get()
                    ->map(function($ad) {
                        return [
                            'id' => $ad->id,
                            'name' => $ad->name ?: 'Ad ' . $ad->id,
                            'campaign_id' => $ad->campaign_id,
                            'status' => $ad->status,
                            'effective_status' => $ad->effective_status
                        ];
                    });
            });

            return response()->json([
                'success' => true,
                'data' => $ads,
                'total' => $ads->count(),
                'cached' => Cache::has($cacheKey)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách Ads: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy danh sách Pages theo Business Manager
     */
    public function getPagesByBusiness($businessId): JsonResponse
    {
        try {
            $cacheKey = "pages_business_{$businessId}";
            
            $pages = Cache::remember($cacheKey, 600, function() use ($businessId) { // Cache 10 phút
                return FacebookAdInsight::select('facebook_ad_insights.page_id')
                    ->join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
                    ->join('facebook_ad_accounts', 'facebook_ads.account_id', '=', 'facebook_ad_accounts.id')
                    ->where('facebook_ad_accounts.business_id', $businessId)
                    ->whereNotNull('facebook_ad_insights.page_id')
                    ->distinct('facebook_ad_insights.page_id')
                    ->orderBy('facebook_ad_insights.page_id')
                    ->limit(100)
                    ->get()
                    ->map(function($item) {
                        return [
                            'id' => $item->page_id,
                            'name' => 'Page ' . $item->page_id,
                            'page_id' => $item->page_id
                        ];
                    });
            });

            return response()->json([
                'success' => true,
                'data' => $pages,
                'total' => $pages->count(),
                'cached' => Cache::has($cacheKey)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách Pages: ' . $e->getMessage()
            ], 500);
        }
    }
}
