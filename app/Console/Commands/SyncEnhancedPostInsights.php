<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SyncEnhancedPostInsights extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'facebook:sync-enhanced-post-insights 
                            {--since= : Start date (Y-m-d format)}
                            {--until= : End date (Y-m-d format)}
                            {--days= : Number of days to sync (auto-calculate since/until)}
                            {--limit=50 : Limit number of posts to process}
                            {--video-only : Only sync video posts}
                            {--daily : Get daily insights with period=day}';

    /**
     * The console command description.
     */
    protected $description = 'Sync enhanced post insights including video metrics and engagement data';

    /**
     * Facebook Graph API base URL
     */
    private $graphApiUrl = 'https://graph.facebook.com/v23.0';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $since = $this->option('since');
        $until = $this->option('until');
        $days = $this->option('days');
        $limit = (int) $this->option('limit');
        $videoOnly = $this->option('video-only');
        $daily = $this->option('daily');

        // Handle days parameter
        if ($days) {
            $since = now()->subDays($days)->format('Y-m-d');
            $until = now()->format('Y-m-d');
        } else {
            $since = $since ?: now()->subDays(30)->format('Y-m-d');
            $until = $until ?: now()->format('Y-m-d');
        }

        $this->info("🚀 Starting enhanced post insights sync...");
        $this->info("📅 Date range: {$since} to {$until}");
        $this->info("📊 Limit: {$limit} posts");
        $this->info("🎥 Video only: " . ($videoOnly ? 'Yes' : 'No'));
        $this->info("📈 Daily insights: " . ($daily ? 'Yes' : 'No'));

        // Lấy page access tokens từ database
        $pages = DB::table('facebook_fanpage')
            ->whereNotNull('access_token')
            ->where('access_token', '!=', '')
            ->get();
            
        if ($pages->isEmpty()) {
            $this->error('❌ No page access tokens found in database');
            return 1;
        }
        
        $this->info("📄 Found " . $pages->count() . " pages with access tokens");

        try {
            // Lấy posts cần sync
            $query = DB::table('post_facebook_fanpage_not_ads')
                ->whereBetween('created_time', [$since, $until])
                ->orderBy('created_time', 'desc');

            if ($videoOnly) {
                $query->where(function($q) {
                    $q->where('attachments', 'like', '%"media_type":"video"%')
                      ->orWhere('attachments', 'like', '%"type":"video"%')
                      ->orWhere('attachments', 'like', '%video%');
                });
            }

            $posts = $query->limit($limit)->get();
            $this->info("📝 Found " . $posts->count() . " posts to sync");

            $totalEnhanced = 0;
            $totalVideoInsights = 0;
            $videoPosts = [];
            $errors = [];

            foreach ($posts as $index => $post) {
                $this->info("\n📄 Processing post " . ($index + 1) . "/" . $posts->count() . ": {$post->post_id}");

                // Tìm page access token tương ứng với post
                $page = $pages->firstWhere('page_id', $post->page_id);
                if (!$page) {
                    $this->warn("⚠️ No access token found for page {$post->page_id}");
                    continue;
                }

                $this->info("🔑 Using page access token: " . substr($page->access_token, 0, 20) . "...");

                try {
                    // Check if this is a video post
                    $isVideo = $this->isVideoPost($post->attachments);
                    $this->info("🔍 Post {$post->post_id} - Is video: " . ($isVideo ? 'Yes' : 'No'));
                    
                    if ($isVideo) {
                        $this->info("🎥 This is a video post - getting insights");
                        
                        // For daily insights, fetch from the post's created_time up to today (bounded by provided since/until if any)
                        $effectiveSince = $since;
                        $effectiveUntil = $until;
                        if ($daily) {
                            $postCreatedDate = Carbon::parse($post->created_time)->format('Y-m-d');
                            // Try to get original video created_time if available
                            $videoIdTmp = $this->extractVideoId($post->attachments);
                            if ($videoIdTmp) {
                                $videoCreated = $this->getVideoCreatedTime($videoIdTmp, $page->access_token);
                                if ($videoCreated) {
                                    // Persist to DB for future runs
                                    DB::table('post_facebook_fanpage_not_ads')
                                        ->where('id', $post->id)
                                        ->update(['created_time_video' => $videoCreated, 'updated_at' => now()]);
                                    $postCreatedDate = Carbon::parse($videoCreated)->format('Y-m-d');
                                }
                            }
                            $todayDate = now()->format('Y-m-d');
                            // If user provided since/until, bound by them; otherwise default to postCreated -> today
                            $effectiveSince = $since ? max($since, $postCreatedDate) : $postCreatedDate;
                            $effectiveUntil = $until ? min($until, $todayDate) : $todayDate;
                            $this->info("📅 Using per-post daily window: {$effectiveSince} → {$effectiveUntil}");
                        }

                        // Get post insights (with chunking for daily backfill)
                        $enhancedSyncedForPost = false;
                        if ($daily) {
                            $cursorStart = Carbon::parse($effectiveSince);
                            $cursorEnd = Carbon::parse($effectiveUntil);
                            while ($cursorStart->lte($cursorEnd)) {
                                $chunkEnd = $cursorStart->copy()->addDays(29);
                                if ($chunkEnd->gt($cursorEnd)) {
                                    $chunkEnd = $cursorEnd->copy();
                                }
                                $chunkSince = $cursorStart->format('Y-m-d');
                                $chunkUntil = $chunkEnd->format('Y-m-d');
                                $this->info("🧩 Fetching daily chunk: {$chunkSince} → {$chunkUntil}");

                                $postInsights = $this->getPostInsights($post->post_id, $page->access_token, true, $chunkSince, $chunkUntil);
                                $this->info("📊 Post insights response: " . json_encode($postInsights));

                                if (!empty($postInsights)) {
                                    $this->saveDailyPostInsights($post->id, $postInsights);
                                    $enhancedSyncedForPost = true;
                                }

                                // move cursor forward
                                $cursorStart = $chunkEnd->copy()->addDay();
                                // slight delay to avoid rate limits
                                usleep(150000);
                            }

                            if ($enhancedSyncedForPost) {
                                $totalEnhanced++;
                                $this->info("✅ Post insights synced (daily, chunked)");
                            } else {
                                $this->warn("⚠️ No insights data returned for post {$post->post_id}");
                            }
                        } else {
                            $postInsights = $this->getPostInsights($post->post_id, $page->access_token, false, $effectiveSince, $effectiveUntil);
                            $this->info("📊 Post insights response: " . json_encode($postInsights));
                            if (!empty($postInsights)) {
                                $this->updateEnhancedPostInsights($post->id, $postInsights);
                                $totalEnhanced++;
                                $this->info("✅ Post insights synced");
                                $this->analyzePostMetrics($post, $postInsights);
                            } else {
                                $this->warn("⚠️ No insights data returned for post {$post->post_id}");
                            }
                        }
                        
                        // Get video insights
                        $videoId = $this->extractVideoId($post->attachments);
                        if (!$videoId) {
                            // Fallback: query post for object_id or attachments.target.id
                            $videoId = $this->getVideoIdFromPost($post->post_id, $page->access_token);
                        }
                        if ($videoId) {
                            if ($daily) {
                                $videoSyncedForPost = false;
                                $cursorStart = Carbon::parse($effectiveSince);
                                $cursorEnd = Carbon::parse($effectiveUntil);
                                while ($cursorStart->lte($cursorEnd)) {
                                    $chunkEnd = $cursorStart->copy()->addDays(29);
                                    if ($chunkEnd->gt($cursorEnd)) {
                                        $chunkEnd = $cursorEnd->copy();
                                    }
                                    $chunkSince = $cursorStart->format('Y-m-d');
                                    $chunkUntil = $chunkEnd->format('Y-m-d');
                                    $this->info("🧩 Fetching video daily chunk: {$chunkSince} → {$chunkUntil}");

                                    $videoInsights = $this->getVideoInsights($videoId, $page->access_token, true, $chunkSince, $chunkUntil);
                                    if (!empty($videoInsights)) {
                                        $this->saveDailyVideoInsights($post->id, $videoId, $videoInsights);
                                        $videoSyncedForPost = true;
                                    }

                                    $cursorStart = $chunkEnd->copy()->addDay();
                                    usleep(150000);
                                }

                                if ($videoSyncedForPost) {
                                    $totalVideoInsights++;
                                    $this->info("✅ Video insights synced (daily, chunked)");
                                }
                            } else {
                                $videoInsights = $this->getVideoInsights($videoId, $page->access_token, false, $effectiveSince, $effectiveUntil);
                                if (!empty($videoInsights)) {
                                    $this->saveVideoInsights($post->id, $videoId, $videoInsights);
                                    $totalVideoInsights++;
                                    $this->info("✅ Video insights synced");
                                    $videoPosts[] = [
                                        'post_id' => $post->post_id,
                                        'video_id' => $videoId,
                                        'post_insights' => $postInsights,
                                        'video_insights' => $videoInsights
                                    ];
                                }
                            }
                        }
                    } else {
                        // Regular post: fetch lifetime only (daily not supported per API behavior)
                        $postInsights = $this->getPostInsights($post->post_id, $page->access_token, false, $since, $until);
                        if (!empty($postInsights)) {
                            $this->updateEnhancedPostInsights($post->id, $postInsights);
                            $totalEnhanced++;
                            $this->info("✅ Post insights synced (lifetime)");
                            $this->analyzePostMetrics($post, $postInsights);
                        } else {
                            $this->warn("⚠️ No lifetime insights returned for post {$post->post_id}");
                        }
                    }

                    // Delay để tránh rate limit
                    sleep(1);

                } catch (\Exception $e) {
                    $error = "Failed to sync post {$post->post_id}: " . $e->getMessage();
                    $this->error("❌ " . $error);
                    $errors[] = $error;
                }
            }

            $this->info("\n🎉 === Sync completed successfully ===");
            $this->info("📊 Enhanced insights synced: {$totalEnhanced}");
            $this->info("🎥 Video insights synced: {$totalVideoInsights}");
            $this->info("❌ Errors: " . count($errors));
            
            // Hiển thị phân tích video posts
            if (!empty($videoPosts)) {
                $this->info("\n📈 === VIDEO POSTS ANALYSIS ===");
                $this->analyzeVideoPosts($videoPosts);
            }

            if (!empty($errors)) {
                $this->error("\nErrors occurred:");
                foreach ($errors as $error) {
                    $this->error("- {$error}");
                }
            }

            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Sync failed: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Get post data with all available fields
     */
    private function getPostData($postId, $accessToken)
    {
        $url = "{$this->graphApiUrl}/{$postId}";
        $params = [
            'access_token' => $accessToken,
            'fields' => implode(',', [
                'id', 'message', 'story', 'created_time', 'updated_time',
                'from', 'to', 'type', 'status_type',
                'permalink_url', 'link', 'picture', 'full_picture',
                'source', 'properties', 'actions', 'privacy',
                'place', 'coordinates', 'targeting', 'feed_targeting',
                'promotion_status', 'scheduled_publish_time', 'backdated_time',
                'call_to_action', 'parent_id', 'timeline_visibility',
                'is_hidden', 'is_expired', 'is_published', 'is_popular',
                'is_spherical', 'is_instagram_eligible', 'is_eligible_for_promotion'
            ])
        ];

        $response = Http::timeout(30)->get($url, $params);

        if (!$response->successful()) {
            throw new \Exception("API Error: " . $response->body());
        }

        return $response->json();
    }

    /**
     * Get post insights (only for video posts)
     */
    private function getPostInsights($postId, $accessToken, $daily = false, $since = null, $until = null)
    {
        $url = "{$this->graphApiUrl}/{$postId}/insights";
        $params = [
            'access_token' => $accessToken,
            
            'metric' => 'post_impressions'
        ];

        // Add period=day for daily insights
        if ($daily) {
            $params['period'] = 'day';
        }

        // Add date range for daily insights
        if ($daily && $since && $until) {
            $params['since'] = $since;
            $params['until'] = $until;
        }

        $response = Http::timeout(30)->get($url, $params);

        if (!$response->successful()) {
            throw new \Exception("API Error: " . $response->body());
        }

        $data = $response->json();
        return $data['data'] ?? [];
    }

    /**
     * Get video insights (for video posts)
     */
    private function getVideoInsights($videoId, $accessToken, $daily = false, $since = null, $until = null)
    {
        $url = "{$this->graphApiUrl}/{$videoId}/video_insights";
        $params = [
            'access_token' => $accessToken,
            'metric' => implode(',', [
                'video_views',
                'video_views_unique',
                'video_views_autoplayed',
                'video_views_clicked_to_play',
                'video_views_organic',
                'video_views_paid',
                'video_views_sound_on',
                'video_views_sound_off',
                'video_complete_views',
                'video_complete_views_unique',
                'video_complete_views_organic',
                'video_complete_views_paid',
                'video_avg_time_watched',
                'video_view_total_time',
                'video_retention_graph',
                'video_views_by_distribution_type',
                'video_views_by_region_id',
                'video_views_by_age_bucket_and_gender'
            ])
        ];

        // Add period=day for daily insights
        if ($daily) {
            $params['period'] = 'day';
        }

        // Add date range for daily insights
        if ($daily && $since && $until) {
            $params['since'] = $since;
            $params['until'] = $until;
        }

        $response = Http::timeout(30)->get($url, $params);

        if (!$response->successful()) {
            throw new \Exception("API Error: " . $response->body());
        }

        $data = $response->json();
        return $data['data'] ?? [];
    }


    /**
     * Update enhanced post insights
     */
    private function updateEnhancedPostInsights($postId, $insights)
    {
        $metrics = [];
        foreach ($insights as $insight) {
            $value = $insight['values'][0]['value'] ?? 0;
            $metrics[$insight['name']] = $value;
        }

        // Tính tổng reactions
        $totalReactions = ($metrics['post_reactions_like_total'] ?? 0) + 
                         ($metrics['post_reactions_love_total'] ?? 0) + 
                         ($metrics['post_reactions_wow_total'] ?? 0) + 
                         ($metrics['post_reactions_haha_total'] ?? 0) + 
                         ($metrics['post_reactions_sorry_total'] ?? 0) + 
                         ($metrics['post_reactions_anger_total'] ?? 0);

        DB::table('post_facebook_fanpage_not_ads')
            ->where('id', $postId)
            ->update([
                // Impressions metrics
                'post_impressions' => $metrics['post_impressions'] ?? 0,
                'post_impressions_unique' => $metrics['post_impressions_unique'] ?? 0,
                'post_impressions_organic' => $metrics['post_impressions_organic'] ?? 0,
                'post_impressions_viral' => $metrics['post_impressions_viral'] ?? 0,
                
                // Engagement metrics
                'post_clicks' => $metrics['post_clicks'] ?? 0,
                'post_engaged_users' => $metrics['post_engaged_users'] ?? 0,
                'post_reactions' => $totalReactions,
                
                // Individual reactions
                'post_reactions_like_total' => $metrics['post_reactions_like_total'] ?? 0,
                'post_reactions_love_total' => $metrics['post_reactions_love_total'] ?? 0,
                'post_reactions_wow_total' => $metrics['post_reactions_wow_total'] ?? 0,
                'post_reactions_haha_total' => $metrics['post_reactions_haha_total'] ?? 0,
                'post_reactions_sorry_total' => $metrics['post_reactions_sorry_total'] ?? 0,
                'post_reactions_anger_total' => $metrics['post_reactions_anger_total'] ?? 0,
                
                // Video metrics (if available) - only basic ones
                'post_video_views' => $metrics['post_video_views'] ?? 0,
                
                // Store all metrics in JSON for detailed analysis
                'insights_data' => json_encode($metrics),
                'insights_synced_at' => now(),
                'updated_at' => now()
            ]);
    }

    /**
     * Save video insights
     */
    private function saveVideoInsights($postId, $videoId, $insights)
    {
        $metrics = [];
        foreach ($insights as $insight) {
            $value = $insight['values'][0]['value'] ?? 0;
            $metrics[$insight['name']] = $value;
        }

        DB::table('facebook_video_insights')->updateOrInsert(
            ['post_id' => $postId],
            [
                'post_id' => $postId,
                'video_id' => $videoId,
                'video_views_autoplayed' => $metrics['video_views_autoplayed'] ?? 0,
                'video_views_clicked_to_play' => $metrics['video_views_clicked_to_play'] ?? 0,
                'video_views_unique' => $metrics['video_views_unique'] ?? 0,
                'video_avg_time_watched' => $metrics['video_avg_time_watched'] ?? 0,
                'video_complete_views' => $metrics['video_complete_views'] ?? 0,
                'video_retention_graph' => isset($metrics['video_retention_graph']) 
                    ? json_encode($metrics['video_retention_graph']) : null,
                'video_play_actions' => isset($metrics['video_play_actions']) 
                    ? json_encode($metrics['video_play_actions']) : null,
                'created_at' => now(),
                'updated_at' => now()
            ]
        );
    }

    /**
     * Check if post is video
     */
    private function isVideoPost($attachments)
    {
        if (!$attachments) return false;
        
        $attachmentsData = json_decode($attachments, true);
        if (!isset($attachmentsData['data'])) return false;

        foreach ($attachmentsData['data'] as $attachment) {
            if (isset($attachment['media_type']) && $attachment['media_type'] === 'video') {
                return true;
            }
        }

        return false;
    }

    /**
     * Extract video ID from attachments
     */
    private function extractVideoId($attachments)
    {
        if (!$attachments) return null;
        
        $attachmentsData = json_decode($attachments, true);
        if (!isset($attachmentsData['data'])) return null;

        foreach ($attachmentsData['data'] as $attachment) {
            if (isset($attachment['media_type']) && $attachment['media_type'] === 'video') {
                if (isset($attachment['media']['source'])) {
                    // Extract video ID from URL
                    $url = $attachment['media']['source'];
                    if (preg_match('/\/videos\/(\d+)/', $url, $matches)) {
                        return $matches[1];
                    }
                }
            }
        }

        return null;
    }

    /**
     * Resolve video_id from a post by querying Graph API fields: object_id, attachments{target{id}}
     */
    private function getVideoIdFromPost(string $postId, string $accessToken): ?string
    {
        try {
            $url = $this->graphApiUrl . "/{$postId}";
            $params = [
                'access_token' => $accessToken,
                'fields' => 'object_id,attachments{media_type,target{id}}'
            ];
            $response = Http::timeout(30)->get($url, $params);
            if (!$response->successful()) {
                return null;
            }
            $data = $response->json();
            if (!empty($data['object_id'])) {
                return (string) $data['object_id'];
            }
            if (isset($data['attachments']['data'][0]['media_type']) && $data['attachments']['data'][0]['media_type'] === 'video') {
                return $data['attachments']['data'][0]['target']['id'] ?? null;
            }
            return null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Get the original video's created_time by video_id
     */
    private function getVideoCreatedTime(string $videoId, string $accessToken): ?string
    {
        try {
            $url = $this->graphApiUrl . "/{$videoId}?fields=created_time&access_token=" . urlencode($accessToken);
            $response = Http::get($url);
            if ($response->ok()) {
                $data = $response->json();
                return $data['created_time'] ?? null;
            }
            return null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Save daily post insights
     */
    private function saveDailyPostInsights($postId, $insights)
    {
        // Map Graph metrics into structured daily row per date
        $dateToRow = [];
        foreach ($insights as $insight) {
            $metricName = $insight['name'];
            $values = $insight['values'] ?? [];
            foreach ($values as $value) {
                $endTime = $value['end_time'] ?? null;
                $metricValue = is_array($value['value'] ?? null) ? 0 : ($value['value'] ?? 0);
                if (!$endTime) continue;
                $date = \Carbon\Carbon::parse($endTime)->format('Y-m-d');
                if (!isset($dateToRow[$date])) {
                    $dateToRow[$date] = [
                        'post_id' => $postId,
                        'date' => $date,
                        'post_impressions' => 0,
                        'post_impressions_unique' => 0,
                        'post_impressions_paid' => 0,
                        'post_impressions_paid_unique' => 0,
                        'post_impressions_organic' => 0,
                        'post_impressions_organic_unique' => 0,
                        'post_impressions_viral' => 0,
                        'post_impressions_viral_unique' => 0,
                        'post_clicks' => 0,
                        'post_clicks_unique' => 0,
                        'post_engaged_users' => 0,
                        'post_reactions' => 0,
                        'post_comments' => 0,
                        'post_shares' => 0,
                        'post_reactions_like_total' => 0,
                        'post_reactions_love_total' => 0,
                        'post_reactions_wow_total' => 0,
                        'post_reactions_haha_total' => 0,
                        'post_reactions_sorry_total' => 0,
                        'post_reactions_anger_total' => 0,
                        'post_video_views' => 0,
                        'post_video_complete_views' => 0,
                        'insights_data' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                // Assign metric
                if (array_key_exists($metricName, $dateToRow[$date])) {
                    $dateToRow[$date][$metricName] = (int) $metricValue;
                } else {
                    // Keep full json for debugging
                    $existing = $dateToRow[$date]['insights_data'] ? json_decode($dateToRow[$date]['insights_data'], true) : [];
                    $existing[$metricName] = $metricValue;
                    $dateToRow[$date]['insights_data'] = json_encode($existing);
                }
            }
        }

        foreach ($dateToRow as $date => $row) {
            DB::table('facebook_daily_insights')->updateOrInsert(
                [ 'post_id' => $postId, 'date' => $date ],
                $row
            );
        }
    }

    /**
     * Save daily video insights
     */
    private function saveDailyVideoInsights($postId, $videoId, $insights)
    {
        foreach ($insights as $insight) {
            $metricName = $insight['name'];
            $values = $insight['values'] ?? [];
            
            foreach ($values as $value) {
                $endTime = $value['end_time'] ?? null;
                $metricValue = $value['value'] ?? 0;
                
                if ($endTime) {
                    DB::table('facebook_daily_video_insights')->updateOrInsert(
                        [
                            'post_id' => $postId,
                            'video_id' => $videoId,
                            'metric_name' => $metricName,
                            'date' => \Carbon\Carbon::parse($endTime)->format('Y-m-d')
                        ],
                        [
                            'metric_value' => $metricValue,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]
                    );
                }
            }
        }
    }

    /**
     * Phân tích metrics của post
     */
    private function analyzePostMetrics($post, $insights)
    {
        $metrics = [];
        foreach ($insights as $insight) {
            $value = $insight['values'][0]['value'] ?? 0;
            $metrics[$insight['name']] = $value;
        }

        $impressions = $metrics['post_impressions'] ?? 0;
        $impressionsUnique = $metrics['post_impressions_unique'] ?? 0;
        $impressionsOrganic = $metrics['post_impressions_organic'] ?? 0;
        $impressionsViral = $metrics['post_impressions_viral'] ?? 0;
        $clicks = $metrics['post_clicks'] ?? 0;
        $engagedUsers = $metrics['post_engaged_users'] ?? 0;
        $reactions = ($metrics['post_reactions_like_total'] ?? 0) + 
                    ($metrics['post_reactions_love_total'] ?? 0) + 
                    ($metrics['post_reactions_wow_total'] ?? 0) + 
                    ($metrics['post_reactions_haha_total'] ?? 0) + 
                    ($metrics['post_reactions_sorry_total'] ?? 0) + 
                    ($metrics['post_reactions_anger_total'] ?? 0);

        // Video metrics
        $videoViews = $metrics['post_video_views'] ?? 0;
        $videoViewsUnique = $metrics['post_video_views_unique'] ?? 0;
        $videoCompleteViews = $metrics['post_video_complete_views'] ?? 0;
        $videoAvgTime = $metrics['post_video_avg_time_watched'] ?? 0;
        $videoTotalTime = $metrics['post_video_view_total_time'] ?? 0;

        $ctr = $impressions > 0 ? round(($clicks / $impressions) * 100, 2) : 0;
        $engagementRate = $impressions > 0 ? round(($reactions / $impressions) * 100, 2) : 0;
        $videoCompletionRate = $videoViews > 0 ? round(($videoCompleteViews / $videoViews) * 100, 2) : 0;

        $this->info("📊 Post Analysis:");
        $this->info("   👁️ Impressions: " . number_format($impressions) . " (Unique: " . number_format($impressionsUnique) . ")");
        $this->info("   📈 Organic: " . number_format($impressionsOrganic) . " | Viral: " . number_format($impressionsViral));
        $this->info("   🖱️ Clicks: " . number_format($clicks) . " (CTR: {$ctr}%)");
        $this->info("   👥 Engaged Users: " . number_format($engagedUsers));
        $this->info("   ❤️ Reactions: " . number_format($reactions) . " (Engagement: {$engagementRate}%)");
        
        if ($videoViews > 0) {
            $this->info("   🎥 Video Views: " . number_format($videoViews) . " (Unique: " . number_format($videoViewsUnique) . ")");
            $this->info("   ⏱️ Complete Views: " . number_format($videoCompleteViews) . " ({$videoCompletionRate}%)");
            $this->info("   📏 Avg Time Watched: " . round($videoAvgTime, 1) . "s | Total Time: " . round($videoTotalTime, 1) . "s");
        }
    }

    /**
     * Phân tích video posts
     */
    private function analyzeVideoPosts($videoPosts)
    {
        $this->info("🎥 Found " . count($videoPosts) . " video posts");
        
        $totalImpressions = 0;
        $totalClicks = 0;
        $totalReactions = 0;
        $bestPerforming = null;
        $maxEngagement = 0;

        foreach ($videoPosts as $videoPost) {
            $metrics = [];
            foreach ($videoPost['post_insights'] as $insight) {
                $value = $insight['values'][0]['value'] ?? 0;
                $metrics[$insight['name']] = $value;
            }

            $impressions = $metrics['post_impressions'] ?? 0;
            $clicks = $metrics['post_clicks'] ?? 0;
            $reactions = ($metrics['post_reactions_like_total'] ?? 0) + 
                        ($metrics['post_reactions_love_total'] ?? 0) + 
                        ($metrics['post_reactions_wow_total'] ?? 0) + 
                        ($metrics['post_reactions_haha_total'] ?? 0) + 
                        ($metrics['post_reactions_sorry_total'] ?? 0) + 
                        ($metrics['post_reactions_anger_total'] ?? 0);

            // Video metrics
            $videoViews = $metrics['post_video_views'] ?? 0;
            $videoCompleteViews = $metrics['post_video_complete_views'] ?? 0;
            $videoAvgTime = $metrics['post_video_avg_time_watched'] ?? 0;

            $totalImpressions += $impressions;
            $totalClicks += $clicks;
            $totalReactions += $reactions;

            $engagementRate = $impressions > 0 ? ($reactions / $impressions) * 100 : 0;
            $videoCompletionRate = $videoViews > 0 ? ($videoCompleteViews / $videoViews) * 100 : 0;
            
            if ($engagementRate > $maxEngagement) {
                $maxEngagement = $engagementRate;
                $bestPerforming = $videoPost;
            }

            $this->info("📹 Video: {$videoPost['post_id']}");
            $this->info("   👁️ Impressions: " . number_format($impressions));
            $this->info("   🖱️ Clicks: " . number_format($clicks));
            $this->info("   ❤️ Reactions: " . number_format($reactions) . " (Engagement: " . round($engagementRate, 2) . "%)");
            $this->info("   🎥 Video Views: " . number_format($videoViews) . " (Complete: " . number_format($videoCompleteViews) . " - " . round($videoCompletionRate, 2) . "%)");
            $this->info("   ⏱️ Avg Watch Time: " . round($videoAvgTime, 1) . "s");
        }

        $avgImpressions = count($videoPosts) > 0 ? round($totalImpressions / count($videoPosts)) : 0;
        $avgClicks = count($videoPosts) > 0 ? round($totalClicks / count($videoPosts)) : 0;
        $avgReactions = count($videoPosts) > 0 ? round($totalReactions / count($videoPosts)) : 0;
        $avgEngagement = $totalImpressions > 0 ? round(($totalReactions / $totalImpressions) * 100, 2) : 0;

        $this->info("\n📊 Video Posts Summary:");
        $this->info("   📈 Average Impressions: " . number_format($avgImpressions));
        $this->info("   📈 Average Clicks: " . number_format($avgClicks));
        $this->info("   📈 Average Reactions: " . number_format($avgReactions));
        $this->info("   📈 Average Engagement Rate: {$avgEngagement}%");

        if ($bestPerforming) {
            $this->info("\n🏆 Best Performing Video:");
            $this->info("   Post ID: {$bestPerforming['post_id']}");
            $this->info("   Engagement Rate: " . round($maxEngagement, 2) . "%");
        }
    }
}