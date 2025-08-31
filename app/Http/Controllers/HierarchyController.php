<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\FacebookAd;
use App\Models\FacebookAdAccount;
use App\Models\FacebookAdSet;
use App\Models\FacebookBusiness;
use App\Models\FacebookCampaign;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HierarchyController extends Controller
{
    public function index(): View
    {
        return view('hierarchy.index');
    }

    public function businesses(): JsonResponse
    {
        try {
            $businesses = FacebookBusiness::withCount('adAccounts')->get();
            return response()->json($businesses);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function accounts(): JsonResponse
    {
        try {
            $accounts = FacebookAdAccount::withCount('campaigns')->get();
            return response()->json($accounts);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function campaigns(): JsonResponse
    {
        try {
            $campaigns = FacebookCampaign::with(['adAccount:id,name'])->get();
            return response()->json($campaigns);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function adsets(): JsonResponse
    {
        try {
            $adsets = FacebookAdSet::with(['campaign:id,name'])->get();
            return response()->json($adsets);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function ads(): JsonResponse
    {
        try {
            $ads = FacebookAd::with(['campaign:id,name', 'adSet:id,name'])
                ->whereNotNull('post_id')
                ->latest('last_insights_sync')
                ->limit(100)
                ->get([
                    'id', 'name', 'status', 'effective_status', 'post_id', 'post_message', 'post_type',
                    'post_created_time', 'ad_spend', 'ad_impressions', 'ad_clicks', 'ad_reach',
                    'post_likes', 'post_shares', 'post_comments', 'post_reactions',
                    'last_insights_sync'
                ]);

            return response()->json($ads);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getHierarchyData(Request $request): JsonResponse
    {
        try {
            $type = $request->get('type', 'businesses');
            $id = $request->get('id');

            switch ($type) {
                case 'businesses':
                    $data = FacebookBusiness::withCount('adAccounts')->get();
                    break;
                case 'accounts':
                    $data = FacebookAdAccount::withCount('campaigns')->get();
                    break;
                case 'campaigns':
                    $data = FacebookCampaign::with(['adAccount:id,name'])->get();
                    break;
                case 'adsets':
                    $data = FacebookAdSet::with(['campaign:id,name'])->get();
                    break;
                case 'ads':
                    $data = FacebookAd::with(['campaign:id,name', 'adSet:id,name'])
                        ->whereNotNull('post_id')
                        ->latest('last_insights_sync')
                        ->limit(100)
                        ->get();
                    break;
                default:
                    return response()->json(['error' => 'Invalid type'], 400);
            }

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getStats(Request $request): JsonResponse
    {
        try {
            $type = $request->get('type');
            $id = $request->get('id');

            switch ($type) {
                case 'business':
                    $stats = [
                        'accounts' => FacebookAdAccount::where('business_id', $id)->count(),
                        'campaigns' => FacebookCampaign::whereHas('adAccount', function ($q) use ($id) {
                            $q->where('business_id', $id);
                        })->count(),
                        'adsets' => FacebookAdSet::whereHas('campaign.adAccount', function ($q) use ($id) {
                            $q->where('business_id', $id);
                        })->count(),
                        'ads' => FacebookAd::whereHas('campaign.adAccount', function ($q) use ($id) {
                            $q->where('business_id', $id);
                        })->count(),
                        'posts' => FacebookAd::whereHas('campaign.adAccount', function ($q) use ($id) {
                            $q->where('business_id', $id);
                        })->whereNotNull('post_id')->distinct('post_id')->count(),
                    ];
                    break;
                case 'account':
                    $stats = [
                        'campaigns' => FacebookCampaign::where('ad_account_id', $id)->count(),
                        'adsets' => FacebookAdSet::whereHas('campaign', function ($q) use ($id) {
                            $q->where('ad_account_id', $id);
                        })->count(),
                        'ads' => FacebookAd::whereHas('campaign', function ($q) use ($id) {
                            $q->where('ad_account_id', $id);
                        })->count(),
                        'posts' => FacebookAd::whereHas('campaign', function ($q) use ($id) {
                            $q->where('ad_account_id', $id);
                        })->whereNotNull('post_id')->distinct('post_id')->count(),
                    ];
                    break;
                case 'campaign':
                    $stats = [
                        'adsets' => FacebookAdSet::where('campaign_id', $id)->count(),
                        'ads' => FacebookAd::where('campaign_id', $id)->count(),
                        'posts' => FacebookAd::where('campaign_id', $id)->whereNotNull('post_id')->distinct('post_id')->count(),
                    ];
                    break;
                case 'adset':
                    $stats = [
                        'ads' => FacebookAd::where('adset_id', $id)->count(),
                        'posts' => FacebookAd::where('adset_id', $id)->whereNotNull('post_id')->distinct('post_id')->count(),
                    ];
                    break;
                default:
                    return response()->json(['error' => 'Invalid type'], 400);
            }

            return response()->json($stats);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
