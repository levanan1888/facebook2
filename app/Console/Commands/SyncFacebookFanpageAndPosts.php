<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SyncFacebookFanpageAndPosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'facebook:sync-fanpage-posts 
                            {--access-token= : Facebook access token}
                            {--user-id= : Facebook user ID}
                            {--since= : Start date (Y-m-d format)}
                            {--until= : End date (Y-m-d format)}
                            {--days=7 : Number of days to sync posts (if since/until not provided)}
                            {--limit=100 : Number of posts per page}
                            {--delay=1 : Delay between requests in seconds}
                            {--pages-only : Only sync pages, skip posts}
                            {--posts-only : Only sync posts, skip pages}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Facebook fanpages and their posts with insights';

    /**
     * Facebook Graph API base URL
     */
    private $graphApiUrl = 'https://graph.facebook.com/v23.0';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $accessToken = config('services.facebook.ads_token');
        $userId = $this->option('user-id') ?? '4267361423539842'; // Sử dụng user ID cố định
        $since = $this->option('since');
        $until = $this->option('until');
        $days = $this->option('days');
        $limit = $this->option('limit');
        $delay = $this->option('delay');
        $pagesOnly = $this->option('pages-only');
        $postsOnly = $this->option('posts-only');

        if (!$accessToken) {
            $this->error('Access token is required');
            $this->info('Usage: php artisan facebook:sync-fanpage-posts --access-token="YOUR_TOKEN"');
            $this->info('Or set FACEBOOK_ADS_TOKEN in .env file');
            return 1;
        }

        $this->info("👤 Using fixed User ID: {$userId}");

        // Xử lý date range như ads insights
        if ($since && $until) {
            $sinceDate = $since;
            $untilDate = $until;
            $this->info("📅 Date range: {$sinceDate} to {$untilDate}");
        } else {
            $sinceDate = Carbon::now()->subDays($days)->format('Y-m-d');
            $untilDate = Carbon::now()->format('Y-m-d');
            $this->info("📅 Days to sync: {$days} (from {$sinceDate} to {$untilDate})");
        }

        $this->info("🚀 Starting Facebook fanpage and posts sync...");
        $this->info("📱 Facebook Graph API Version: v23.0");
        $this->info("🔑 Access Token: " . substr($accessToken, 0, 20) . "...");
        $this->info("👤 User ID: {$userId}");
        $this->info("📊 Posts per page: {$limit}");
        $this->info("⏱️  Delay between requests: {$delay}s");

        try {
            if (!$postsOnly) {
                // Step 1: Get user's pages
                $this->info("\n=== 📄 Step 1: Fetching user's pages ===");
                $pages = $this->getUserPages($userId, $accessToken);
                
                if (empty($pages)) {
                    $this->warn('❌ No pages found for this user');
                    return 0;
                }

                $this->info("✅ Found " . count($pages) . " pages");

                // Step 2: Sync pages to database
                $this->info("\n=== 💾 Step 2: Syncing pages to database ===");
                $this->syncPagesToDatabase($pages);
                $this->info("✅ Pages synced successfully");
            } else {
                // Get pages from database if posts-only mode
                $pages = DB::table('facebook_fanpage')->get()->toArray();
                $pages = array_map(function($page) {
                    return (array) $page;
                }, $pages);
                
                if (empty($pages)) {
                    $this->error('❌ No pages found in database. Please sync pages first.');
                    return 1;
                }
                
                $this->info("📄 Using " . count($pages) . " pages from database");
            }

            if (!$pagesOnly) {
                // Step 3: Get posts for each page
                $this->info("\n=== 📝 Step 3: Fetching posts for each page ===");
                $totalPosts = 0;
                $totalInsights = 0;
                
                foreach ($pages as $index => $page) {
                    $this->info("\n📄 Processing page " . ($index + 1) . "/" . count($pages) . ": {$page['name']} ({$page['id']})");
                    
                    // Sử dụng access token của page nếu có, nếu không thì dùng token chung
                    $pageAccessToken = $page['access_token'] ?? $accessToken;
                    $this->info("🔑 Using access token: " . substr($pageAccessToken, 0, 20) . "...");
                    
                    $posts = $this->getPagePosts($page['id'], $pageAccessToken, $sinceDate, $untilDate, $limit);
                    
                    if (!empty($posts)) {
                        $this->info("📝 Found " . count($posts) . " posts");
                        $insightsCount = $this->syncPostsToDatabase($posts, $page['id'], $pageAccessToken, $delay, $sinceDate, $untilDate);
                        $totalPosts += count($posts);
                        $totalInsights += $insightsCount;
                        $this->info("✅ Synced " . count($posts) . " posts with " . $insightsCount . " insights");
                    } else {
                        $this->warn("⚠️  No posts found for this page");
                    }
                    
                    // Delay between pages
                    if ($index < count($pages) - 1) {
                        sleep($delay);
                    }
                }

                $this->info("\n🎉 === Sync completed successfully ===");
                $this->info("📄 Total pages processed: " . count($pages));
                $this->info("📝 Total posts synced: {$totalPosts}");
                $this->info("📊 Total insights synced: {$totalInsights}");
            }

        } catch (\Exception $e) {
            $this->error("❌ Error during sync: " . $e->getMessage());
            Log::error('Facebook fanpage sync error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }

        return 0;
    }

    /**
     * Get user's pages from Facebook Graph API
     */
    private function getUserPages($userId, $accessToken)
    {
        $url = "{$this->graphApiUrl}/{$userId}/accounts";
        $params = [
            'access_token' => $accessToken,
            'fields' => 'id,name,category,about,website,phone,emails,location,cover,picture,followers_count,fan_count,is_published,is_verified,access_token,tasks'
        ];

        $allPages = [];
        $nextUrl = $url;
        $safety = 0; // guard loop
        while ($nextUrl && $safety < 1000) { // practically unlimited pages
            $this->info("  📄 Fetching fanpages batch " . ($safety + 1) . "...");
            $response = Http::timeout(30)->get($nextUrl, $params);
            if (!$response->successful()) {
                throw new \Exception("Failed to fetch pages: " . $response->body());
            }
            $data = $response->json();
            $batch = $data['data'] ?? [];
            $allPages = array_merge($allPages, $batch);
            $nextUrl = $data['paging']['next'] ?? null;
            $params = []; // next URL already contains query
            $safety++;
        }

        return $allPages;
    }

    /**
     * Get page posts from Facebook Graph API
     */
    private function getPagePosts($pageId, $accessToken, $sinceDate, $untilDate, $limit = 100)
    {
        // Chuyển đổi date format như ads insights
        $since = Carbon::parse($sinceDate)->timestamp;
        $until = Carbon::parse($untilDate)->timestamp;
        
        $url = "{$this->graphApiUrl}/{$pageId}/posts";
        $params = [
            'access_token' => $accessToken,
            'fields' => 'id,message,created_time,permalink_url,from{id,name,picture},attachments{media_type,media,url,title,description},shares,comments.limit(10){id,message,from,created_time},likes.limit(10){id,name}',
            'since' => $since,
            'until' => $until,
            'limit' => $limit
        ];

        $allPosts = [];
        $nextUrl = $url;
        $pageCount = 0;
        $maxPages = 10; // Limit to prevent infinite loops

        while ($nextUrl && $pageCount < $maxPages) {
            $this->info("  📄 Fetching page " . ($pageCount + 1) . " of posts...");
            
            $response = Http::timeout(30)->get($nextUrl, $params);

            if (!$response->successful()) {
                $errorBody = $response->body();
                $this->warn("  ⚠️  Failed to fetch posts for page {$pageId}: " . $errorBody);
                
                // Check if it's a rate limit error
                if (strpos($errorBody, 'rate limit') !== false || strpos($errorBody, 'too many requests') !== false) {
                    $this->info("  ⏳ Rate limit hit, waiting 60 seconds...");
                    sleep(60);
                    continue;
                }
                
                // Check if it's a permission error - skip this page
                if (strpos($errorBody, 'Invalid OAuth') !== false || strpos($errorBody, 'OAuthException') !== false) {
                    $this->warn("  ⚠️  Permission denied for page {$pageId}, skipping...");
                    return []; // Return empty array instead of throwing exception
                }
                
                throw new \Exception("Failed to fetch posts for page {$pageId}: " . $errorBody);
            }

            $data = $response->json();
            $posts = $data['data'] ?? [];
            $allPosts = array_merge($allPosts, $posts);
            
            $this->info("  📝 Found " . count($posts) . " posts on this page");

            // Get next page URL
            $nextUrl = $data['paging']['next'] ?? null;
            $params = []; // Clear params for next page as URL contains them
            $pageCount++;
        }

        $this->info("  ✅ Total posts collected: " . count($allPosts));
        return $allPosts;
    }

    /**
     * Get post insights from Facebook Graph API
     */
    private function getPostInsights($postId, $accessToken, $sinceDate = null, $untilDate = null)
    {
        $url = "{$this->graphApiUrl}/{$postId}/insights";
        $params = [
            'access_token' => $accessToken,
            'metric' => 'post_impressions,post_impressions_unique,post_impressions_paid,post_impressions_organic,post_impressions_viral,post_clicks,post_video_views,post_video_views_paid,post_video_views_organic,post_reactions_like_total,post_reactions_love_total,post_reactions_wow_total,post_reactions_haha_total,post_reactions_sorry_total,post_reactions_anger_total'
        ];

        // Không dùng period để lấy cả lifetime và day data
        // $params['period'] = 'day';
        
        // Thêm time_range nếu có date range như ads insights
        if ($sinceDate && $untilDate) {
            $params['time_range'] = json_encode([
                'since' => $sinceDate,
                'until' => $untilDate
            ]);
        }

        $response = Http::timeout(30)->get($url, $params);

        if (!$response->successful()) {
            $errorBody = $response->body();
            
            // Check if it's a rate limit error
            if (strpos($errorBody, 'rate limit') !== false || strpos($errorBody, 'too many requests') !== false) {
                $this->warn("  ⏳ Rate limit hit for insights, waiting 30 seconds...");
                sleep(30);
                return $this->getPostInsights($postId, $accessToken, $sinceDate, $untilDate); // Retry
            }
            
            Log::warning("Failed to fetch insights for post {$postId}: " . $errorBody);
            return [];
        }

        $data = $response->json();
        return $data['data'] ?? [];
    }

    /**
     * Sync pages to database
     */
    private function syncPagesToDatabase($pages)
    {
        foreach ($pages as $page) {
            try {
                // Helper function to safely convert arrays to strings
                $safeString = function($value) {
                    if (is_array($value)) {
                        return json_encode($value);
                    }
                    return $value;
                };
                
                $this->info("Processing page: " . ($page['id'] ?? 'unknown'));
                
                $pageData = [
                    'page_id' => $page['id'],
                    'name' => $safeString($page['name'] ?? ''),
                    'category' => $safeString($page['category'] ?? null),
                    'about' => $safeString($page['about'] ?? ''),
                    'website' => $safeString($page['website'] ?? ''),
                    'phone' => $safeString($page['phone'] ?? ''),
                    'email' => isset($page['emails']) ? (is_array($page['emails']) ? implode(',', $page['emails']) : $page['emails']) : '',
                    'location' => isset($page['location']) ? (is_array($page['location']) ? json_encode($page['location']) : $page['location']) : null,
                    'cover_photo_url' => isset($page['cover']['source']) ? $page['cover']['source'] : '',
                    'profile_picture_url' => isset($page['picture']['data']['url']) ? $page['picture']['data']['url'] : '',
                    'followers_count' => $page['followers_count'] ?? 0,
                    'likes_count' => $page['fan_count'] ?? 0,
                    'is_published' => $page['is_published'] ?? true,
                    'is_verified' => $page['is_verified'] ?? false,
                    'access_token' => $safeString($page['access_token'] ?? ''),
                    'last_synced_at' => now(),
                    'updated_at' => now(),
                    'created_at' => now()
                ];
                
                
                DB::table('facebook_fanpage')->updateOrInsert(
                    ['page_id' => $page['id']],
                    $pageData
                );
            } catch (\Exception $e) {
                $this->error("Error syncing page {$page['id']}: " . $e->getMessage());
                continue;
            }
        }
    }

    /**
     * Sync posts to database
     */
    private function syncPostsToDatabase($posts, $pageId, $accessToken, $delay = 1, $sinceDate = null, $untilDate = null)
    {
        $insightsCount = 0;
        $totalPosts = count($posts);
        
        foreach ($posts as $index => $post) {
            $this->info("  📝 Processing post " . ($index + 1) . "/{$totalPosts}: {$post['id']}");
            
            // Process insights data từ API response (đã có sẵn trong post data)
            $insightsData = [];
            $postImpressions = 0;
            $postEngagedUsers = 0;
            $postClicks = 0;
            $postReactions = 0;
            $postComments = 0;
            $postShares = 0;
            $postVideoViews = 0;
            $postVideoCompleteViews = 0;
            
            // Breakdown fields
            $postImpressionsUnique = 0;
            $postImpressionsPaid = 0;
            $postImpressionsPaidUnique = 0;
            $postImpressionsOrganic = 0;
            $postImpressionsOrganicUnique = 0;
            $postImpressionsViral = 0;
            $postImpressionsViralUnique = 0;
            $postClicksUnique = 0;
            $postVideoViewsPaid = 0;
            $postVideoViewsOrganic = 0;
            $postReactionsLikeTotal = 0;
            $postReactionsLoveTotal = 0;
            $postReactionsWowTotal = 0;
            $postReactionsHahaTotal = 0;
            $postReactionsSorryTotal = 0;
            $postReactionsAngerTotal = 0;

            // Lấy insights riêng cho post
            $insights = $this->getPostInsights($post['id'], $accessToken, $sinceDate, $untilDate);
            
            if (!empty($insights)) {
                foreach ($insights as $insight) {
                    // Lấy giá trị từ period "lifetime" trước (vì day data có thể không có)
                    $value = 0;
                    if (isset($insight['values'])) {
                        // Ưu tiên lifetime data trước
                        foreach ($insight['values'] as $val) {
                            if (!isset($val['end_time'])) {
                                // Đây là lifetime data
                                $value = $val['value'] ?? 0;
                                break;
                            }
                        }
                        
                        // Nếu không có lifetime data, tính tổng day data
                        if ($value == 0) {
                            foreach ($insight['values'] as $val) {
                                if (isset($val['end_time'])) {
                                    // Đây là data theo ngày
                                    $value += $val['value'] ?? 0;
                                }
                            }
                        }
                    }
                    
                    $insightsData[$insight['name']] = $value;
                    
                    switch ($insight['name']) {
                        case 'post_impressions':
                            $postImpressions = $value;
                            break;
                        case 'post_impressions_unique':
                            $postImpressionsUnique = $value;
                            break;
                        case 'post_impressions_paid':
                            $postImpressionsPaid = $value;
                            break;
                        case 'post_impressions_paid_unique':
                            $postImpressionsPaidUnique = $value;
                            break;
                        case 'post_impressions_organic':
                            $postImpressionsOrganic = $value;
                            break;
                        case 'post_impressions_organic_unique':
                            $postImpressionsOrganicUnique = $value;
                            break;
                        case 'post_impressions_viral':
                            $postImpressionsViral = $value;
                            break;
                        case 'post_impressions_viral_unique':
                            $postImpressionsViralUnique = $value;
                            break;
                        case 'post_engaged_users':
                            $postEngagedUsers = $value;
                            break;
                        case 'post_clicks':
                            $postClicks = $value;
                            break;
                        case 'post_clicks_unique':
                            $postClicksUnique = $value;
                            break;
                        case 'post_reactions_like_total':
                            $postReactionsLikeTotal = $value;
                            break;
                        case 'post_reactions_love_total':
                            $postReactionsLoveTotal = $value;
                            break;
                        case 'post_reactions_wow_total':
                            $postReactionsWowTotal = $value;
                            break;
                        case 'post_reactions_haha_total':
                            $postReactionsHahaTotal = $value;
                            break;
                        case 'post_reactions_sorry_total':
                            $postReactionsSorryTotal = $value;
                            break;
                        case 'post_reactions_anger_total':
                            $postReactionsAngerTotal = $value;
                            break;
                        case 'post_video_views':
                            $postVideoViews = $value;
                            break;
                        case 'post_video_views_paid':
                            $postVideoViewsPaid = $value;
                            break;
                        case 'post_video_views_organic':
                            $postVideoViewsOrganic = $value;
                            break;
                    }
                }
            }
            
            // Process comments và likes từ post data
            $commentsData = isset($post['comments']['data']) ? $post['comments']['data'] : [];
            $likesData = isset($post['likes']['data']) ? $post['likes']['data'] : [];
            $sharesData = isset($post['shares']) ? $post['shares'] : [];
            $fromData = isset($post['from']) ? $post['from'] : [];
            
            if (!empty($insightsData)) {
                $insightsCount++;
            }

            // Check if post already exists to preserve original created_time
            // created_time = when the post was created on Facebook (from API)
            // created_at = when the record was synced to our database
            $existingPost = DB::table('post_facebook_fanpage_not_ads')
                ->where('post_id', $post['id'])
                ->first();
            
            $postData = [
                'post_id' => $post['id'],
                'page_id' => $pageId,
                'message' => $post['message'] ?? null,
                'story' => $post['story'] ?? null,
                'type' => $post['type'] ?? null,
                'status_type' => $post['status_type'] ?? null,
                'link' => $post['link'] ?? null,
                'picture' => $post['picture'] ?? null,
                'full_picture' => $post['full_picture'] ?? null,
                'source' => $post['source'] ?? null,
                'description' => $post['description'] ?? null,
                'caption' => $post['caption'] ?? null,
                'name' => $post['name'] ?? null,
                'attachments' => isset($post['attachments']) ? json_encode($post['attachments']) : null,
                'properties' => isset($post['properties']) ? json_encode($post['properties']) : null,
                'is_published' => $post['is_published'] ?? true,
                'is_hidden' => $post['is_hidden'] ?? false,
                'is_expired' => $post['is_expired'] ?? false,
                'updated_time' => isset($post['updated_time']) ? Carbon::parse($post['updated_time']) : null,
                
                // Breakdown fields
                'permalink_url' => $post['permalink_url'] ?? null,
                'from_data' => json_encode($fromData),
                'shares_data' => json_encode($sharesData),
                'comments_data' => json_encode($commentsData),
                'likes_data' => json_encode($likesData),
                
                // Basic insights
                'post_impressions' => $postImpressions,
                'post_engaged_users' => $postEngagedUsers,
                'post_clicks' => $postClicks,
                'post_reactions' => $postReactions,
                'post_comments' => $postComments,
                'post_shares' => $postShares,
                'post_video_views' => $postVideoViews,
                'post_video_complete_views' => $postVideoCompleteViews,
                
                // Breakdown insights
                'post_impressions_unique' => $postImpressionsUnique,
                'post_impressions_paid' => $postImpressionsPaid,
                'post_impressions_paid_unique' => $postImpressionsPaidUnique,
                'post_impressions_organic' => $postImpressionsOrganic,
                'post_impressions_organic_unique' => $postImpressionsOrganicUnique,
                'post_impressions_viral' => $postImpressionsViral,
                'post_impressions_viral_unique' => $postImpressionsViralUnique,
                'post_clicks_unique' => $postClicksUnique,
                'post_video_views_paid' => $postVideoViewsPaid,
                'post_video_views_organic' => $postVideoViewsOrganic,
                
                // Reactions breakdown
                'post_reactions_like_total' => $postReactionsLikeTotal,
                'post_reactions_love_total' => $postReactionsLoveTotal,
                'post_reactions_wow_total' => $postReactionsWowTotal,
                'post_reactions_haha_total' => $postReactionsHahaTotal,
                'post_reactions_sorry_total' => $postReactionsSorryTotal,
                'post_reactions_anger_total' => $postReactionsAngerTotal,
                
                'insights_data' => json_encode($insightsData),
                'insights_synced_at' => now(),
                'last_synced_at' => now(),
                'updated_at' => now(),
            ];
            
        // Parse created_time from Facebook API (ISO 8601 format with timezone)
        // Always update created_time from Facebook API to ensure accuracy
        try {
            $createdTime = Carbon::parse($post['created_time']);
            // Convert to Vietnam timezone for consistent storage
            $postData['created_time'] = $createdTime->setTimezone('Asia/Ho_Chi_Minh');

            if (!$existingPost) {
                // New post: also set created_at
                $postData['created_at'] = now();
            }
            // For existing posts, don't update created_at

        } catch (\Exception $e) {
            // Fallback: use current time if parsing fails
            $this->warn("⚠️ Failed to parse created_time for post {$post['id']}: " . $e->getMessage());
            $postData['created_time'] = now();
            if (!$existingPost) {
                $postData['created_at'] = now();
            }
        }
            
            DB::table('post_facebook_fanpage_not_ads')->updateOrInsert(
                ['post_id' => $post['id']],
                $postData
            );
            
            // Delay between posts to avoid rate limiting
            if ($index < $totalPosts - 1) {
                sleep($delay);
            }
        }
        
        return $insightsCount;
    }

}
