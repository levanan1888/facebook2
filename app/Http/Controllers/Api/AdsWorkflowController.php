<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FacebookBusiness;
use App\Models\FacebookAdAccount;
use App\Models\FacebookCampaign;
use App\Models\FacebookAdSet;
use App\Models\FacebookAd;
use App\Models\FacebookInsight;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AdsWorkflowController extends Controller
{
    /**
     * Trả về dữ liệu theo cấu trúc gần giống workflow n8n để dễ so sánh/xuất sang Google Sheets.
     * Query params: date=y-m-d (mặc định: hôm qua)
     */
    public function export(Request $request): JsonResponse
    {
        $date = (string) $request->query('date', now()->subDay()->toDateString());

        $businesses = FacebookBusiness::query()
            ->with(['adAccounts' => function ($q) {
                $q->select(['id','account_id','name','account_status','business_id']);
            }, 'adAccounts.campaigns' => function ($q) {
                $q->select(['id','name','status','objective','ad_account_id']);
            }, 'adAccounts.campaigns.adSets' => function ($q) {
                $q->select(['id','name','status','optimization_goal','campaign_id']);
            }, 'adAccounts.campaigns.adSets.ads' => function ($q) {
                $q->select(['id','name','status','effective_status','adset_id','campaign_id','account_id','created_time','updated_time']);
            }])
            ->get(['id','name','verification_status','created_time']);

        // Insights theo ngày cho account/adset/ad
        $insightsByRef = FacebookInsight::query()
            ->whereDate('date', $date)
            ->get(['level','ref_id','date','spend','reach','impressions','clicks','ctr','cpc','cpm','frequency','unique_clicks','actions'])
            ->groupBy(fn ($in) => $in->level . ':' . $in->ref_id);

        $data = [
            'date' => $date,
            'businesses' => [],
        ];

        foreach ($businesses as $bm) {
            $bmNode = [
                'bm_id' => $bm->id,
                'bm_name' => $bm->name,
                'verification_status' => $bm->verification_status,
                'created_time' => optional($bm->created_time)->toISOString(),
                'ad_accounts' => [],
            ];

            foreach ($bm->adAccounts as $acc) {
                $accKey = 'account:' . $acc->id;
                $accInsight = $insightsByRef->get($accKey)?->first();
                $accNode = [
                    'ad_account_id' => $acc->id,
                    'ad_account_name' => $acc->name,
                    'account_status' => $acc->account_status,
                    'insights' => $accInsight ? $this->mapInsight($accInsight) : null,
                    'campaigns' => [],
                ];

                foreach ($acc->campaigns as $camp) {
                    $campNode = [
                        'campaign_id' => $camp->id,
                        'campaign_name' => $camp->name,
                        'campaign_status' => $camp->status,
                        'objective' => $camp->objective,
                        'ad_sets' => [],
                    ];

                    foreach ($camp->adSets as $adset) {
                        $adsetKey = 'adset:' . $adset->id;
                        $adsetInsight = $insightsByRef->get($adsetKey)?->first();
                        $adsetNode = [
                            'ad_set_id' => $adset->id,
                            'ad_set_name' => $adset->name,
                            'ad_set_status' => $adset->status,
                            'optimization_goal' => $adset->optimization_goal,
                            'insights' => $adsetInsight ? $this->mapInsight($adsetInsight) : null,
                            'ads' => [],
                        ];

                        foreach ($adset->ads as $ad) {
                            $adKey = 'ad:' . $ad->id;
                            $adInsight = $insightsByRef->get($adKey)?->first();
                            $adNode = [
                                'ad_id' => $ad->id,
                                'ad_name' => $ad->name,
                                'ad_status' => $ad->status,
                                'effective_status' => $ad->effective_status,
                                'created_time' => optional($ad->created_time)->toISOString(),
                                'updated_time' => optional($ad->updated_time)->toISOString(),
                                'insights' => $adInsight ? $this->mapInsight($adInsight) : null,
                            ];
                            $adsetNode['ads'][] = $adNode;
                        }

                        $campNode['ad_sets'][] = $adsetNode;
                    }

                    $accNode['campaigns'][] = $campNode;
                }

                $bmNode['ad_accounts'][] = $accNode;
            }

            $data['businesses'][] = $bmNode;
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    private function mapInsight(FacebookInsight $in): array
    {
        return [
            'date' => optional($in->date)->toDateString(),
            'spend' => (float) $in->spend,
            'reach' => (int) $in->reach,
            'impressions' => (int) $in->impressions,
            'clicks' => (int) $in->clicks,
            'ctr' => $in->ctr !== null ? (float) $in->ctr : null,
            'cpc' => $in->cpc !== null ? (float) $in->cpc : null,
            'cpm' => $in->cpm !== null ? (float) $in->cpm : null,
            'frequency' => $in->frequency !== null ? (float) $in->frequency : null,
            'unique_clicks' => $in->unique_clicks !== null ? (int) $in->unique_clicks : null,
            'actions' => $in->actions,
        ];
    }
}



