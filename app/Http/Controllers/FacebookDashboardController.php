<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\FacebookAd;
use App\Models\FacebookAdAccount;
use App\Models\FacebookAdSet;
use App\Models\FacebookBusiness;
use App\Models\FacebookCampaign;
use App\Models\FacebookAdInsight;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class FacebookDashboardController extends Controller
{
    public function overview(Request $request): View
    {
        $data = $this->getOverviewData($request);
        // Map overview aggregates to $agg for Blade convenience
        $agg = $data['overviewAgg'] ?? [];
        // Debug nhanh: dd breakdowns trực tiếp nếu cần kiểm tra dữ liệu phân khúc
        if ($request->boolean('dd_breakdowns') || $request->get('dd') === 'breakdowns') {
            dd($data['breakdowns'] ?? []);
        }
        return view('facebook.dashboard.overview', compact('data', 'agg'));
    }

    public function hierarchy(Request $request): View
    {
        $data = $this->getHierarchyData();
        return view('facebook.dashboard.hierarchy', compact('data'));
    }

    public function analytics(Request $request): View
    {
        $data = $this->getAnalyticsData();
        return view('facebook.dashboard.analytics', compact('data'));
    }

    public function dataRaw(Request $request): View
    {
        $data = $this->getRawData();
        return view('facebook.dashboard.data-raw', compact('data'));
    }

    private function getOverviewData(Request $request = null): array
    {
        $request = $request ?? request();
        $from = $request->get('from');
        $to = $request->get('to');
        $selectedBusinessId = $request->get('business_id');
        $selectedAccountId = $request->get('account_id');
        $selectedCampaignId = $request->get('campaign_id');
        $selectedPageId = $request->get('page_id');

        // Phạm vi mặc định: từ ngày 1 tháng hiện tại đến hôm nay
        $hasFilters = $from || $to || $selectedBusinessId || $selectedAccountId || $selectedCampaignId || $selectedPageId;
        if (!$from || !$to) {
            $to = now()->toDateString();
            $from = now()->startOfMonth()->toDateString();
        }

        // Tối ưu: Tính totals nhanh hơn với query tối ưu
        $totals = $this->calculateOptimizedTotals($selectedBusinessId, $selectedAccountId, $selectedCampaignId, $selectedPageId, $from, $to);

        // Tối ưu: Lấy dữ liệu từ facebook_ad_insights với select fields cần thiết
        $insightsQuery = FacebookAdInsight::select([
            'facebook_ad_insights.id',
            'facebook_ad_insights.ad_id', 
            'facebook_ad_insights.date',
            'facebook_ad_insights.spend',
            'facebook_ad_insights.impressions',
            'facebook_ad_insights.clicks',
            'facebook_ad_insights.reach',
            'facebook_ad_insights.conversions',
            'facebook_ad_insights.conversion_values',
            'facebook_ad_insights.ctr',
            'facebook_ad_insights.cpc',
            'facebook_ad_insights.cpm',
            'facebook_ad_insights.page_id',
            'facebook_ad_insights.post_id',
            'facebook_ads.campaign_id',
            'facebook_ads.account_id'
        ])->join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id');
        
        // Join với facebook_ad_accounts nếu cần filter theo business
        if ($selectedBusinessId) {
            $insightsQuery->join('facebook_ad_accounts', 'facebook_ads.account_id', '=', 'facebook_ad_accounts.id')
                          ->where('facebook_ad_accounts.business_id', $selectedBusinessId);
        }
        
        if ($selectedAccountId) {
            $insightsQuery->where('facebook_ads.account_id', $selectedAccountId);
        }
        if ($selectedCampaignId) {
            $insightsQuery->where('facebook_ads.campaign_id', $selectedCampaignId);
        }
        if ($selectedPageId) {
            $insightsQuery->where('facebook_ad_insights.page_id', $selectedPageId);
        }
        
        // Tối ưu: Giới hạn số lượng records và sử dụng pagination
        if ($from && $to) {
            $insightsData = $insightsQuery->whereBetween('facebook_ad_insights.date', [$from, $to])
                ->orderBy('facebook_ad_insights.date', 'desc')
                ->limit(10000) // Giới hạn 10k records để tránh memory issues
                ->get();
        } else {
            $insightsData = $insightsQuery->orderBy('facebook_ad_insights.date', 'desc')
                ->limit(10000) // Giới hạn 10k records
                ->get();
        }

        // Aggregate tất cả chỉ số cần thiết (video, messaging, engagement, leads) có cache theo filter
        // Bump key version to invalidate old cached aggregates after metric rename fixes
        $cacheKey = 'fb_overview_agg:v2:' . md5(json_encode([
            'business' => $selectedBusinessId,
            'account' => $selectedAccountId,
            'campaign' => $selectedCampaignId,
            'page' => $selectedPageId,
            'from' => $from,
            'to' => $to,
        ]));
        $overviewAgg = \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addMinutes(10), function () use ($selectedBusinessId, $selectedAccountId, $selectedCampaignId, $selectedPageId, $from, $to) {
            $q = FacebookAdInsight::query();
            if ($selectedBusinessId || $selectedAccountId || $selectedCampaignId) {
                $q->join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id');
                if ($selectedBusinessId) {
                    $q->join('facebook_ad_accounts', 'facebook_ads.account_id', '=', 'facebook_ad_accounts.id')
                      ->where('facebook_ad_accounts.business_id', $selectedBusinessId);
                }
                if ($selectedAccountId) { $q->where('facebook_ads.account_id', $selectedAccountId); }
                if ($selectedCampaignId) { $q->where('facebook_ads.campaign_id', $selectedCampaignId); }
            }
            if ($selectedPageId) { $q->where('facebook_ad_insights.page_id', $selectedPageId); }
            if ($from && $to) { $q->whereBetween('facebook_ad_insights.date', [$from, $to]); }

            $q->selectRaw('COALESCE(SUM(spend),0) as spend')
              ->selectRaw('COALESCE(SUM(impressions),0) as impressions')
              ->selectRaw('COALESCE(SUM(clicks),0) as clicks')
              ->selectRaw('COALESCE(SUM(reach),0) as reach')
              ->selectRaw('COALESCE(AVG(ctr),0) as avg_ctr')
              ->selectRaw('COALESCE(AVG(cpc),0) as avg_cpc')
              ->selectRaw('COALESCE(AVG(cpm),0) as avg_cpm')
              // Video
              ->selectRaw('COALESCE(SUM(video_plays),0) as video_plays')
              ->selectRaw('COALESCE(SUM(video_views),0) as video_views')
              ->selectRaw('COALESCE(SUM(video_view_time),0) as video_view_time')
              ->selectRaw('COALESCE(AVG(video_avg_time_watched),0) as video_avg_time_watched')
              ->selectRaw('COALESCE(SUM(video_30_sec_watched),0) as video_30_sec_watched')
              ->selectRaw('COALESCE(SUM(video_p25_watched_actions),0) as video_p25_watched_actions')
              ->selectRaw('COALESCE(SUM(video_p50_watched_actions),0) as video_p50_watched_actions')
              ->selectRaw('COALESCE(SUM(video_p75_watched_actions),0) as video_p75_watched_actions')
              ->selectRaw('COALESCE(SUM(video_p95_watched_actions),0) as video_p95_watched_actions')
              ->selectRaw('COALESCE(SUM(video_p100_watched_actions),0) as video_p100_watched_actions')
            
              ->selectRaw('COALESCE(SUM(video_30_sec_watched),0) as video_30s')
              ->selectRaw('COALESCE(SUM(video_p25_watched_actions),0) as v_p25')
              ->selectRaw('COALESCE(SUM(video_p50_watched_actions),0) as v_p50')
              ->selectRaw('COALESCE(SUM(video_p75_watched_actions),0) as v_p75')
              ->selectRaw('COALESCE(SUM(video_p95_watched_actions),0) as v_p95')
              ->selectRaw('COALESCE(SUM(video_p100_watched_actions),0) as v_p100')
              ->selectRaw('COALESCE(SUM(thruplays),0) as thruplays')
              // Messaging
              ->selectRaw('COALESCE(SUM(messaging_conversation_started_7d),0) as msg_started')
              ->selectRaw('COALESCE(SUM(total_messaging_connection),0) as msg_total')
              ->selectRaw('COALESCE(SUM(messaging_conversation_replied_7d),0) as msg_replied')
              ->selectRaw('COALESCE(SUM(messaging_welcome_message_view),0) as msg_welcome')
              ->selectRaw('COALESCE(SUM(messaging_first_reply),0) as msg_first_reply')
              ->selectRaw('COALESCE(SUM(messaging_user_depth_2_message_send),0) as msg_depth2')
              ->selectRaw('COALESCE(SUM(messaging_user_depth_3_message_send),0) as msg_depth3')
              ->selectRaw('COALESCE(SUM(messaging_user_depth_5_message_send),0) as msg_depth5')
              ->selectRaw('COALESCE(SUM(messaging_block),0) as msg_block')
              // Engagement
              ->selectRaw('COALESCE(SUM(post_engagement),0) as post_engagement')
              ->selectRaw('COALESCE(SUM(page_engagement),0) as page_engagement')
              ->selectRaw('COALESCE(SUM(post_interaction_gross),0) as post_interaction_gross')
              ->selectRaw('COALESCE(SUM(post_reaction),0) as post_reaction')
              ->selectRaw('COALESCE(SUM(link_click),0) as link_click')
              // Leads & Checkout
              ->selectRaw('COALESCE(SUM(lead),0) as lead')
              ->selectRaw('COALESCE(SUM(onsite_conversion_lead),0) as onsite_conversion_lead')
              ->selectRaw('COALESCE(SUM(onsite_web_lead),0) as onsite_web_lead')
              ->selectRaw('COALESCE(SUM(lead_grouped),0) as lead_grouped')
              ->selectRaw('COALESCE(SUM(offsite_complete_registration_add_meta_leads),0) as offsite_complete_registration_add_meta_leads')
              ->selectRaw('COALESCE(SUM(offsite_search_add_meta_leads),0) as offsite_search_add_meta_leads')
              ->selectRaw('COALESCE(SUM(offsite_content_view_add_meta_leads),0) as offsite_content_view_add_meta_leads')
              ->selectRaw('COALESCE(SUM(onsite_conversion_initiate_checkout),0) as onsite_conversion_initiate_checkout')
              ->selectRaw('COALESCE(SUM(onsite_web_initiate_checkout),0) as onsite_web_initiate_checkout')
              ->selectRaw('COALESCE(SUM(omni_initiated_checkout),0) as omni_initiated_checkout');

            $row = $q->first();
            $agg = $row ? $row->toArray() : [];

            // Also enrich with Page-level organic vs paid conversations (28d) if page filter is selected
            if ($selectedPageId) {
                $pageAgg = \Illuminate\Support\Facades\DB::table('facebook_fanpage')
                    ->where('page_id', $selectedPageId)
                    ->first([
                        'msg_new_conversations_day', 'msg_paid_conversations_day', 'msg_organic_conversations_day',
                        'msg_new_conversations_28d', 'msg_paid_conversations_28d', 'msg_organic_conversations_28d',
                        'messages_last_synced_at',
                    ]);
                if ($pageAgg) {
                    $agg['page_msg_day_total'] = (int) ($pageAgg->msg_new_conversations_day ?? 0);
                    $agg['page_msg_day_paid'] = (int) ($pageAgg->msg_paid_conversations_day ?? 0);
                    $agg['page_msg_day_organic'] = (int) ($pageAgg->msg_organic_conversations_day ?? 0);
                    $agg['page_msg_28d_total'] = (int) ($pageAgg->msg_new_conversations_28d ?? 0);
                    $agg['page_msg_28d_paid'] = (int) ($pageAgg->msg_paid_conversations_28d ?? 0);
                    $agg['page_msg_28d_organic'] = (int) ($pageAgg->msg_organic_conversations_28d ?? 0);
                }
            }

            return $agg;
        });

        // Debug: cho phép dd nhanh aggregate video khi có ?dd=video
        if ($request->get('dd') === 'video') {
            dd($overviewAgg);
        }

        // Fallback: nếu một số chỉ số mới (video/messaging/engagement) bị 0 do dữ liệu lịch sử chưa fill cột,
        // ta tính nhanh từ JSON actions trong 30 ngày để hiển thị đúng ở Overview
        $fallbackNeeded = (
            (int)($overviewAgg['video_plays'] ?? 0) === 0 ||
            (int)($overviewAgg['msg_started'] ?? 0) === 0 ||
            (int)($overviewAgg['msg_replied'] ?? 0) === 0 ||
            (int)($overviewAgg['link_click'] ?? 0) === 0
        );
        if ($fallbackNeeded) {
            $q2 = FacebookAdInsight::query();
            if ($selectedBusinessId || $selectedAccountId || $selectedCampaignId) {
                $q2->join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id');
                if ($selectedBusinessId) {
                    $q2->join('facebook_ad_accounts', 'facebook_ads.account_id', '=', 'facebook_ad_accounts.id')
                       ->where('facebook_ad_accounts.business_id', $selectedBusinessId);
                }
                if ($selectedAccountId) { $q2->where('facebook_ads.account_id', $selectedAccountId); }
                if ($selectedCampaignId) { $q2->where('facebook_ads.campaign_id', $selectedCampaignId); }
            }
            if ($selectedPageId) { $q2->where('facebook_ad_insights.page_id', $selectedPageId); }
            if ($from && $to) { $q2->whereBetween('facebook_ad_insights.date', [$from, $to]); }

            $actionTotals = [];
            $q2->select('facebook_ad_insights.id','facebook_ad_insights.actions')
               ->orderBy('facebook_ad_insights.id')
               ->chunk(5000, function ($rows) use (&$actionTotals) {
                    foreach ($rows as $row) {
                        $actions = $row->actions;
                        if (is_string($actions)) {
                            $decoded = json_decode($actions, true);
                        } else {
                            $decoded = is_array($actions) ? $actions : [];
                        }
                        foreach ($decoded as $a) {
                            $type = $a['action_type'] ?? null; $val = (int)($a['value'] ?? 0);
                            if (!$type || $val === 0) { continue; }
                            $actionTotals[$type] = ($actionTotals[$type] ?? 0) + $val;
                        }
                    }
               });

            // Map về overviewAgg nếu đang 0
            $mapIfZero = function(string $key, int $value) use (&$overviewAgg) {
                if (!isset($overviewAgg[$key]) || (int)$overviewAgg[$key] === 0) { $overviewAgg[$key] = $value; }
            };
            // fill canonical keys
            $mapIfZero('video_plays', (int)($actionTotals['video_view'] ?? 0));
            $mapIfZero('thruplays', (int)(($actionTotals['video_thruplay_watched_actions'] ?? 0) + ($actionTotals['thruplay'] ?? 0)));
            $mapIfZero('video_p25_watched_actions', (int)($actionTotals['video_p25_watched_actions'] ?? 0));
            $mapIfZero('video_p50_watched_actions', (int)($actionTotals['video_p50_watched_actions'] ?? 0));
            $mapIfZero('video_p75_watched_actions', (int)($actionTotals['video_p75_watched_actions'] ?? 0));
            $mapIfZero('video_p95_watched_actions', (int)($actionTotals['video_p95_watched_actions'] ?? 0));
            $mapIfZero('video_p100_watched_actions', (int)($actionTotals['video_p100_watched_actions'] ?? 0));
            $mapIfZero('video_30_sec_watched', (int)($actionTotals['video_30_sec_watched_actions'] ?? 0));
            // also backfill legacy alias keys used by the chart
            $mapIfZero('v_p25', (int)($actionTotals['video_p25_watched_actions'] ?? 0));
            $mapIfZero('v_p50', (int)($actionTotals['video_p50_watched_actions'] ?? 0));
            $mapIfZero('v_p75', (int)($actionTotals['video_p75_watched_actions'] ?? 0));
            $mapIfZero('v_p95', (int)($actionTotals['video_p95_watched_actions'] ?? 0));
            $mapIfZero('v_p100', (int)($actionTotals['video_p100_watched_actions'] ?? 0));

            $mapIfZero('msg_started', (int)(($actionTotals['onsite_conversion.messaging_conversation_started_7d'] ?? 0) + ($actionTotals['omni_messaging_conversation_started_7d'] ?? 0)));
            $mapIfZero('msg_total', (int)(($actionTotals['onsite_conversion.total_messaging_connection'] ?? 0) + ($actionTotals['omni_total_messaging_connection'] ?? 0)));
            $mapIfZero('msg_replied', (int)(($actionTotals['onsite_conversion.messaging_conversation_replied_7d'] ?? 0) + ($actionTotals['omni_messaging_conversation_replied_7d'] ?? 0)));
            $mapIfZero('msg_welcome', (int)($actionTotals['onsite_conversion.messaging_welcome_message_view'] ?? 0));
            $mapIfZero('post_engagement', (int)($actionTotals['post_engagement'] ?? 0));
            $mapIfZero('page_engagement', (int)($actionTotals['page_engagement'] ?? 0));
            $mapIfZero('post_reaction', (int)($actionTotals['post_reaction'] ?? 0));
            $mapIfZero('post_interaction_gross', (int)($actionTotals['post_interaction_gross'] ?? 0));
            $mapIfZero('link_click', (int)($actionTotals['link_click'] ?? 0));
            $mapIfZero('lead', (int)($actionTotals['lead'] ?? 0));
            $mapIfZero('onsite_conversion_lead', (int)($actionTotals['onsite_conversion.lead'] ?? 0));
            $mapIfZero('onsite_web_lead', (int)($actionTotals['onsite_web_lead'] ?? 0));
            $mapIfZero('lead_grouped', (int)($actionTotals['onsite_conversion.lead_grouped'] ?? 0));
            $mapIfZero('onsite_conversion_initiate_checkout', (int)($actionTotals['onsite_conversion.initiate_checkout'] ?? 0));
            $mapIfZero('onsite_web_initiate_checkout', (int)($actionTotals['onsite_web_initiate_checkout'] ?? 0));
            $mapIfZero('omni_initiated_checkout', (int)($actionTotals['omni_initiated_checkout'] ?? 0));
        }

        // Ép kiểu số cho các chỉ số tổng hợp để view không bị 0 do kiểu string/null
        foreach ([
            'spend','impressions','clicks','reach',
            'video_plays','video_views','video_view_time','video_30_sec_watched','video_30s',
            'video_p25_watched_actions','video_p50_watched_actions','video_p75_watched_actions','video_p95_watched_actions','video_p100_watched_actions','v_p25','v_p50','v_p75','v_p95','v_p100','thruplays',
            'msg_started','msg_total','msg_replied','msg_welcome',
            'post_engagement','page_engagement','post_interaction_gross','post_reaction','link_click',
            'lead','onsite_conversion_lead','onsite_web_lead','lead_grouped',
            'onsite_conversion_initiate_checkout','onsite_web_initiate_checkout','omni_initiated_checkout'
        ] as $key) {
            if (isset($overviewAgg[$key]) && is_numeric($overviewAgg[$key])) {
                $overviewAgg[$key] = (int) $overviewAgg[$key];
            }
        }

        // Chuỗi hoạt động từ trước tới nay (theo dải from/to đã xác định ở trên)
        $activityAll = [];
        if ($insightsData->count() > 0) {
            $grouped = $insightsData->groupBy('date')->sortKeys();
            foreach ($grouped as $d => $dayData) {
                $activityAll[] = [
                    'date' => $d instanceof \Carbon\Carbon ? $d->toDateString() : (string) $d,
                    'ads' => $dayData->count(),
                    'posts' => $dayData->whereNotNull('post_id')->count(),
                    'campaigns' => $dayData->pluck('campaign_id')->unique()->count(),
                    'spend' => $dayData->sum('spend'),
                ];
            }
        }

        // Tính toán stats tổng hợp
        $stats = [
            'total_spend' => $insightsData->sum('spend'),
            'total_impressions' => $insightsData->sum('impressions'),
            'total_clicks' => $insightsData->sum('clicks'),
            'total_reach' => $insightsData->sum('reach'),
            'total_conversions' => $insightsData->sum('conversions'),
            'total_conversion_values' => $insightsData->sum('conversion_values'),
            'avg_ctr' => $insightsData->avg('ctr'),
            'avg_cpc' => $insightsData->avg('cpc'),
            'avg_cpm' => $insightsData->avg('cpm'),
        ];

        // Top performing ads - áp dụng filter
        $topAds = $this->getFilteredTopAds($selectedBusinessId, $selectedAccountId, $selectedCampaignId, $selectedPageId, $from, $to);

        // Top performing posts - áp dụng filter
        $facebookDataService = new \App\Services\FacebookDataService();
        $topPosts = $this->getFilteredTopPosts($selectedBusinessId, $selectedAccountId, $selectedCampaignId, $selectedPageId, $from, $to);
        
        // Lấy breakdown data cho overview - áp dụng filter
        $breakdowns = $this->getFilteredBreakdowns($selectedBusinessId, $selectedAccountId, $selectedCampaignId, $selectedPageId, $from, $to);
        


        // Lấy accounts và campaigns cho filter - luôn lấy tất cả để dropdown không bị mất options
        $accounts = FacebookAdAccount::select('id', 'name', 'account_id', 'business_id')->get();
        $campaigns = FacebookCampaign::select('id', 'name', 'ad_account_id')->get();
        
        // Tối ưu: Sử dụng lại insightsData đã load ở trên thay vì query lại
        $allInsightsData = $insightsData;
        
        // Lấy Business Managers cho filter - luôn lấy tất cả để dropdown không bị mất options
        $businesses = FacebookBusiness::select('id', 'name')->get();
        
        // Lấy Facebook Pages cho filter - luôn lấy tất cả để dropdown không bị mất options
        $uniquePageIds = FacebookAdInsight::whereNotNull('facebook_ad_insights.page_id')->distinct('facebook_ad_insights.page_id')->pluck('facebook_ad_insights.page_id');
        $pages = $uniquePageIds->map(function($pageId) {
            // Tìm business_id của page này từ insights data
            $pageInsights = FacebookAdInsight::where('facebook_ad_insights.page_id', $pageId)
                ->join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
                ->join('facebook_ad_accounts', 'facebook_ads.account_id', '=', 'facebook_ad_accounts.id')
                ->first();
            
            $businessId = $pageInsights ? $pageInsights->business_id : null;
            
            return (object) [
                'id' => $pageId,
                'name' => 'Page ' . $pageId,
                'business_id' => $businessId
            ];
        });

        // Thống kê trạng thái cho biểu đồ donut - áp dụng filter
        $statusStats = $this->calculateFilteredStatusStats($selectedBusinessId, $selectedAccountId, $selectedCampaignId, $selectedPageId, $from, $to);

        // Hiệu suất tổng hợp để hiển thị phụ chú
        $performanceStats = [
            'totalSpend' => (float) $insightsData->sum('spend'),
            'totalImpressions' => (int) $insightsData->sum('impressions'),
            'totalClicks' => (int) $insightsData->sum('clicks'),
            'totalReach' => (int) $insightsData->sum('reach'),
            'totalConversions' => (int) $insightsData->sum('conversions'),
            'totalConversionValues' => (float) $insightsData->sum('conversion_values'),
            'avgCTR' => (float) $insightsData->avg('ctr'),
            'avgCPC' => (float) $insightsData->avg('cpc'),
            'avgCPM' => (float) $insightsData->avg('cpm'),
        ];

        return [
            'totals' => $totals,
            'stats' => $stats,
            'overviewAgg' => $overviewAgg,
            'last7Days' => $activityAll, // dùng key cũ cho biểu đồ – nay là all-time
            'topAds' => $topAds,
            'topPosts' => $topPosts,
            'breakdowns' => $breakdowns,
            'statusStats' => $statusStats,
            'performanceStats' => $performanceStats,
            'filters' => [
                'from' => $from,
                'to' => $to,
                'account_id' => $selectedAccountId,
                'campaign_id' => $selectedCampaignId,
                'business_id' => $request->get('business_id'),
                'page_id' => $selectedPageId,
                'accounts' => $accounts,
                'campaigns' => $campaigns,
                'businesses' => $businesses,
                'pages' => $pages,
            ]
        ];
    }

    /**
     * Tính toán totals tối ưu theo filter đã chọn
     */
    private function calculateOptimizedTotals($selectedBusinessId, $selectedAccountId, $selectedCampaignId, $selectedPageId, $from, $to): array
    {
        // Xác định loại filter: entity filters vs chỉ filter theo ngày
        $entityFiltersApplied = $selectedBusinessId || $selectedAccountId || $selectedCampaignId || $selectedPageId;
        $hasFilters = $entityFiltersApplied || ($from && $to);
        
        $totals = [
            'businesses' => 0,
            'accounts' => 0,
            'campaigns' => 0,
            'adsets' => 0,
            'ads' => 0,
            'pages' => 0,
            'posts' => 0,
            'ad_insights' => 0,
        ];
        
        if (!$hasFilters || (!$entityFiltersApplied && $hasFilters)) {
            // Khi không có filter, sử dụng count() từ bảng chính
            $totals['businesses'] = FacebookBusiness::count();
            $totals['accounts'] = FacebookAdAccount::count();
            $totals['campaigns'] = FacebookCampaign::count();
            $totals['ads'] = FacebookAd::count();
            $totals['adsets'] = FacebookAdSet::count();
            $totals['ad_insights'] = FacebookAdInsight::count();

            // Count pages và posts từ insights
            $totals['pages'] = FacebookAdInsight::whereNotNull('facebook_ad_insights.page_id')->distinct('facebook_ad_insights.page_id')->count();
            $totals['posts'] = FacebookAdInsight::whereNotNull('facebook_ad_insights.post_id')->distinct('facebook_ad_insights.post_id')->count();
        } else {
            // Khi có filter, sử dụng query tối ưu
            $insightsQuery = FacebookAdInsight::query();
            
            // Apply filters với joins chỉ khi cần
            if ($selectedBusinessId || $selectedAccountId || $selectedCampaignId) {
                $insightsQuery->join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id');
                
                if ($selectedBusinessId) {
                    $insightsQuery->join('facebook_ad_accounts', 'facebook_ads.account_id', '=', 'facebook_ad_accounts.id')
                                  ->where('facebook_ad_accounts.business_id', $selectedBusinessId);
                }
                
                if ($selectedAccountId) {
                    $insightsQuery->where('facebook_ads.account_id', $selectedAccountId);
                }
                
                if ($selectedCampaignId) {
                    $insightsQuery->where('facebook_ads.campaign_id', $selectedCampaignId);
                }
            }
            
            if ($selectedPageId) {
                $insightsQuery->where('facebook_ad_insights.page_id', $selectedPageId);
            }
            
            // Chỉ áp dụng filter thời gian nếu có
            if ($from && $to) {
                $insightsQuery->whereBetween('facebook_ad_insights.date', [$from, $to]);
            }
            
            // Count insights (theo dải ngày nếu có)
            $totals['ad_insights'] = $insightsQuery->count('facebook_ad_insights.id');
            
            // Count unique values từ insights - sửa lỗi ambiguous column
            $totals['pages'] = $insightsQuery->whereNotNull('facebook_ad_insights.page_id')->distinct('facebook_ad_insights.page_id')->count();
            $totals['posts'] = $insightsQuery->whereNotNull('facebook_ad_insights.post_id')->distinct('facebook_ad_insights.post_id')->count();
            
            // Count ads, campaigns, accounts từ filtered insights (chỉ bị ảnh hưởng bởi entity filters, không theo ngày)
            $adsScope = FacebookAd::query();
            if ($selectedBusinessId) {
                $adsScope->whereIn('account_id', FacebookAdAccount::where('business_id', $selectedBusinessId)->pluck('id'));
            }
            if ($selectedAccountId) {
                $adsScope->where('account_id', $selectedAccountId);
            }
            if ($selectedCampaignId) {
                $adsScope->where('campaign_id', $selectedCampaignId);
            }
            if ($selectedPageId) {
                $adsScope->where('page_id', $selectedPageId);
            }
            $totals['ads'] = (clone $adsScope)->count('id');
            $totals['campaigns'] = (clone $adsScope)->distinct('campaign_id')->count('campaign_id');
            $totals['accounts'] = (clone $adsScope)->distinct('account_id')->count('account_id');
            $totals['adsets'] = (clone $adsScope)->distinct('adset_id')->count('adset_id');
            $accountIds = (clone $adsScope)->distinct('account_id')->pluck('account_id');
            if ($selectedBusinessId) { $totals['businesses'] = 1; }
            else if ($accountIds->isNotEmpty()) { $totals['businesses'] = FacebookAdAccount::whereIn('id', $accountIds)->distinct('business_id')->count('business_id'); }
            else { $totals['businesses'] = FacebookBusiness::count(); }
        }
        
        return $totals;
    }

    /**
     * Tính toán totals theo filter đã chọn (deprecated - sử dụng calculateOptimizedTotals)
     */
    private function calculateFilteredTotals($selectedBusinessId, $selectedAccountId, $selectedCampaignId, $selectedPageId, $from, $to): array
    {
        // Kiểm tra xem có filter nào được áp dụng không
        $hasFilters = $selectedBusinessId || $selectedAccountId || $selectedCampaignId || $selectedPageId || ($from && $to);
        
        if (!$hasFilters) {
            // Khi không có filter, lấy toàn bộ dữ liệu
            $insightsData = FacebookAdInsight::all();
        } else {
            // Khi có filter, áp dụng các điều kiện
            $insightsQuery = FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id');
            
            // Apply filters
            if ($selectedBusinessId) {
                $insightsQuery->join('facebook_ad_accounts', 'facebook_ads.account_id', '=', 'facebook_ad_accounts.id')
                              ->where('facebook_ad_accounts.business_id', $selectedBusinessId);
            }
            
            if ($selectedAccountId) {
                $insightsQuery->where('facebook_ads.account_id', $selectedAccountId);
            }
            
            if ($selectedCampaignId) {
                $insightsQuery->where('facebook_ads.campaign_id', $selectedCampaignId);
            }
            
            if ($selectedPageId) {
                $insightsQuery->where('facebook_ad_insights.page_id', $selectedPageId);
            }
            
            // Chỉ áp dụng filter thời gian nếu có
            if ($from && $to) {
                $insightsQuery->whereBetween('facebook_ad_insights.date', [$from, $to]);
            }
            
            $insightsData = $insightsQuery->get();
        }
        
        // Tính totals từ insights data đã filter
        $totals = [
            'businesses' => 0,
            'accounts' => 0,
            'campaigns' => 0,
            'adsets' => 0,
            'ads' => 0,
            'pages' => 0,
            'posts' => 0,
            'ad_insights' => $insightsData->count(),
        ];
        
        if ($insightsData->count() > 0) {
            if (!$hasFilters) {
                // Khi không có filter, đếm tất cả từ insights data
                $uniqueAdIds = $insightsData->pluck('ad_id')->unique();
                $uniquePageIds = $insightsData->whereNotNull('page_id')->pluck('page_id')->unique();
                $uniquePostIds = $insightsData->whereNotNull('post_id')->pluck('post_id')->unique();
                
                // Lấy thông tin campaign và account từ ads
                $adsData = FacebookAd::whereIn('id', $uniqueAdIds)->get();
                $uniqueCampaignIds = $adsData->pluck('campaign_id')->unique();
                $uniqueAccountIds = $adsData->pluck('account_id')->unique();
                $uniqueAdsetIds = $adsData->pluck('adset_id')->unique();
                
                // Khi không có filter, đếm tất cả từ bảng chính
                $totals['businesses'] = FacebookBusiness::count();
                $totals['accounts'] = FacebookAdAccount::count();
                $totals['campaigns'] = FacebookCampaign::count();
                $totals['ads'] = FacebookAd::count();
                $totals['adsets'] = FacebookAdSet::count();
                $totals['pages'] = $uniquePageIds->count();
                $totals['posts'] = $uniquePostIds->count();
            } else {
                // Khi có filter, đếm từ insights data đã filter
                $uniqueAdIds = $insightsData->pluck('ad_id')->unique();
                $uniquePageIds = $insightsData->whereNotNull('page_id')->pluck('page_id')->unique();
                $uniquePostIds = $insightsData->whereNotNull('post_id')->pluck('post_id')->unique();
                
                // Lấy thông tin campaign và account từ ads
                $adsData = FacebookAd::whereIn('id', $uniqueAdIds)->get();
                $uniqueCampaignIds = $adsData->pluck('campaign_id')->unique();
                $uniqueAccountIds = $adsData->pluck('account_id')->unique();
                $uniqueAdsetIds = $adsData->pluck('adset_id')->unique();
                
                // Đếm từ các bảng chính
                $totals['ads'] = $uniqueAdIds->count();
                $totals['campaigns'] = $uniqueCampaignIds->count();
                $totals['accounts'] = $uniqueAccountIds->count();
                $totals['adsets'] = $uniqueAdsetIds->count();
                $totals['pages'] = $uniquePageIds->count();
                $totals['posts'] = $uniquePostIds->count();
                
                // Đếm businesses từ accounts
                if ($uniqueAccountIds->count() > 0) {
                    $totals['businesses'] = FacebookAdAccount::whereIn('id', $uniqueAccountIds)
                        ->distinct('business_id')
                        ->count('business_id');
                }
            }
        }
        
        return $totals;
    }

    /**
     * Tính toán status stats theo filter đã chọn
     */
    private function calculateFilteredStatusStats($selectedBusinessId, $selectedAccountId, $selectedCampaignId, $selectedPageId, $from, $to): array
    {
        // Kiểm tra xem có filter nào được áp dụng không
        $hasFilters = $selectedBusinessId || $selectedAccountId || $selectedCampaignId || $selectedPageId || ($from && $to);
        
        if (!$hasFilters) {
            // Khi không có filter, lấy toàn bộ dữ liệu
            $insightsData = FacebookAdInsight::all();
        } else {
            // Khi có filter, áp dụng các điều kiện
            $insightsQuery = FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id');
            
            // Apply filters
            if ($selectedBusinessId) {
                $insightsQuery->join('facebook_ad_accounts', 'facebook_ads.account_id', '=', 'facebook_ad_accounts.id')
                              ->where('facebook_ad_accounts.business_id', $selectedBusinessId);
            }
            
            if ($selectedAccountId) {
                $insightsQuery->where('facebook_ads.account_id', $selectedAccountId);
            }
            
            if ($selectedCampaignId) {
                $insightsQuery->where('facebook_ads.campaign_id', $selectedCampaignId);
            }
            
            if ($selectedPageId) {
                $insightsQuery->where('facebook_ad_insights.page_id', $selectedPageId);
            }
            
            // Chỉ áp dụng filter thời gian nếu có
            if ($from && $to) {
                $insightsQuery->whereBetween('facebook_ad_insights.date', [$from, $to]);
            }
            
            $insightsData = $insightsQuery->get();
        }
        
        $statusStats = [
            'campaigns' => [],
            'ads' => [],
        ];
        
        if ($insightsData->count() > 0) {
            // Lấy unique ad IDs và campaign IDs
            $uniqueAdIds = $insightsData->pluck('ad_id')->unique();
            $adsData = FacebookAd::whereIn('id', $uniqueAdIds)->get();
            $uniqueCampaignIds = $adsData->pluck('campaign_id')->unique();
            
            // Thống kê status của campaigns
            if ($uniqueCampaignIds->count() > 0) {
                $statusStats['campaigns'] = FacebookCampaign::whereIn('id', $uniqueCampaignIds)
                    ->select('status', DB::raw('count(*) as count'))
                    ->groupBy('status')
                    ->pluck('count', 'status')
                    ->toArray();
            }
            
            // Thống kê status của ads
            if ($uniqueAdIds->count() > 0) {
                $statusStats['ads'] = FacebookAd::whereIn('id', $uniqueAdIds)
                    ->select('status', DB::raw('count(*) as count'))
                    ->groupBy('status')
                    ->pluck('count', 'status')
                    ->toArray();
            }
        }
        
        return $statusStats;
    }

    /**
     * Lấy top ads theo filter đã chọn
     */
    private function getFilteredTopAds($selectedBusinessId, $selectedAccountId, $selectedCampaignId, $selectedPageId, $from, $to)
    {
        // Base query cho insights
        $insightsQuery = FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id');
        
        // Apply filters
        if ($selectedBusinessId) {
            $insightsQuery->join('facebook_ad_accounts', 'facebook_ads.account_id', '=', 'facebook_ad_accounts.id')
                          ->where('facebook_ad_accounts.business_id', $selectedBusinessId);
        }
        
        if ($selectedAccountId) {
            $insightsQuery->where('facebook_ads.account_id', $selectedAccountId);
        }
        
        if ($selectedCampaignId) {
            $insightsQuery->where('facebook_ads.campaign_id', $selectedCampaignId);
        }
        
        if ($selectedPageId) {
            $insightsQuery->where('facebook_ad_insights.page_id', $selectedPageId);
        }
        
        // Chỉ áp dụng filter thời gian nếu có
        if ($from && $to) {
            $insightsQuery->whereBetween('facebook_ad_insights.date', [$from, $to]);
        }
        
        $insightsData = $insightsQuery->get();
        
        if ($insightsData->count() === 0) {
            return collect();
        }
        
        // Lấy unique ad IDs
        $uniqueAdIds = $insightsData->pluck('ad_id')->unique();
        
        // Lấy top ads với insights data
        $adsQuery = FacebookAd::with(['campaign', 'adSet'])
            ->whereIn('id', $uniqueAdIds);
            
        // Chỉ áp dụng filter thời gian cho withSum nếu có filter
        if ($from && $to) {
            $adsQuery->withSum(['insights as total_spend' => function ($query) use ($from, $to) {
                $query->whereBetween('date', [$from, $to]);
            }], 'spend')
            ->withSum(['insights as total_impressions' => function ($query) use ($from, $to) {
                $query->whereBetween('date', [$from, $to]);
            }], 'impressions')
            ->withSum(['insights as total_clicks' => function ($query) use ($from, $to) {
                $query->whereBetween('date', [$from, $to]);
            }], 'clicks');
        } else {
            $adsQuery->withSum('insights as total_spend', 'spend')
            ->withSum('insights as total_impressions', 'impressions')
            ->withSum('insights as total_clicks', 'clicks');
        }
        
        // Lấy dữ liệu, sau đó sắp xếp theo hiệu suất (CTR) rồi theo clicks và spend
        $ads = $adsQuery->get();
        if ($ads->isEmpty()) {
            return $ads;
        }
        $ads = $ads->map(function ($ad) {
            $impr = (int) ($ad->total_impressions ?? 0);
            $clicks = (int) ($ad->total_clicks ?? 0);
            $spend = (float) ($ad->total_spend ?? 0);
            // CTR làm thước đo hiệu suất chính; thêm backups
            $ad->perf_ctr = $impr > 0 ? ($clicks / $impr) : 0.0;
            $ad->perf_cpc = $clicks > 0 ? ($spend / $clicks) : INF; // thấp hơn là tốt
            return $ad;
        });
        // Sắp xếp: CTR desc, rồi clicks desc, rồi spend desc
        $ads = $ads->sort(function ($a, $b) {
            $cmp = ($b->perf_ctr <=> $a->perf_ctr);
            if ($cmp !== 0) return $cmp;
            $cmp2 = ((int)($b->total_clicks ?? 0) <=> (int)($a->total_clicks ?? 0));
            if ($cmp2 !== 0) return $cmp2;
            return ((float)($b->total_spend ?? 0) <=> (float)($a->total_spend ?? 0));
        })->values();

        return $ads->take(5);
    }

    /**
     * Lấy top posts theo filter đã chọn
     */
    private function getFilteredTopPosts($selectedBusinessId, $selectedAccountId, $selectedCampaignId, $selectedPageId, $from, $to)
    {
        // Base query cho insights
        $insightsQuery = FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
            ->whereNotNull('facebook_ad_insights.post_id');
        
        // Apply filters
        if ($selectedBusinessId) {
            $insightsQuery->join('facebook_ad_accounts', 'facebook_ads.account_id', '=', 'facebook_ad_accounts.id')
                          ->where('facebook_ad_accounts.business_id', $selectedBusinessId);
        }
        
        if ($selectedAccountId) {
            $insightsQuery->where('facebook_ads.account_id', $selectedAccountId);
        }
        
        if ($selectedCampaignId) {
            $insightsQuery->where('facebook_ads.campaign_id', $selectedCampaignId);
        }
        
        if ($selectedPageId) {
            $insightsQuery->where('facebook_ad_insights.page_id', $selectedPageId);
        }
        
        // Chỉ áp dụng filter thời gian nếu có
        if ($from && $to) {
            $insightsQuery->whereBetween('facebook_ad_insights.date', [$from, $to]);
        }
        
        $insightsData = $insightsQuery->get();
        
        if ($insightsData->count() === 0) {
            return collect();
        }
        
        // Group by post_id và tính tổng metrics
        $postMetrics = $insightsData->groupBy('post_id')->map(function ($postInsights) {
            $firstInsight = $postInsights->first();
            return [
                'post_id' => $firstInsight->post_id,
                'page_id' => $firstInsight->page_id,
                'message' => 'Post ID: ' . $firstInsight->post_id, // Fallback message
                'total_spend' => $postInsights->sum('spend'),
                'total_impressions' => $postInsights->sum('impressions'),
                'total_clicks' => $postInsights->sum('clicks'),
                'total_reach' => $postInsights->sum('reach'),
                'total_conversions' => $postInsights->sum('conversions'),
                'total_conversion_values' => $postInsights->sum('conversion_values'),
                'total_video_views' => $postInsights->sum('video_views'),
                'avg_ctr' => $postInsights->avg('ctr'),
                'avg_cpc' => $postInsights->avg('cpc'),
                'avg_cpm' => $postInsights->avg('cpm'),
            ];
        });
        
        // Sort by total_spend và lấy top 5
        return $postMetrics->sortByDesc('total_spend')->take(5)->values();
    }

    /**
     * Lấy breakdowns theo filter đã chọn
     */
    private function getFilteredBreakdowns($selectedBusinessId, $selectedAccountId, $selectedCampaignId, $selectedPageId, $from, $to): array
    {
        try {
            // Base query cho insights
            $insightsQuery = FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id');
            
            // Apply filters
            if ($selectedBusinessId) {
                $insightsQuery->join('facebook_ad_accounts', 'facebook_ads.account_id', '=', 'facebook_ad_accounts.id')
                              ->where('facebook_ad_accounts.business_id', $selectedBusinessId);
            }
            
            if ($selectedAccountId) {
                $insightsQuery->where('facebook_ads.account_id', $selectedAccountId);
            }
            
            if ($selectedCampaignId) {
                $insightsQuery->where('facebook_ads.campaign_id', $selectedCampaignId);
            }
            
            if ($selectedPageId) {
                $insightsQuery->where('facebook_ad_insights.page_id', $selectedPageId);
            }
            
            // Chỉ áp dụng filter thời gian nếu có
            if ($from && $to) {
                $insightsQuery->whereBetween('facebook_ad_insights.date', [$from, $to]);
            }
            
            // Lấy danh sách insight IDs
            $insightIds = $insightsQuery->pluck('facebook_ad_insights.id');
            
            if ($insightIds->isEmpty()) {
                return [];
            }
            
            // Lấy breakdowns từ facebook_breakdowns
            $breakdowns = \App\Models\FacebookBreakdown::whereIn('ad_insight_id', $insightIds->all())
                ->orderBy('breakdown_type')
                ->orderBy('breakdown_value')
                ->get()
                ->groupBy('breakdown_type');
            
            $result = [];
            foreach ($breakdowns as $breakdownType => $items) {
                $result[$breakdownType] = [];
                foreach ($items as $item) {
                    $breakdownValue = $item->breakdown_value;
                    $metrics = $item->metrics ?? [];
                    
                    if (!isset($result[$breakdownType][$breakdownValue])) {
                        $result[$breakdownType][$breakdownValue] = [
                            'spend' => 0,
                            'impressions' => 0,
                            'clicks' => 0,
                            'reach' => 0,
                            'conversions' => 0,
                            'conversion_values' => 0,
                            'video_views' => 0,
                        ];
                    }
                    
                    $result[$breakdownType][$breakdownValue]['spend'] += $metrics['spend'] ?? 0;
                    $result[$breakdownType][$breakdownValue]['impressions'] += $metrics['impressions'] ?? 0;
                    $result[$breakdownType][$breakdownValue]['clicks'] += $metrics['clicks'] ?? 0;
                    $result[$breakdownType][$breakdownValue]['reach'] += $metrics['reach'] ?? 0;
                    $result[$breakdownType][$breakdownValue]['conversions'] += $metrics['conversions'] ?? 0;
                    $result[$breakdownType][$breakdownValue]['conversion_values'] += $metrics['conversion_values'] ?? 0;
                    $result[$breakdownType][$breakdownValue]['video_views'] += $metrics['video_views'] ?? 0;
                }
            }
            
            return $result;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in getFilteredBreakdowns', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }



    /**
     * Gọi AI tóm tắt tổng quan dashboard
     */
    public function overviewAiSummary(Request $request)
    {
        $data = $this->getOverviewData($request);
       
        // Nhận data breakdowns từ frontend nếu có
        $frontendBreakdowns = $request->input('breakdowns_data', []);
        
        // Ưu tiên khoảng thời gian người dùng chọn; nếu thiếu, mặc định 36 tháng
        $since = $request->get('from') ?: ($data['filters']['from'] ?? now()->subMonthsNoOverflow(36)->toDateString());
        $until = $request->get('to') ?: ($data['filters']['to'] ?? now()->toDateString());

        // Tổng hợp dữ liệu chi tiết từ database theo khoảng ngày
        $agg = FacebookAdInsight::query()
            ->whereBetween('date', [$since, $until])
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

        // Breakdown theo thiết bị/khu vực/giới tính/độ tuổi/vị trí/nền tảng – tái sử dụng service đang dùng ở Post Detail/Overview
        $breakdownsService = new \App\Services\FacebookDataService();
        $breakdownsAgg = $breakdownsService->getOverviewBreakdowns(null, $since, $until);
        // Debug optional: kiểm tra trực tiếp bảng facebook_breakdowns nếu số liệu bất thường
        if ($request->boolean('debug_breakdowns')) {
            $insightIds = FacebookAdInsight::query()
                ->whereBetween('date', [$since, $until])
                ->pluck('id');
            $raw = \App\Models\FacebookBreakdown::whereIn('ad_insight_id', $insightIds->all())
                ->limit(50)
                ->get(['ad_insight_id','breakdown_type','breakdown_value','metrics']);
            return response()->json(['raw_breakdowns_samples' => $raw, 'ids_count' => $insightIds->count(), 'since' => $since, 'until' => $until]);
        }
      
        // Fallback: nếu service không trả về, dùng breakdowns từ frontend gửi lên (giống view)
        if (empty($breakdownsAgg) && !empty($frontendBreakdowns['breakdowns'])) {
            $breakdownsAgg = $frontendBreakdowns['breakdowns'];
        }

        // Chuẩn hoá breakdowns cho AI: gom thành các nhóm dễ hiểu
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
                    'video_views' => 0
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
            if (!empty($breakdownsAgg[$k]) && is_array($breakdownsAgg[$k])) {
                foreach ($breakdownsAgg[$k] as $value => $metrics) {
                    $label = (string) ($value ?: 'unknown');
                    $sumInto($devices, $label, is_array($metrics) ? $metrics : []);
                }
            }
        }
      
        $regions = [];
        if (!empty($breakdownsAgg['region'])) {
            foreach ($breakdownsAgg['region'] as $value => $metrics) {
                $sumInto($regions, (string) ($value ?: 'unknown'), is_array($metrics) ? $metrics : []);
            }
        }
        $countries = [];
        if (!empty($breakdownsAgg['country'])) {
            foreach ($breakdownsAgg['country'] as $value => $metrics) {
                $sumInto($countries, (string) ($value ?: 'unknown'), is_array($metrics) ? $metrics : []);
            }
        }

        $ages = [];
        if (!empty($breakdownsAgg['age'])) {
            foreach ($breakdownsAgg['age'] as $value => $metrics) {
                $sumInto($ages, (string) ($value ?: 'unknown'), is_array($metrics) ? $metrics : []);
            }
        }
        $genders = [];
        if (!empty($breakdownsAgg['gender'])) {
            foreach ($breakdownsAgg['gender'] as $value => $metrics) {
                $sumInto($genders, (string) ($value ?: 'unknown'), is_array($metrics) ? $metrics : []);
            }
        }

        $placements = [
            'publisher_platform' => [],
            'platform_position' => [],
            'impression_device' => [],
        ];
        foreach (['publisher_platform','platform_position','impression_device'] as $k) {
            if (!empty($breakdownsAgg[$k]) && is_array($breakdownsAgg[$k])) {
                foreach ($breakdownsAgg[$k] as $value => $metrics) {
                    $sumInto($placements[$k], (string) ($value ?: 'unknown'), is_array($metrics) ? $metrics : []);
                }
            }
        }

        // Tính toán top/worst cho từng nhóm để AI có thể suy luận trực tiếp
        $computeTopWorst = function(array $bucket, string $by = 'spend', int $limit = 5) {
            // Chuẩn hoá thành mảng [name => metrics]
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
            'last7Days' => $data['last7Days'] ?? [],
            'status' => $data['statusStats'] ?? [],
            // Dữ liệu phân khúc đã chuẩn hoá cho AI
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
            // Dữ liệu breakdowns từ frontend (nếu có)
            'frontend_breakdowns' => $frontendBreakdowns,
            // Raw để debug khi cần
            'breakdowns_raw' => $breakdownsAgg,
        ];

        $gemini = new \App\Services\GeminiService();
        // Debug optional: nếu ?debug=1 sẽ trả ra dữ liệu tổng hợp thay vì gọi AI
        if ($request->boolean('debug')) {
            return response()->json([
                'ok' => true,
                'debug' => true,
                'metrics' => $metrics,
            ]);
        }

        $summary = $gemini->generateMarketingSummary('facebook-dashboard', $since, $until, $metrics);
        return response()->json([
            'ok' => true, 
            'summary' => $summary, 
            'hasBreakdowns' => !empty($metrics['breakdowns']),
            'hasFrontendBreakdowns' => !empty($frontendBreakdowns),
            'breakdownsCount' => count($frontendBreakdowns)
        ]);
    }

    private function getHierarchyData(): array
    {
        // Sử dụng cấu trúc mới với relationships
        $businesses = FacebookBusiness::withCount(['adAccounts'])
            ->with(['adAccounts' => function ($query) {
                $query->withCount(['campaigns']);
            }])
            ->get();

        $campaigns = FacebookCampaign::withCount(['adSets', 'ads'])
            ->with(['adSets' => function ($query) {
                $query->withCount('ads');
            }])
            ->get();

        $adSets = FacebookAdSet::withCount('ads')
            ->with(['ads' => function ($query) {
                $query->with('insights');
            }])
            ->get();

        return [
            'businesses' => $businesses,
            'campaigns' => $campaigns,
            'adsets' => $adSets,
        ];
    }

    private function getAnalyticsData(): array
    {
        // Sử dụng facebook_ad_insights cho analytics
        $insightsData = FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
            ->whereBetween('facebook_ad_insights.date', [
                now()->subDays(30)->toDateString(),
                now()->toDateString()
            ])
            ->get();

        $dailyStats = $insightsData->groupBy('date')->map(function ($dayData) {
            $firstInsight = $dayData->first();
            $date = $firstInsight->date;
            return [
                'date' => $date instanceof \Carbon\Carbon ? $date->toDateString() : (string) $date,
                'total_spend' => $dayData->sum('spend'),
                'total_impressions' => $dayData->sum('impressions'),
                'total_clicks' => $dayData->sum('clicks'),
                'total_reach' => $dayData->sum('reach'),
                'avg_ctr' => $dayData->avg('ctr'),
                'avg_cpc' => $dayData->avg('cpc'),
                'avg_cpm' => $dayData->avg('cpm'),
            ];
        })->values();

        // Performance by campaign
        $campaignPerformance = FacebookCampaign::with(['ads.insights'])
            ->withSum(['ads.insights as total_spend' => function ($query) {
                $query->whereBetween('date', [now()->subDays(30)->toDateString(), now()->toDateString()]);
            }], 'spend')
            ->withSum(['ads.insights as total_impressions' => function ($query) {
                $query->whereBetween('date', [now()->subDays(30)->toDateString(), now()->toDateString()]);
            }], 'impressions')
            ->withSum(['ads.insights as total_clicks' => function ($query) {
                $query->whereBetween('date', [now()->subDays(30)->toDateString(), now()->toDateString()]);
            }], 'clicks')
            ->orderByDesc('total_spend')
            ->limit(20)
            ->get();

        // Performance by post type - sử dụng dữ liệu từ facebook_ads
        $postTypePerformance = FacebookAd::whereNotNull('facebook_ads.post_id')
            ->whereHas('insights', function ($query) {
                $query->whereBetween('date', [now()->subDays(30)->toDateString(), now()->toDateString()]);
            })
            ->withSum(['insights as total_spend' => function ($query) {
                $query->whereBetween('date', [now()->subDays(30)->toDateString(), now()->toDateString()]);
            }], 'spend')
            ->groupBy('facebook_ads.post_id')
            ->select('facebook_ads.post_id', DB::raw('count(*) as ad_count'), DB::raw('sum(total_spend) as total_spend'))
            ->get();

        return [
            'dailyStats' => $dailyStats,
            'campaignPerformance' => $campaignPerformance,
            'postTypePerformance' => $postTypePerformance,
        ];
    }

    private function getRawData(): array
    {
        // Sử dụng cấu trúc mới với pagination
        $ads = FacebookAd::with(['campaign', 'adSet', 'insights'])
            ->orderBy('created_time', 'desc')
            ->paginate(50);

        // Lấy posts từ facebook_ad_insights thay vì facebook_posts
        $posts = FacebookAdInsight::whereNotNull('facebook_ad_insights.post_id')
            ->select('facebook_ad_insights.post_id', 'facebook_ad_insights.page_id', DB::raw('MAX(facebook_ad_insights.date) as last_date'))
            ->groupBy('facebook_ad_insights.post_id', 'facebook_ad_insights.page_id')
            ->orderBy('last_date', 'desc')
            ->paginate(50);

        $insights = FacebookAdInsight::with(['ad'])
            ->orderBy('date', 'desc')
            ->paginate(50);

        return [
            'ads' => $ads,
            'posts' => $posts,
            'insights' => $insights,
        ];
    }
}