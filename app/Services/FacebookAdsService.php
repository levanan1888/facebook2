<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Spatie\Browsershot\Browsershot;

class FacebookAdsService
{
    private string $accessToken;
    private string $apiVersion;

    public function __construct()
    {
        $this->accessToken = config('services.facebook.ads_token') ?? '';
        // Sử dụng API version mới nhất
        $this->apiVersion = 'v23.0'; // Force sử dụng v23.0
        
        if (empty($this->accessToken)) {
            throw new \Exception('Facebook ads token không được cấu hình. Vui lòng kiểm tra FACEBOOK_ADS_TOKEN trong .env');
        }
    }

    /**
     * Helper method để xử lý HTTP requests với timeout và retry
     */
    private function makeRequest(string $url, array $params, int $maxRetries = 3): array
    {
        $attempt = 0;
        $delays = [180]; // Tăng delays để tránh rate limit

        while ($attempt < $maxRetries) {
            try {
                $response = Http::timeout(60) // Tăng timeout lên 60 giây
                    ->retry(1, 1000) // Retry 1 lần với delay 1 giây
                    ->get($url, $params);

                if ($response->successful()) {
                    return $response->json();
                }

                $responseData = $response->json();
                
                // Kiểm tra rate limit error
                if (isset($responseData['error']['code']) && $responseData['error']['code'] == 17) {
                    Log::warning("Facebook API rate limit reached", [
                        'url' => $url,
                        'attempt' => $attempt + 1,
                        'error' => $responseData['error']
                    ]);
                    
                    // Delay cho rate limit (giảm còn 180 giây)
                    $delay = $delays[min($attempt, count($delays) - 1)];
                    Log::info("Waiting {$delay} seconds before retry due to rate limit");
                    sleep($delay);
                    $attempt++;
                    
                    if ($attempt >= $maxRetries) {
                        return ['error' => $responseData['error']];
                    }
                    continue;
                }

                // Log lỗi khác
                Log::warning("Facebook API request failed", [
                    'url' => $url,
                    'status' => $response->status(),
                    'attempt' => $attempt + 1,
                    'response' => $responseData
                ]);

                return ['error' => $responseData];

            } catch (\Exception $e) {
                $attempt++;
                
                Log::error("Facebook API request exception", [
                    'url' => $url,
                    'attempt' => $attempt,
                    'error' => $e->getMessage()
                ]);

                if ($attempt >= $maxRetries) {
                    return ['error' => ['message' => 'Request timeout after ' . $maxRetries . ' attempts: ' . $e->getMessage()]];
                }

                // Delay trước khi retry
                sleep($delays[min($attempt - 1, count($delays) - 1)]);
            }
        }

        return ['error' => ['message' => 'Request failed after ' . $maxRetries . ' attempts']];
    }

    /**
     * Lấy danh sách Business Managers
     */
    public function getBusinessManagers(): array
    {
        $url = "https://graph.facebook.com/{$this->apiVersion}/me/businesses";
        $params = [
            'access_token' => $this->accessToken,
            'fields' => 'id,name,verification_status,created_time'
        ];

        return $this->makeRequest($url, $params);
    }

    /**
     * Lấy danh sách Ad Accounts được client quản lý
     */
    public function getClientAdAccounts(string $businessId): array
    {
        $url = "https://graph.facebook.com/{$this->apiVersion}/{$businessId}/client_ad_accounts";
        $params = [
            'access_token' => $this->accessToken,
            'fields' => 'id,account_id,name,account_status,created_time,updated_time'
        ];

        return $this->makeRequest($url, $params);
    }

    /**
     * Lấy danh sách Ad Accounts sở hữu
     */
    public function getOwnedAdAccounts(string $businessId): array
    {
        $url = "https://graph.facebook.com/{$this->apiVersion}/{$businessId}/owned_ad_accounts";
        $params = [
            'access_token' => $this->accessToken,
            'fields' => 'id,account_id,name,account_status,created_time,updated_time'
        ];

        return $this->makeRequest($url, $params);
    }

    /**
     * Lấy danh sách Campaigns
     */
    public function getCampaigns(string $accountId): array
    {
        $url = "https://graph.facebook.com/{$this->apiVersion}/{$accountId}/campaigns";
        $params = [
            'access_token' => $this->accessToken,
            'fields' => 'id,name,status,objective,start_time,stop_time,effective_status,configured_status,created_time,updated_time'
        ];

        return $this->makeRequest($url, $params);
    }

    /**
     * Lấy danh sách Ad Sets theo Campaign
     */
    public function getAdSetsByCampaign(string $campaignId): array
    {
        $url = "https://graph.facebook.com/{$this->apiVersion}/{$campaignId}/adsets";
        $params = [
            'access_token' => $this->accessToken,
            'fields' => 'id,name,status,optimization_goal,created_time,updated_time'
        ];

        return $this->makeRequest($url, $params);
    }

    /**
     * Lấy danh sách Ads theo Ad Set
     */
    public function getAdsByAdSet(string $adSetId): array
    {
        $url = "https://graph.facebook.com/{$this->apiVersion}/{$adSetId}/ads";
        $params = [
            'access_token' => $this->accessToken,
            'fields' => 'id,name,status,effective_status,creative{id,title,body,object_story_spec,object_story_id,effective_object_story_id},created_time,updated_time,object_story_id,effective_object_story_id'
        ];

        return $this->makeRequest($url, $params);
    }

    /**
     * Lấy chi tiết Post
     */
    public function getPostDetails(string $postId, ?string $accessToken = null, ?string $overrideFields = null): array
    {
        // Graph API yêu cầu post_id ở dạng {pageId}_{postId}. Nếu thiếu phần pageId, API có thể coi như statuses (deprecated).
        // Hàm này giữ nguyên tham số truyền vào; bên call site sẽ cố gắng chuẩn hoá id trước khi gọi.
        $url = "https://graph.facebook.com/{$this->apiVersion}/{$postId}";
        $params = [
            'access_token' => $accessToken ?: $this->accessToken,
            'fields' => $overrideFields ?: implode(',', [
                'id',
                'message',
                'created_time',
                'permalink_url',
                'from{id,name,picture}',
                'attachments{media_type,media,url,title,description}',
                'shares',
                // tổng quan comment
                'comments.summary(true).limit(0)',
                // tổng quan reaction (chỉ 1 field reactions duy nhất)
                'reactions.summary(true).limit(0)'
            ])
        ];
        

        return $this->makeRequest($url, $params);
    }

    /**
     * Lấy Post Insights mở rộng với dữ liệu video đầy đủ
     * Theo tài liệu Facebook API v23.0, sử dụng endpoint trực tiếp trên post
     */
    public function getPostInsightsExtended(string $postId): array
    {
        // Sử dụng endpoint trực tiếp trên post với format đầy đủ
        $url = "https://graph.facebook.com/{$this->apiVersion}/{$postId}/insights";
        $params = [
            'access_token' => $this->accessToken,
            'fields' => 'post_impressions,post_impressions_unique,post_engaged_users,post_video_views,post_video_avg_time_watched,post_video_complete_views_30s,post_video_views_10s,post_video_views_paid,post_video_views_organic'
        ];

        return $this->makeRequest($url, $params);
    }

    /**
     * Lấy Post Insights với breakdown theo các tiêu chí (tuân thủ quy tắc Facebook API)
     * Sử dụng endpoint insights thay vì statuses (deprecated)
     */
    public function getPostInsightsWithBreakdowns(string $postId, array $breakdowns = ['age', 'gender', 'region', 'platform_position']): array
    {
        $url = "https://graph.facebook.com/{$this->apiVersion}/{$postId}/insights";
        
        // Theo tài liệu Facebook, một số trường không thể dùng với breakdown
        $fields = 'impressions,reach,clicks,unique_clicks,reactions,saves,hides,hide_all_clicks,unlikes,negative_feedback,engagement_rate,ctr,cpm,cpc,spend,frequency,actions,action_values,cost_per_action_type,cost_per_unique_action_type';
        
        // Thêm video fields nếu không có region breakdown (theo tài liệu Meta API)
        if (!in_array('region', $breakdowns)) {
            $fields .= ',post_video_views,post_video_views_unique,post_video_avg_time_watched,post_video_complete_views_30s,post_video_views_10s,post_video_views_paid,post_video_views_organic';
        }
        
        $params = [
            'access_token' => $this->accessToken,
            'fields' => $fields,
            'breakdowns' => implode(',', $breakdowns),
            'time_range' => json_encode([
                'since' => date('Y-m-d', strtotime('-36 months')),
                'until' => date('Y-m-d')
            ])
        ];

        return $this->makeRequest($url, $params);
    }

