<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\FacebookAd;
use App\Models\FacebookAdAccount;
use App\Models\FacebookAdSet;
use App\Models\FacebookBusiness;
use App\Models\FacebookCampaign;
use App\Models\FacebookCreative;
use App\Models\FacebookPost;
use App\Models\FacebookPage;
use App\Models\FacebookPostInsight;
use App\Models\FacebookAdInsight;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

class FacebookAdsSyncService
{
    private const API_VERSION = 'v23.0';
    private const BATCH_SIZE = 1000;
    private const RATE_LIMIT_DELAY = 1; // 1 giây giữa các API calls
    private ?int $lastProcessedAdInsightId = null;
    
    public function __construct(private FacebookAdsService $api)
    {
    }

    /**
     * Đồng bộ dữ liệu Facebook Ads theo cấu trúc mới đã normalize
     * Campaign → Ad Set → Ad → Ad Creative (Post) + Insights
     */
    public function syncFacebookData(?callable $onProgress = null, ?string $since = null, ?string $until = null, ?int $limit = null, bool $fixVideoMetrics = false): array
    {
        $since = $since ?: now()->format('Y-m-d');
        $until = $until ?: now()->format('Y-m-d');
        
        $result = [
            'businesses' => 0,
            'accounts' => 0,
            'campaigns' => 0,
            'adsets' => 0,
            'ads' => 0,
            'posts' => 0,
            'pages' => 0,
            'post_insights' => 0,
            'ad_insights' => 0,
            'breakdowns' => 0,
            'errors' => [],
            'start_time' => now(),
            'time_range' => [
                'since' => $since,
                'until' => $until
            ],
        ];

        try {
            $this->reportProgress($onProgress, 'Bắt đầu đồng bộ dữ liệu Facebook', $result);
            
            // 1. Lấy Business Managers
            $businesses = $this->syncBusinesses($result, $onProgress);
            
            // 2. Lấy Ad Accounts cho mỗi Business
            foreach ($businesses as $business) {
                $this->syncAdAccounts($business, $result, $onProgress);
            }
            
            // 3. Lấy Campaigns cho mỗi Ad Account
            $adAccounts = FacebookAdAccount::all();
            foreach ($adAccounts as $adAccount) {
                $this->syncCampaigns($adAccount, $result, $onProgress);
            }
            
            // 4. Lấy Ad Sets cho mỗi Campaign
            $campaigns = FacebookCampaign::all();
            foreach ($campaigns as $campaign) {
                $this->syncAdSets($campaign, $result, $onProgress);
            }
            
            // 5. Lấy Ads và Insights cho mỗi Ad Set
            $adSets = FacebookAdSet::all();
            if ($limit) {
                $adSets = $adSets->take($limit);
            }
            foreach ($adSets as $adSet) {
                $this->syncAdsAndInsights($adSet, $result, $onProgress, $fixVideoMetrics);
            }
            
            $result['end_time'] = now();
            $result['duration'] = $result['start_time']->diffInSeconds($result['end_time']);
            
            $this->reportProgress($onProgress, 'Hoàn thành đồng bộ dữ liệu', $result);
            
        } catch (\Exception $e) {
            Log::error('Lỗi trong quá trình đồng bộ: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            $result['errors'][] = [
                'stage' => 'main_sync',
                'error' => $e->getMessage(),
                'time' => now()->toISOString()
            ];
        }
        
        return $result;
    }

    /**
     * Đồng bộ Business Managers
     */
    private function syncBusinesses(array &$result, ?callable $onProgress): array
    {
        $this->reportProgress($onProgress, 'Đang lấy Business Managers', $result);
        
        $businesses = $this->api->getBusinessManagers();
        
        if (isset($businesses['error'])) {
            $result['errors'][] = [
                'stage' => 'businesses',
                'error' => $businesses['error']['message'] ?? 'Unknown error',
                'time' => now()->toISOString()
            ];
            return [];
        }
        
        $syncedBusinesses = [];
        foreach ($businesses['data'] ?? [] as $business) {
            try {
                $syncedBusiness = FacebookBusiness::updateOrCreate(
                    ['id' => $business['id']],
                    [
                        'name' => $business['name'] ?? null,
                        'verification_status' => $business['verification_status'] ?? null,
                        'created_time' => isset($business['created_time']) ? Carbon::parse($business['created_time']) : null,
                        'updated_time' => isset($business['updated_time']) ? Carbon::parse($business['updated_time']) : null,
                    ]
                );
                $syncedBusinesses[] = $syncedBusiness;
                $result['businesses']++;
            } catch (\Exception $e) {
                Log::error("Lỗi khi sync business", [
                    'business_id' => $business['id'] ?? 'unknown',
                    'error' => $e->getMessage()
                ]);
                $result['errors'][] = [
                    'stage' => 'business',
                    'business_id' => $business['id'] ?? 'unknown',
                    'error' => $e->getMessage(),
                    'time' => now()->toISOString()
                ];
            }
        }

        return $syncedBusinesses;
    }

    /**
     * Đồng bộ Ad Accounts cho Business
     */
    private function syncAdAccounts(FacebookBusiness $business, array &$result, ?callable $onProgress): void
    {
        $this->reportProgress($onProgress, "Đang lấy Ad Accounts cho Business: {$business->name}", $result);
        
        // Lấy cả client và owned ad accounts
        $clientAccounts = $this->api->getClientAdAccounts($business->id);
        $ownedAccounts = $this->api->getOwnedAdAccounts($business->id);
        
        $allAccounts = [];
        if (!isset($clientAccounts['error'])) {
            $allAccounts = array_merge($allAccounts, $clientAccounts['data'] ?? []);
        }
        if (!isset($ownedAccounts['error'])) {
            $allAccounts = array_merge($allAccounts, $ownedAccounts['data'] ?? []);
        }
        
        // Loại bỏ duplicates
        $uniqueAccounts = [];
        foreach ($allAccounts as $account) {
            $uniqueAccounts[$account['id']] = $account;
        }
        
        $accounts = ['data' => array_values($uniqueAccounts)];
        
        if (isset($accounts['error'])) {
            $result['errors'][] = [
                'stage' => 'ad_accounts',
                'business_id' => $business->id,
                'error' => $accounts['error']['message'] ?? 'Unknown error',
                'time' => now()->toISOString()
            ];
            return;
        }

        foreach ($accounts['data'] ?? [] as $account) {
            try {
                FacebookAdAccount::updateOrCreate(
                    ['id' => $account['id']],
                    [
                        'business_id' => $business->id,
                        'name' => $account['name'] ?? null,
                        'account_id' => $account['account_id'] ?? null,
                        'account_status' => $account['account_status'] ?? null,
                        'currency' => $account['currency'] ?? null,
                        'timezone_name' => $account['timezone_name'] ?? null,
                        'created_time' => isset($account['created_time']) ? Carbon::parse($account['created_time']) : null,
                        'updated_time' => isset($account['updated_time']) ? Carbon::parse($account['updated_time']) : null,
                    ]
                );
                $result['accounts']++;
            } catch (\Exception $e) {
                Log::error("Lỗi khi sync ad account", [
                    'account_id' => $account['id'] ?? 'unknown',
                    'error' => $e->getMessage()
                ]);
                $result['errors'][] = [
                    'stage' => 'ad_account',
                    'account_id' => $account['id'] ?? 'unknown',
                    'error' => $e->getMessage(),
                    'time' => now()->toISOString()
                ];
            }
        }
    }

    /**
     * Đồng bộ Campaigns cho Ad Account
     */
    private function syncCampaigns(FacebookAdAccount $adAccount, array &$result, ?callable $onProgress): void
    {
        $this->reportProgress($onProgress, "Đang lấy Campaigns cho Account: {$adAccount->name}", $result);
        
        $campaigns = $this->api->getCampaigns($adAccount->id);
        
        if (isset($campaigns['error'])) {
                                    $result['errors'][] = [
                'stage' => 'campaigns',
                'account_id' => $adAccount->id,
                'error' => $campaigns['error']['message'] ?? 'Unknown error',
                'time' => now()->toISOString()
            ];
            return;
        }
        
        foreach ($campaigns['data'] ?? [] as $campaign) {
            try {
                FacebookCampaign::updateOrCreate(
                    ['id' => $campaign['id']],
                    [
                        'ad_account_id' => $adAccount->id,
                        'name' => $campaign['name'] ?? null,
                        'status' => $campaign['status'] ?? null,
                        'objective' => $campaign['objective'] ?? null,
                        'special_ad_categories' => isset($campaign['special_ad_categories']) ? json_encode($campaign['special_ad_categories']) : null,
                        'created_time' => isset($campaign['created_time']) ? Carbon::parse($campaign['created_time']) : null,
                        'updated_time' => isset($campaign['updated_time']) ? Carbon::parse($campaign['updated_time']) : null,
                    ]
                );
                $result['campaigns']++;
            } catch (\Exception $e) {
                Log::error("Lỗi khi sync campaign", [
                    'campaign_id' => $campaign['id'] ?? 'unknown',
                    'error' => $e->getMessage()
                ]);
                $result['errors'][] = [
                    'stage' => 'campaign',
                    'campaign_id' => $campaign['id'] ?? 'unknown',
                    'error' => $e->getMessage(),
                    'time' => now()->toISOString()
                ];
            }
        }
    }

    /**
     * Đồng bộ Ad Sets cho Campaign
     */
    private function syncAdSets(FacebookCampaign $campaign, array &$result, ?callable $onProgress): void
    {
        $this->reportProgress($onProgress, "Đang lấy Ad Sets cho Campaign: {$campaign->name}", $result);
        
        $adSets = $this->api->getAdSetsByCampaign($campaign->id);
        
        if (isset($adSets['error'])) {
            // Cải thiện message để dễ debug hơn
            $errorMessage = 'Unknown error';
            if (is_array($adSets['error'])) {
                $errorMessage = $adSets['error']['message']
                    ?? $adSets['error']['error_user_msg']
                    ?? $adSets['error']['error_subcode']
                    ?? json_encode($adSets['error']);
            } elseif (is_string($adSets['error'])) {
                $errorMessage = $adSets['error'];
            } elseif (!empty($adSets)) {
                $errorMessage = json_encode($adSets);
            }
            
            Log::error("Lỗi khi lấy Ad Sets", [
                'campaign_id' => $campaign->id,
                'campaign_name' => $campaign->name,
                'error' => $errorMessage,
            ]);
            
            $result['errors'][] = [
                'stage' => 'adsets',
                'campaign_id' => $campaign->id,
                'error' => $errorMessage,
                'time' => now()->toISOString()
            ];
            return;
        }
        
        foreach ($adSets['data'] ?? [] as $adSet) {
            try {
                FacebookAdSet::updateOrCreate(
                    ['id' => $adSet['id']],
                    [
                        'campaign_id' => $campaign->id,
                        'name' => $adSet['name'] ?? null,
                        'status' => $adSet['status'] ?? null,
                        'daily_budget' => $adSet['daily_budget'] ?? null,
                        'lifetime_budget' => $adSet['lifetime_budget'] ?? null,
                        'billing_event' => $adSet['billing_event'] ?? null,
                        'optimization_goal' => $adSet['optimization_goal'] ?? null,
                        'bid_amount' => $adSet['bid_amount'] ?? null,
                        'bid_strategy' => $adSet['bid_strategy'] ?? null,
                        'created_time' => isset($adSet['created_time']) ? Carbon::parse($adSet['created_time']) : null,
                        'updated_time' => isset($adSet['updated_time']) ? Carbon::parse($adSet['updated_time']) : null,
                    ]
                );
                $result['adsets']++;
            } catch (\Exception $e) {
                Log::error("Lỗi khi sync adset", [
                    'adset_id' => $adSet['id'] ?? 'unknown',
                    'error' => $e->getMessage()
                ]);
                $result['errors'][] = [
                    'stage' => 'adset',
                    'adset_id' => $adSet['id'] ?? 'unknown',
                    'error' => $e->getMessage(),
                    'time' => now()->toISOString()
                ];
            }
        }
    }

    /**
     * Đồng bộ Ads và Insights cho Ad Set
     */
    private function syncAdsAndInsights(FacebookAdSet $adSet, array &$result, ?callable $onProgress, bool $fixVideoMetrics = false): void
    {
        $this->reportProgress($onProgress, "Đang lấy Ads cho Ad Set: {$adSet->name}", $result);
        
        $ads = $this->api->getAdsByAdSet($adSet->id);
        
        if (isset($ads['error'])) {
            $errorMessage = 'Unknown error';
            if (is_array($ads['error'])) {
                $errorMessage = $ads['error']['message'] ?? $ads['error']['error_user_msg'] ?? 'Unknown error';
            } elseif (is_string($ads['error'])) {
                $errorMessage = $ads['error'];
            }
            
            Log::error("Lỗi khi lấy Ads", [
                'adset_id' => $adSet->id,
                'adset_name' => $adSet->name,
                'error' => $ads['error']
            ]);
            
            $result['errors'][] = [
                'stage' => 'ads',
                'adset_id' => $adSet->id,
                'error' => $errorMessage,
                'time' => now()->toISOString()
            ];
            return;
        }
        
        foreach ($ads['data'] ?? [] as $ad) {
            try {
                // Guard: đảm bảo adset tồn tại trước khi insert ad
                if (!FacebookAdSet::where('id', $adSet->id)->exists()) {
                    Log::warning('Bỏ qua insert ad vì thiếu adset', [
                        'ad_id' => $ad['id'] ?? null,
                        'adset_id' => $adSet->id,
                    ]);
                    continue;
                }

                $this->processAdWithNormalizedStructure($ad, $adSet, $result);
            } catch (\Exception $e) {
                Log::error("Lỗi khi process ad", [
                    'ad_id' => $ad['id'] ?? 'unknown',
                    'error' => $e->getMessage()
                ]);
                $result['errors'][] = [
                    'stage' => 'ad_processing',
                    'ad_id' => $ad['id'] ?? 'unknown',
                    'error' => $e->getMessage(),
                    'time' => now()->toISOString()
                ];
            }
        }
    }

    /**
     * Xử lý Ad với cấu trúc database đã normalize
     */
    private function processAdWithNormalizedStructure(array $ad, FacebookAdSet $adSet, array &$result): void
    {
        // 1. Lưu Ad cơ bản (KHÔNG sync insights - sẽ được xử lý bởi SyncInsightsForExistingAds)
        FacebookAd::updateOrCreate(
            ['id' => $ad['id']],
            [
                'name' => $ad['name'] ?? null,
                'status' => $ad['status'] ?? null,
                'effective_status' => $ad['effective_status'] ?? null,
                'adset_id' => $adSet->id,
                'campaign_id' => $adSet->campaign_id,
                'account_id' => $adSet->campaign->ad_account_id,
                'created_time' => isset($ad['created_time']) ? Carbon::parse($ad['created_time']) : null,
                'updated_time' => isset($ad['updated_time']) ? Carbon::parse($ad['updated_time']) : null,
                // KHÔNG set last_insights_sync vì chưa sync insights
                // last_insights_sync sẽ được set bởi SyncInsightsForExistingAds command
            ]
        );
        $result['ads']++;

        // 2. Lưu Creative JSON và meta post/page trực tiếp vào facebook_ads (nếu cần)
        // if (isset($ad['creative'])) {
        //     try {
        //         $creativeData = $ad['creative'];
        //         $pageId = $this->extractPageId($facebookAd, $creativeData);
        //         $postMeta = $this->extractPostData($ad) ?? [];
        //         $facebookAd->update([
        //             'page_id' => $pageId ?: $facebookAd->page_id,
        //             'post_id' => $postMeta['id'] ?? $facebookAd->post_id,
        //             'page_meta' => $pageId ? json_encode($creativeData['object_story_spec']['page_id'] ?? []) : $facebookAd->page_meta,
        //             'post_meta' => !empty($postMeta) ? json_encode($postMeta) : $facebookAd->post_meta,
        //             'creative_json' => json_encode($creativeData),
        //         ]);
        //     } catch (\Exception $e) {
        //         Log::warning('Không thể lưu creative/post/page meta vào facebook_ads', [
        //             'ad_id' => $facebookAd->id,
        //             'error' => $e->getMessage()
        //         ]);
        //     }
        // }

        // 3. BỎ QUA việc lưu Ad Insights và Breakdowns
        // - Ad Insights sẽ được xử lý bởi: php artisan facebook:sync-insights-only
        // - Breakdowns sẽ được xử lý bởi: php artisan facebook:sync-insights-only --with-breakdowns
        // - Video metrics sẽ được xử lý bởi: php artisan facebook:sync-enhanced-post-insights
        
        Log::info("✅ Đã lưu Ad cơ bản: {$ad['id']} (insights sẽ được sync riêng)", [
            'ad_id' => $ad['id'],
            'ad_name' => $ad['name'] ?? 'N/A',
            'adset_id' => $adSet->id,
            'campaign_id' => $adSet->campaign_id
        ]);
    }