    /**
     * Lấy engagement data (likes, shares, comments) từ Ad Insights API
     * Sử dụng quyền admin BM thay vì Page API
     */
    public function getAdEngagementData(string $adId): array
    {
        try {
            // 1. Lấy thông tin ad để biết ngày tạo
            $adInfo = $this->getAdInfo($adId);
            
            // Xử lý format ngày từ Facebook API
            $sinceDate = date('Y-m-d', strtotime('-1 year')); // Mặc định 1 năm trước
            
            if (isset($adInfo['created_time'])) {
                $createdTime = $adInfo['created_time'];
                if (is_string($createdTime)) {
                    // Facebook trả về ISO 8601 format, chuyển về Y-m-d
                    $sinceDate = date('Y-m-d', strtotime($createdTime));
                }
            }
            
            $url = "https://graph.facebook.com/{$this->apiVersion}/{$adId}/insights";
            
            $params = [
                'access_token' => $this->accessToken,
                'fields' => 'actions,action_values,date_start,date_stop',
                'action_breakdowns' => 'action_type,action_reaction',
                'time_range' => json_encode([
                    'since' => $sinceDate,
                    'until' => date('Y-m-d')
                ])
            ];

            $result = $this->makeRequest($url, $params);
            
            if (isset($result['error'])) {
                Log::warning("Không thể lấy engagement data từ Ad Insights API", [
                    'ad_id' => $adId,
                    'error' => $result['error']
                ]);
                return [
                    'likes' => 0,
                    'shares' => 0,
                    'comments' => 0,
                    'reactions' => 0,
                    'error' => $result['error']
                ];
            }

            $likes = 0;
            $shares = 0;
            $comments = 0;
            $reactions = 0;

            // Parse engagement data từ actions
            if (isset($result['data'][0]['actions'])) {
                foreach ($result['data'][0]['actions'] as $action) {
                    $actionType = $action['action_type'] ?? '';
                    $value = (int) ($action['value'] ?? 0);

                    switch ($actionType) {
                        case 'like':
                        case 'post_reaction':
                            $likes += $value;
                            $reactions += $value;
                            break;
                        case 'share':
                        case 'post_share':
                            $shares += $value;
                            break;
                        case 'comment':
                        case 'post_comment':
                            $comments += $value;
                            break;
                        case 'reaction':
                            $reactions += $value;
                            break;
                        case 'page_engagement':
                            // page_engagement là tổng engagement, KHÔNG cộng vào reactions
                            // Chỉ dùng để tham khảo, không tính vào metrics cụ thể
                            break;
                        case 'post_engagement':
                            // post_engagement là tổng engagement, KHÔNG cộng vào reactions
                            // Chỉ dùng để tham khảo, không tính vào metrics cụ thể
                            break;
                    }
                }
            }

            // Nếu không có data từ actions, thử lấy từ action_breakdowns
            if ($likes === 0 && $shares === 0 && $comments === 0) {
                $breakdownResult = $this->getAdEngagementWithBreakdowns($adId);
                if (!isset($breakdownResult['error'])) {
                    $likes = $breakdownResult['likes'];
                    $shares = $breakdownResult['shares'];
                    $comments = $breakdownResult['comments'];
                    $reactions = $breakdownResult['reactions'];
                }
            }

            // Thử lấy shares từ Post API nếu có thể
            if ($shares === 0) {
                $shares = $this->getPostSharesFromAd($adId);
            }

            Log::info("Đã lấy engagement data từ Ad Insights API", [
                'ad_id' => $adId,
                'since_date' => $sinceDate,
                'likes' => $likes,
                'shares' => $shares,
                'comments' => $comments,
                'reactions' => $reactions
            ]);

            return [
                'likes' => $likes,
                'shares' => $shares,
                'comments' => $comments,
                'reactions' => $reactions,
                'raw_data' => $result
            ];

        } catch (\Exception $e) {
            Log::error("Lỗi khi lấy engagement data từ Ad Insights API", [
                'ad_id' => $adId,
                'error' => $e->getMessage()
            ]);
            
            return [
                'likes' => 0,
                'shares' => 0,
                'comments' => 0,
                'reactions' => 0,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Lấy thông tin cơ bản của ad để biết ngày tạo
     */
    private function getAdInfo(string $adId): array
    {
        try {
            $url = "https://graph.facebook.com/{$this->apiVersion}/{$adId}";
            $params = [
                'access_token' => $this->accessToken,
                'fields' => 'created_time,updated_time,status'
            ];

            $result = $this->makeRequest($url, $params);
            
            if (isset($result['error'])) {
                return ['created_time' => date('Y-m-d', strtotime('-1 year'))];
            }

            return $result;
        } catch (\Exception $e) {
            return ['created_time' => date('Y-m-d', strtotime('-1 year'))];
        }
    }

    /**
     * Thử lấy shares từ Post API nếu có thể
     */
    private function getPostSharesFromAd(string $adId): int
    {
        try {
            // Lấy creative info để tìm post_id
            $url = "https://graph.facebook.com/{$this->apiVersion}/{$adId}";
            $params = [
                'access_token' => $this->accessToken,
                'fields' => 'creative{object_story_id,effective_object_story_id}'
            ];

            $result = $this->makeRequest($url, $params);
            
            if (isset($result['error']) || !isset($result['creative'])) {
                return 0;
            }

            $creative = $result['creative'];
            $storyId = $creative['object_story_id'] ?? $creative['effective_object_story_id'] ?? null;
            
            if (!$storyId) {
                return 0;
            }

            // Tách post_id từ story_id
            $parts = explode('_', $storyId);
            if (count($parts) < 2) {
                return 0;
            }

            $postId = $parts[1];
            
            // Thử lấy shares từ post (có thể cần page access token)
            $postUrl = "https://graph.facebook.com/{$this->apiVersion}/{$postId}";
            $postParams = [
                'access_token' => $this->accessToken,
                'fields' => 'shares'
            ];

            $postResult = $this->makeRequest($postUrl, $postParams);
            
            if (isset($postResult['error'])) {
                return 0;
            }

            return isset($postResult['shares']['count']) ? (int) $postResult['shares']['count'] : 0;

        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Lấy engagement data với breakdowns chi tiết
     */
    public function getAdEngagementWithBreakdowns(string $adId): array
    {
        try {
            $url = "https://graph.facebook.com/{$this->apiVersion}/{$adId}/insights";
            
            $params = [
                'access_token' => $this->accessToken,
                'fields' => 'actions,action_values',
                'action_breakdowns' => 'action_type,action_reaction,action_device',
                'breakdowns' => 'publisher_platform,platform_position',
                'time_range' => json_encode([
                    'since' => date('Y-m-d', strtotime('-1 year')),
                    'until' => date('Y-m-d')
                ])
            ];

            $result = $this->makeRequest($url, $params);
            
            if (isset($result['error'])) {
                return ['error' => $result['error']];
            }

            $likes = 0;
            $shares = 0;
            $comments = 0;
            $reactions = 0;

            // Parse từ actions breakdowns
            if (isset($result['data'])) {
                foreach ($result['data'] as $insight) {
                    if (isset($insight['actions'])) {
                        foreach ($insight['actions'] as $action) {
                            $actionType = $action['action_type'] ?? '';
                            $value = (int) ($action['value'] ?? 0);

                            switch ($actionType) {
                                case 'like':
                                case 'post_reaction':
                                    $likes += $value;
                                    $reactions += $value;
                                    break;
                                case 'share':
                                case 'post_share':
                                    $shares += $value;
                                    break;
                                case 'comment':
                                case 'post_comment':
                                    $comments += $value;
                                    break;
                                case 'reaction':
                                    $reactions += $value;
                                    break;
                            }
                        }
                    }
                }
            }

            return [
                'likes' => $likes,
                'shares' => $shares,
                'comments' => $comments,
                'reactions' => $reactions,
                'breakdowns' => $result['data'] ?? []
            ];

        } catch (\Exception $e) {
            Log::error("Lỗi khi lấy engagement data với breakdowns", [
                'ad_id' => $adId,
                'error' => $e->getMessage()
            ]);
            
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Lấy engagement data cho nhiều ads cùng lúc
     */
    public function getBatchAdEngagementData(array $adIds): array
    {
        $results = [];
        $chunks = array_chunk($adIds, 5); // Process 5 ads at a time
        
        foreach ($chunks as $chunk) {
            $promises = [];
            
            foreach ($chunk as $adId) {
                $url = "https://graph.facebook.com/{$this->apiVersion}/{$adId}/insights";
                $params = [
                    'access_token' => $this->accessToken,
                    'fields' => 'actions,action_values',
                    'action_breakdowns' => 'action_type',
                    'time_range' => json_encode([
                        'since' => date('Y-m-d', strtotime('-1 year')),
                        'until' => date('Y-m-d')
                    ])
                ];
                
                $promises[$adId] = Http::async()->get($url, $params);
            }
            
            // Wait for all requests to complete
            foreach ($promises as $adId => $promise) {
                try {
                    $response = $promise->wait();
                    
                    if ($response->successful()) {
                        $data = $response->json();
                        $results[$adId] = $this->parseEngagementFromActions($data);
                    } else {
                        $results[$adId] = [
                            'likes' => 0,
                            'shares' => 0,
                            'comments' => 0,
                            'reactions' => 0,
                            'error' => $response->json()
                        ];
                    }
                } catch (\Exception $e) {
                    $results[$adId] = [
                        'likes' => 0,
                        'shares' => 0,
                        'comments' => 0,
                        'reactions' => 0,
                        'error' => $e->getMessage()
                    ];
                }
            }
            
            // Rate limiting
            usleep(500000); // 0.5 seconds
        }
        
        return $results;
    }

    /**
     * Parse engagement data từ actions array
     */
    private function parseEngagementFromActions(array $data): array
    {
        $likes = 0;
        $shares = 0;
        $comments = 0;
        $reactions = 0;

        if (isset($data['data'][0]['actions'])) {
            foreach ($data['data'][0]['actions'] as $action) {
                $actionType = $action['action_type'] ?? '';
                $value = (int) ($action['value'] ?? 0);

                switch ($actionType) {
                    case 'like':
                    case 'post_reaction':
                        $likes += $value;
                        $reactions += $value;
                        break;
                    case 'share':
                    case 'post_share':
                        $shares += $value;
                        break;
                    case 'comment':
                    case 'post_comment':
                        $comments += $value;
                        break;
                    case 'reaction':
                        $reactions += $value;
                        break;
                }
            }
        }

        return [
            'likes' => $likes,
            'shares' => $shares,
            'comments' => $comments,
            'reactions' => $reactions
        ];
    }

    /**
     * Lấy Insights cho nhiều Ads cùng lúc
     * Sử dụng endpoint insights thay vì statuses (deprecated)
     */
    public function getInsightsForAdsBatch(array $adIds, int $concurrency = 5): array
    {
        $results = [];
        $chunks = array_chunk($adIds, $concurrency);
        
        foreach ($chunks as $chunk) {
            $promises = [];
            
            foreach ($chunk as $adId) {
                $url = "https://graph.facebook.com/{$this->apiVersion}/{$adId}/insights";
                $params = [
                    'access_token' => $this->accessToken,
                    'fields' => 'spend,reach,impressions,clicks,ctr,cpc,cpm,frequency,unique_clicks,actions,action_values,conversions,conversion_values,purchase_roas,outbound_clicks,unique_outbound_clicks,video_avg_time_watched_actions,video_p25_watched_actions,video_p50_watched_actions,video_p75_watched_actions,video_p100_watched_actions',
                    'time_range' => json_encode([
                        'since' => date('Y-m-d', strtotime('-36 months')),
                        'until' => date('Y-m-d')
                    ])
                ];
                
                $promises[$adId] = Http::async()->get($url, $params);
            }
            
            // Wait for all requests to complete
            foreach ($promises as $adId => $promise) {
                $response = $promise->wait();
                
                if ($response->successful()) {
                    $results[$adId] = $response->json();
                } else {
                    $results[$adId] = ['error' => $response->json()];
                }
            }
            
            // Rate limiting - pause between chunks
            if (count($chunks) > 1) {
                usleep(1000000); // 1 second
            }
        }
        
        return $results;
    }

    /**
     * Lấy Insights cho một Ad đơn lẻ với dữ liệu đầy đủ theo Facebook Marketing API v23.0
     * Sử dụng endpoint insights với tất cả fields hợp lệ
     */
    public function getInsightsForAd(string $adId, ?string $since = null, ?string $until = null, string $timeIncrement = '1'): array
    {
        $url = "https://graph.facebook.com/{$this->apiVersion}/{$adId}/insights";
        
        // Sử dụng tất cả fields hợp lệ theo Facebook Marketing API v23.0
        $fields = [
            // Basic metrics
            'spend', 'reach', 'impressions', 'clicks', 'ctr', 'cpc', 'cpm', 'frequency',
            'unique_clicks', 'unique_ctr', 'ad_name', 'ad_id',
            
            // Actions và action values (chứa video metrics)
            'actions', 'action_values',
            
            // Cost metrics
            'cost_per_action_type', 'cost_per_unique_action_type',
            
            // Conversion metrics
            'conversions', 'conversion_values', 'cost_per_conversion', 'purchase_roas',
            
            // Click metrics
            'outbound_clicks', 'unique_outbound_clicks', 'inline_link_clicks', 'unique_inline_link_clicks',
            
            // Video metrics (available in actions)
            'video_30_sec_watched_actions', 'video_avg_time_watched_actions',
            'video_p25_watched_actions', 'video_p50_watched_actions', 
            'video_p75_watched_actions', 'video_p95_watched_actions', 'video_p100_watched_actions',
            // average watch time is provided via actions below
            
            // Date fields
            'date_start', 'date_stop'
        ];
        
        $params = [
            'access_token' => $this->accessToken,
            'fields' => implode(',', $fields),
            'time_range' => json_encode([
                'since' => $since ?: now()->format('Y-m-d'),
                'until' => $until ?: date('Y-m-d')
            ]),
            // Quan trọng: yêu cầu dữ liệu theo ngày để lưu xuống DB (giống Ads Manager)
            'time_increment' => $timeIncrement,
            // Align with Ads Manager defaults unless overridden
            'action_report_time' => 'impression',
            'action_attribution_windows' => json_encode(['7d_click','1d_view']),
            // Thêm các tham số để đảm bảo lấy đủ dữ liệu
            'level' => 'ad',
            'use_unified_attribution_setting' => 'true'
        ];

        $result = $this->makeRequest($url, $params);
        
        Log::info("Ad Insights response", [
            'ad_id' => $adId,
            'fields' => implode(',', $fields),
            'has_data' => isset($result['data']) && !empty($result['data']),
            'data_count' => isset($result['data']) ? count($result['data']) : 0,
            'sample_actions' => isset($result['data'][0]['actions']) ? $result['data'][0]['actions'] : null
        ]);

        return $result;
    }

    /**
     * Lấy Insights cho Ad với breakdown theo các tiêu chí (tuân thủ quy tắc Facebook API)
     * Sử dụng endpoint insights với các fields phù hợp cho từng breakdown
     */
    public function getInsightsForAdWithBreakdowns(string $adId, array $breakdowns = ['age', 'gender']): array
    {
        $url = "https://graph.facebook.com/{$this->apiVersion}/{$adId}/insights";
        
        // Fields cơ bản hợp lệ với tất cả breakdowns
        $baseFields = [
            'spend', 'reach', 'impressions', 'clicks', 'ctr', 'cpc', 'cpm', 'frequency',
            'unique_clicks', 'unique_ctr', 'conversions', 'conversion_values', 'cost_per_conversion', 'purchase_roas',
            'outbound_clicks', 'unique_outbound_clicks', 'inline_link_clicks', 'unique_inline_link_clicks',
            'cost_per_action_type', 'cost_per_unique_action_type',
            'actions', 'action_values', 'ad_name', 'ad_id'
        ];
        
        // Video metrics fields chỉ hợp lệ với một số breakdowns
        $videoFields = [
            'video_30_sec_watched_actions', 'video_avg_time_watched_actions',
            'video_p25_watched_actions', 'video_p50_watched_actions', 
            'video_p75_watched_actions', 'video_p95_watched_actions', 'video_p100_watched_actions'
        ];
        
        // Kiểm tra breakdown có hỗ trợ video metrics không
        $restrictedBreakdowns = ['region', 'dma', 'hourly_stats_aggregated_by_advertiser_time_zone', 'hourly_stats_aggregated_by_audience_time_zone'];
        $supportsVideo = !array_intersect($breakdowns, $restrictedBreakdowns);
        
        $fields = $baseFields;
        if ($supportsVideo) {
            $fields = array_merge($fields, $videoFields);
        }
        
        $params = [
            'access_token' => $this->accessToken,
            'fields' => implode(',', $fields),
            'breakdowns' => implode(',', $breakdowns),
            'time_range' => json_encode([
                'since' => date('Y-m-d', strtotime('-36 months')),
                'until' => date('Y-m-d')
            ])
        ];

        $result = $this->makeRequest($url, $params);
        
        Log::info("Ad Insights with breakdowns", [
            'ad_id' => $adId,
            'breakdowns' => $breakdowns,
            'supports_video' => $supportsVideo,
            'has_data' => isset($result['data']) && !empty($result['data'])
        ]);

        return $result;
    }

    /**
     * Lấy Insights với breakdown theo age và gender (permutation được hỗ trợ)
     * Sử dụng endpoint insights thay vì statuses (deprecated)
     */
    public function getInsightsWithAgeGenderBreakdown(string $objectId, ?string $since = null, ?string $until = null, string $timeIncrement = '1', string $objectType = 'ad'): array
    {
        $url = "https://graph.facebook.com/{$this->apiVersion}/{$objectId}/insights";
        
        $fields = 'impressions,reach,clicks,ctr,cpc,cpm,spend,frequency,actions,action_values,'
                . 'video_30_sec_watched_actions,video_avg_time_watched_actions,'
                . 'video_p25_watched_actions,video_p50_watched_actions,video_p75_watched_actions,video_p95_watched_actions,video_p100_watched_actions,video_play_actions';
        
        $params = [
            'access_token' => $this->accessToken,
            'fields' => $fields,
            'breakdowns' => 'age,gender',
            'time_range' => json_encode([
                'since' => $since ?: date('Y-m-d', strtotime('-7 days')),
                'until' => $until ?: date('Y-m-d')
            ]),
            'time_increment' => $timeIncrement,
        ];

        return $this->makeRequest($url, $params);
    }

    /**
     * Lấy Insights với breakdown theo region (permutation được hỗ trợ)
     * Sử dụng endpoint insights thay vì statuses (deprecated)
     */
    public function getInsightsWithRegionBreakdown(string $objectId, ?string $since = null, ?string $until = null, string $timeIncrement = '1', string $objectType = 'ad'): array
    {
        $url = "https://graph.facebook.com/{$this->apiVersion}/{$objectId}/insights";
        
        // Region breakdown không hỗ trợ video metrics
        $fields = 'impressions,reach,clicks,ctr,cpc,cpm,spend,frequency,actions,action_values';
        
        $params = [
            'access_token' => $this->accessToken,
            'fields' => $fields,
            'breakdowns' => 'region',
            'time_range' => json_encode([
                'since' => $since ?: date('Y-m-d', strtotime('-7 days')),
                'until' => $until ?: date('Y-m-d')
            ]),
            'time_increment' => $timeIncrement,
        ];

        return $this->makeRequest($url, $params);
    }

    /**
     * Lấy Insights với breakdown theo platform_position (permutation được hỗ trợ)
     * Sử dụng endpoint insights thay vì statuses (deprecated)
     */
    public function getInsightsWithPlatformPositionBreakdown(string $objectId, ?string $since = null, ?string $until = null, string $timeIncrement = '1', string $objectType = 'ad'): array
    {
        $url = "https://graph.facebook.com/{$this->apiVersion}/{$objectId}/insights";
        
        $fields = 'impressions,reach,clicks,ctr,cpc,cpm,spend,frequency,actions,action_values,'
                . 'video_30_sec_watched_actions,video_avg_time_watched_actions,'
                . 'video_p25_watched_actions,video_p50_watched_actions,video_p75_watched_actions,video_p95_watched_actions,video_p100_watched_actions,video_play_actions';
        
        $params = [
            'access_token' => $this->accessToken,
            'fields' => $fields,
            'breakdowns' => 'platform_position',
            'time_range' => json_encode([
                'since' => $since ?: date('Y-m-d', strtotime('-7 days')),
                'until' => $until ?: date('Y-m-d')
            ]),
            'time_increment' => $timeIncrement,
        ];

        return $this->makeRequest($url, $params);
    }

    /**
     * Lấy Insights với breakdown theo publisher_platform (permutation được hỗ trợ)
     */
    public function getInsightsWithPublisherPlatformBreakdown(string $objectId, ?string $since = null, ?string $until = null, string $timeIncrement = '1', string $objectType = 'ad'): array
    {
        $url = "https://graph.facebook.com/{$this->apiVersion}/{$objectId}/insights";
        
        $fields = 'impressions,reach,clicks,ctr,cpc,cpm,spend,frequency,actions,action_values,'
                . 'video_30_sec_watched_actions,video_avg_time_watched_actions,'
                . 'video_p25_watched_actions,video_p50_watched_actions,video_p75_watched_actions,video_p95_watched_actions,video_p100_watched_actions,video_play_actions';
        
        $params = [
            'access_token' => $this->accessToken,
            'fields' => $fields,
            'breakdowns' => 'publisher_platform',
            'time_range' => json_encode([
                'since' => $since ?: date('Y-m-d', strtotime('-7 days')),
                'until' => $until ?: date('Y-m-d')
            ]),
            'time_increment' => $timeIncrement,
        ];

        return $this->makeRequest($url, $params);
    }

    /**
     * Lấy Insights với breakdown theo device_platform (permutation được hỗ trợ)
     */
    public function getInsightsWithDevicePlatformBreakdown(string $objectId, ?string $since = null, ?string $until = null, string $timeIncrement = '1', string $objectType = 'ad'): array
    {
        $url = "https://graph.facebook.com/{$this->apiVersion}/{$objectId}/insights";
        
        $fields = 'impressions,reach,clicks,ctr,cpc,cpm,spend,frequency,actions,action_values,'
                . 'video_30_sec_watched_actions,video_avg_time_watched_actions,'
                . 'video_p25_watched_actions,video_p50_watched_actions,video_p75_watched_actions,video_p95_watched_actions,video_p100_watched_actions,video_play_actions';
        
        $params = [
            'access_token' => $this->accessToken,
            'fields' => $fields,
            'breakdowns' => 'device_platform',
            'time_range' => json_encode([
                'since' => $since ?: date('Y-m-d', strtotime('-7 days')),
                'until' => $until ?: date('Y-m-d')
            ]),
            'time_increment' => $timeIncrement,
        ];

        return $this->makeRequest($url, $params);
    }

    /**
     * Lấy Insights với breakdown theo country (permutation được hỗ trợ)
     */
    public function getInsightsWithCountryBreakdown(string $objectId, ?string $since = null, ?string $until = null, string $timeIncrement = '1', string $objectType = 'ad'): array
    {
        $url = "https://graph.facebook.com/{$this->apiVersion}/{$objectId}/insights";
        
        $fields = 'impressions,reach,clicks,ctr,cpc,cpm,spend,frequency,actions,action_values,'
                . 'video_30_sec_watched_actions,video_avg_time_watched_actions,'
                . 'video_p25_watched_actions,video_p50_watched_actions,video_p75_watched_actions,video_p95_watched_actions,video_p100_watched_actions,video_play_actions';
        
        $params = [
            'access_token' => $this->accessToken,
            'fields' => $fields,
            'breakdowns' => 'country',
            'time_range' => json_encode([
                'since' => $since ?: date('Y-m-d', strtotime('-7 days')),
                'until' => $until ?: date('Y-m-d')
            ]),
            'time_increment' => $timeIncrement,
        ];

        return $this->makeRequest($url, $params);
    }

    /**
     * Lấy Insights với breakdown theo impression_device (permutation được hỗ trợ)
     */
    public function getInsightsWithImpressionDeviceBreakdown(string $objectId, ?string $since = null, ?string $until = null, string $timeIncrement = '1', string $objectType = 'ad'): array
    {
        $url = "https://graph.facebook.com/{$this->apiVersion}/{$objectId}/insights";
        
        $fields = 'impressions,reach,clicks,ctr,cpc,cpm,spend,frequency,actions,action_values';
        
        $params = [
            'access_token' => $this->accessToken,
            'fields' => $fields,
            'breakdowns' => 'impression_device',
            'time_range' => json_encode([
                'since' => $since ?: date('Y-m-d', strtotime('-7 days')),
                'until' => $until ?: date('Y-m-d')
            ]),
            'time_increment' => $timeIncrement,
        ];

        return $this->makeRequest($url, $params);
    }

    /**
     * Lấy Insights với breakdown theo action_type (permutation được hỗ trợ)
     */
    public function getInsightsWithActionTypeBreakdown(string $objectId, string $objectType = 'ad'): array
    {
        $url = "https://graph.facebook.com/{$this->apiVersion}/{$objectId}/insights";
        
        $fields = 'impressions,reach,clicks,ctr,cpc,cpm,spend,frequency,actions,action_values';
        
        $params = [
            'access_token' => $this->accessToken,
            'fields' => $fields,
            'action_breakdowns' => 'action_type',
            'time_range' => json_encode([
                'since' => date('Y-m-d', strtotime('-36 months')),
                'until' => date('Y-m-d')
            ])
        ];

        return $this->makeRequest($url, $params);
    }

    /**
     * Lấy Insights với breakdown theo action_device (permutation được hỗ trợ)
     */
    public function getInsightsWithActionDeviceBreakdown(string $objectId, string $objectType = 'ad'): array
    {
        $url = "https://graph.facebook.com/{$this->apiVersion}/{$objectId}/insights";
        
        $fields = 'impressions,reach,clicks,ctr,cpc,cpm,spend,frequency,actions,action_values';
        
        $params = [
            'access_token' => $this->accessToken,
            'fields' => $fields,
            'action_breakdowns' => 'action_device',
            'time_range' => json_encode([
                'since' => date('Y-m-d', strtotime('-36 months')),
                'until' => date('Y-m-d')
            ])
        ];

        return $this->makeRequest($url, $params);
    }

    /**
     * Lấy Insights với breakdown theo action_destination (permutation được hỗ trợ)
     */
    public function getInsightsWithActionDestinationBreakdown(string $objectId, string $objectType = 'ad'): array
    {
        $url = "https://graph.facebook.com/{$this->apiVersion}/{$objectId}/insights";
        
        $fields = 'impressions,reach,clicks,ctr,cpc,cpm,spend,frequency,actions,action_values';
        
        $params = [
            'access_token' => $this->accessToken,
            'fields' => $fields,
            'action_breakdowns' => 'action_destination',
            'time_range' => json_encode([
                'since' => date('Y-m-d', strtotime('-36 months')),
                'until' => date('Y-m-d')
            ])
        ];

        return $this->makeRequest($url, $params);
    }

    /**
     * Lấy Insights với breakdown theo action_target_id (permutation được hỗ trợ)
     */
    public function getInsightsWithActionTargetIdBreakdown(string $objectId, string $objectType = 'ad'): array
    {
        $url = "https://graph.facebook.com/{$this->apiVersion}/{$objectId}/insights";
        
        $fields = 'impressions,reach,clicks,ctr,cpc,cpm,spend,frequency,actions,action_values';
        
        $params = [
            'access_token' => $this->accessToken,
            'fields' => $fields,
            'action_breakdowns' => 'action_target_id',
            'time_range' => json_encode([
                'since' => date('Y-m-d', strtotime('-36 months')),
                'until' => date('Y-m-d')
            ])
        ];

        return $this->makeRequest($url, $params);
    }

    /**
     * Lấy Insights với breakdown theo action_reaction (permutation được hỗ trợ)
     */
    public function getInsightsWithActionReactionBreakdown(string $objectId, string $objectType = 'ad'): array
    {
        $url = "https://graph.facebook.com/{$this->apiVersion}/{$objectId}/insights";
        
        $fields = 'impressions,reach,clicks,ctr,cpc,cpm,spend,frequency,actions,action_values';
        
        $params = [
            'access_token' => $this->accessToken,
            'fields' => $fields,
            'action_breakdowns' => 'action_reaction',
            'time_range' => json_encode([
                'since' => date('Y-m-d', strtotime('-36 months')),
                'until' => date('Y-m-d')
            ])
        ];

        return $this->makeRequest($url, $params);
    }

    /**
     * Lấy Insights với breakdown theo action_video_sound (permutation được hỗ trợ)
     */
    public function getInsightsWithActionVideoSoundBreakdown(string $objectId, string $objectType = 'ad'): array
    {
        $url = "https://graph.facebook.com/{$this->apiVersion}/{$objectId}/insights";
        
        $fields = 'impressions,reach,clicks,ctr,cpc,cpm,spend,frequency,actions,action_values';
        
        $params = [
            'access_token' => $this->accessToken,
            'fields' => $fields,
            'action_breakdowns' => 'action_video_sound',
            'time_range' => json_encode([
                'since' => date('Y-m-d', strtotime('-36 months')),
                'until' => date('Y-m-d')
            ])
        ];

        return $this->makeRequest($url, $params);
    }

    /**
     * Lấy Insights với breakdown theo action_video_type (permutation được hỗ trợ)
     */
    public function getInsightsWithActionVideoTypeBreakdown(string $objectId, string $objectType = 'ad'): array
    {
        $url = "https://graph.facebook.com/{$this->apiVersion}/{$objectId}/insights";
        
        $fields = 'impressions,reach,clicks,ctr,cpc,cpm,spend,frequency,actions,action_values';
        
        $params = [
            'access_token' => $this->accessToken,
            'fields' => $fields,
            'action_breakdowns' => 'action_video_type',
            'time_range' => json_encode([
                'since' => date('Y-m-d', strtotime('-36 months')),
                'until' => date('Y-m-d')
            ])
        ];

        return $this->makeRequest($url, $params);
    }

    /**
     * Lấy Insights với breakdown theo action_carousel_card_id (permutation được hỗ trợ)
     */
    public function getInsightsWithActionCarouselCardIdBreakdown(string $objectId, string $objectType = 'ad'): array
    {
        $url = "https://graph.facebook.com/{$this->apiVersion}/{$objectId}/insights";
        
        $fields = 'impressions,reach,clicks,ctr,cpc,cpm,spend,frequency,actions,action_values';
        
        $params = [
            'access_token' => $this->accessToken,
            'fields' => $fields,
            'action_breakdowns' => 'action_carousel_card_id',
            'time_range' => json_encode([
                'since' => date('Y-m-d', strtotime('-36 months')),
                'until' => date('Y-m-d')
            ])
        ];

        return $this->makeRequest($url, $params);
    }

    /**
     * Lấy Insights với breakdown theo action_carousel_card_name (permutation được hỗ trợ)
     */
    public function getInsightsWithActionCarouselCardNameBreakdown(string $objectId, string $objectType = 'ad'): array
    {
        $url = "https://graph.facebook.com/{$this->apiVersion}/{$objectId}/insights";
        
        $fields = 'impressions,reach,clicks,ctr,cpc,cpm,spend,frequency,actions,action_values';
        
        $params = [
            'access_token' => $this->accessToken,
            'fields' => $fields,
            'action_breakdowns' => 'action_carousel_card_name',
            'time_range' => json_encode([
                'since' => date('Y-m-d', strtotime('-36 months')),
                'until' => date('Y-m-d')
            ])
        ];

        return $this->makeRequest($url, $params);
    }

    /**
     * Lấy Insights với breakdown theo action_canvas_component_name (permutation được hỗ trợ)
     */
    public function getInsightsWithActionCanvasComponentNameBreakdown(string $objectId, string $objectType = 'ad'): array
    {
        $url = "https://graph.facebook.com/{$this->apiVersion}/{$objectId}/insights";
        
        $fields = 'impressions,reach,clicks,ctr,cpc,cpm,spend,frequency,actions,action_values';
        
        $params = [
            'access_token' => $this->accessToken,
            'fields' => $fields,
            'action_breakdowns' => 'action_canvas_component_name',
            'time_range' => json_encode([
                'since' => date('Y-m-d', strtotime('-36 months')),
                'until' => date('Y-m-d')
            ])
        ];

        return $this->makeRequest($url, $params);
    }

    /**
     * Lấy Insights với action breakdown (theo tài liệu Facebook)
     * Sử dụng endpoint insights thay vì statuses (deprecated)
     */
    public function getInsightsWithActionBreakdown(string $objectId, string $objectType = 'ad', array $actionBreakdowns = ['action_type', 'action_device']): array
    {
        $url = "https://graph.facebook.com/{$this->apiVersion}/{$objectId}/insights";
        
        $fields = 'impressions,reach,clicks,ctr,cpc,cpm,spend,frequency,actions,action_values';
        
        $params = [
            'access_token' => $this->accessToken,
            'fields' => $fields,
            'action_breakdowns' => implode(',', $actionBreakdowns),
            'time_range' => json_encode([
                'since' => date('Y-m-d', strtotime('-36 months')),
                'until' => date('Y-m-d')
            ])
        ];

        return $this->makeRequest($url, $params);
    }

    /**
     * Lấy chi tiết Ad
     */
    public function getAdDetails(string $adId): array
    {
        $url = "https://graph.facebook.com/{$this->apiVersion}/{$adId}";
        $params = [
            'access_token' => $this->accessToken,
            'fields' => 'id,name,status,effective_status,creative{id,name,object_type,object_story_spec,thumbnail_url,asset_feed_spec,object_story_id,effective_object_story_id},created_time,updated_time'
        ];

        return $this->makeRequest($url, $params);
    }

    /**
     * Lấy Page Insights (thay thế cho statuses API deprecated)
     */
    public function getPageInsights(string $pageId): array
    {
        $url = "https://graph.facebook.com/{$this->apiVersion}/{$pageId}/insights";
        $params = [
            'access_token' => $this->accessToken,
            'fields' => 'page_impressions,page_impressions_unique,page_engaged_users,page_consumptions,page_consumptions_unique,page_negative_feedback,page_negative_feedback_unique,page_positive_feedback_by_type,page_fans,page_fan_adds,page_fan_removes',
            'time_range' => json_encode([
                'since' => date('Y-m-d', strtotime('-30 days')),
                'until' => date('Y-m-d')
            ])
        ];

        return $this->makeRequest($url, $params);
    }

    /**
     * Lấy Campaign Insights (thay thế cho statuses API deprecated)
     */
    public function getCampaignInsights(string $campaignId): array
    {
        $url = "https://graph.facebook.com/{$this->apiVersion}/{$campaignId}/insights";
        $params = [
            'access_token' => $this->accessToken,
            'fields' => 'spend,reach,impressions,clicks,ctr,cpc,cpm,frequency,unique_clicks,actions,action_values,conversions,conversion_values,outbound_clicks,unique_outbound_clicks,cost_per_action_type,cost_per_unique_action_type,video_avg_time_watched_actions,video_p25_watched_actions,video_p50_watched_actions,video_p75_watched_actions,video_p100_watched_actions',
            'time_range' => json_encode([
                'since' => date('Y-m-d', strtotime('-36 months')),
                'until' => date('Y-m-d')
            ])
        ];

        return $this->makeRequest($url, $params);
    }

    /**
     * Lấy Ad Set Insights (thay thế cho statuses API deprecated)
     */
    public function getAdSetInsights(string $adSetId): array
    {
        $url = "https://graph.facebook.com/{$this->apiVersion}/{$adSetId}/insights";
        $params = [
            'access_token' => $this->accessToken,
            'fields' => 'spend,reach,impressions,clicks,ctr,cpc,cpm,frequency,unique_clicks,actions,action_values,conversions,conversion_values,outbound_clicks,unique_outbound_clicks,cost_per_action_type,cost_per_unique_action_type,video_avg_time_watched_actions,video_p25_watched_actions,video_p50_watched_actions,video_p75_watched_actions,video_p100_watched_actions',
            'time_range' => json_encode([
                'since' => date('Y-m-d', strtotime('-36 months')),
                'until' => date('Y-m-d')
            ])
        ];

        return $this->makeRequest($url, $params);
    }

    /**
     * Lấy Insights với breakdown theo DMA (Designated Market Area - chỉ cho US)
     */
    public function getInsightsWithDMABreakdown(string $objectId): array
    {
        $url = "https://graph.facebook.com/{$this->apiVersion}/{$objectId}/insights";
        
        $fields = 'impressions,reach,clicks,ctr,cpc,cpm,spend,frequency,actions,action_values';
        // DMA không hỗ trợ video metrics
        
        $params = [
            'access_token' => $this->accessToken,
            'fields' => $fields,
            'breakdowns' => 'dma',
            'time_range' => json_encode([
                'since' => date('Y-m-d', strtotime('-36 months')),
                'until' => date('Y-m-d')
            ])
        ];

        return $this->makeRequest($url, $params);
    }

    /**
     * Lấy Insights với breakdown theo hourly stats (theo múi giờ của advertiser)
     */
    public function getInsightsWithHourlyStatsAdvertiserBreakdown(string $objectId): array
    {
        $url = "https://graph.facebook.com/{$this->apiVersion}/{$objectId}/insights";
        
        $fields = 'impressions,clicks,ctr,cpc,cpm,spend,actions,action_values';
        // Hourly breakdowns không hỗ trợ reach, frequency, unique fields
        
        $params = [
            'access_token' => $this->accessToken,
            'fields' => $fields,
            'breakdowns' => 'hourly_stats_aggregated_by_advertiser_time_zone',
            'time_range' => json_encode([
                'since' => date('Y-m-d', strtotime('-36 months')),
                'until' => date('Y-m-d')
            ])
        ];

        return $this->makeRequest($url, $params);
    }

    /**
     * Lấy Insights với breakdown theo hourly stats (theo múi giờ của audience)
     */
    public function getInsightsWithHourlyStatsAudienceBreakdown(string $objectId): array
    {
        $url = "https://graph.facebook.com/{$this->apiVersion}/{$objectId}/insights";
        
        $fields = 'impressions,clicks,ctr,cpc,cpm,spend,actions,action_values';
        // Hourly breakdowns không hỗ trợ reach, frequency, unique fields
        
        $params = [
            'access_token' => $this->accessToken,
            'fields' => $fields,
            'breakdowns' => 'hourly_stats_aggregated_by_audience_time_zone',
            'time_range' => json_encode([
                'since' => date('Y-m-d', strtotime('-36 months')),
                'until' => date('Y-m-d')
            ])
        ];

        return $this->makeRequest($url, $params);
    }

    /**
     * Lấy Insights với breakdown theo product_id (cho Dynamic Ads)
     */
    public function getInsightsWithProductIdBreakdown(string $objectId): array
    {
        $url = "https://graph.facebook.com/{$this->apiVersion}/{$objectId}/insights";
        
        $fields = 'impressions,reach,clicks,ctr,cpc,cpm,spend,frequency,actions,action_values';
        
        $params = [
            'access_token' => $this->accessToken,
            'fields' => $fields,
            'breakdowns' => 'product_id',
            'time_range' => json_encode([
                'since' => date('Y-m-d', strtotime('-36 months')),
                'until' => date('Y-m-d')
            ])
        ];

        return $this->makeRequest($url, $params);
    }

    /**
     * Lấy Insights với breakdown theo frequency_value (cho Reach and Frequency campaigns)
     */
    public function getInsightsWithFrequencyValueBreakdown(string $objectId): array
    {
        $url = "https://graph.facebook.com/{$this->apiVersion}/{$objectId}/insights";
        
        $fields = 'reach,frequency';
        // frequency_value chỉ hỗ trợ với reach
        
        $params = [
            'access_token' => $this->accessToken,
            'fields' => $fields,
            'breakdowns' => 'frequency_value',
            'time_range' => json_encode([
                'since' => date('Y-m-d', strtotime('-36 months')),
                'until' => date('Y-m-d')
            ])
        ];

        return $this->makeRequest($url, $params);
    }

    /**
     * Lấy Insights với breakdown theo user_segment_key (cho Advantage+ Shopping Campaigns)
     */
    public function getInsightsWithUserSegmentBreakdown(string $objectId): array
    {
        $url = "https://graph.facebook.com/{$this->apiVersion}/{$objectId}/insights";
        
        $fields = 'impressions,reach,clicks,ctr,cpc,cpm,spend,frequency,actions,action_values';
        
        $params = [
            'access_token' => $this->accessToken,
            'fields' => $fields,
            'breakdowns' => 'user_segment_key',
            'time_range' => json_encode([
                'since' => date('Y-m-d', strtotime('-36 months')),
                'until' => date('Y-m-d')
            ])
        ];

        return $this->makeRequest($url, $params);
    }

    /**
     * Lấy Insights với breakdown theo place_page_id (cho Place ads)
     */
    public function getInsightsWithPlacePageBreakdown(string $objectId): array
    {
        $url = "https://graph.facebook.com/{$this->apiVersion}/{$objectId}/insights";
        
        $fields = 'impressions,clicks,ctr,cpc,cpm,spend,actions,action_values';
        // place_page_id không hỗ trợ reach, frequency
        
        $params = [
            'access_token' => $this->accessToken,
            'fields' => $fields,
            'breakdowns' => 'place_page_id',
            'time_range' => json_encode([
                'since' => date('Y-m-d', strtotime('-36 months')),
                'until' => date('Y-m-d')
            ])
        ];

        return $this->makeRequest($url, $params);
    }

    /**
     * Lấy Insights với breakdown theo app_id (cho app installs)
     */
    public function getInsightsWithAppIdBreakdown(string $objectId): array
    {
        $url = "https://graph.facebook.com/{$this->apiVersion}/{$objectId}/insights";
        
        $fields = 'total_postbacks';
        // app_id chỉ hỗ trợ total_postbacks field
        
        $params = [
            'access_token' => $this->accessToken,
            'fields' => $fields,
            'breakdowns' => 'app_id',
            'time_range' => json_encode([
                'since' => date('Y-m-d', strtotime('-36 months')),
                'until' => date('Y-m-d')
            ])
        ];

        return $this->makeRequest($url, $params);
    }

    /**
     * Lấy Insights với breakdown theo skan_conversion_id (cho iOS 15+ SKAdNetwork)
     */
    public function getInsightsWithSkanConversionIdBreakdown(string $objectId): array
    {
        $url = "https://graph.facebook.com/{$this->apiVersion}/{$objectId}/insights";
        
        $fields = 'total_postbacks';
        // skan_conversion_id chỉ hỗ trợ total_postbacks field
        
        $params = [
            'access_token' => $this->accessToken,
            'fields' => $fields,
            'breakdowns' => 'skan_conversion_id',
            'time_range' => json_encode([
                'since' => date('Y-m-d', strtotime('-36 months')),
                'until' => date('Y-m-d')
            ])
        ];

        return $this->makeRequest($url, $params);
    }

    /**
     * Lấy Insights với breakdown theo skan_campaign_id (cho iOS 15+ SKAdNetwork)
     */
    public function getInsightsWithSkanCampaignIdBreakdown(string $objectId): array
    {
        $url = "https://graph.facebook.com/{$this->apiVersion}/{$objectId}/insights";
        
        $fields = 'total_postbacks_detailed';
        // skan_campaign_id chỉ hỗ trợ total_postbacks_detailed field
        
        $params = [
            'access_token' => $this->accessToken,
            'fields' => $fields,
            'breakdowns' => 'skan_campaign_id',
            'time_range' => json_encode([
                'since' => date('Y-m-d', strtotime('-36 months')),
                'until' => date('Y-m-d')
            ])
        ];

        return $this->makeRequest($url, $params);
    }

    /**
     * Lấy Insights với breakdown theo is_conversion_id_modeled (cho modeled conversions)
     */
    public function getInsightsWithConversionIdModeledBreakdown(string $objectId): array
    {
        $url = "https://graph.facebook.com/{$this->apiVersion}/{$objectId}/insights";
        
        $fields = 'total_postbacks_detailed';
        // is_conversion_id_modeled chỉ hỗ trợ total_postbacks_detailed field
        
        $params = [
            'access_token' => $this->accessToken,
            'fields' => $fields,
            'breakdowns' => 'is_conversion_id_modeled',
            'time_range' => json_encode([
                'since' => date('Y-m-d', strtotime('-36 months')),
                'until' => date('Y-m-d')
            ])
        ];

        return $this->makeRequest($url, $params);
    }

    /**
     * Lấy Insights với breakdown theo Dynamic Creative assets
     */
    public function getInsightsWithDynamicCreativeBreakdown(string $objectId, string $assetType): array
    {
        $url = "https://graph.facebook.com/{$this->apiVersion}/{$objectId}/insights";
        
        $fields = 'impressions,clicks,spend,reach,actions,action_values';
        // Dynamic Creative breakdowns chỉ hỗ trợ limited metrics
        
        $validAssetTypes = [
            'ad_format_asset', 'body_asset', 'call_to_action_asset', 
            'description_asset', 'image_asset', 'link_url_asset', 
            'title_asset', 'video_asset'
        ];
        
        if (!in_array($assetType, $validAssetTypes)) {
            throw new \InvalidArgumentException("Invalid asset type. Must be one of: " . implode(', ', $validAssetTypes));
        }
        
        $params = [
            'access_token' => $this->accessToken,
            'fields' => $fields,
            'breakdowns' => $assetType,
            'time_range' => json_encode([
                'since' => date('Y-m-d', strtotime('-36 months')),
                'until' => date('Y-m-d')
            ])
        ];

        return $this->makeRequest($url, $params);
    }

    /**
     * Lấy Ad Sets cho một Campaign
     */
    public function getAdSets(string $campaignId): array
    {
        $url = "https://graph.facebook.com/{$this->apiVersion}/{$campaignId}/adsets";
        $params = [
            'access_token' => $this->accessToken,
            'fields' => 'id,name,status,created_time,updated_time'
        ];

        return $this->makeRequest($url, $params);
    }

    /**
     * Lấy Ads cho một Ad Set
     */
    public function getAds(string $adSetId): array
    {
        $url = "https://graph.facebook.com/{$this->apiVersion}/{$adSetId}/ads";
        $params = [
            'access_token' => $this->accessToken,
            'fields' => 'id,name,status,effective_status,creative,created_time,updated_time'
        ];

        return $this->makeRequest($url, $params);
    }

    /**
     * Lấy tất cả insights đầy đủ cho một ad
     */
    public function getCompleteAdInsights(string $adId): array
    {
        $results = [];
        
        // 1. Lấy basic insights với video metrics
        $results['basic_insights'] = $this->getInsightsWithActionBreakdowns($adId, ['action_type']);
        
        // 2. Lấy insights với các breakdowns chính (tránh combinations không hợp lệ)
        $mainBreakdowns = [
            'demographics' => ['age', 'gender'],
            'geographic' => ['country', 'region'],
            'platform' => ['publisher_platform', 'device_platform', 'impression_device']
            // Loại bỏ platform_position vì có conflict với action_type
        ];
        
        foreach ($mainBreakdowns as $category => $breakdowns) {
            foreach ($breakdowns as $breakdown) {
                try {
                    $results["breakdown_{$breakdown}"] = $this->getInsightsForAdWithBreakdowns($adId, [$breakdown]);
                } catch (\Exception $e) {
                    Log::warning("Không thể lấy breakdown {$breakdown}", [
                        'ad_id' => $adId,
                        'error' => $e->getMessage()
                    ]);
                    $results["breakdown_{$breakdown}"] = ['error' => $e->getMessage()];
                }
            }
        }
        
        // 3. Lấy action breakdowns chi tiết (mỗi cái riêng biệt để tránh conflict)
        $actionBreakdowns = [
            'action_device',
            'action_destination', 
            'action_target_id',
            'action_reaction',
            'action_video_sound', // Video sound breakdown
            'action_video_type',  // Video type breakdown
            'action_carousel_card_id',
            'action_carousel_card_name',
            'action_canvas_component_name'
        ];
        
        foreach ($actionBreakdowns as $breakdown) {
            try {
                $results["action_breakdown_{$breakdown}"] = $this->getInsightsWithActionBreakdowns($adId, [$breakdown]);
            } catch (\Exception $e) {
                Log::warning("Không thể lấy action breakdown {$breakdown}", [
                    'ad_id' => $adId,
                    'error' => $e->getMessage()
                ]);
                $results["action_breakdown_{$breakdown}"] = ['error' => $e->getMessage()];
            }
        }
        
        // 4. Lấy asset breakdowns (video_asset, image_asset, etc.)
        $assetBreakdowns = [
            'video_asset',      // Video asset breakdown
            'image_asset',
            'body_asset',
            'title_asset',
            'description_asset',
            'call_to_action_asset',
            'link_url_asset',
            'ad_format_asset'
        ];
        
        foreach ($assetBreakdowns as $breakdown) {
            try {
                $results["asset_breakdown_{$breakdown}"] = $this->getInsightsForAdWithBreakdowns($adId, [$breakdown]);
            } catch (\Exception $e) {
                Log::warning("Không thể lấy asset breakdown {$breakdown}", [
                    'ad_id' => $adId,
                    'error' => $e->getMessage()
                ]);
                $results["asset_breakdown_{$breakdown}"] = ['error' => $e->getMessage()];
            }
        }
        
        // 5. Lấy engagement data với breakdowns chi tiết
        $results['engagement_breakdowns'] = $this->getAdEngagementWithBreakdowns($adId);
        
        return $results;
    }
    
    /**
     * Lấy Insights với action breakdowns
     */
    public function getInsightsWithActionBreakdowns(string $adId, array $actionBreakdowns = ['action_type']): array
    {
        $url = "https://graph.facebook.com/{$this->apiVersion}/{$adId}/insights";
        
        $fields = [
            'spend', 'reach', 'impressions', 'clicks', 'ctr', 'cpc', 'cpm', 'frequency',
            'unique_clicks', 'unique_ctr', 'actions', 'action_values', 'ad_name', 'ad_id',
            
            // Conversion metrics
            'conversions', 'conversion_values', 'cost_per_conversion', 'purchase_roas',
            
            // Click metrics
            'outbound_clicks', 'unique_outbound_clicks', 'inline_link_clicks', 'unique_inline_link_clicks',
            
            // Cost metrics
            'cost_per_action_type', 'cost_per_unique_action_type',
            
            // Chỉ sử dụng các video metrics fields hợp lệ theo Facebook API documentation
            'video_30_sec_watched_actions', 'video_avg_time_watched_actions',
            'video_p25_watched_actions', 'video_p50_watched_actions', 
            'video_p75_watched_actions', 'video_p95_watched_actions', 'video_p100_watched_actions',
            'video_play_actions', // Field mới phát hiện từ test
            
            // Date fields
            'date_start', 'date_stop'
        ];
        
        $params = [
            'access_token' => $this->accessToken,
            'fields' => implode(',', $fields),
            'action_breakdowns' => implode(',', $actionBreakdowns),
            'time_range' => json_encode([
                'since' => date('Y-m-d', strtotime('-1 year')),
                'until' => date('Y-m-d')
            ])
        ];

        $result = $this->makeRequest($url, $params);
        
        Log::info("Ad Insights with action breakdowns", [
            'ad_id' => $adId,
            'action_breakdowns' => $actionBreakdowns,
            'has_data' => isset($result['data']) && !empty($result['data'])
        ]);

        return $result;
    }

    /**
     * Lấy access token
     */
    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    /**
     * Lấy tất cả breakdowns có sẵn cho một object
     * Dựa trên Facebook Marketing API documentation
     */
    public function getAllAvailableBreakdowns(): array
    {
        return [
            'demographics' => [
                'age' => 'Độ tuổi',
                'gender' => 'Giới tính',
                'age,gender' => 'Độ tuổi và giới tính'
            ],
            'geographic' => [
                'country' => 'Quốc gia',
                'region' => 'Khu vực',
                'dma' => 'Designated Market Area (chỉ US)'
            ],
            'platform' => [
                'publisher_platform' => 'Nền tảng (Facebook, Instagram, Audience Network)',
                'platform_position' => 'Vị trí trên nền tảng',
                'device_platform' => 'Loại thiết bị (mobile, desktop)',
                'impression_device' => 'Thiết bị cụ thể (iPhone, Android, Desktop)'
            ],
            'time' => [
                'hourly_stats_aggregated_by_advertiser_time_zone' => 'Theo giờ (múi giờ advertiser)',
                'hourly_stats_aggregated_by_audience_time_zone' => 'Theo giờ (múi giờ audience)'
            ],
            'actions' => [
                'action_type' => 'Loại hành động',
                'action_device' => 'Thiết bị thực hiện hành động',
                'action_destination' => 'Đích đến của hành động',
                'action_target_id' => 'ID đích đến',
                'action_reaction' => 'Loại reaction (Like, Love, Haha, etc.)',
                'action_video_sound' => 'Trạng thái âm thanh video (on/off)',
                'action_video_type' => 'Loại video',
                'action_carousel_card_id' => 'ID card carousel',
                'action_carousel_card_name' => 'Tên card carousel',
                'action_canvas_component_name' => 'Tên component Canvas',
                // Mới thêm theo docs
                'action_converted_product_id' => 'ID sản phẩm được convert (Collaborative Ads)',
                'matched_persona_id' => 'ID persona được match',
                'matched_persona_name' => 'Tên persona được match',
                'signal_source_bucket' => 'Nguồn signal',
                'standard_event_content_type' => 'Loại nội dung event chuẩn',
                'conversion_destination' => 'Đích đến conversion'
            ],
            'campaign_specific' => [
                'frequency_value' => 'Giá trị frequency (Reach & Frequency campaigns)',
                'user_segment_key' => 'Phân khúc người dùng (Advantage+ Shopping)',
                'product_id' => 'ID sản phẩm (Dynamic Ads)',
                'place_page_id' => 'ID trang địa điểm (Place ads)'
            ],
            'app_tracking' => [
                'app_id' => 'ID ứng dụng',
                'skan_conversion_id' => 'SKAdNetwork Conversion ID',
                'skan_campaign_id' => 'SKAdNetwork Campaign ID',
                'is_conversion_id_modeled' => 'Modeled conversion ID'
            ],
            'dynamic_creative' => [
                'ad_format_asset' => 'Ad format asset',
                'body_asset' => 'Body asset',
                'call_to_action_asset' => 'Call to action asset',
                'description_asset' => 'Description asset',
                'image_asset' => 'Image asset',
                'link_url_asset' => 'Link URL asset',
                'title_asset' => 'Title asset',
                'video_asset' => 'Video asset'
            ]
        ];
    }

    /**
     * Kiểm tra breakdowns có hợp lệ không
     * Dựa trên Facebook API limitations
     */
    public function validateBreakdowns(array $breakdowns): array
    {
        $errors = [];
        $warnings = [];

        // Kiểm tra breakdowns không hỗ trợ
        $unsupportedBreakdowns = [
            'app_store_clicks',
            'newsfeed_avg_position', 
            'newsfeed_clicks',
            'relevance_score',
            'newsfeed_impressions'
        ];

        foreach ($breakdowns as $breakdown) {
            if (in_array($breakdown, $unsupportedBreakdowns)) {
                $errors[] = "Breakdown '{$breakdown}' không được hỗ trợ";
            }
        }

        // Kiểm tra video metrics limitations
        $videoRestrictedBreakdowns = [
            'region', 
            'dma', 
            'hourly_stats_aggregated_by_advertiser_time_zone', 
            'hourly_stats_aggregated_by_audience_time_zone'
        ];

        $hasVideoRestriction = array_intersect($breakdowns, $videoRestrictedBreakdowns);
        if (!empty($hasVideoRestriction)) {
            $warnings[] = "Video metrics không được hỗ trợ với breakdowns: " . implode(', ', $hasVideoRestriction);
        }

        // Kiểm tra video_avg_time_watched_actions limitation
        if (in_array('region', $breakdowns)) {
            $warnings[] = "video_avg_time_watched_actions không hỗ trợ với region breakdown";
        }

        // Kiểm tra hourly breakdowns limitations
        $hourlyBreakdowns = [
            'hourly_stats_aggregated_by_advertiser_time_zone',
            'hourly_stats_aggregated_by_audience_time_zone'
        ];

        $hasHourlyBreakdown = array_intersect($breakdowns, $hourlyBreakdowns);
        if (!empty($hasHourlyBreakdown)) {
            $warnings[] = "Hourly breakdowns không hỗ trợ reach, frequency, unique fields";
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings
        ];
    }

    /**
     * Lấy các breakdown combinations được hỗ trợ
     * Dựa trên Facebook API documentation
     */
    public function getSupportedBreakdownCombinations(): array
    {
        return [
            'single' => [
                'action_type',
                'action_target_id', 
                'action_device',
                'action_reaction',
                'age',
                'gender',
                'country',
                'region',
                'publisher_platform',
                'product_id',
                'hourly_stats_aggregated_by_advertiser_time_zone',
                'hourly_stats_aggregated_by_audience_time_zone',
                'action_carousel_card_id',
                'action_carousel_card_name'
            ],
            'combinations' => [
                'age,gender',
                'action_device,impression_device',
                'action_device,publisher_platform',
                'action_device,publisher_platform,impression_device',
                'action_device,publisher_platform,platform_position',
                'action_device,publisher_platform,platform_position,impression_device',
                'action_type,action_reaction',
                'action_carousel_card_id,impression_device',
                'action_carousel_card_id,country',
                'action_carousel_card_id,age',
                'action_carousel_card_id,gender',
                'action_carousel_card_id,age,gender',
                'publisher_platform,impression_device',
                'publisher_platform,platform_position',
                'publisher_platform,platform_position,impression_device'
            ],
            'action_converted_product_id' => [
                'action_type,action_converted_product_id' // Chỉ cho Collaborative Ads
            ]
        ];
    }

    /**
     * Lấy số reactions, comments, shares của một post.
     * Cần Page Access Token hoặc quyền phù hợp (pages_read_engagement hoặc Page Public Content Access).
     * Sử dụng endpoint mới thay vì statuses (deprecated)
     * 
     * @deprecated Sử dụng getAdEngagementData() thay thế vì method này yêu cầu quyền admin page
     */
    public function getPostEngagementCounts(string $postId): array
    {
        // Ưu tiên token có quyền Page (pages_read_engagement / Page Public Content Access)
        $pageToken = config('services.facebook.page_token') ?: $this->accessToken;

        // Gọi 1 lần lấy đủ shares, comments.summary, reactions.summary
        $url = "https://graph.facebook.com/{$this->apiVersion}/{$postId}";
        $resp = Http::timeout(60)
            ->retry(1, 1000)
            ->get($url, [
                'access_token' => $pageToken,
                'fields' => 'shares,comments.limit(0).summary(true),reactions.limit(0).summary(true)'
            ]);

        if (!$resp->successful()) {
            Log::warning('Post API request failed', [
                'post_id' => $postId,
                'status' => $resp->status(),
                'response' => $resp->json(),
            ]);
            return [
                'reactions' => 0,
                'comments' => 0,
                'shares' => 0,
                'error' => $resp->json(),
            ];
        }

        $data = $resp->json();

        $reactions = (int) ($data['reactions']['summary']['total_count'] ?? 0);
        $comments = (int) ($data['comments']['summary']['total_count'] ?? 0);
        $shares = (int) ($data['shares']['count'] ?? 0);

        return [
            'reactions' => $reactions,
            'comments' => $comments,
            'shares' => $shares,
            'raw' => $data,
        ];
    }

    /**
     * Crawl số liệu engagement trực tiếp từ giao diện (UI) công khai của Facebook.
     * Ưu tiên dùng mbasic.facebook.com (ít JS), fallback sang m.facebook.com nếu cần.
     * Lưu ý: chỉ hoạt động với bài viết công khai; kết quả phụ thuộc ngôn ngữ hiển thị.
     */
    public function getPostEngagementCountsViaUI(string $postUrl): array
    {
        try {
            $normalized = $this->normalizeFacebookPostUrl($postUrl);
            $variants = [
                $normalized['mbasic'],
                $normalized['mbasic'] . (str_contains($normalized['mbasic'], '?') ? '&' : '?') . '_rdr',
                $normalized['mobile'],
                $normalized['www'],
            ];

            $html = '';
            $fetchedFrom = '';
            $httpStatus = 0;
            foreach ($variants as $v) {
                $meta = $this->fetchHtmlMeta($v);
                $html = $meta['body'] ?? '';
                $httpStatus = (int)($meta['status'] ?? 0);
                $fetchedFrom = $meta['url'] ?? $v;
                if ($html !== '') { break; }
            }

            if ($html === '') {
                return ['reactions' => 0, 'comments' => 0, 'shares' => 0, 'error' => 'empty_html'];
            }

            // Parse Vietnamese và một số mẫu chung
            $likes = $this->matchFirstInt($html, '/>([0-9][0-9\.,]*)\s*(?:lượt thích|thích)\b/i');
            if ($likes === 0) {
                // Một số layout hiển thị số ngay sau icon
                $likes = $this->matchFirstInt($html, '/aria-label="[^\"]*Thích[^\"]*"[^>]*>.*?<span[^>]*>\s*([0-9][0-9\.,]*)\s*<\/span>/is');
            }
            // Comments
            $comments = $this->matchFirstInt($html, '/([0-9][0-9\.,]*)\s*bình luận\b/i');
            // Shares
            $shares = $this->matchFirstInt($html, '/([0-9][0-9\.,]*)\s*lượt chia sẻ\b/i');

            // Fallback các ngôn ngữ khác (English)
            if ($comments === 0) {
                $comments = $this->matchFirstInt($html, '/([0-9][0-9\.,]*)\s*comments\b/i');
            }
            if ($shares === 0) {
                $shares = $this->matchFirstInt($html, '/([0-9][0-9\.,]*)\s*shares\b/i');
            }
            if ($likes === 0) {
                $likes = $this->matchFirstInt($html, '/([0-9][0-9\.,]*)\s*likes\b/i');
            }

            return [
                'reactions' => $likes,
                'comments' => $comments,
                'shares' => $shares,
                'raw_html_len' => strlen($html),
                'source_url' => $fetchedFrom,
                'http_status' => $httpStatus,
            ];
        } catch (\Throwable $e) {
            Log::warning('UI crawl failed', [
                'url' => $postUrl,
                'error' => $e->getMessage()
            ]);
            return ['reactions' => 0, 'comments' => 0, 'shares' => 0, 'error' => $e->getMessage()];
        }
    }

    private function normalizeFacebookPostUrl(string $url): array
    {
        $url = trim($url);
        // Thay host sang mbasic/m/www và giữ nguyên path+query
        $parsed = parse_url($url);
        $path = ($parsed['path'] ?? '/') . (isset($parsed['query']) ? ('?' . $parsed['query']) : '');
        $mbasic = 'https://mbasic.facebook.com' . $path;
        $mobile = 'https://m.facebook.com' . $path;
        $www = 'https://www.facebook.com' . $path;
        return ['mbasic' => $mbasic, 'mobile' => $mobile, 'www' => $www];
    }

    private function fetchHtmlMeta(string $url): array
    {
        try {
            $cookie = (string) (config('services.facebook.crawl_cookie') ?? 'locale=vi_VN; m_pixel_ratio=2.0; wd=1366x768');
            $resp = Http::withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
                    'Accept-Language' => 'vi-VN,vi;q=0.9,en-US;q=0.8,en;q=0.7',
                    'Referer' => 'https://mbasic.facebook.com/',
                    'Cache-Control' => 'no-cache',
                    'Pragma' => 'no-cache',
                    'Cookie' => $cookie,
                ])
                ->withOptions(['allow_redirects' => true])
                ->timeout(30)
                ->retry(1, 1000)
                ->get($url);
            return [
                // Trả cả body cả khi không 2xx để có nội dung debug (ví dụ trang chặn/redirect)
                'body' => (string) $resp->body(),
                'status' => $resp->status(),
                'url' => $url,
            ];
        } catch (\Throwable $e) {
            return ['body' => '', 'status' => 0, 'url' => $url];
        }
    }

    private function matchFirstInt(string $html, string $regex): int
    {
        if (preg_match($regex, $html, $m)) {
            $num = str_replace(['.', ','], '', $m[1]);
            return (int) $num;
        }
        return 0;
    }

    /**
     * Lấy engagement bằng headless Chrome (Browsershot) – ổn định hơn khi bị chặn.
     */
    public function getPostEngagementCountsViaHeadless(string $postUrl): array
    {
        try {
            $cookieStr = (string) (config('services.facebook.crawl_cookie') ?? '');
            $cookies = $this->parseCookieString($cookieStr);
            // Bổ sung mặc định
            $this->ensureCookie($cookies, 'locale', 'vi_VN');
            $this->ensureCookie($cookies, 'wd', '1366x768');

            $bs = Browsershot::url($postUrl)
                ->userAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126 Safari/537.36')
                ->setCookies($cookies)
                ->timeout(60)
                ->setChromeFlags(['--disable-gpu','--no-sandbox','--lang=vi-VN','--disable-dev-shm-usage'])
                ->waitUntilNetworkIdle()
                ->setDelay(500);

            $chromePath = (string) (config('services.facebook.chrome_path') ?? '');
            if ($chromePath !== '') {
                $bs->setChromePath($chromePath);
            }

            $html = $bs->bodyHtml();

            if ($html === null || $html === '') {
                return ['reactions' => 0, 'comments' => 0, 'shares' => 0, 'error' => 'empty_html'];
            }

            $likes = $this->matchFirstInt($html, '/>([0-9][0-9\.,]*)\s*(?:lượt thích|thích)\b/i');
            if ($likes === 0) { $likes = $this->matchFirstInt($html, '/([0-9][0-9\.,]*)\s*likes\b/i'); }
            $comments = $this->matchFirstInt($html, '/([0-9][0-9\.,]*)\s*(?:bình luận|comments)\b/i');
            $shares = $this->matchFirstInt($html, '/([0-9][0-9\.,]*)\s*(?:lượt chia sẻ|shares)\b/i');

            return [
                'reactions' => $likes,
                'comments' => $comments,
                'shares' => $shares,
                'raw_html_len' => strlen($html),
                'source_url' => $postUrl,
                'http_status' => 200,
            ];

        } catch (\Throwable $e) {
            Log::warning('Headless crawl failed', ['url' => $postUrl, 'error' => $e->getMessage()]);
            return ['reactions' => 0, 'comments' => 0, 'shares' => 0, 'error' => $e->getMessage()];
        }
    }

    private function parseCookieString(string $cookieStr): array
    {
        if ($cookieStr === '') { return []; }
        $parts = array_filter(array_map('trim', explode(';', $cookieStr)));
        $cookies = [];
        foreach ($parts as $p) {
            $kv = explode('=', $p, 2);
            if (count($kv) === 2) {
                $cookies[] = [
                    'name' => trim($kv[0]),
                    'value' => trim($kv[1]),
                    'domain' => '.facebook.com'
                ];
            }
        }
        return $cookies;
    }

    private function ensureCookie(array &$cookies, string $name, string $value): void
    {
        foreach ($cookies as $c) { if (($c['name'] ?? '') === $name) { return; } }
        $cookies[] = ['name' => $name, 'value' => $value, 'domain' => '.facebook.com'];
    }

    /**
     * Lấy thông tin page từ Facebook API
     */
    public function getPageInfo(string $pageId): array
    {
        try {
            $url = "https://graph.facebook.com/{$this->apiVersion}/{$pageId}";
            $params = [
                'access_token' => $this->accessToken,
                'fields' => 'id,name,category,fan_count,verification_status,created_time,about,description,phone,website,emails,location'
            ];

            $response = $this->makeRequest($url, $params);

            if (isset($response['error'])) {
                Log::warning('Facebook API error when getting page info', [
                    'page_id' => $pageId,
                    'error' => $response['error']
                ]);
                return $response;
            }

            return $response;

        } catch (\Exception $e) {
            Log::error('Error getting page info from Facebook API', [
                'page_id' => $pageId,
                'error' => $e->getMessage()
            ]);
            return ['error' => ['message' => $e->getMessage()]];
        }
    }

    /**
     * Lấy tất cả insights với breakdowns cho một ad
     * Bao gồm video metrics, action breakdowns, và các breakdowns khác
     */
    public function getAllInsightsWithBreakdowns(string $adId): array
    {
        $results = [];
        
        // 1. Lấy basic insights với video metrics
        $results['basic_insights'] = $this->getInsightsWithActionBreakdowns($adId, ['action_type']);
        
        // 2. Lấy insights với các breakdowns chính (tránh combinations không hợp lệ)
        $mainBreakdowns = [
            'demographics' => ['age', 'gender'],
            'geographic' => ['country', 'region'],
            'platform' => ['publisher_platform', 'device_platform', 'impression_device']
            // Loại bỏ platform_position vì có conflict với action_type
        ];
        
        foreach ($mainBreakdowns as $category => $breakdowns) {
            foreach ($breakdowns as $breakdown) {
                try {
                    // Validate breakdown trước khi gọi API
                    $validation = $this->validateBreakdowns([$breakdown]);
                    if (!$validation['valid']) {
                        Log::warning("Breakdown {$breakdown} không hợp lệ", $validation['errors']);
                        continue;
                    }
                    
                    $results["breakdown_{$breakdown}"] = $this->getInsightsForAdWithBreakdowns($adId, [$breakdown]);
                } catch (\Exception $e) {
                    Log::warning("Không thể lấy breakdown {$breakdown}", [
                        'ad_id' => $adId,
                        'error' => $e->getMessage()
                    ]);
                    $results["breakdown_{$breakdown}"] = ['error' => $e->getMessage()];
                }
            }
        }
        
        // 3. Lấy action breakdowns chi tiết (mỗi cái riêng biệt để tránh conflict)
        $actionBreakdowns = [
            'action_device',
            'action_destination', 
            'action_target_id',
            'action_reaction',
            'action_video_sound', // Video sound breakdown
            'action_video_type',  // Video type breakdown
            'action_carousel_card_id',
            'action_carousel_card_name',
            'action_canvas_component_name',
            // Mới thêm theo docs
            'matched_persona_id',
            'matched_persona_name',
            'signal_source_bucket',
            'standard_event_content_type',
            'conversion_destination'
        ];
        
        foreach ($actionBreakdowns as $breakdown) {
            try {
                $results["action_breakdown_{$breakdown}"] = $this->getInsightsWithActionBreakdowns($adId, [$breakdown]);
            } catch (\Exception $e) {
                Log::warning("Không thể lấy action breakdown {$breakdown}", [
                    'ad_id' => $adId,
                    'error' => $e->getMessage()
                ]);
                $results["action_breakdown_{$breakdown}"] = ['error' => $e->getMessage()];
            }
        }
        
        // 4. Lấy asset breakdowns (video_asset, image_asset, etc.)
        $assetBreakdowns = [
            'video_asset',      // Video asset breakdown
            'image_asset',
            'body_asset',
            'title_asset',
            'description_asset',
            'call_to_action_asset',
            'link_url_asset',
            'ad_format_asset'
        ];
        
        foreach ($assetBreakdowns as $breakdown) {
            try {
                $results["asset_breakdown_{$breakdown}"] = $this->getInsightsForAdWithBreakdowns($adId, [$breakdown]);
            } catch (\Exception $e) {
                Log::warning("Không thể lấy asset breakdown {$breakdown}", [
                    'ad_id' => $adId,
                    'error' => $e->getMessage()
                ]);
                $results["asset_breakdown_{$breakdown}"] = ['error' => $e->getMessage()];
            }
        }
        
        // 5. Lấy campaign specific breakdowns
        $campaignBreakdowns = [
            'frequency_value',
            'user_segment_key',
            'product_id',
            'place_page_id'
        ];
        
        foreach ($campaignBreakdowns as $breakdown) {
            try {
                $results["campaign_breakdown_{$breakdown}"] = $this->getInsightsForAdWithBreakdowns($adId, [$breakdown]);
            } catch (\Exception $e) {
                Log::warning("Không thể lấy campaign breakdown {$breakdown}", [
                    'ad_id' => $adId,
                    'error' => $e->getMessage()
                ]);
                $results["campaign_breakdown_{$breakdown}"] = ['error' => $e->getMessage()];
            }
        }
        
        // 6. Lấy app tracking breakdowns
        $appTrackingBreakdowns = [
            'app_id',
            'skan_conversion_id',
            'skan_campaign_id',
            'is_conversion_id_modeled'
        ];
        
        foreach ($appTrackingBreakdowns as $breakdown) {
            try {
                $results["app_breakdown_{$breakdown}"] = $this->getInsightsForAdWithBreakdowns($adId, [$breakdown]);
            } catch (\Exception $e) {
                Log::warning("Không thể lấy app tracking breakdown {$breakdown}", [
                    'ad_id' => $adId,
                    'error' => $e->getMessage()
                ]);
                $results["app_breakdown_{$breakdown}"] = ['error' => $e->getMessage()];
            }
        }
        
        // 7. Lấy engagement data với breakdowns chi tiết
        $results['engagement_breakdowns'] = $this->getAdEngagementWithBreakdowns($adId);
        
        return $results;
    }
}