    /**
     * Xử lý Post data và lưu vào bảng facebook_posts
     * Lấy data từ creative thay vì gọi API riêng
     */
    private function processPostData(array $postData, FacebookAd $facebookAd, array &$result): void
    {
        try {
            // Không còn lưu Page
            $pageId = $this->extractPageId($facebookAd, $postData);

            // Lưu Post với data từ creative
            $post = FacebookPost::updateOrCreate(
                ['id' => $postData['id']],
                [
                    'page_id' => $pageId,
                    'message' => $postData['message'] ?? null,
                    'type' => $postData['type'] ?? 'post',
                    'status_type' => $postData['status_type'] ?? null,
                    'attachments' => isset($postData['attachments']) ? json_encode($postData['attachments']) : null,
                    'permalink_url' => $postData['permalink_url'] ?? null,
                    'created_time' => isset($postData['created_time']) ? Carbon::parse($postData['created_time']) : null,
                    'updated_time' => isset($postData['updated_time']) ? Carbon::parse($postData['updated_time']) : null,
                ]
            );
            $result['posts']++;

            // Cập nhật foreign key trong FacebookAd
            $facebookAd->update(['post_id' => $post->id]);

            // Lấy engagement data (like, share, comment) từ Ad Insights API
            try {
                // Tìm ad có post_id này để lấy ad_id
                $ad = FacebookAd::where('post_id', $post->id)->first();
                if ($ad) {
                    $engagementData = $this->api->getAdEngagementData($ad->id);
                    
                    if (!isset($engagementData['error'])) {
                        // Cập nhật post với engagement data
                        $post->update([
                            'likes_count' => $engagementData['likes'] ?? 0,
                            'shares_count' => $engagementData['shares'] ?? 0,
                            'comments_count' => $engagementData['comments'] ?? 0,
                            'reactions_count' => $engagementData['reactions'] ?? 0,
                            'engagement_updated_at' => now(),
                        ]);
                        
                        Log::info("Đã lấy được engagement data từ Ad Insights API", [
                            'post_id' => $postData['id'],
                            'ad_id' => $ad->id,
                            'likes' => $engagementData['likes'] ?? 0,
                            'shares' => $engagementData['shares'] ?? 0,
                            'comments' => $engagementData['comments'] ?? 0,
                            'reactions' => $engagementData['reactions'] ?? 0,
                        ]);
                    } else {
                        Log::warning("Không lấy được engagement data từ Ad Insights API", [
                            'post_id' => $postData['id'],
                            'ad_id' => $ad->id,
                            'error' => $engagementData['error'] ?? 'Unknown error'
                        ]);
                    }
                } else {
                    Log::warning("Không tìm thấy ad cho post_id: {$postData['id']}");
                }
            } catch (\Exception $e) {
                Log::warning("Lỗi khi lấy engagement data từ Ad Insights API", [
                    'post_id' => $postData['id'],
                    'error' => $e->getMessage()
                ]);
                // Không throw exception để không ảnh hưởng đến việc lưu post
            }

            Log::info("Đã lưu post data thành công", [
                'post_id' => $postData['id'],
                'page_id' => $pageId,
                'facebook_post_id' => $post->id
            ]);

        } catch (\Exception $e) {
            Log::error("Lỗi khi process post data", [
                'post_id' => $postData['id'] ?? 'unknown',
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Xử lý Post Insights và lưu vào bảng facebook_post_insights
     */
    private function processPostInsights(array $postInsights, FacebookAd $facebookAd, array &$result): void
    {
        try {
            if (!isset($postInsights['data']) || empty($postInsights['data'])) {
                return;
            }

            foreach ($postInsights['data'] as $insight) {
                $date = $insight['date'] ?? null;
                // Strict guard: skip if no date or out of requested range
                $since = $result['time_range']['since'] ?? null;
                $until = $result['time_range']['until'] ?? null;
                if (!$date || ($since && $date < $since) || ($until && $date > $until)) {
                    continue;
                }
                FacebookPostInsight::updateOrCreate(
                    [
                        'post_id' => $facebookAd->post_id,
                        'date' => $date,
                    ],
                    [
                        'impressions' => (int) ($insight['impressions'] ?? 0),
                        'reach' => (int) ($insight['reach'] ?? 0),
                        'clicks' => (int) ($insight['clicks'] ?? 0),
                        'unique_clicks' => (int) ($insight['unique_clicks'] ?? 0),
                        'likes' => (int) ($insight['likes'] ?? 0),
                        'shares' => (int) ($insight['shares'] ?? 0),
                        'comments' => (int) ($insight['comments'] ?? 0),
                        'reactions' => (int) ($insight['reactions'] ?? 0),
                        'saves' => (int) ($insight['saves'] ?? 0),
                        'hides' => (int) ($insight['hides'] ?? 0),
                        'hide_all_clicks' => (int) ($insight['hide_all_clicks'] ?? 0),
                        'unlikes' => (int) ($insight['unlikes'] ?? 0),
                        'negative_feedback' => (int) ($insight['negative_feedback'] ?? 0),
                        'video_views' => $this->extractVideoMetricValue($insight, 'video_play_actions'),
                        // Không có field video_watch_time hợp lệ trong Ads Insights; giữ theo fallback cũ nếu có
                        'video_view_time' => (int) ($insight['video_view_time'] ?? 0),
                        'video_avg_time_watched' => $this->extractVideoMetricValueDecimal($insight, 'video_avg_time_watched_actions'),
                        'video_plays' => $this->extractVideoMetricValue($insight, 'video_play_actions'),
                        'video_plays_at_25' => $this->extractVideoMetricValue($insight, 'video_p25_watched_actions'),
                        'video_plays_at_50' => $this->extractVideoMetricValue($insight, 'video_p50_watched_actions'),
                        'video_plays_at_75' => $this->extractVideoMetricValue($insight, 'video_p75_watched_actions'),
                        'video_plays_at_100' => $this->extractVideoMetricValue($insight, 'video_p95_watched_actions'),
                        // Lấy đúng giá trị từ mảng actions (không ép array -> int)
                        'video_p25_watched_actions' => $this->extractVideoMetricValue($insight, 'video_p25_watched_actions'),
                        'video_p50_watched_actions' => $this->extractVideoMetricValue($insight, 'video_p50_watched_actions'),
                        'video_p75_watched_actions' => $this->extractVideoMetricValue($insight, 'video_p75_watched_actions'),
                        'video_p95_watched_actions' => $this->extractVideoMetricValue($insight, 'video_p95_watched_actions'),
                        'video_p100_watched_actions' => $this->extractVideoMetricValue($insight, 'video_p100_watched_actions'),
                        'thruplays' => $this->extractVideoMetricValue($insight, 'video_thruplay_watched_actions'),
                        'engagement_rate' => (float) ($insight['engagement_rate'] ?? 0),
                        'ctr' => (float) ($insight['ctr'] ?? 0),
                        'cpm' => (float) ($insight['cpm'] ?? 0),
                        'cpc' => (float) ($insight['cpc'] ?? 0),
                        'spend' => (float) ($insight['spend'] ?? 0),
                        'frequency' => (float) ($insight['frequency'] ?? 0),
                        'actions' => isset($insight['actions']) ? json_encode($insight['actions']) : null,
                        'action_values' => isset($insight['action_values']) ? json_encode($insight['action_values']) : null,
                        'cost_per_action_type' => isset($insight['cost_per_action_type']) ? json_encode($insight['cost_per_action_type']) : null,
                        'cost_per_unique_action_type' => isset($insight['cost_per_unique_action_type']) ? json_encode($insight['cost_per_unique_action_type']) : null,
                        'breakdowns' => isset($insight['breakdowns']) ? json_encode($insight['breakdowns']) : null,
                    ]
                );
                $result['post_insights']++;
            }

        } catch (\Exception $e) {
            Log::error("Lỗi khi process post insights", [
                'post_id' => $facebookAd->post_id ?? 'unknown',
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Extract video metric value from Facebook API response
     * Facebook returns video metrics as array with action_type and value
     */
    private function extractVideoMetricValue(array $insight, string $field): int
    {
        if (!isset($insight[$field])) {
            return 0;
        }

        $value = $insight[$field];

        // Nếu là số, trả thẳng
        if (is_numeric($value)) {
            return (int) $value;
        }

        // Map field -> expected action_type(s)
        $expectedTypes = $this->expectedActionTypesForField($field);

        // Nếu là mảng actions: cộng dồn các action_type phù hợp
        if (is_array($value) && !empty($value)) {
            $total = 0;
            foreach ($value as $action) {
                $type = $action['action_type'] ?? null;
                if ($type && in_array($type, $expectedTypes, true)) {
                    $total += (int) ($action['value'] ?? 0);
                }
            }
            return $total;
        }

        return 0;
    }

    private function expectedActionTypesForField(string $field): array
    {
        return match ($field) {
            'video_thruplay_watched_actions' => ['video_thruplay', 'thruplay'],
            default => ['video_view'],
        };
    }

    /**
     * Extract video views from actions array
     */
    private function extractVideoViews(array $insight): int
    {
        // Thử lấy từ video_views trực tiếp
        if (isset($insight['video_views']) && is_numeric($insight['video_views'])) {
            return (int) $insight['video_views'];
        }

        // Lấy từ actions array với action_type = video_view
        if (isset($insight['actions']) && is_array($insight['actions'])) {
            foreach ($insight['actions'] as $action) {
                if (isset($action['action_type']) && $action['action_type'] === 'video_view') {
                    return (int) $action['value'];
                }
            }
        }

        return 0;
    }

    /**
     * Extract video metric value for decimal fields (like video_avg_time_watched)
     */
    private function extractVideoMetricValueDecimal(array $insight, string $field): float
    {
        if (!isset($insight[$field])) {
            return 0.0;
        }
        
        $value = $insight[$field];
        
        // If it's already a number, return it
        if (is_numeric($value)) {
            return (float) $value;
        }
        
        // If it's an array with action_type and value structure
        if (is_array($value) && !empty($value)) {
            foreach ($value as $action) {
                if (isset($action['action_type']) && isset($action['value'])) {
                    return (float) $action['value'];
                }
            }
        }
        
        return 0.0;
    }

    /**
     * Extract video avg time watched theo logic TestVideoMetricsResponse.php
     */
    private function extractVideoAvgTimeWatched(array $insight): float
    {
        // Thử lấy từ video_avg_time_watched trực tiếp
        if (isset($insight['video_avg_time_watched']) && is_numeric($insight['video_avg_time_watched'])) {
            return (float) $insight['video_avg_time_watched'];
        }

        // Thử lấy từ video_avg_time_watched_actions array
        if (isset($insight['video_avg_time_watched_actions']) && is_array($insight['video_avg_time_watched_actions'])) {
            foreach ($insight['video_avg_time_watched_actions'] as $action) {
                if (isset($action['action_type']) && $action['action_type'] === 'video_view') {
                    return (float) $action['value'];
                }
            }
        }

        // Thử lấy từ actions array với action_type = video_avg_time_watched_actions
        if (isset($insight['actions']) && is_array($insight['actions'])) {
            foreach ($insight['actions'] as $action) {
                if (isset($action['action_type']) && $action['action_type'] === 'video_avg_time_watched_actions') {
                    return (float) $action['value'];
                }
            }
        }

        return 0.0;
    }

    /**
     * Extract video view time from Facebook API response
     * Facebook không cung cấp trực tiếp video_view_time, cần tính toán từ các metrics khác
     */
    private function extractVideoViewTime(array $insight): int
    {
        // Thử lấy từ video_view_time trực tiếp (nếu có)
        if (isset($insight['video_view_time']) && is_numeric($insight['video_view_time'])) {
            return (int) $insight['video_view_time'];
        }

        // Thử lấy từ video_view_time array nếu có
        if (isset($insight['video_view_time']) && is_array($insight['video_view_time'])) {
            foreach ($insight['video_view_time'] as $action) {
                if (isset($action['action_type']) && $action['action_type'] === 'video_view') {
                    return (int) $action['value'];
                }
            }
        }

        // Tính toán từ video_avg_time_watched và video_views
        $avgTimeWatched = $this->extractVideoAvgTimeWatched($insight);
        $videoViews = $this->extractVideoViews($insight);

        if ($avgTimeWatched > 0 && $videoViews > 0) {
            return (int) ($avgTimeWatched * $videoViews);
        }

        // Fallback: tính từ video_30_sec_watched_actions
        if (isset($insight['video_30_sec_watched_actions'])) {
            $thirtySecWatched = $this->extractVideoMetricValue($insight, 'video_30_sec_watched_actions');
            return $thirtySecWatched * 30; // Ước tính 30 giây cho mỗi view
        }

        // Fallback: tính từ video_p25_watched_actions (25% completion)
        if (isset($insight['video_p25_watched_actions'])) {
            $p25Watched = $this->extractVideoMetricValue($insight, 'video_p25_watched_actions');
            return $p25Watched * 25; // Ước tính 25 giây cho mỗi view
        }

        return 0;
    }

    /**
     * Extract và xử lý video metrics một cách toàn diện
     */
    private function extractVideoMetrics(array $insight): array
    {
        $videoMetrics = [
            'video_views' => 0,
            'video_plays' => 0,
            'video_plays_at_25' => 0,
            'video_plays_at_50' => 0,
            'video_plays_at_75' => 0,
            'video_plays_at_100' => 0,
            'video_avg_time_watched' => 0.0,
            'video_view_time' => 0,
            'video_30_sec_watched' => 0,
            'thruplays' => 0,
        ];

        // Video views - lấy từ actions array
        $videoMetrics['video_views'] = $this->extractVideoViews($insight);

        // Video plays - sử dụng video_views làm video_plays
        $videoMetrics['video_plays'] = $this->extractVideoViews($insight);

        // Video completion rates
        $videoMetrics['video_p25_watched_actions'] = $this->extractVideoMetricValue($insight, 'video_p25_watched_actions');
        $videoMetrics['video_p50_watched_actions'] = $this->extractVideoMetricValue($insight, 'video_p50_watched_actions');
        $videoMetrics['video_p75_watched_actions'] = $this->extractVideoMetricValue($insight, 'video_p75_watched_actions');
        $videoMetrics['video_p100_watched_actions'] = $this->extractVideoMetricValue($insight, 'video_p100_watched_actions');

        // Average time watched - sử dụng method mới
        $videoMetrics['video_avg_time_watched'] = $this->extractVideoAvgTimeWatched($insight);

        // Video view time - sử dụng method mới
        $videoMetrics['video_view_time'] = $this->extractVideoViewTime($insight);

        // 30 second watched
        $videoMetrics['video_30_sec_watched'] = $this->extractVideoMetricValue($insight, 'video_30_sec_watched_actions');

        // Thruplays
        $videoMetrics['thruplays'] = $this->extractVideoMetricValue($insight, 'video_thruplay_watched_actions');

        // Log để debug
        Log::info("Extracted video metrics", [
            'insight_id' => $insight['ad_id'] ?? 'unknown',
            'video_metrics' => $videoMetrics
        ]);

        return $videoMetrics;
    }

    /**
     * Xử lý Complete Ad Insights với tất cả breakdowns
     */
    private function processCompleteAdInsights(FacebookAd $facebookAd, array &$result): void
    {
        try {
            Log::info("🔄 Đang lấy Complete Ad Insights cho Ad: {$facebookAd->id}");
            
            $insights = $this->api->getCompleteAdInsights($facebookAd->id);
            
            Log::info("📊 Insights data structure", [
                'ad_id' => $facebookAd->id,
                'insights_keys' => array_keys($insights),
                'has_basic_insights' => isset($insights['basic_insights']),
                'basic_insights_data_count' => isset($insights['basic_insights']['data']) ? count($insights['basic_insights']['data']) : 0
            ]);
            
            if (isset($insights['basic_insights']['data']) && !empty($insights['basic_insights']['data'])) {
                $basicInsight = $insights['basic_insights']['data'][0];
                
                Log::info("✅ Có basic insights data", [
                    'ad_id' => $facebookAd->id,
                    'insight_keys' => array_keys($basicInsight),
                    'has_video_metrics' => isset($basicInsight['video_30_sec_watched_actions'])
                ]);
                
                // 1. Xử lý basic insights
                $this->processBasicAdInsights($insights['basic_insights'], $facebookAd, $result);
                
                // 2. Xử lý các breakdowns chính
                $this->processMainBreakdowns($facebookAd, $insights, $result);
                
                // 3. Xử lý action breakdowns
                $this->processActionBreakdowns($facebookAd, $insights, $result);
                
                // 4. Xử lý asset breakdowns
                $this->processAssetBreakdowns($facebookAd, $insights, $result);
                
                // 5. Xử lý engagement breakdowns
                if (isset($insights['engagement_breakdowns'])) {
                    $this->processEngagementBreakdowns($facebookAd, $insights['engagement_breakdowns'], $result);
                }
                
                $result['ad_insights']++;
                Log::info("✅ Đã xử lý Complete Ad Insights cho Ad: {$facebookAd->id}", [
                    'ad_id' => $facebookAd->id,
                    'result' => $result
                ]);
                
            } else {
                Log::warning("⚠️ Không có basic insights data cho Ad: {$facebookAd->id}", [
                    'ad_id' => $facebookAd->id,
                    'insights_structure' => $insights
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error("❌ Lỗi khi xử lý Complete Ad Insights cho Ad: {$facebookAd->id}");
            Log::error("Process Complete Ad Insights failed", [
                'ad_id' => $facebookAd->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    /**
     * Xử lý engagement breakdowns
     */
    private function processEngagementBreakdowns(FacebookAd $facebookAd, array $engagementData, array &$result): void
    {
        // Xử lý engagement breakdowns nếu có
        if (isset($engagementData['data']) && !empty($engagementData['data'])) {
            foreach ($engagementData['data'] as $engagement) {
                // Lưu engagement breakdown data
                // Có thể mở rộng thêm logic xử lý engagement breakdowns
            }
        }
    }
    
    /**
     * Xử lý các breakdowns chính
     */
    private function processMainBreakdowns(FacebookAd $facebookAd, array $insights, array &$result): void
    {
        $mainBreakdowns = [
            'age', 'gender', 'country', 'region', 'publisher_platform', 
            'platform_position', 'device_platform', 'impression_device'
        ];
        
        foreach ($mainBreakdowns as $breakdown) {
            $key = "breakdown_{$breakdown}";
            if (isset($insights[$key]) && !isset($insights[$key]['error'])) {
                $this->saveBreakdownData($facebookAd, $insights[$key], $breakdown, $result);
            }
        }
    }
    
    /**
     * Xử lý action breakdowns
     */
    private function processActionBreakdowns(FacebookAd $facebookAd, array $insights, array &$result): void
    {
        $actionBreakdowns = [
            'action_device', 'action_destination', 'action_target_id', 
            'action_reaction', 'action_video_sound', 'action_video_type',
            'action_carousel_card_id', 'action_carousel_card_name', 'action_canvas_component_name'
        ];
        
        foreach ($actionBreakdowns as $breakdown) {
            $key = "action_breakdown_{$breakdown}";
            if (isset($insights[$key]) && !isset($insights[$key]['error'])) {
                $this->saveActionBreakdownData($facebookAd, $insights[$key], $breakdown, $result);
            }
        }
    }
    
    /**
     * Xử lý asset breakdowns
     */
    private function processAssetBreakdowns(FacebookAd $facebookAd, array $insights, array &$result): void
    {
        $assetBreakdowns = [
            'video_asset', 'image_asset', 'body_asset', 'title_asset',
            'description_asset', 'call_to_action_asset', 'link_url_asset', 'ad_format_asset'
        ];
        
        foreach ($assetBreakdowns as $breakdown) {
            $key = "asset_breakdown_{$breakdown}";
            if (isset($insights[$key]) && !isset($insights[$key]['error'])) {
                $this->saveAssetBreakdownData($facebookAd, $insights[$key], $breakdown, $result);
            }
        }
    }
    
    /**
     * Trích xuất breakdown value một cách thông minh từ row data
     */

    
    /**
     * Lưu breakdown data vào database
     */
    private function saveBreakdownData(FacebookAd $facebookAd, array $breakdownData, string $breakdownType, array &$result): void
    {
        if (!isset($this->lastProcessedAdInsightId) || !$this->lastProcessedAdInsightId) {
            Log::warning('Không có ad_insight_id để lưu breakdown', [
                'ad_id' => $facebookAd->id,
                'breakdown_type' => $breakdownType,
            ]);
            return;
        }

        if (isset($breakdownData['data']) && !empty($breakdownData['data'])) {
            foreach ($breakdownData['data'] as $row) {
                // Sử dụng method extractBreakdownValue đã được cải thiện
                $breakdownValue = $this->extractBreakdownValue($row, $breakdownType);
                
                // Chỉ lưu nếu có giá trị hợp lệ
                if ($breakdownValue !== null) {
                    \App\Models\FacebookBreakdown::updateOrCreate(
                        [
                            'ad_insight_id' => $this->lastProcessedAdInsightId,
                            'breakdown_type' => $breakdownType,
                            'breakdown_value' => $breakdownValue,
                        ],
                        [
                            'metrics' => json_encode($row),
                        ]
                    );
                }
            }
            $result['breakdowns']++;
        }
    }
    
    /**
     * Lưu action breakdown data
     */
    private function saveActionBreakdownData(FacebookAd $facebookAd, array $breakdownData, string $breakdownType, array &$result): void
    {
        if (!isset($this->lastProcessedAdInsightId) || !$this->lastProcessedAdInsightId) {
            Log::warning('Không có ad_insight_id để lưu action breakdown', [
                'ad_id' => $facebookAd->id,
                'breakdown_type' => $breakdownType,
            ]);
            return;
        }

        if (isset($breakdownData['data']) && !empty($breakdownData['data'])) {
            foreach ($breakdownData['data'] as $row) {
                // Với action breakdowns, cần xử lý đặc biệt
                if (strpos($breakdownType, 'action_') === 0) {
                    $this->processActionBreakdownRow($row, $breakdownType);
                } else {
                    // Sử dụng method extractBreakdownValue đã được cải thiện cho non-action breakdowns
                    $breakdownValue = $this->extractBreakdownValue($row, $breakdownType);
                    
                    // Chỉ lưu nếu có giá trị hợp lệ
                    if ($breakdownValue !== null) {
                        \App\Models\FacebookBreakdown::updateOrCreate(
                            [
                                'ad_insight_id' => $this->lastProcessedAdInsightId,
                                'breakdown_type' => $breakdownType,
                                'breakdown_value' => $breakdownValue,
                            ],
                            [
                                'metrics' => json_encode($row),
                            ]
                        );
                    }
                }
            }
            $result['breakdowns']++;
        }
    }

    /**
     * Xử lý action breakdown row - breakdown values nằm trong actions array
     */
    private function processActionBreakdownRow(array $row, string $breakdownType): void
    {
        // Kiểm tra có actions array không
        if (!isset($row['actions']) || !is_array($row['actions'])) {
            return;
        }

        // Tạo map để group theo breakdown value
        $breakdownGroups = [];

        foreach ($row['actions'] as $action) {
            // Lấy breakdown value từ action
            $breakdownValue = $this->extractActionBreakdownValue($action, $breakdownType);
            
            if ($breakdownValue === null) {
                continue; // Bỏ qua action không có breakdown value
            }

            // Group actions theo breakdown value
            if (!isset($breakdownGroups[$breakdownValue])) {
                $breakdownGroups[$breakdownValue] = [
                    'breakdown_value' => $breakdownValue,
                    'actions' => [],
                    'metrics' => $row // Copy basic metrics
                ];
            }

            $breakdownGroups[$breakdownValue]['actions'][] = $action;
        }

        // Lưu từng breakdown group
        foreach ($breakdownGroups as $group) {
            \App\Models\FacebookBreakdown::updateOrCreate(
                [
                    'ad_insight_id' => $this->lastProcessedAdInsightId,
                    'breakdown_type' => $breakdownType,
                    'breakdown_value' => $group['breakdown_value'],
                ],
                [
                    'metrics' => json_encode($group),
                ]
            );
        }
    }

    /**
     * Extract breakdown value từ action item
     */
    private function extractActionBreakdownValue(array $action, string $breakdownType): ?string
    {
        switch ($breakdownType) {
            case 'action_device':
                return $action['action_device'] ?? null;
            
            case 'action_destination':
                return $action['action_destination'] ?? null;
            
            case 'action_target_id':
                return $action['action_target_id'] ?? null;
            
            case 'action_reaction':
                return $action['action_reaction'] ?? null;
            
            case 'action_video_sound':
                return $action['action_video_sound'] ?? null;
            
            case 'action_video_type':
                return $action['action_video_type'] ?? null;
            
            case 'action_carousel_card_id':
                return $action['action_carousel_card_id'] ?? null;
            
            case 'action_carousel_card_name':
                return $action['action_carousel_card_name'] ?? null;
            
            case 'action_canvas_component_name':
                return $action['action_canvas_component_name'] ?? null;
            
            default:
                // Tìm kiếm trường có tên tương ứng
                $fieldName = str_replace('action_', '', $breakdownType);
                return $action[$fieldName] ?? null;
        }
    }
    
    /**
     * Lưu asset breakdown data
     */
    private function saveAssetBreakdownData(FacebookAd $facebookAd, array $breakdownData, string $breakdownType, array &$result): void
    {
        if (!isset($this->lastProcessedAdInsightId) || !$this->lastProcessedAdInsightId) {
            Log::warning('Không có ad_insight_id để lưu asset breakdown', [
                'ad_id' => $facebookAd->id,
                'breakdown_type' => $breakdownType,
            ]);
            return;
        }

        if (isset($breakdownData['data']) && !empty($breakdownData['data'])) {
            foreach ($breakdownData['data'] as $row) {
                // Sử dụng method extractBreakdownValue đã được cải thiện
                $breakdownValue = $this->extractBreakdownValue($row, $breakdownType);
                
                // Chỉ lưu nếu có giá trị hợp lệ
                if ($breakdownValue !== null) {
                    \App\Models\FacebookBreakdown::updateOrCreate(
                        [
                            'ad_insight_id' => $this->lastProcessedAdInsightId,
                            'breakdown_type' => $breakdownType,
                            'breakdown_value' => $breakdownValue,
                        ],
                        [
                            'metrics' => json_encode($row),
                        ]
                    );
                }
            }
            $result['breakdowns']++;
        }
    }

    /**
     * Xử lý Ad Insights cơ bản và lưu vào bảng facebook_ad_insights
     */
    private function processBasicAdInsights(array $adInsights, FacebookAd $facebookAd, array &$result): void
    {
        try {
            if (!isset($adInsights['data'])) {
                Log::warning('Không có basic insights data cho Ad', [
                    'ad_id' => $facebookAd->id,
                    'adInsights' => $adInsights
                ]);
                return;
            }
            
            // Nếu data rỗng, vẫn tạo record với giá trị 0
            if (empty($adInsights['data'])) {
                Log::info('Basic insights data rỗng, tạo record với giá trị 0', [
                    'ad_id' => $facebookAd->id
                ]);
                
                // Không tạo bản ghi 0 khi không có data; bỏ qua để tránh trùng
                $date = null;
                $postIdForSave = $facebookAd->post_id ?? null;
                $pageIdForSave = $facebookAd->page_id ?? null;
                // return sớm, không lưu gì nếu không có data
                return;
            }

            foreach ($adInsights['data'] as $insight) {
                // Parse actions để map về các trường quan trọng - lưu đầy đủ theo response Facebook API
                $actions = $insight['actions'] ?? [];
                $actionTotals = [];
                foreach ($actions as $a) {
                    $type = $a['action_type'] ?? '';
                    $val = (int) ($a['value'] ?? 0);
                    if ($type === '') { continue; }
                    $actionTotals[$type] = ($actionTotals[$type] ?? 0) + $val;
                }
                
                // Log để debug actions parsing
                Log::info("Parsed actions for ad insights", [
                    'ad_id' => $facebookAd->id,
                    'actions_count' => count($actions),
                    'action_totals' => $actionTotals
                ]);
                
                // Xác định date từ insight và áp dụng strict guard theo khoảng sync
                $date = $insight['date_start'] ?? ($insight['date_stop'] ?? null);
                $since = $result['time_range']['since'] ?? null;
                $until = $result['time_range']['until'] ?? null;
                if (!$date || ($since && $date < $since) || ($until && $date > $until)) {
                    continue;
                }

                // Trích xuất page_id, post_id từ creative.object_story_id nếu có
                $pageIdFromCreative = null;
                $postIdFromCreative = null;
                try {
                    if (isset($facebookAd->creative) && isset($facebookAd->creative->creative_data)) {
                        $creative = $facebookAd->creative->creative_data;
                        // creative_data có thể là JSON string -> decode
                        if (is_string($creative)) {
                            $decoded = json_decode($creative, true);
                            if (json_last_error() === JSON_ERROR_NONE) {
                                $creative = $decoded;
                            }
                        }
                        $objectStoryId = $creative['object_story_id'] ?? ($creative['effective_object_story_id'] ?? null);
                        if (is_string($objectStoryId) && strpos($objectStoryId, '_') !== false) {
                            [$pageIdFromCreative, $postIdFromCreative] = explode('_', $objectStoryId, 2);
                        }
                    }
                } catch (\Throwable $e) {
                    // Bỏ qua nếu không có creative
                }
                // Fallback sang trường trên ad nếu có
                $postIdForSave = $postIdFromCreative ?: ($facebookAd->post_id ?? null);
                $pageIdForSave = $pageIdFromCreative ?: ($facebookAd->page_id ?? null);
                
                // (logs removed)

                $adInsight = FacebookAdInsight::updateOrCreate(
                    [
                        'ad_id' => $facebookAd->id,
                        'date' => $date,
                        ...(Schema::hasColumn('facebook_ad_insights', 'post_id') && $postIdForSave ? ['post_id' => (string) $postIdForSave] : ['post_id' => null]),
                    ],
                    [
                        // Basic metrics - lưu đầy đủ theo response Facebook API
                        'spend' => (float) ($insight['spend'] ?? 0),
                        'reach' => (int) ($insight['reach'] ?? 0),
                        'impressions' => (int) ($insight['impressions'] ?? 0),
                        'clicks' => (int) ($insight['clicks'] ?? 0),
                        'unique_clicks' => (int) ($insight['unique_clicks'] ?? 0),
                        'ctr' => (float) ($insight['ctr'] ?? 0),
                        'unique_ctr' => (float) ($insight['unique_ctr'] ?? 0),
                        'unique_link_clicks_ctr' => (float) ($insight['unique_link_clicks_ctr'] ?? 0),
                        'unique_impressions' => (int) ($insight['unique_impressions'] ?? 0),
                        'cpc' => (float) ($insight['cpc'] ?? 0),
                        'cpm' => (float) ($insight['cpm'] ?? 0),
                        'frequency' => (float) ($insight['frequency'] ?? 0),
                        
                        // Conversion metrics - lưu đầy đủ theo response Facebook API
                        'conversions' => (int) ($insight['conversions'] ?? (
                            ($actionTotals['lead'] ?? 0)
                            + ($actionTotals['onsite_conversion.lead'] ?? 0)
                            + ($actionTotals['onsite_web_lead'] ?? 0)
                            + ($actionTotals['onsite_conversion.lead_grouped'] ?? 0)
                        )),
                        'conversion_values' => (float) ($insight['conversion_values'] ?? 0),
                        'cost_per_conversion' => (float) ($insight['cost_per_conversion'] ?? 0),
                        'purchase_roas' => (float) ($insight['purchase_roas'] ?? 0),
                        
                        // Click metrics - lưu đầy đủ theo response Facebook API
                        'outbound_clicks' => (int) ($insight['outbound_clicks'] ?? 0),
                        'unique_outbound_clicks' => (int) ($insight['unique_outbound_clicks'] ?? 0),
                        'inline_link_clicks' => (int) ($insight['inline_link_clicks'] ?? ($actionTotals['link_click'] ?? 0)),
                        'unique_inline_link_clicks' => (int) ($insight['unique_inline_link_clicks'] ?? 0),
                        'website_clicks' => (int) ($insight['website_clicks'] ?? ($actionTotals['link_click'] ?? 0)),
                        
                        // JSON fields - Laravel tự động handle JSON casting
                        'actions' => $insight['actions'] ?? null,
                        'action_values' => $insight['action_values'] ?? null,
                        'cost_per_action_type' => $insight['cost_per_action_type'] ?? null,
                        'cost_per_unique_action_type' => $insight['cost_per_unique_action_type'] ?? null,
                        
                        // Video metrics - xử lý theo logic TestVideoMetricsResponse.php và response Facebook API
                        'video_views' => (int) ($insight['video_views'] ?? ($actionTotals['video_view'] ?? 0)),
                        'video_plays' => (int) ($insight['video_plays'] ?? ($actionTotals['video_view'] ?? 0)),
                        'video_avg_time_watched' => $this->extractVideoAvgTimeWatched($insight),
                        'video_p25_watched_actions' => (int) ($this->extractVideoMetricValue($insight, 'video_p25_watched_actions')),
                        'video_p50_watched_actions' => (int) ($this->extractVideoMetricValue($insight, 'video_p50_watched_actions')),
                        'video_p75_watched_actions' => (int) ($this->extractVideoMetricValue($insight, 'video_p75_watched_actions')),
                        'video_p95_watched_actions' => (int) ($this->extractVideoMetricValue($insight, 'video_p95_watched_actions')),
                        'video_p100_watched_actions' => (int) ($this->extractVideoMetricValue($insight, 'video_p100_watched_actions')),
                        'thruplays' => (int) ($insight['thruplays'] ?? 0),
                        'video_30_sec_watched' => (int) ($this->extractVideoMetricValue($insight, 'video_30_sec_watched_actions')),
                        'video_play_actions' => (int) ($insight['video_play_actions'] ?? 0),
                        'video_view_time' => $this->extractVideoViewTime($insight),
                        
                        // Lưu mapping post/page nếu schema có
                        ...(Schema::hasColumn('facebook_ad_insights', 'post_id') && $postIdForSave ? ['post_id' => (string) $postIdForSave] : []),
                        ...(Schema::hasColumn('facebook_ad_insights', 'page_id') && $pageIdForSave ? ['page_id' => (string) $pageIdForSave] : []),
                    ]
                );
                $result['ad_insights']++;
                
                // Log để debug video metrics đã lưu
                Log::info("Đã lưu ad insights với video metrics", [
                    'ad_id' => $facebookAd->id,
                    'ad_insight_id' => $adInsight->id,
                    'date' => $date,
                    'video_views' => $adInsight->video_views,
                    'video_plays' => $adInsight->video_plays,
                    'video_avg_time_watched' => $adInsight->video_avg_time_watched,
                    'video_view_time' => $adInsight->video_view_time,
                    'video_30_sec_watched' => $adInsight->video_30_sec_watched,
                    'video_p25_watched_actions' => $adInsight->video_p25_watched_actions,
                    'video_p50_watched_actions' => $adInsight->video_p50_watched_actions,
                    'video_p75_watched_actions' => $adInsight->video_p75_watched_actions,
                    'video_p95_watched_actions' => $adInsight->video_p95_watched_actions,
                    'video_p100_watched_actions' => $adInsight->video_p100_watched_actions,
                    'thruplays' => $adInsight->thruplays,
                ]);
                
                // Lưu ad_insight_id để sử dụng cho breakdowns
                $this->lastProcessedAdInsightId = $adInsight->id;
            }

        } catch (\Exception $e) {
            Log::error("Lỗi khi process basic ad insights", [
                'ad_id' => $facebookAd->id ?? 'unknown',
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Extract video metrics đầy đủ từ insight data
     */
    private function extractCompleteVideoMetrics(array $insight): array
    {
        // Remove debug statement
        $videoMetrics = [
            'video_views' => 0,
            'video_plays' => 0,
            'video_plays_at_25' => 0,
            'video_plays_at_50' => 0,
            'video_plays_at_75' => 0,
            'video_plays_at_100' => 0,
            'video_avg_time_watched' => 0.0,
            'video_p25_watched_actions' => 0,
            'video_p50_watched_actions' => 0,
            'video_p75_watched_actions' => 0,
            'video_p95_watched_actions' => 0,
            'video_p100_watched_actions' => 0,
            'thruplays' => 0,
            'video_view_time' => 0,
            'video_30_sec_watched' => 0,
            // Chỉ giữ lại các fields thực sự có sẵn từ Facebook API
            'video_play_actions' => 0,
            // Các fields nâng cao sẽ được set NULL vì không có trong API response
            'video_watch_at_75_percent_actions' => null,
            'video_watch_at_100_percent_actions' => null,
            'video_retention_graph' => null,
            'video_sound_on_actions' => null,
            'video_sound_off_actions' => null,
            'video_quality_actions' => null,
            'video_engagement_rate' => null,
            'video_completion_rate' => null,
            'video_skip_actions' => null,
            'video_mute_actions' => null,
            'video_unmute_actions' => null,
            'video_performance_p25' => null,
            'video_performance_p50' => null,
            'video_performance_p75' => null,
            'video_performance_p95' => null,
            'video_attributed_views' => null,
            'video_attributed_view_time' => null,
        ];
        
        // Extract từ video fields trực tiếp - theo response Facebook API
        if (isset($insight['video_30_sec_watched_actions'])) {
            foreach ($insight['video_30_sec_watched_actions'] as $action) {
                if ($action['action_type'] === 'video_view') {
                    $videoMetrics['video_30_sec_watched'] = (int) $action['value'];
                }
            }
        }
        
        // Extract từ video_30_sec_watched trực tiếp nếu có
        if (isset($insight['video_30_sec_watched'])) {
            $videoMetrics['video_30_sec_watched'] = (int) $insight['video_30_sec_watched'];
        }
        
        // Sử dụng method mới để extract video_avg_time_watched
        $videoMetrics['video_avg_time_watched'] = $this->extractVideoAvgTimeWatched($insight);
        
        // Sử dụng method mới để extract video_view_time
        $videoMetrics['video_view_time'] = $this->extractVideoViewTime($insight);
        
        // Log để debug video metrics extraction
        Log::info("Extracted video metrics from insight", [
            'insight_keys' => array_keys($insight),
            'video_metrics' => $videoMetrics,
            'has_video_30_sec_watched_actions' => isset($insight['video_30_sec_watched_actions']),
            'has_video_avg_time_watched_actions' => isset($insight['video_avg_time_watched_actions']),
            'has_video_p25_watched_actions' => isset($insight['video_p25_watched_actions']),
            'has_video_p50_watched_actions' => isset($insight['video_p50_watched_actions']),
            'has_video_p75_watched_actions' => isset($insight['video_p75_watched_actions']),
            'has_video_p95_watched_actions' => isset($insight['video_p95_watched_actions']),
            'has_video_p100_watched_actions' => isset($insight['video_p100_watched_actions']),
        ]);
        
                // Extract từ video plays percentage - theo response Facebook API
        $videoPlaysFields = [
            'video_plays_at_25' => 'video_plays_at_25',
            'video_plays_at_50' => 'video_plays_at_50',
            'video_plays_at_75' => 'video_plays_at_75',
            'video_plays_at_100' => 'video_plays_at_100'
        ];
        
        foreach ($videoPlaysFields as $field => $metricKey) {
            if (isset($insight[$field])) {
                $videoMetrics[$metricKey] = (int) $insight[$field];
            }
        }
        
        // Extract từ video percentage watched actions - theo response Facebook API
        $percentageFields = [
            'video_p25_watched_actions' => 'video_p25_watched_actions',
            'video_p50_watched_actions' => 'video_p50_watched_actions',
            'video_p75_watched_actions' => 'video_p75_watched_actions',
            'video_p95_watched_actions' => 'video_p95_watched_actions',
            'video_p100_watched_actions' => 'video_p100_watched_actions'
        ];
        
        foreach ($percentageFields as $field => $metricKey) {
            if (isset($insight[$field])) {
                foreach ($insight[$field] as $action) {
                    if ($action['action_type'] === 'video_view') {
                        $videoMetrics[$metricKey] = (int) $action['value'];
                    }
                }
            }
        }
        
        // Log để debug video percentage metrics
        Log::info("Extracted video percentage metrics", [
            'video_p25_watched_actions' => $videoMetrics['video_p25_watched_actions'],
            'video_p50_watched_actions' => $videoMetrics['video_p50_watched_actions'],
            'video_p75_watched_actions' => $videoMetrics['video_p75_watched_actions'],
            'video_p95_watched_actions' => $videoMetrics['video_p95_watched_actions'],
            'video_p100_watched_actions' => $videoMetrics['video_p100_watched_actions'],
        ]);
        
        // Extract video_play_actions - field mới phát hiện từ response Facebook API
        if (isset($insight['video_play_actions'])) {
            foreach ($insight['video_play_actions'] as $action) {
                if ($action['action_type'] === 'video_view') {
                    $videoMetrics['video_play_actions'] = (int) $action['value'];
                }
            }
        }
        
        // Log để debug video play actions
        Log::info("Extracted video play actions", [
            'video_play_actions' => $videoMetrics['video_play_actions'],
            'has_video_play_actions' => isset($insight['video_play_actions']),
        ]);
        
        // Extract từ actions array (fallback) - chỉ xử lý các trường cơ bản từ response Facebook API
        if (isset($insight['actions'])) {
            foreach ($insight['actions'] as $action) {
                switch ($action['action_type']) {
                    case 'video_view':
                        $videoMetrics['video_views'] = (int) $action['value'];
                        $videoMetrics['video_plays'] = (int) $action['value']; // Sử dụng video_view làm video_plays
                        break;
                    case 'video_play':
                        $videoMetrics['video_plays'] = (int) $action['value'];
                        break;
                    case 'video_p25_watched_actions':
                        $videoMetrics['video_p25_watched_actions'] = (int) $action['value'];
                        $videoMetrics['video_plays_at_25'] = (int) $action['value'];
                        break;
                    case 'video_p50_watched_actions':
                        $videoMetrics['video_p50_watched_actions'] = (int) $action['value'];
                        $videoMetrics['video_plays_at_50'] = (int) $action['value'];
                        break;
                    case 'video_p75_watched_actions':
                        $videoMetrics['video_p75_watched_actions'] = (int) $action['value'];
                        $videoMetrics['video_plays_at_75'] = (int) $action['value'];
                        break;
                    case 'video_p95_watched_actions':
                        $videoMetrics['video_p95_watched_actions'] = (int) $action['value'];
                        break;
                    case 'video_p100_watched_actions':
                        $videoMetrics['video_p100_watched_actions'] = (int) $action['value'];
                        $videoMetrics['video_plays_at_100'] = (int) $action['value'];
                        break;
                    case 'video_thruplay_watched_actions':
                    case 'thruplay':
                        $videoMetrics['thruplays'] = (int) $action['value'];
                        break;
                    // video_avg_time_watched và video_view_time đã được xử lý ở trên bằng method mới
                }
            }
        }
        
        // Log để debug final video metrics
        Log::info("Final extracted video metrics", [
            'video_views' => $videoMetrics['video_views'],
            'video_plays' => $videoMetrics['video_plays'],
            'video_avg_time_watched' => $videoMetrics['video_avg_time_watched'],
            'video_view_time' => $videoMetrics['video_view_time'],
            'video_30_sec_watched' => $videoMetrics['video_30_sec_watched'],
            'thruplays' => $videoMetrics['thruplays'],
        ]);
        
        return $videoMetrics;
    }
    
    /**
     * Xử lý Ad Insights breakdowns và lưu vào bảng facebook_breakdowns
     */
    private function processAdInsightsBreakdowns(array $insightsData, FacebookAd $facebookAd, string $breakdownType, array &$result): void
    {
        try {
            if (!isset($insightsData['data']) || empty($insightsData['data'])) {
                return;
            }

            foreach ($insightsData['data'] as $insight) {
                $videoMetrics = $this->extractCompleteVideoMetrics($insight);
                $date = $insight['date_start'] ?? $insight['date_stop'] ?? now()->toDateString();
                
                // Tạo breakdown data
                $breakdownValues = [];
                $dimensionFields = ['age', 'gender', 'country', 'region', 'publisher_platform', 'platform_position', 'device_platform', 'impression_device'];
                
                foreach ($dimensionFields as $field) {
                    if (isset($insight[$field])) {
                        $breakdownValues[$field] = $insight[$field];
                    }
                }
                
                // Tạo breakdown cho từng giá trị breakdown
                foreach ($breakdownValues as $breakdownField => $breakdownValue) {
                    // Tạo breakdown_type dạng "age:25-34" hoặc "gender:male"
                    $breakdownTypeValue = $breakdownField . ':' . $breakdownValue;
                    $metrics = [
                        // Basic metrics
                        'spend' => (float) ($insight['spend'] ?? 0),
                        'reach' => (int) ($insight['reach'] ?? 0),
                        'impressions' => (int) ($insight['impressions'] ?? 0),
                        'clicks' => (int) ($insight['clicks'] ?? 0),
                        'unique_clicks' => (int) ($insight['unique_clicks'] ?? 0),
                        'ctr' => (float) ($insight['ctr'] ?? 0),
                        'unique_ctr' => (float) ($insight['unique_ctr'] ?? 0),
                        'unique_link_clicks_ctr' => (float) ($insight['unique_link_clicks_ctr'] ?? 0),
                        'unique_impressions' => (int) ($insight['unique_impressions'] ?? 0),
                        'cpc' => (float) ($insight['cpc'] ?? 0),
                        'cpm' => (float) ($insight['cpm'] ?? 0),
                        'frequency' => (float) ($insight['frequency'] ?? 0),
                        
                        // Video metrics
                        'video_views' => $videoMetrics['video_views'],
                        'video_plays' => $videoMetrics['video_plays'],
                        'video_avg_time_watched' => $videoMetrics['video_avg_time_watched'],
                        'video_p25_watched_actions' => $videoMetrics['video_p25_watched_actions'],
                        'video_p50_watched_actions' => $videoMetrics['video_p50_watched_actions'],
                        'video_p75_watched_actions' => $videoMetrics['video_p75_watched_actions'],
                        'video_p95_watched_actions' => $videoMetrics['video_p95_watched_actions'],
                        'video_p100_watched_actions' => $videoMetrics['video_p100_watched_actions'],
                        'thruplays' => $videoMetrics['thruplays'],
                        'video_30_sec_watched' => $videoMetrics['video_30_sec_watched'],
                        // Chỉ lưu các fields thực sự có data từ Facebook API
                        'video_play_actions' => $videoMetrics['video_play_actions'],
                        // Các fields nâng cao sẽ NULL vì không có trong API response
                        'video_watch_at_75_percent_actions' => $videoMetrics['video_watch_at_75_percent_actions'],
                        'video_watch_at_100_percent_actions' => $videoMetrics['video_watch_at_100_percent_actions'],
                        'video_retention_graph' => $videoMetrics['video_retention_graph'],
                        'video_sound_on_actions' => $videoMetrics['video_sound_on_actions'],
                        'video_sound_off_actions' => $videoMetrics['video_sound_off_actions'],
                        'video_quality_actions' => $videoMetrics['video_quality_actions'],
                        'video_engagement_rate' => $videoMetrics['video_engagement_rate'],
                        'video_completion_rate' => $videoMetrics['video_completion_rate'],
                        'video_skip_actions' => $videoMetrics['video_skip_actions'],
                        'video_mute_actions' => $videoMetrics['video_mute_actions'],
                        'video_unmute_actions' => $videoMetrics['video_unmute_actions'],
                        'video_performance_p25' => $videoMetrics['video_performance_p25'],
                        'video_performance_p50' => $videoMetrics['video_performance_p50'],
                        'video_performance_p75' => $videoMetrics['video_performance_p75'],
                        'video_performance_p95' => $videoMetrics['video_performance_p95'],
                        'video_attributed_views' => $videoMetrics['video_attributed_views'],
                        'video_attributed_view_time' => $videoMetrics['video_attributed_view_time'],
                        
                        // JSON fields
                        'actions' => isset($insight['actions']) ? $insight['actions'] : null,
                        'action_values' => isset($insight['action_values']) ? $insight['action_values'] : null,
                    ];
                    
                    // Sử dụng ad_insight_id đã lưu từ processBasicAdInsights
                    if ($this->lastProcessedAdInsightId) {
                        \App\Models\FacebookBreakdown::create([
                            'ad_insight_id' => $this->lastProcessedAdInsightId,
                            'breakdown_type' => $breakdownType,
                            'breakdown_value' => $breakdownTypeValue,
                            'metrics' => $metrics
                        ]);
                        $result['breakdowns']++;
                    }
                }
            }

        } catch (\Exception $e) {
            Log::error("Lỗi khi xử lý ad insights breakdowns", [
                'ad_id' => $facebookAd->id,
                'breakdown_type' => $breakdownType,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Extract breakdown data từ API response
     */
    private function extractBreakdownsFromData(array $data): array
    {
        $breakdowns = [];
        
        foreach ($data as $insight) {
            if (isset($insight['age'])) {
                $breakdowns[] = [
                    'dimension' => 'age',
                    'value' => $insight['age'],
                    'impressions' => (int) ($insight['impressions'] ?? 0),
                    'reach' => (int) ($insight['reach'] ?? 0),
                    'clicks' => (int) ($insight['clicks'] ?? 0),
                    'spend' => (float) ($insight['spend'] ?? 0),
                ];
            }
            
            if (isset($insight['gender'])) {
                $breakdowns[] = [
                    'dimension' => 'gender',
                    'value' => $insight['gender'],
                    'impressions' => (int) ($insight['impressions'] ?? 0),
                    'reach' => (int) ($insight['reach'] ?? 0),
                    'clicks' => (int) ($insight['clicks'] ?? 0),
                    'spend' => (float) ($insight['spend'] ?? 0),
                ];
            }
            
            if (isset($insight['region'])) {
                $breakdowns[] = [
                    'dimension' => 'region',
                    'value' => $insight['region'],
                    'impressions' => (int) ($insight['impressions'] ?? 0),
                    'reach' => (int) ($insight['reach'] ?? 0),
                    'clicks' => (int) ($insight['clicks'] ?? 0),
                    'spend' => (float) ($insight['spend'] ?? 0),
                ];
            }
            
            if (isset($insight['platform_position'])) {
                $breakdowns[] = [
                    'dimension' => 'platform_position',
                    'value' => $insight['platform_position'],
                    'impressions' => (int) ($insight['impressions'] ?? 0),
                    'reach' => (int) ($insight['reach'] ?? 0),
                    'clicks' => (int) ($insight['clicks'] ?? 0),
                    'spend' => (float) ($insight['spend'] ?? 0),
                ];
            }
            
            if (isset($insight['action_type'])) {
                $breakdowns[] = [
                    'dimension' => 'action_type',
                    'value' => $insight['action_type'],
                    'impressions' => (int) ($insight['impressions'] ?? 0),
                    'reach' => (int) ($insight['reach'] ?? 0),
                    'clicks' => (int) ($insight['clicks'] ?? 0),
                    'spend' => (float) ($insight['spend'] ?? 0),
                ];
            }
        }
        
        return $breakdowns;
    }

    /**
     * Extract video metrics từ actions array của Ad Insights
     */
    private function extractVideoMetricsFromActions(array $actions): array
    {
        $videoMetrics = [
            'video_views' => 0,
            'video_view_time' => 0,
            'video_avg_time_watched' => 0,
            'video_plays' => 0,
            'video_plays_at_25' => 0,
            'video_plays_at_50' => 0,
            'video_plays_at_75' => 0,
            'video_plays_at_100' => 0,
            'video_p25_watched_actions' => 0,
            'video_p50_watched_actions' => 0,
            'video_p75_watched_actions' => 0,
            'video_p95_watched_actions' => 0,
            'video_p100_watched_actions' => 0,
            'thruplays' => 0,
        ];

        foreach ($actions as $action) {
            $actionType = $action['action_type'] ?? '';
            $value = (int) ($action['value'] ?? 0);

            switch ($actionType) {
                case 'video_view':
                    $videoMetrics['video_views'] = $value;
                    $videoMetrics['video_plays'] = $value; // Sử dụng video_view làm video_plays
                    break;
                case 'video_play':
                    $videoMetrics['video_plays'] = $value;
                    break;
                case 'video_p25_watched_actions':
                    $videoMetrics['video_p25_watched_actions'] = $value;
                    $videoMetrics['video_plays_at_25'] = $value;
                    break;
                case 'video_p50_watched_actions':
                    $videoMetrics['video_p50_watched_actions'] = $value;
                    $videoMetrics['video_plays_at_50'] = $value;
                    break;
                case 'video_p75_watched_actions':
                    $videoMetrics['video_p75_watched_actions'] = $value;
                    $videoMetrics['video_plays_at_75'] = $value;
                    break;
                case 'video_p95_watched_actions':
                    $videoMetrics['video_p95_watched_actions'] = $value;
                    break;
                case 'video_p100_watched_actions':
                    $videoMetrics['video_p100_watched_actions'] = $value;
                    $videoMetrics['video_plays_at_100'] = $value;
                    break;
                case 'video_thruplay_watched_actions':
                case 'thruplay':
                    $videoMetrics['thruplays'] = $value;
                    break;
                case 'video_avg_time_watched_actions':
                    $videoMetrics['video_avg_time_watched'] = (float) $value;
                    break;
                case 'video_view_time':
                    $videoMetrics['video_view_time'] = $value;
                    break;
            }
        }

        return $videoMetrics;
    }

    /**
     * Xác định loại Ad
     */
    private function determineAdType(array $ad): string
    {
        $creative = $ad['creative'] ?? [];
        
        // Kiểm tra creative có chứa post_id không
        if (isset($creative['object_story_id']) || 
            isset($creative['effective_object_story_id']) ||
            isset($creative['object_story_spec'])) {
            return 'post_ad';
        }
        
        // Mặc định là Link Ad
        return 'link_ad';
    }

    /**
     * Trích xuất Post data từ Ad
     */
    private function extractPostData(array $ad): ?array
    {
        $creative = $ad['creative'] ?? [];
        
        // Log creative info để debug
        Log::info("Extract post data từ creative", [
            'ad_id' => $ad['id'] ?? 'N/A',
            'ad_name' => $ad['name'] ?? 'N/A',
            'creative_keys' => array_keys($creative),
            'has_object_story_id' => isset($creative['object_story_id']),
            'has_effective_object_story_id' => isset($creative['effective_object_story_id']),
            'has_object_story_spec' => isset($creative['object_story_spec'])
        ]);
        
        // Cách 1: Từ object_story_id (chuẩn cho post ads)
        if (isset($creative['object_story_id'])) {
            $storyId = $creative['object_story_id'];
            Log::info("Processing object_story_id", [
                'ad_id' => $ad['id'] ?? 'N/A',
                'story_id' => $storyId,
                'story_id_type' => gettype($storyId)
            ]);
            
            // Tách post_id từ story_id (format: pageId_postId)
            $parts = explode('_', $storyId);
            Log::info("Exploded story_id parts", [
                'ad_id' => $ad['id'] ?? 'N/A',
                'parts' => $parts,
                'parts_count' => count($parts)
            ]);
            
            if (count($parts) >= 2) {
                $postId = $parts[1]; // Lấy phần thứ 2 (post ID)
                $pageId = $parts[0]; // Lấy phần thứ 1 (page ID)
                
                Log::info("Successfully extracted page_id and post_id", [
                    'ad_id' => $ad['id'] ?? 'N/A',
                    'page_id' => $pageId,
                    'post_id' => $postId
                ]);
                
                // Tạo post data từ thông tin có sẵn, không cần gọi API
                return [
                    'id' => $postId,
                    'page_id' => $pageId,
                    'from' => [
                        'id' => $pageId,
                        'name' => 'Page', // Có thể lấy từ page data sau
                        'category' => null,
                        'verification_status' => null,
                    ],
                    'message' => $ad['name'] ?? null,
                    'created_time' => $ad['created_time'] ?? null,
                    'updated_time' => $ad['updated_time'] ?? null,
                ];
            } else {
                Log::warning("object_story_id format invalid", [
                    'ad_id' => $ad['id'] ?? 'N/A',
                    'story_id' => $storyId,
                    'parts' => $parts
                ]);
            }
        }
        
        // Cách 2: Từ effective_object_story_id
        if (isset($creative['effective_object_story_id'])) {
            $storyId = $creative['effective_object_story_id'];
            // Tách post_id từ story_id (format: pageId_postId)
            $parts = explode('_', $storyId);
            if (count($parts) >= 2) {
                $postId = $parts[1]; // Lấy phần thứ 2 (post ID)
                $pageId = $parts[0]; // Lấy phần thứ 1 (page ID)
                
                // Tạo post data từ thông tin có sẵn, không cần gọi API
                return [
                    'id' => $postId,
                    'page_id' => $pageId,
                    'from' => [
                        'id' => $pageId,
                        'name' => 'Page', // Có thể lấy từ page data sau
                        'category' => null,
                        'verification_status' => null,
                    ],
                    'message' => $ad['name'] ?? null,
                    'created_time' => $ad['created_time'] ?? null,
                    'updated_time' => $ad['updated_time'] ?? null,
                ];
            }
        }
        
        // Cách 3: Từ object_story_spec
        if (isset($creative['object_story_spec'])) {
            $spec = $creative['object_story_spec'];
            
            if (isset($spec['link_data']['post_id'])) {
                $postId = $spec['link_data']['post_id'];
                $pageId = $ad['adset']['campaign']['ad_account']['business']['pages'][0]['id'] ?? null;
                
                return [
                    'id' => $postId,
                    'page_id' => $pageId,
                    'from' => [
                        'id' => $pageId,
                        'name' => 'Page',
                        'category' => null,
                        'verification_status' => null,
                    ],
                    'message' => $ad['name'] ?? null,
                    'created_time' => $ad['created_time'] ?? null,
                    'updated_time' => $ad['updated_time'] ?? null,
                ];
            }
            
            if (isset($spec['video_data']['post_id'])) {
                $postId = $spec['video_data']['post_id'];
                $pageId = $ad['adset']['campaign']['ad_account']['business']['pages'][0]['id'] ?? null;
                
                return [
                    'id' => $postId,
                    'page_id' => $pageId,
                    'from' => [
                        'id' => $pageId,
                        'name' => 'Page',
                        'category' => null,
                        'verification_status' => null,
                    ],
                    'message' => $ad['name'] ?? null,
                    'created_time' => $ad['created_time'] ?? null,
                    'updated_time' => $ad['updated_time'] ?? null,
                ];
            }
            
            if (isset($spec['photo_data']['post_id'])) {
                $postId = $spec['photo_data']['post_id'];
                $pageId = $ad['adset']['campaign']['ad_account']['business']['pages'][0]['id'] ?? null;
                
                return [
                    'id' => $postId,
                    'page_id' => $pageId,
                    'from' => [
                        'id' => $pageId,
                        'name' => 'Page',
                        'category' => null,
                        'verification_status' => null,
                    ],
                    'message' => $ad['name'] ?? null,
                    'created_time' => $ad['created_time'] ?? null,
                    'updated_time' => $ad['updated_time'] ?? null,
                ];
            }
        }
        
        return null;
    }

    /**
     * Lấy chi tiết Post từ Facebook API
     */
    private function getPostDetails(string $postId): ?array
    {
        try {
            $postData = $this->api->getPostDetails($postId);
            
            // Log post details info
            Log::info("Lấy được post details", [
                'post_id' => $postId,
                'post_type' => $postData['type'] ?? 'N/A',
                'has_message' => isset($postData['message'])
            ]);
            
            if (isset($postData['error'])) {
                Log::warning("Không lấy được post data", [
                    'post_id' => $postId,
                    'error' => $postData['error']
                ]);
                return null;
            }
            
            return $postData;
            
        } catch (\Exception $e) {
            Log::error("Lỗi khi lấy post details", [
                'post_id' => $postId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Trích xuất Page ID từ post data
     */
    private function extractPageId(FacebookAd $facebookAd, array $creativeData): ?string
    {
        try {
            $pageId = null;
            $storyId = null;

            // Kiểm tra object_story_id
            if (isset($creativeData['object_story_id'])) {
                $storyId = $creativeData['object_story_id'];
                Log::info("Found object_story_id", ['story_id' => $storyId]);
            } elseif (isset($creativeData['effective_object_story_id'])) {
                $storyId = $creativeData['effective_object_story_id'];
                Log::info("Found effective_object_story_id", ['story_id' => $storyId]);
            }

            // Parse story_id để lấy page_id
            if ($storyId && strpos($storyId, '_') !== false) {
                $parts = explode('_', $storyId);
                if (count($parts) >= 2) {
                    $pageId = $parts[0];
                    Log::info("Parsed story_id for page_id", ['page_id' => $pageId]);
                }
            }

            // Nếu không tìm thấy từ story_id, kiểm tra object_story_spec
            if (!$pageId && isset($creativeData['object_story_spec'])) {
                $spec = $creativeData['object_story_spec'];
                Log::info("Found object_story_spec", ['spec' => $spec]);
                
                if (isset($spec['page_id'])) {
                    $pageId = $spec['page_id'];
                    Log::info("Found page_id from object_story_spec", ['page_id' => $pageId]);
                }
            }

            // Fallback: kiểm tra postData nếu có
            if (!$pageId && isset($creativeData['page_id'])) {
                $pageId = $creativeData['page_id'];
                Log::info("Found page_id from creativeData", ['page_id' => $pageId]);
            }

            return $pageId;
            
        } catch (\Exception $e) {
            Log::error("Lỗi khi extract page_id", [
                'ad_id' => $facebookAd->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Trích xuất Post ID từ Ad
     */
    private function extractPostId(FacebookAd $facebookAd): ?string
    {
        try {
            if ($facebookAd->creative) {
                $creative = $facebookAd->creative->creative_data;
                
                // Ưu tiên từ object_story_id (format: pageId_postId)
                if (isset($creative['object_story_id'])) {
                    $storyId = $creative['object_story_id'];
                    if (is_string($storyId)) {
                        $parts = explode('_', $storyId);
                        return $parts[1] ?? null; // Lấy phần thứ 2 (post ID)
                    }
                }
                
                // Từ effective_object_story_id (format: pageId_postId)
                if (isset($creative['effective_object_story_id'])) {
                    $storyId = $creative['effective_object_story_id'];
                    if (is_string($storyId)) {
                        $parts = explode('_', $storyId);
                        return $parts[1] ?? null; // Lấy phần thứ 2 (post ID)
                    }
                }
            }
            
            return null;
            
        } catch (\Exception $e) {
            Log::error("Lỗi khi extract post_id", [
                'ad_id' => $facebookAd->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Báo cáo tiến độ
     */
    private function reportProgress(?callable $onProgress, string $message, array $result): void
    {
        Log::info($message, $result);
        
        if ($onProgress) {
            $onProgress([
                'message' => $message,
                'counts' => [
                    'businesses' => $result['businesses'],
                    'accounts' => $result['accounts'],
                    'campaigns' => $result['campaigns'],
                    'adsets' => $result['adsets'],
                    'ads' => $result['ads'],
                    'posts' => $result['posts'],
                    'pages' => $result['pages'],
                    'post_insights' => $result['post_insights'],
                    'ad_insights' => $result['ad_insights'],
                ],
                'errors' => $result['errors'],
                'timestamp' => now()->toISOString(),
            ]);
        }
    }

    /**
     * Đồng bộ dữ liệu ngày hôm qua (tương thích với code cũ)
     */
    public function syncYesterday(?callable $onProgress = null): array
    {
        return $this->syncFacebookData($onProgress);
    }

    /**
     * Đồng bộ dữ liệu theo khoảng thời gian tùy chỉnh
     */
    public function syncFacebookDataInRange(?callable $onProgress = null, string $since, string $until): array
    {
        return $this->syncFacebookData($onProgress, $since, $until);
    }

    /**
     * Xử lý Post Insights với breakdown data
     */
    private function processPostInsightsWithBreakdown(array $postInsightsWithBreakdown, FacebookAd $facebookAd, array &$result): void
    {
        try {
            if (!isset($postInsightsWithBreakdown['data']) || empty($postInsightsWithBreakdown['data'])) {
                return;
            }

            foreach ($postInsightsWithBreakdown['data'] as $insight) {
                // Lưu breakdown data vào trường breakdowns
                $breakdowns = [];
                if (isset($insight['breakdowns'])) {
                    foreach ($insight['breakdowns'] as $breakdown) {
                        $breakdowns[] = [
                            'dimension' => $breakdown['dimension'] ?? '',
                            'value' => $breakdown['value'] ?? '',
                            'impressions' => (int) ($breakdown['impressions'] ?? 0),
                            'reach' => (int) ($breakdown['reach'] ?? 0),
                            'clicks' => (int) ($breakdown['clicks'] ?? 0),
                            'spend' => (float) ($breakdown['spend'] ?? 0),
                        ];
                    }
                }

                FacebookPostInsight::updateOrCreate(
                    [
                        'post_id' => $facebookAd->post_id,
                        'date' => $insight['date'] ?? now()->toDateString(),
                    ],
                    [
                        'breakdowns' => json_encode($breakdowns),
                    ]
                );
                $result['post_insights']++;
            }

        } catch (\Exception $e) {
            Log::error("Lỗi khi process post insights với breakdown", [
                'post_id' => $facebookAd->post_id ?? 'unknown',
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Xử lý Ad Insights với breakdown data
     */
    private function processAdInsightsWithBreakdown(array $adInsightsWithBreakdown, FacebookAd $facebookAd, array &$result): void
    {
        try {
            if (!isset($adInsightsWithBreakdown['data']) || empty($adInsightsWithBreakdown['data'])) {
                return;
            }

            foreach ($adInsightsWithBreakdown['data'] as $insight) {
                // Lưu breakdown data vào trường breakdowns
                $breakdowns = [];
                if (isset($insight['breakdowns'])) {
                    foreach ($insight['breakdowns'] as $breakdown) {
                        $breakdowns[] = [
                            'dimension' => $breakdown['dimension'] ?? '',
                            'value' => $breakdown['value'] ?? '',
                            'impressions' => (int) ($breakdown['impressions'] ?? 0),
                            'reach' => (int) ($breakdown['reach'] ?? 0),
                            'clicks' => (int) ($breakdown['clicks'] ?? 0),
                            'spend' => (float) ($breakdown['spend'] ?? 0),
                        ];
                    }
                }

                // Chuẩn bị post/page từ creative nếu có
                $pageIdForSave = $facebookAd->page_id ?? null;
                $postIdForSave = $facebookAd->post_id ?? null;
                try {
                    if (isset($facebookAd->creative) && isset($facebookAd->creative->creative_data)) {
                        $creative = $facebookAd->creative->creative_data;
                        if (is_string($creative)) {
                            $decoded = json_decode($creative, true);
                            if (json_last_error() === JSON_ERROR_NONE) {
                                $creative = $decoded;
                            }
                        }
                        $objectStoryId = $creative['object_story_id'] ?? ($creative['effective_object_story_id'] ?? null);
                        if (is_string($objectStoryId) && strpos($objectStoryId, '_') !== false) {
                            [$pageIdFromCreative, $postIdFromCreative] = explode('_', $objectStoryId, 2);
                            $pageIdForSave = $pageIdForSave ?: $pageIdFromCreative;
                            $postIdForSave = $postIdForSave ?: $postIdFromCreative;
                        }
                    }
                } catch (\Throwable $e) {}

                // Strict guard for breakdown save path as well
                $date = $insight['date'] ?? null;
                $since = $result['time_range']['since'] ?? null;
                $until = $result['time_range']['until'] ?? null;
                if (!$date || ($since && $date < $since) || ($until && $date > $until)) {
                    continue;
                }

                FacebookAdInsight::updateOrCreate(
                    [
                        'ad_id' => $facebookAd->id,
                        'date' => $date,
                        ...(Schema::hasColumn('facebook_ad_insights', 'post_id') && $postIdForSave ? ['post_id' => (string) $postIdForSave] : ['post_id' => null]),
                    ],
                    [
                        'breakdowns' => json_encode($breakdowns),
                        ...(Schema::hasColumn('facebook_ad_insights', 'post_id') && $postIdForSave ? ['post_id' => (string) $postIdForSave] : []),
                        ...(Schema::hasColumn('facebook_ad_insights', 'page_id') && $pageIdForSave ? ['page_id' => (string) $pageIdForSave] : []),
                    ]
                );
                $result['ad_insights']++;
            }

        } catch (\Exception $e) {
            Log::error("Lỗi khi process ad insights với breakdown", [
                'ad_id' => $facebookAd->id ?? 'unknown',
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Cập nhật logic sync để lưu data đúng vào từng bảng
     */
    public function syncWithProperDataStructure(): array
    {
        $result = [
            'businesses' => 0,
            'accounts' => 0,
            'campaigns' => 0,
            'adsets' => 0,
            'ads' => 0,
            'posts' => 0,
            'pages' => 0,
            'post_insights' => 0,
            'ad_insights' => 0,
            'errors' => [],
            'start_time' => now()->toDateTimeString(),
            'time_range' => [
                'since' => date('Y-m-d', strtotime('-7 days')),
                'until' => date('Y-m-d')
            ]
        ];

        try {
            // 1. Lấy Business Managers
            $businesses = $this->api->getBusinessManagers();
            if ($businesses && !isset($businesses['error'])) {
                foreach ($businesses['data'] ?? [] as $business) {
                    $this->processBusiness($business, $result);
                }
            }

            Log::info("Sync completed", $result);
            return $result;

        } catch (\Exception $e) {
            Log::error("Lỗi trong sync", ['error' => $e->getMessage()]);
            $result['errors'][] = $e->getMessage();
            return $result;
        }
    }

    /**
     * Xử lý Business và lưu data đúng cấu trúc
     */
    private function processBusiness(array $business, array &$result): void
    {
        try {
            // Lưu Business
            $facebookBusiness = FacebookBusiness::updateOrCreate(
                ['id' => $business['id']],
                [
                    'name' => $business['name'] ?? null,
                    'verification_status' => $business['verification_status'] ?? null,
                    'created_time' => isset($business['created_time']) ? Carbon::parse($business['created_time']) : null,
                ]
            );
            $result['businesses']++;

            // Lấy Ad Accounts
            $clientAccounts = $this->api->getClientAdAccounts($business['id']);
            $ownedAccounts = $this->api->getOwnedAdAccounts($business['id']);

            $allAccounts = array_merge(
                $clientAccounts['data'] ?? [],
                $ownedAccounts['data'] ?? []
            );

            foreach ($allAccounts as $account) {
                $this->processAdAccount($account, $facebookBusiness, $result);
            }

        } catch (\Exception $e) {
            Log::error("Lỗi khi process business", [
                'business_id' => $business['id'],
                'error' => $e->getMessage()
            ]);
            $result['errors'][] = "Business {$business['id']}: " . $e->getMessage();
        }
    }

    /**
     * Xử lý Ad Account và lưu data đúng cấu trúc
     */
    private function processAdAccount(array $account, FacebookBusiness $business, array &$result): void
    {
        try {
            // Lưu Ad Account
            $facebookAccount = FacebookAdAccount::updateOrCreate(
                ['id' => $account['id']],
                [
                    'account_id' => $account['account_id'] ?? null,
                    'name' => $account['name'] ?? null,
                    'account_status' => $account['account_status'] ?? null,
                    'business_id' => $business->id,
                    'created_time' => isset($account['created_time']) ? Carbon::parse($account['created_time']) : null,
                    'updated_time' => isset($account['updated_time']) ? Carbon::parse($account['updated_time']) : null,
                ]
            );
            $result['accounts']++;

            // Lấy Campaigns
            $campaigns = $this->api->getCampaigns($account['id']);
            if ($campaigns && !isset($campaigns['error'])) {
                foreach ($campaigns['data'] ?? [] as $campaign) {
                    $this->processCampaign($campaign, $facebookAccount, $result);
                }
            }

        } catch (\Exception $e) {
            Log::error("Lỗi khi process ad account", [
                'account_id' => $account['id'],
                'error' => $e->getMessage()
            ]);
            $result['errors'][] = "Account {$account['id']}: " . $e->getMessage();
        }
    }

    /**
     * Xử lý Campaign và lưu data đúng cấu trúc
     */
    private function processCampaign(array $campaign, FacebookAdAccount $account, array &$result): void
    {
        try {
            // Lưu Campaign
            $facebookCampaign = FacebookCampaign::updateOrCreate(
                ['id' => $campaign['id']],
                [
                    'name' => $campaign['name'] ?? null,
                    'status' => $campaign['status'] ?? null,
                    'objective' => $campaign['objective'] ?? null,
                    'account_id' => $account->id,
                    'created_time' => isset($campaign['created_time']) ? Carbon::parse($campaign['created_time']) : null,
                    'updated_time' => isset($campaign['updated_time']) ? Carbon::parse($campaign['updated_time']) : null,
                ]
            );
            $result['campaigns']++;

            // Lấy Ad Sets
            $adSets = $this->api->getAdSetsByCampaign($campaign['id']);
            if ($adSets && !isset($adSets['error'])) {
                foreach ($adSets['data'] ?? [] as $adSet) {
                    $this->processAdSet($adSet, $facebookCampaign, $result);
                }
            }

        } catch (\Exception $e) {
            Log::error("Lỗi khi process campaign", [
                'campaign_id' => $campaign['id'],
                'error' => $e->getMessage()
            ]);
            $result['errors'][] = "Campaign {$campaign['id']}: " . $e->getMessage();
        }
    }

    /**
     * Xử lý Ad Set và lưu data đúng cấu trúc
     */
    private function processAdSet(array $adSet, FacebookCampaign $campaign, array &$result): void
    {
        try {
            // Lưu Ad Set
            $facebookAdSet = FacebookAdSet::updateOrCreate(
                ['id' => $adSet['id']],
                [
                    'name' => $adSet['name'] ?? null,
                    'status' => $adSet['status'] ?? null,
                    'campaign_id' => $campaign->id,
                    'created_time' => isset($adSet['created_time']) ? Carbon::parse($adSet['created_time']) : null,
                    'updated_time' => isset($adSet['updated_time']) ? Carbon::parse($adSet['updated_time']) : null,
                ]
            );
            $result['adsets']++;

            // Lấy Ads
            $ads = $this->api->getAdsByAdSet($adSet['id']);
            if ($ads && !isset($ads['error'])) {
                foreach ($ads['data'] ?? [] as $ad) {
                    $this->processAdWithCompleteData($ad, $facebookAdSet, $result);
                }
            }

        } catch (\Exception $e) {
            Log::error("Lỗi khi process ad set", [
                'adset_id' => $adSet['id'],
                'error' => $e->getMessage()
            ]);
            $result['errors'][] = "AdSet {$adSet['id']}: " . $e->getMessage();
        }
    }

    /**
     * Xử lý Ad với đầy đủ data (Post, Insights, Breakdowns)
     * 
     * ⚠️ DEPRECATED: Method này đã được thay thế bởi processAdWithNormalizedStructure
     * để tách biệt việc sync ads và insights. Insights sẽ được xử lý riêng
     * bởi SyncInsightsForExistingAds command.
     */
    private function processAdWithCompleteData(array $ad, FacebookAdSet $adSet, array &$result): void
    {
        // ⚠️ METHOD DEPRECATED - Sử dụng processAdWithNormalizedStructure thay thế
        // để tách biệt việc sync ads và insights
        
        Log::warning("⚠️ processAdWithCompleteData đã deprecated - sử dụng processAdWithNormalizedStructure", [
            'ad_id' => $ad['id'] ?? 'unknown',
            'reason' => 'Insights sẽ được sync riêng bởi SyncInsightsForExistingAds command'
        ]);
        
        // Fallback: sử dụng method mới
        $this->processAdWithNormalizedStructure($ad, $adSet, $result);
        
        // try {
        //     // 1. Lưu Ad
        //     $facebookAd = FacebookAd::updateOrCreate(
        //         ['ad_id' => $ad['id']],
        //         [
        //             'name' => $ad['name'] ?? null,
        //             'status' => $ad['status'] ?? null,
        //             'effective_status' => $ad['effective_status'] ?? null,
        //             'adset_id' => $adSet->id,
        //             'creative' => $ad['creative'] ?? null,
        //             'created_time' => isset($ad['created_time']) ? Carbon::parse($ad['created_time']) : null,
        //             'updated_time' => isset($ad['updated_time']) ? Carbon::parse($ad['updated_time']) : null,
        //         ]
        //     );
        //     $result['ads']++;

        //     // 2. Extract và lưu Post data từ Creative
        //     $postData = $this->extractAndSavePostFromCreative($ad, $facebookAd);
        //     if ($postData) {
        //         $result['posts']++;
        //         $result['pages']++; // Page cũng được tạo
        //     }

        //     // 3. Lấy và lưu Ad Insights với video metrics
        //     $this->processAdInsightsWithVideoMetrics($ad, $facebookAd, $result);

        //     // 4. Lấy và lưu Breakdown data
        //     $this->processAdBreakdowns($ad, $facebookAd, $result);

        // } catch (\Exception $e) {
        //     Log::error("Lỗi khi process ad", [
        //         'ad_id' => $ad['id'],
        //         'error' => $e->getMessage()
        //     ]);
        //     $result['errors'][] = "Ad {$ad['id']}: " . $e->getMessage();
        // }
    }

    /**
     * Extract và lưu Post từ Creative với đầy đủ thông tin
     */
    private function extractAndSavePostFromCreative(array $ad, FacebookAd $facebookAd): ?array
    {
        $creative = $ad['creative'] ?? [];
        
        Log::info("Extract post từ creative", [
            'ad_id' => $ad['id'],
            'creative_keys' => array_keys($creative)
        ]);
        
        // Tìm story_id từ creative
        $storyId = null;
        $pageId = null;
        $postId = null;

        // Kiểm tra object_story_id
        if (isset($creative['object_story_id'])) {
            $storyId = $creative['object_story_id'];
            Log::info("Found object_story_id", ['story_id' => $storyId]);
        } elseif (isset($creative['effective_object_story_id'])) {
            $storyId = $creative['effective_object_story_id'];
            Log::info("Found effective_object_story_id", ['story_id' => $storyId]);
        }

        // Parse story_id để lấy page_id và post_id
        if ($storyId && strpos($storyId, '_') !== false) {
            $parts = explode('_', $storyId);
            if (count($parts) >= 2) {
                $pageId = $parts[0];
                $postId = $parts[1];
                Log::info("Parsed story_id", ['page_id' => $pageId, 'post_id' => $postId]);
            }
        }

        // Nếu không tìm thấy từ story_id, kiểm tra object_story_spec
        if (!$pageId && isset($creative['object_story_spec'])) {
            $spec = $creative['object_story_spec'];
            Log::info("Found object_story_spec", ['spec' => $spec]);
            
            if (isset($spec['page_id'])) {
                $pageId = $spec['page_id'];
                if (isset($spec['video_data']['post_id'])) {
                    $postId = $spec['video_data']['post_id'];
                } elseif (isset($spec['link_data']['post_id'])) {
                    $postId = $spec['link_data']['post_id'];
                }
            }
        }

        if ($pageId && $postId) {
            // Lưu Page
            $page = FacebookPage::firstOrCreate(
                ['id' => $pageId],
                [
                    'name' => 'Page ' . $pageId,
                    'category' => null,
                    'verification_status' => null,
                ]
            );

            // Lưu Post với thông tin từ creative
            $post = FacebookPost::updateOrCreate(
                ['id' => $postId],
                [
                    'page_id' => $pageId,
                    'message' => $creative['title'] ?? $creative['body'] ?? null,
                    'type' => 'post',
                    'status_type' => null,
                    'attachments' => isset($creative['object_story_spec']) ? json_encode($creative['object_story_spec']) : null,
                    'permalink_url' => null,
                    'created_time' => null,
                    'updated_time' => null,
                ]
            );

            // Cập nhật foreign key trong FacebookAd
            $facebookAd->update(['post_id' => $post->id]);

            Log::info("Đã lưu post data từ creative", [
                'ad_id' => $ad['id'],
                'page_id' => $pageId,
                'post_id' => $postId,
                'facebook_post_id' => $post->id,
                'creative_title' => $creative['title'] ?? null,
                'creative_body' => $creative['body'] ?? null
            ]);

            return [
                'page_id' => $pageId,
                'post_id' => $postId,
                'facebook_post_id' => $post->id
            ];
        }

        Log::warning("Không tìm thấy post data trong creative", [
            'ad_id' => $ad['id'],
            'creative' => $creative
        ]);

        return null;
    }

    /**
     * Xử lý Ad Insights với video metrics đầy đủ
     */
    private function processAdInsightsWithVideoMetrics(array $ad, FacebookAd $facebookAd, array &$result): void
    {
        try {
            // Lấy Ad Insights với video metrics đầy đủ theo ngày trong khoảng đang sync
            $since = $result['time_range']['since'] ?? now()->format('Y-m-d');
            $until = $result['time_range']['until'] ?? now()->format('Y-m-d');
            $adInsights = $this->api->getInsightsForAd($ad['id'], $since, $until, '1');
            
            if ($adInsights && !isset($adInsights['error']) && isset($adInsights['data'])) {
                foreach ($adInsights['data'] as $insight) {
                    // Lưu Ad Insight với đầy đủ video metrics từ API
                    FacebookAdInsight::updateOrCreate(
                        [
                            'ad_id' => $facebookAd->id,
                            // Chuẩn hóa: luôn lưu theo ngày (YYYY-MM-DD)
                            'date' => isset($insight['date_start']) ? \Illuminate\Support\Carbon::parse($insight['date_start'])->toDateString() : date('Y-m-d'),
                            ...(Schema::hasColumn('facebook_ad_insights', 'post_id') && ($facebookAd->post_id ?? null) ? ['post_id' => (string) $facebookAd->post_id] : ['post_id' => null]),
                        ],
                        [
                            'spend' => (float) ($insight['spend'] ?? 0),
                            'reach' => (int) ($insight['reach'] ?? 0),
                            'impressions' => (int) ($insight['impressions'] ?? 0),
                            'clicks' => (int) ($insight['clicks'] ?? 0),
                            'ctr' => (float) ($insight['ctr'] ?? 0),
                            'cpc' => (float) ($insight['cpc'] ?? 0),
                            'cpm' => (float) ($insight['cpm'] ?? 0),
                            'frequency' => (float) ($insight['frequency'] ?? 0),
                            'unique_clicks' => (int) ($insight['unique_clicks'] ?? 0),
                            'unique_ctr' => (float) ($insight['unique_ctr'] ?? 0),
                            'unique_link_clicks_ctr' => (float) ($insight['unique_link_clicks_ctr'] ?? 0),
                            'unique_impressions' => (int) ($insight['unique_impressions'] ?? 0),
                            'conversions' => (int) ($insight['conversions'] ?? 0),
                            'conversion_values' => (float) ($insight['conversion_values'] ?? 0),
                            'cost_per_conversion' => (float) ($insight['cost_per_conversion'] ?? 0),
                            'purchase_roas' => (float) ($insight['purchase_roas'] ?? 0),
                            'outbound_clicks' => (int) ($insight['outbound_clicks'] ?? 0),
                            'unique_outbound_clicks' => (int) ($insight['unique_outbound_clicks'] ?? 0),
                            'inline_link_clicks' => (int) ($insight['inline_link_clicks'] ?? 0),
                            'unique_inline_link_clicks' => (int) ($insight['unique_inline_link_clicks'] ?? 0),
                            'website_clicks' => (int) ($insight['website_clicks'] ?? 0),
                            'actions' => isset($insight['actions']) ? json_encode($insight['actions']) : null,
                            'action_values' => isset($insight['action_values']) ? json_encode($insight['action_values']) : null,
                            
                            // Video metrics chính từ API - sử dụng method extractVideoMetrics
                            ...$this->extractVideoMetrics($insight),
                         
                            'video_p25_watched_actions' => (int) ($insight['video_p25_watched_actions'] ?? 0),
                            'video_p50_watched_actions' => (int) ($insight['video_p50_watched_actions'] ?? 0),
                            'video_p75_watched_actions' => (int) ($insight['video_p75_watched_actions'] ?? 0),
                            'video_p95_watched_actions' => (int) ($insight['video_p95_watched_actions'] ?? 0),
                            'video_p100_watched_actions' => (int) ($insight['video_p100_watched_actions'] ?? 0),
                        ]
                    );
                }
                $result['ad_insights']++;
                
                Log::info("Đã lưu ad insights với video metrics", [
                    'ad_id' => $ad['id'],
                    'insights_count' => count($adInsights['data'])
                ]);
            } else {
                Log::warning("Không lấy được ad insights", [
                    'ad_id' => $ad['id'],
                    'error' => $adInsights['error'] ?? 'Unknown error'
                ]);
            }

        } catch (\Exception $e) {
            Log::error("Lỗi khi process ad insights", [
                'ad_id' => $ad['id'],
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Xử lý Ad Breakdowns và lưu vào bảng riêng
     */
    private function processAdBreakdowns(array $ad, FacebookAd $facebookAd, array &$result): void
    {
        try {
            // Tìm insight record
            $existingInsight = FacebookAdInsight::where('ad_id', $facebookAd->id)->first();
            if (!$existingInsight) {
                Log::warning("Không tìm thấy insight record để lưu breakdown", ['ad_id' => $facebookAd->id]);
                return;
            }

            // Age/Gender breakdown
            $ageGenderBreakdown = $this->api->getInsightsWithAgeGenderBreakdown($ad['id']);
            if ($ageGenderBreakdown && !isset($ageGenderBreakdown['error'])) {
                $this->saveBreakdownsToTable($ageGenderBreakdown['data'] ?? [], $existingInsight->id, 'age_gender');
            }

            // Region breakdown
            $regionBreakdown = $this->api->getInsightsWithRegionBreakdown($ad['id']);
            if ($regionBreakdown && !isset($regionBreakdown['error'])) {
                $this->saveBreakdownsToTable($regionBreakdown['data'] ?? [], $existingInsight->id, 'region');
            }

            // Platform position breakdown
            $platformPositionBreakdown = $this->api->getInsightsWithPlatformPositionBreakdown($ad['id']);
            if ($platformPositionBreakdown && !isset($platformPositionBreakdown['error'])) {
                $this->saveBreakdownsToTable($platformPositionBreakdown['data'] ?? [], $existingInsight->id, 'platform_position');
            }

            // Publisher platform breakdown
            $publisherPlatformBreakdown = $this->api->getInsightsWithPublisherPlatformBreakdown($ad['id']);
            if ($publisherPlatformBreakdown && !isset($publisherPlatformBreakdown['error'])) {
                $this->saveBreakdownsToTable($publisherPlatformBreakdown['data'] ?? [], $existingInsight->id, 'publisher_platform');
            }

            // Device platform breakdown
            $devicePlatformBreakdown = $this->api->getInsightsWithDevicePlatformBreakdown($ad['id']);
            if ($devicePlatformBreakdown && !isset($devicePlatformBreakdown['error'])) {
                $this->saveBreakdownsToTable($devicePlatformBreakdown['data'] ?? [], $existingInsight->id, 'device_platform');
            }

            // Country breakdown
            $countryBreakdown = $this->api->getInsightsWithCountryBreakdown($ad['id']);
            if ($countryBreakdown && !isset($countryBreakdown['error'])) {
                $this->saveBreakdownsToTable($countryBreakdown['data'] ?? [], $existingInsight->id, 'country');
            }

            // Impression device breakdown
            $impressionDeviceBreakdown = $this->api->getInsightsWithImpressionDeviceBreakdown($ad['id']);
            if ($impressionDeviceBreakdown && !isset($impressionDeviceBreakdown['error'])) {
                $this->saveBreakdownsToTable($impressionDeviceBreakdown['data'] ?? [], $existingInsight->id, 'impression_device');
            }

            // Action type breakdown
            $actionTypeBreakdown = $this->api->getInsightsWithActionTypeBreakdown($ad['id']);
            if ($actionTypeBreakdown && !isset($actionTypeBreakdown['error'])) {
                $this->saveBreakdownsToTable($actionTypeBreakdown['data'] ?? [], $existingInsight->id, 'action_type');
            }

            // Action device breakdown
            $actionDeviceBreakdown = $this->api->getInsightsWithActionDeviceBreakdown($ad['id']);
            if ($actionDeviceBreakdown && !isset($actionDeviceBreakdown['error'])) {
                $this->saveBreakdownsToTable($actionDeviceBreakdown['data'] ?? [], $existingInsight->id, 'action_device');
            }

            // Action destination breakdown
            $actionDestinationBreakdown = $this->api->getInsightsWithActionDestinationBreakdown($ad['id']);
            if ($actionDestinationBreakdown && !isset($actionDestinationBreakdown['error'])) {
                $this->saveBreakdownsToTable($actionDestinationBreakdown['data'] ?? [], $existingInsight->id, 'action_destination');
            }

            // Action target ID breakdown
            $actionTargetIdBreakdown = $this->api->getInsightsWithActionTargetIdBreakdown($ad['id']);
            if ($actionTargetIdBreakdown && !isset($actionTargetIdBreakdown['error'])) {
                $this->saveBreakdownsToTable($actionTargetIdBreakdown['data'] ?? [], $existingInsight->id, 'action_target_id');
            }

            // Action reaction breakdown
            $actionReactionBreakdown = $this->api->getInsightsWithActionReactionBreakdown($ad['id']);
            if ($actionReactionBreakdown && !isset($actionReactionBreakdown['error'])) {
                $this->saveBreakdownsToTable($actionReactionBreakdown['data'] ?? [], $existingInsight->id, 'action_reaction');
            }

            // Action video sound breakdown
            $actionVideoSoundBreakdown = $this->api->getInsightsWithActionVideoSoundBreakdown($ad['id']);
            if ($actionVideoSoundBreakdown && !isset($actionVideoSoundBreakdown['error'])) {
                $this->saveBreakdownsToTable($actionVideoSoundBreakdown['data'] ?? [], $existingInsight->id, 'action_video_sound');
            }

            // Action video type breakdown
            $actionVideoTypeBreakdown = $this->api->getInsightsWithActionVideoTypeBreakdown($ad['id']);
            if ($actionVideoTypeBreakdown && !isset($actionVideoTypeBreakdown['error'])) {
                $this->saveBreakdownsToTable($actionVideoTypeBreakdown['data'] ?? [], $existingInsight->id, 'action_video_type');
            }

            // Action carousel card ID breakdown
            $actionCarouselCardIdBreakdown = $this->api->getInsightsWithActionCarouselCardIdBreakdown($ad['id']);
            if ($actionCarouselCardIdBreakdown && !isset($actionCarouselCardIdBreakdown['error'])) {
                $this->saveBreakdownsToTable($actionCarouselCardIdBreakdown['data'] ?? [], $existingInsight->id, 'action_carousel_card_id');
            }

            // Action carousel card name breakdown
            $actionCarouselCardNameBreakdown = $this->api->getInsightsWithActionCarouselCardNameBreakdown($ad['id']);
            if ($actionCarouselCardNameBreakdown && !isset($actionCarouselCardNameBreakdown['error'])) {
                $this->saveBreakdownsToTable($actionCarouselCardNameBreakdown['data'] ?? [], $existingInsight->id, 'action_carousel_card_name');
            }

            // Action canvas component name breakdown
            $actionCanvasComponentNameBreakdown = $this->api->getInsightsWithActionCanvasComponentNameBreakdown($ad['id']);
            if ($actionCanvasComponentNameBreakdown && !isset($actionCanvasComponentNameBreakdown['error'])) {
                $this->saveBreakdownsToTable($actionCanvasComponentNameBreakdown['data'] ?? [], $existingInsight->id, 'action_canvas_component_name');
            }

            Log::info("Đã lưu breakdown data", [
                'ad_id' => $ad['id'],
                'insight_id' => $existingInsight->id
            ]);

        } catch (\Exception $e) {
            Log::error("Lỗi khi process ad breakdowns", [
                'ad_id' => $ad['id'],
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Sync only insights for existing Ads in DB within a date range.
     */
    public function syncInsightsForExistingAds(?callable $onProgress = null, string $since, string $until, ?int $limit = null, bool $withBreakdowns = false): array
    {
        $result = [
            'ads' => 0,
            'ad_insights' => 0,
            'breakdowns' => 0,
            'errors' => [],
            'time_range' => ['since' => $since, 'until' => $until],
            // Thêm các keys cần thiết cho reportProgress
            'businesses' => 0,
            'accounts' => 0,
            'campaigns' => 0,
            'adsets' => 0,
            'posts' => 0,
            'pages' => 0,
            'post_insights' => 0,
        ];

        // Lọc ads trong database theo tiêu chí thời gian trước khi gọi API
        $adsQuery = \App\Models\FacebookAd::query()
            ->whereBetween('created_time', [$since, $until])
            ->orderBy('id');
            
        if ($limit) {
            $adsQuery->limit($limit);
        }
        
        $ads = $adsQuery->get(['id', 'post_id', 'page_id', 'created_time', 'updated_time']);
        
        // Log thông tin lọc ads
        if ($onProgress) {
            $onProgress([
                'message' => "🔍 Filtered {$ads->count()} ads from database for date range {$since} to {$until}",
                'counts' => ['ads' => $ads->count()]
            ]);
        }

        foreach ($ads as $ad) {
            try {
                $result['ads']++;
                // Fetch insights strictly in range with daily increment and save
                $this->fetchAndSaveInsightsInRangeForAd($ad, $since, $until, $result);
                if ($withBreakdowns) {
                    $this->fetchAndSaveBreakdownsInRangeForAd($ad, $since, $until, $result);
                }
                // Immediately persist post info for this ad (lifetime) to facebook_post_ads
                $this->persistPostAdIfNeeded($ad);
                $this->reportProgress($onProgress, 'Processed insights for ad ' . $ad->id, $result);
            } catch (\Throwable $e) {
                $result['errors'][] = $e->getMessage();
            }
        }

        return $result;
    }

    /**
     * Persist post info for an ad into facebook_post_ads if not existing yet.
     */
    private function persistPostAdIfNeeded(FacebookAd $facebookAd): void
    {
        try {
            Log::debug('persistPostAdIfNeeded: start', ['ad_id' => (string) $facebookAd->id]);
            if (!\Illuminate\Support\Facades\Schema::hasTable('facebook_post_ads')) {
                Log::warning('persistPostAdIfNeeded: table missing facebook_post_ads');
                return;
            }
            $postId = (string) ($facebookAd->post_id ?? '');
            $pageId = (string) ($facebookAd->page_id ?? '');

            // If missing, try extract from ad creative
            if (!$postId || !$pageId) {
                try {
                    $adData = $this->api->getAdDetails((string)$facebookAd->id);
                    if (is_array($adData)) {
                        $postData = $this->extractPostData($adData);
                        if ($postData) {
                            $postId = $postId ?: (string) ($postData['id'] ?? '');
                            $pageId = $pageId ?: (string) ($postData['page_id'] ?? '');
                        } else {
                            $storyId = $adData['creative']['effective_object_story_id'] ?? ($adData['creative']['object_story_id'] ?? null);
                            if (is_string($storyId) && str_contains($storyId, '_')) {
                                [$pg, $ps] = explode('_', $storyId, 2);
                                $pageId = $pageId ?: (string) $pg;
                                $postId = $postId ?: (string) $ps;
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    Log::warning('persistPostAdIfNeeded: extract post/page failed', ['ad_id' => (string) $facebookAd->id, 'error' => $e->getMessage()]);
                }
            }

            if (!$postId || !$pageId) { Log::info('persistPostAdIfNeeded: skip missing post or page', ['ad_id' => (string) $facebookAd->id]); return; }

            // Không bỏ qua nếu đã có; sẽ update lại thông tin
            $exists = \Illuminate\Support\Facades\DB::table('facebook_post_ads')
                ->where('page_id', $pageId)
                ->where('post_id', $postId)
                ->exists();
            if ($exists) { Log::debug('persistPostAdIfNeeded: already exists', ['page_id'=>$pageId,'post_id'=>$postId]); }

            // Chỉ gọi nếu page tồn tại trong bảng facebook_fanpage và có access token
            $fanpage = null; $pageAccessToken = null;
            try {
                $fanpage = $pageId ? \App\Models\FacebookFanpage::where('page_id', (string)$pageId)->first() : null;
                $pageAccessToken = $fanpage?->access_token ?: null;
            } catch (\Throwable $e) { /* ignore */ }

            if (!$fanpage || !$pageAccessToken) {
                \Log::info('persistPostAdIfNeeded: skip no fanpage/token', ['page_id' => $pageId, 'post_id' => $postId]);
                return; // giữ nguyên logic cũ cho insights, chỉ bỏ qua lưu post
            }

            // Chuẩn hoá post id: {pageId}_{postId}
            $normalizedId = strpos($postId, '_') !== false ? $postId : ($pageId . '_' . $postId);

            // Fallback: nếu request với bộ fields đầy đủ lỗi (#10/#12), thử bộ fields tối giản
            $details = $this->api->getPostDetails($normalizedId, $pageAccessToken);
            if (!is_array($details) || isset($details['error'])) {
                \Log::warning('persistPostAdIfNeeded: retry getPostDetails with minimal fields', ['post_id'=>$normalizedId, 'resp'=>$details ?? null]);
                if (defined('STDOUT')) { @fwrite(STDOUT, "POST_API_ERROR[full]: ".json_encode($details)."\n"); }
                $minimalFields = 'id,message,created_time,permalink_url';
                $details = $this->api->getPostDetails($normalizedId, $pageAccessToken, $minimalFields);
            }
            if (!is_array($details) || isset($details['error'])) { 
                \Log::info('persistPostAdIfNeeded: getPostDetails failed', ['post_id'=>$postId,'resp'=>$details ?? null]); 
                if (defined('STDOUT')) { @fwrite(STDOUT, "POST_API_ERROR[min]: ".json_encode($details)."\n"); }
                return; 
            }

            // Bỏ dd: luôn tiếp tục lưu dữ liệu

            // Extract lightweight engagement metrics from response if available
            $likesCount = 0; $commentsCount = 0; $sharesCount = 0; $reactionsCount = 0;
            try {
                if (isset($details['likes']['summary']['total_count'])) { $likesCount = (int)$details['likes']['summary']['total_count']; }
                if (isset($details['comments']['summary']['total_count'])) { $commentsCount = (int)$details['comments']['summary']['total_count']; }
                if (isset($details['shares']['count'])) { $sharesCount = (int)$details['shares']['count']; }
                if (isset($details['reactions']['summary']['total_count'])) { $reactionsCount = (int)$details['reactions']['summary']['total_count']; }
            } catch (\Throwable $e) { /* ignore */ }

            // Extract attachment info
            $attachType = null; $attachImage = null; $attachSource = null;
            try {
                if (isset($details['attachments']['data'][0])) {
                    $att = $details['attachments']['data'][0];
                    $attachType = $att['media_type'] ?? null;
                    if (isset($att['media']['image']['src'])) { $attachImage = $att['media']['image']['src']; }
                    if (isset($att['media']['source'])) { $attachSource = $att['media']['source']; }
                }
            } catch (\Throwable $e) { /* ignore */ }

            // Extract from info
            $fromId = $details['from']['id'] ?? null;
            $fromName = $details['from']['name'] ?? null;
            $fromPicture = $details['from']['picture']['data']['url'] ?? null;

            \Illuminate\Support\Facades\DB::table('facebook_post_ads')->updateOrInsert(
                ['page_id' => $pageId, 'post_id' => $postId],
                [
                'page_id' => $pageId,
                'post_id' => $postId,
                'time_range' => 'lifetime',
                'message' => $details['message'] ?? null,
                'type' => $details['type'] ?? null,
                'attachment_type' => $attachType,
                'attachment_image' => $attachImage,
                'attachment_source' => $attachSource,
                'permalink_url' => $details['permalink_url'] ?? null,
                'created_time' => isset($details['created_time']) ? \Carbon\Carbon::parse($details['created_time']) : null,
                'updated_time' => isset($details['updated_time']) ? \Carbon\Carbon::parse($details['updated_time']) : null,
                'from_id' => $fromId,
                'from_name' => $fromName,
                'from_picture' => $fromPicture,
                'likes_count' => $likesCount,
                'comments_count' => $commentsCount,
                'shares_count' => $sharesCount,
                'reactions_count' => $reactionsCount,
                'raw' => json_encode($details),
                'updated_at' => now(),
                'created_at' => $exists ? \Illuminate\Support\Facades\DB::raw('created_at') : now(),
            ]);
            Log::info('persistPostAdIfNeeded: saved', ['page_id'=>$pageId,'post_id'=>$postId]);
        } catch (\Throwable $e) {
            Log::error('persistPostAdIfNeeded: error', ['ad_id' => (string) $facebookAd->id, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Fetch insights in given date range (time_increment=1) and save using strict guards.
     */
    private function fetchAndSaveInsightsInRangeForAd(FacebookAd $facebookAd, string $since, string $until, array &$result): void
    {
        $adId = $facebookAd->id;
        $adInsights = $this->api->getInsightsForAd($adId, $since, $until, '1');
        if (!$adInsights || isset($adInsights['error']) || !isset($adInsights['data'])) {
            return;
        }

        foreach ($adInsights['data'] as $insight) {
            $date = isset($insight['date_start']) ? \Illuminate\Support\Carbon::parse($insight['date_start'])->toDateString() : null;
            if (!$date || $date < $since || $date > $until) {
                continue;
            }

            // Map creative post/page if available on the ad
            $postIdForSave = $facebookAd->post_id ?? null;
            $pageIdForSave = $facebookAd->page_id ?? null;
            
            // Nếu chưa có page_id/post_id, thử extract từ creative
            if (!$postIdForSave || !$pageIdForSave) {
                try {
                    // Lấy creative data từ API
                    $adData = $this->api->getAdDetails($adId);
                    if ($adData && isset($adData['creative'])) {
                        $postData = $this->extractPostData($adData);
                        if ($postData) {
                            $postIdForSave = $postData['id'] ?? $postIdForSave;
                            $pageIdForSave = $postData['page_id'] ?? $pageIdForSave;
                            
                            // Cập nhật vào database để lần sau không cần gọi API
                            $facebookAd->update([
                                'post_id' => $postIdForSave,
                                'page_id' => $pageIdForSave
                            ]);
                            
                            Log::info("Extracted page_id and post_id from creative", [
                                'ad_id' => $adId,
                                'page_id' => $pageIdForSave,
                                'post_id' => $postIdForSave
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning("Failed to extract page_id/post_id from creative", [
                        'ad_id' => $adId,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // (logs removed)

            // Tổng hợp actions để suy ra các chỉ số messaging và engagement nếu có
            $actionTotals = [];
            if (isset($insight['actions']) && is_array($insight['actions'])) {
                foreach ($insight['actions'] as $action) {
                    $type = $action['action_type'] ?? null;
                    if (!$type) { continue; }
                    $actionTotals[$type] = (int) ($action['value'] ?? 0);
                }
            }

            \App\Models\FacebookAdInsight::updateOrCreate(
                [
                    'ad_id' => (string) $adId,
                    'date' => $date,
                    ...(\Illuminate\Support\Facades\Schema::hasColumn('facebook_ad_insights', 'post_id') && $postIdForSave ? ['post_id' => (string) $postIdForSave] : ['post_id' => null]),
                ],
                [
                    'spend' => (float) ($insight['spend'] ?? 0),
                    'reach' => (int) ($insight['reach'] ?? 0),
                    'impressions' => (int) ($insight['impressions'] ?? 0),
                    'clicks' => (int) ($insight['clicks'] ?? 0),
                    'ctr' => (float) ($insight['ctr'] ?? 0),
                    'cpc' => (float) ($insight['cpc'] ?? 0),
                    'cpm' => (float) ($insight['cpm'] ?? 0),
                    'frequency' => (float) ($insight['frequency'] ?? 0),
                    'unique_clicks' => (int) ($insight['unique_clicks'] ?? 0),
                    'unique_ctr' => (float) ($insight['unique_ctr'] ?? 0),
                    'unique_link_clicks_ctr' => (float) ($insight['unique_link_clicks_ctr'] ?? 0),
                    'unique_impressions' => (int) ($insight['unique_impressions'] ?? 0),
                    'conversions' => (int) ($insight['conversions'] ?? 0),
                    'conversion_values' => (float) ($insight['conversion_values'] ?? 0),
                    'cost_per_conversion' => (float) ($insight['cost_per_conversion'] ?? 0),
                    'purchase_roas' => (float) ($insight['purchase_roas'] ?? 0),
                    'outbound_clicks' => (int) ($insight['outbound_clicks'] ?? 0),
                    'unique_outbound_clicks' => (int) ($insight['unique_outbound_clicks'] ?? 0),
                    'inline_link_clicks' => (int) ($insight['inline_link_clicks'] ?? 0),
                    'unique_inline_link_clicks' => (int) ($insight['unique_inline_link_clicks'] ?? 0),
                    'website_clicks' => (int) ($insight['website_clicks'] ?? ($actionTotals['link_click'] ?? 0)),
                    'actions' => isset($insight['actions']) ? json_encode($insight['actions']) : null,
                    'action_values' => isset($insight['action_values']) ? json_encode($insight['action_values']) : null,
                    // Single source of truth for video metrics; do NOT override below
                    ...$this->extractVideoMetrics($insight),
                    // Messaging & common action fields (nếu tồn tại cột) suy ra từ actions
                    ...(function() use ($actionTotals) {
                        $out = [];
                        if (\Illuminate\Support\Facades\Schema::hasColumn('facebook_ad_insights', 'messaging_conversation_started_7d')) {
                            $out['messaging_conversation_started_7d'] = (int) (($actionTotals['onsite_conversion.messaging_conversation_started_7d'] ?? 0)
                                + ($actionTotals['omni_messaging_conversation_started_7d'] ?? 0));
                        }
                        if (\Illuminate\Support\Facades\Schema::hasColumn('facebook_ad_insights', 'total_messaging_connection')) {
                            $out['total_messaging_connection'] = (int) (($actionTotals['onsite_conversion.total_messaging_connection'] ?? 0)
                                + ($actionTotals['omni_total_messaging_connection'] ?? 0));
                        }
                        if (\Illuminate\Support\Facades\Schema::hasColumn('facebook_ad_insights', 'messaging_conversation_replied_7d')) {
                            $out['messaging_conversation_replied_7d'] = (int) (($actionTotals['onsite_conversion.messaging_conversation_replied_7d'] ?? 0)
                                + ($actionTotals['omni_messaging_conversation_replied_7d'] ?? 0));
                        }
                        if (\Illuminate\Support\Facades\Schema::hasColumn('facebook_ad_insights', 'messaging_welcome_message_view')) {
                            $out['messaging_welcome_message_view'] = (int) ($actionTotals['onsite_conversion.messaging_welcome_message_view'] ?? 0);
                        }
                        // Messaging extras
                        if (\Illuminate\Support\Facades\Schema::hasColumn('facebook_ad_insights', 'messaging_first_reply')) {
                            $out['messaging_first_reply'] = (int) ($actionTotals['onsite_conversion.messaging_first_reply'] ?? 0);
                        }
                        if (\Illuminate\Support\Facades\Schema::hasColumn('facebook_ad_insights', 'messaging_user_depth_2_message_send')) {
                            $out['messaging_user_depth_2_message_send'] = (int) ($actionTotals['onsite_conversion.messaging_user_depth_2_message_send'] ?? 0);
                        }
                        if (\Illuminate\Support\Facades\Schema::hasColumn('facebook_ad_insights', 'messaging_user_depth_3_message_send')) {
                            $out['messaging_user_depth_3_message_send'] = (int) ($actionTotals['onsite_conversion.messaging_user_depth_3_message_send'] ?? 0);
                        }
                        if (\Illuminate\Support\Facades\Schema::hasColumn('facebook_ad_insights', 'messaging_user_depth_5_message_send')) {
                            $out['messaging_user_depth_5_message_send'] = (int) ($actionTotals['onsite_conversion.messaging_user_depth_5_message_send'] ?? 0);
                        }
                        if (\Illuminate\Support\Facades\Schema::hasColumn('facebook_ad_insights', 'messaging_block')) {
                            $out['messaging_block'] = (int) ($actionTotals['onsite_conversion.messaging_block'] ?? 0);
                        }
                        // Engagement / interactions
                        foreach ([
                            'post_engagement' => 'post_engagement',
                            'page_engagement' => 'page_engagement',
                            'post_interaction_gross' => 'post_interaction_gross',
                            'post_reaction' => 'post_reaction',
                            'link_click' => 'link_click',
                        ] as $col => $actionKey) {
                            if (\Illuminate\Support\Facades\Schema::hasColumn('facebook_ad_insights', $col)) {
                                $out[$col] = (int) ($actionTotals[$actionKey] ?? 0);
                            }
                        }
                        // Leads & checkout
                        if (\Illuminate\Support\Facades\Schema::hasColumn('facebook_ad_insights', 'lead')) {
                            $out['lead'] = (int) ($actionTotals['lead'] ?? 0);
                        }
                        if (\Illuminate\Support\Facades\Schema::hasColumn('facebook_ad_insights', 'onsite_conversion_lead')) {
                            $out['onsite_conversion_lead'] = (int) ($actionTotals['onsite_conversion.lead'] ?? 0);
                        }
                        if (\Illuminate\Support\Facades\Schema::hasColumn('facebook_ad_insights', 'onsite_web_lead')) {
                            $out['onsite_web_lead'] = (int) ($actionTotals['onsite_web_lead'] ?? 0);
                        }
                        if (\Illuminate\Support\Facades\Schema::hasColumn('facebook_ad_insights', 'lead_grouped')) {
                            $out['lead_grouped'] = (int) ($actionTotals['onsite_conversion.lead_grouped'] ?? 0);
                        }
                        foreach ([
                            'offsite_complete_registration_add_meta_leads',
                            'offsite_search_add_meta_leads',
                            'offsite_content_view_add_meta_leads',
                        ] as $col) {
                            if (\Illuminate\Support\Facades\Schema::hasColumn('facebook_ad_insights', $col)) {
                                $out[$col] = (int) ($actionTotals[$col] ?? 0);
                            }
                        }
                        if (\Illuminate\Support\Facades\Schema::hasColumn('facebook_ad_insights', 'onsite_conversion_initiate_checkout')) {
                            $out['onsite_conversion_initiate_checkout'] = (int) ($actionTotals['onsite_conversion.initiate_checkout'] ?? 0);
                        }
                        if (\Illuminate\Support\Facades\Schema::hasColumn('facebook_ad_insights', 'onsite_web_initiate_checkout')) {
                            $out['onsite_web_initiate_checkout'] = (int) ($actionTotals['onsite_web_initiate_checkout'] ?? 0);
                        }
                        if (\Illuminate\Support\Facades\Schema::hasColumn('facebook_ad_insights', 'omni_initiated_checkout')) {
                            $out['omni_initiated_checkout'] = (int) ($actionTotals['omni_initiated_checkout'] ?? 0);
                        }
                        return $out;
                    })(),
                    ...(\Illuminate\Support\Facades\Schema::hasColumn('facebook_ad_insights', 'page_id') && $pageIdForSave ? ['page_id' => (string) $pageIdForSave] : []),
                ]
            );

            $result['ad_insights']++;
        }
    }

    /**
     * Fetch breakdowns for a given ad in date range and attach to corresponding ad_insight rows.
     */
    private function fetchAndSaveBreakdownsInRangeForAd(FacebookAd $facebookAd, string $since, string $until, array &$result): void
    {
        $adId = $facebookAd->id;
        $callers = [
            'age' => fn() => $this->api->getInsightsWithAgeGenderBreakdown($adId, $since, $until, '1'),
            'gender' => fn() => $this->api->getInsightsWithAgeGenderBreakdown($adId, $since, $until, '1'),
            'country' => fn() => $this->api->getInsightsWithCountryBreakdown($adId, $since, $until, '1'),
            'region' => fn() => $this->api->getInsightsWithRegionBreakdown($adId, $since, $until, '1'),
            'publisher_platform' => fn() => $this->api->getInsightsWithPublisherPlatformBreakdown($adId, $since, $until, '1'),
            // Loại bỏ platform_position vì có conflict với action_type breakdown
            // 'platform_position' => fn() => $this->api->getInsightsWithPlatformPositionBreakdown($adId, $since, $until, '1'),
            'device_platform' => fn() => $this->api->getInsightsWithDevicePlatformBreakdown($adId, $since, $until, '1'),
            'impression_device' => fn() => $this->api->getInsightsWithImpressionDeviceBreakdown($adId, $since, $until, '1'),
        ];
        foreach ($callers as $dimension => $fn) {
            $resp = $fn();
            if (!$resp || isset($resp['error']) || !isset($resp['data'])) { continue; }

            // Gom rows theo ngày
            $rowsByDate = [];
            foreach ($resp['data'] as $row) {
                $date = isset($row['date_start']) ? \Illuminate\Support\Carbon::parse($row['date_start'])->toDateString() : null;
                if (!$date || $date < $since || $date > $until) { continue; }
                $rowsByDate[$date][] = $row;
            }

            $breakdownType = match ($dimension) {
                'age', 'gender' => 'age_gender',
                'region' => 'region',
                'platform_position' => 'platform_position',
                'publisher_platform' => 'publisher_platform',
                'device_platform' => 'device_platform',
                'country' => 'country',
                'impression_device' => 'impression_device',
                default => $dimension,
            };

            foreach ($rowsByDate as $date => $rows) {
                $insightRow = \App\Models\FacebookAdInsight::where('ad_id', $adId)
                    ->where('date', $date)
                    ->orderByDesc('id')
                    ->first();
                if (!$insightRow) { continue; }

                $this->saveBreakdownsToTable($rows, $insightRow->id, $breakdownType);
                $result['breakdowns'] += count($rows);
            }
        }
    }

    /**
     * Lưu breakdown data vào bảng riêng
     */
    private function saveBreakdownsToTable(array $breakdownData, int $insightId, string $breakdownType): void
    {
        foreach ($breakdownData as $data) {
            // Xác định breakdown value dựa trên type
            $breakdownValue = $this->extractBreakdownValue($data, $breakdownType);
            
            if ($breakdownValue) {
                // Tổng hợp actions thành map để không bỏ sót chỉ số
                $actionTotals = [];
                if (isset($data['actions']) && is_array($data['actions'])) {
                    foreach ($data['actions'] as $action) {
                        $type = $action['action_type'] ?? null;
                        if (!$type) { continue; }
                        $actionTotals[$type] = (int) ($action['value'] ?? 0);
                    }
                }

                // Helper: trích xuất giá trị từ các field mảng dạng [{action_type,value}]
                $extractActionMetric = function(array $row, string $field, string $targetType = 'video_view'): int {
                    if (!isset($row[$field])) { return 0; }
                    $v = $row[$field];
                    if (is_array($v)) {
                        $sum = 0;
                        foreach ($v as $item) {
                            if (($item['action_type'] ?? null) === $targetType) {
                                $sum += (int) ($item['value'] ?? 0);
                            }
                        }
                        return $sum;
                    }
                    return (int) $v;
                };
                // Chuẩn bị metrics sẽ lưu
                $metrics = [
                    'impressions' => (int) ($data['impressions'] ?? 0),
                    'reach' => (int) ($data['reach'] ?? 0),
                    'clicks' => (int) ($data['clicks'] ?? 0),
                    'spend' => (float) ($data['spend'] ?? 0),
                    'ctr' => (float) ($data['ctr'] ?? 0),
                    'cpc' => (float) ($data['cpc'] ?? 0),
                    'cpm' => (float) ($data['cpm'] ?? 0),
                    'frequency' => (float) ($data['frequency'] ?? 0),
                    // Video metrics (phân biệt rõ ràng)
                    'video_views' => (int) ($actionTotals['video_view'] ?? 0), // Chỉ lấy từ actions
                    'video_plays' => (int) ($extractActionMetric($data, 'video_play_actions', 'video_view') ?? 0), // Chỉ lấy từ video_play_actions
                    'video_plays_at_25_percent' => (int) (($actionTotals['video_p25_watched_actions'] ?? 0) ?: $extractActionMetric($data, 'video_p25_watched_actions')),
                    'video_plays_at_50_percent' => (int) (($actionTotals['video_p50_watched_actions'] ?? 0) ?: $extractActionMetric($data, 'video_p50_watched_actions')),
                    'video_plays_at_75_percent' => (int) (($actionTotals['video_p75_watched_actions'] ?? 0) ?: $extractActionMetric($data, 'video_p75_watched_actions')),
                    'video_plays_at_100_percent' => (int) (($actionTotals['video_p100_watched_actions'] ?? 0) ?: $extractActionMetric($data, 'video_p100_watched_actions')),
                    'video_p25_watched_actions' => (int) (($actionTotals['video_p25_watched_actions'] ?? 0) ?: $extractActionMetric($data, 'video_p25_watched_actions')),
                    'video_p50_watched_actions' => (int) (($actionTotals['video_p50_watched_actions'] ?? 0) ?: $extractActionMetric($data, 'video_p50_watched_actions')),
                    'video_p75_watched_actions' => (int) (($actionTotals['video_p75_watched_actions'] ?? 0) ?: $extractActionMetric($data, 'video_p75_watched_actions')),
                    'video_p95_watched_actions' => (int) (($actionTotals['video_p95_watched_actions'] ?? 0) ?: $extractActionMetric($data, 'video_p95_watched_actions')),
                    'video_p100_watched_actions' => (int) (($actionTotals['video_p100_watched_actions'] ?? 0) ?: $extractActionMetric($data, 'video_p100_watched_actions')),
                    'thruplays' => (int) ($actionTotals['video_thruplay_watched_actions'] ?? $actionTotals['thruplay'] ?? $data['thruplays'] ?? 0),
                    // Engagement & interactions
                    'page_engagement' => (int) ($actionTotals['page_engagement'] ?? 0),
                    'post_engagement' => (int) ($actionTotals['post_engagement'] ?? 0),
                    'post_reaction' => (int) ($actionTotals['post_reaction'] ?? 0),
                    'post_interaction_gross' => (int) ($actionTotals['post_interaction_gross'] ?? 0),
                    'link_click' => (int) ($actionTotals['link_click'] ?? 0),
                    // Messaging
                    'messaging_conversation_started_7d' => (int) (($actionTotals['onsite_conversion.messaging_conversation_started_7d'] ?? 0) + ($actionTotals['omni_messaging_conversation_started_7d'] ?? 0)),
                    'total_messaging_connection' => (int) (($actionTotals['onsite_conversion.total_messaging_connection'] ?? 0) + ($actionTotals['omni_total_messaging_connection'] ?? 0)),
                    'messaging_conversation_replied_7d' => (int) (($actionTotals['onsite_conversion.messaging_conversation_replied_7d'] ?? 0) + ($actionTotals['omni_messaging_conversation_replied_7d'] ?? 0)),
                    'messaging_welcome_message_view' => (int) ($actionTotals['onsite_conversion.messaging_welcome_message_view'] ?? 0),
                ];

                // Loại bỏ các key video không được Facebook trả trong breakdown (tránh lưu số 0 gây hiểu lầm)
                $maybeVideoKeys = [
                    // percentage watched and plays
                    'video_plays_at_25_percent' => ['video_p25_watched_actions'],
                    'video_plays_at_50_percent' => ['video_p50_watched_actions'],
                    'video_plays_at_75_percent' => ['video_p75_watched_actions'],
                    'video_plays_at_100_percent' => ['video_p100_watched_actions'],
                    'video_p25_watched_actions' => ['video_p25_watched_actions'],
                    'video_p50_watched_actions' => ['video_p50_watched_actions'],
                    'video_p75_watched_actions' => ['video_p75_watched_actions'],
                    'video_p95_watched_actions' => ['video_p95_watched_actions'],
                    'video_p100_watched_actions' => ['video_p100_watched_actions'],
                    // time-based
                    'video_avg_time_watched' => ['video_avg_time_watched_actions'],
                    'video_view_time' => ['video_view_time'],
                ];
                foreach ($maybeVideoKeys as $metricKey => $relatedActionTypes) {
                    $hasSource = false;
                    foreach ($relatedActionTypes as $type) {
                        if (isset($actionTotals[$type]) || isset($data[$metricKey]) || isset($data[$type])) {
                            $hasSource = true; break;
                        }
                    }
                    if (!$hasSource && empty($metrics[$metricKey])) {
                        unset($metrics[$metricKey]);
                    }
                }
                // Chỉ giữ video_views nếu có video_view
                if (empty($metrics['video_views']) && !isset($actionTotals['video_view'])) {
                    unset($metrics['video_views']);
                }
                // Chỉ giữ video_plays nếu có video_play hoặc video_view
                if (empty($metrics['video_plays']) && !isset($actionTotals['video_play']) && !isset($actionTotals['video_view'])) {
                    unset($metrics['video_plays']);
                }
                // Bỏ thruplays nếu không có nguồn
                if (empty($metrics['thruplays']) && !isset($actionTotals['video_thruplay_watched_actions']) && !isset($actionTotals['thruplay'])) {
                    unset($metrics['thruplays']);
                }

                // Debug dd removed after verification

                // Lưu vào bảng facebook_breakdowns
                \App\Models\FacebookBreakdown::updateOrCreate(
                    [
                        'ad_insight_id' => $insightId,
                        'breakdown_type' => $breakdownType,
                        'breakdown_value' => $breakdownValue
                    ],
                    [
                        'metrics' => $metrics
                    ]
                );
            }
        }
    }

    /**
     * Extract breakdown value từ data
     */
    private function extractBreakdownValue(array $data, string $breakdownType): ?string
    {
        // Nếu có trường breakdown chính
        if (isset($data[$breakdownType])) {
            $value = $data[$breakdownType];
            
            // Nếu là array, lấy id hoặc name
            if (is_array($value)) {
                return (string)($value['id'] ?? $value['name'] ?? json_encode($value));
            }
            
            // Nếu là string hoặc number
            return (string)$value;
        }
        
        switch ($breakdownType) {
            case 'age_gender':
                $age = $data['age'] ?? '';
                $gender = $data['gender'] ?? '';
                return $age && $gender ? "{$age}_{$gender}" : ($age ?: $gender);
            
            case 'region':
                return $data['region'] ?? null;
            
            case 'platform_position':
                return $data['platform_position'] ?? null;
            
            case 'publisher_platform':
                return $data['publisher_platform'] ?? null;
            
            case 'device_platform':
                return $data['device_platform'] ?? null;
            
            case 'country':
                return $data['country'] ?? null;
            
            case 'impression_device':
                return $data['impression_device'] ?? null;
            
            case 'action_type':
                return $data['action_type'] ?? null;
            
            case 'action_device':
                return $data['action_device'] ?? $data['device'] ?? $data['device_type'] ?? null;
            
            case 'action_destination':
                return $data['action_destination'] ?? $data['destination'] ?? $data['target'] ?? null;
            
            case 'action_target_id':
                return $data['action_target_id'] ?? $data['target_id'] ?? $data['object_id'] ?? null;
            
            case 'action_reaction':
                return $data['action_reaction'] ?? $data['reaction'] ?? $data['reaction_type'] ?? null;
            
            case 'action_video_sound':
                return $data['action_video_sound'] ?? $data['video_sound'] ?? $data['sound'] ?? null;
            
            case 'action_video_type':
                return $data['action_video_type'] ?? $data['video_type'] ?? $data['type'] ?? null;
            
            case 'action_carousel_card_id':
                return $data['action_carousel_card_id'] ?? $data['carousel_card_id'] ?? $data['card_id'] ?? null;
            
            case 'action_carousel_card_name':
                return $data['action_carousel_card_name'] ?? $data['carousel_card_name'] ?? $data['card_name'] ?? null;
            
            case 'action_canvas_component_name':
                return $data['action_canvas_component_name'] ?? $data['canvas_component_name'] ?? $data['component_name'] ?? null;
            
            default:
                // Tìm kiếm các trường có thể liên quan
                foreach ($data as $key => $value) {
                    if (strpos($key, str_replace('action_', '', $breakdownType)) !== false) {
                        return (string)$value;
                    }
                }
                
                // Log để debug
                Log::info("Không tìm thấy breakdown value", [
                    'breakdown_type' => $breakdownType,
                    'available_keys' => array_keys($data),
                    'sample_data' => array_slice($data, 0, 3)
                ]);
                
                return null;
        }
    }

    /**
     * Lưu Creative data vào bảng facebook_creatives
     */
    private function saveCreativeData(array $creativeData, FacebookAd $facebookAd): void
    {
        try {
            // Extract các thông tin từ creative data
            $creativeFields = [
                'id' => $creativeData['id'] ?? null,
                'ad_id' => $facebookAd->id,
                'creative_data' => $creativeData,
                'link_url' => $creativeData['link_data']['link'] ?? null,
                'link_message' => $creativeData['link_data']['message'] ?? null,
                'link_name' => $creativeData['link_data']['name'] ?? null,
                'image_hash' => $creativeData['image_hash'] ?? null,
                'call_to_action_type' => $creativeData['call_to_action_type'] ?? null,
                'page_welcome_message' => $creativeData['page_welcome_message'] ?? null,
                'created_time' => isset($creativeData['created_time']) ? Carbon::parse($creativeData['created_time']) : null,
                'updated_time' => isset($creativeData['updated_time']) ? Carbon::parse($creativeData['updated_time']) : null,
            ];

            // Lưu hoặc cập nhật creative
            FacebookCreative::updateOrCreate(
                ['id' => $creativeFields['id'] ?? $facebookAd->id],
                $creativeFields
            );

            Log::info("Đã lưu creative data", [
                'ad_id' => $facebookAd->id,
                'creative_id' => $creativeFields['id']
            ]);

        } catch (\Exception $e) {
            Log::error("Lỗi khi lưu creative data", [
                'ad_id' => $facebookAd->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Cập nhật engagement data cho post từ Ad Insights
     */
    private function updatePostEngagementFromAdInsights(FacebookAd $facebookAd, array &$result): void
    {
        try {
            $post = FacebookPost::find($facebookAd->post_id);
            if (!$post) {
                Log::warning("Không tìm thấy post với ID: {$facebookAd->post_id}");
                return;
            }
            
            // Lấy engagement data từ Ad Insights API
            $engagementData = $this->api->getAdEngagementData($facebookAd->id);
            
            if (!isset($engagementData['error'])) {
                // Cập nhật post với engagement data
                $post->update([
                    'likes_count' => $engagementData['likes'] ?? 0,
                    'shares_count' => $engagementData['shares'] ?? 0,
                    'comments_count' => $engagementData['comments'] ?? 0,
                    'reactions_count' => $engagementData['reactions'] ?? 0,
                    'engagement_updated_at' => now(),
                ]);
                
                Log::info("Đã cập nhật engagement data cho post từ Ad Insights", [
                    'post_id' => $post->id,
                    'ad_id' => $facebookAd->id,
                    'likes' => $engagementData['likes'] ?? 0,
                    'shares' => $engagementData['shares'] ?? 0,
                    'comments' => $engagementData['comments'] ?? 0,
                    'reactions' => $engagementData['reactions'] ?? 0,
                ]);
            } else {
                Log::warning("Không lấy được engagement data từ Ad Insights API", [
                    'post_id' => $post->id,
                    'ad_id' => $facebookAd->id,
                    'error' => $engagementData['error'] ?? 'Unknown error'
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error("Lỗi khi cập nhật engagement data cho post", [
                'post_id' => $facebookAd->post_id,
                'ad_id' => $facebookAd->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}




