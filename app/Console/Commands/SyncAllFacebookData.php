<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class SyncAllFacebookData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'facebook:sync-all-data 
                            {--days=30 : Number of days to sync (ignored if since/until provided)}
                            {--since= : Start date (Y-m-d)}
                            {--until= : End date (Y-m-d)}
                            {--limit=100 : Limit number of posts to sync}
                            {--force : Force sync even if data exists}
                            {--user-id= : Facebook user ID (optional)}
                            {--access-token= : Facebook access token (optional)}
                            {--pages-only : Only sync pages, skip posts}
                            {--posts-only : Only sync posts, skip pages}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync all Facebook data: fanpages, posts, and insights in one command';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Starting comprehensive Facebook data sync...');
        
        $days = (int) $this->option('days');
        $sinceOpt = $this->option('since');
        $untilOpt = $this->option('until');
        $limit = $this->option('limit');
        $force = $this->option('force');
        $userId = $this->option('user-id');
        $accessToken = $this->option('access-token');
        $pagesOnly = $this->option('pages-only');
        $postsOnly = $this->option('posts-only');
        
        // Resolve date range: since/until take precedence over days
        if ($sinceOpt || $untilOpt) {
            $until = $untilOpt ? Carbon::parse((string) $untilOpt) : now();
            $since = $sinceOpt ? Carbon::parse((string) $sinceOpt) : $until->copy()->subDays(max($days, 1) - 1);
            if ($since->gt($until)) {
                [$since, $until] = [$until->copy(), $since->copy()];
            }
            $days = $since->diffInDays($until) + 1;
        } else {
            $until = now();
            $since = $until->copy()->subDays(max($days, 1) - 1);
        }
        
        $this->info("📅 Date range: {$since->toDateString()} → {$until->toDateString()} ({$days} days)");
        $this->info("📊 Limit: {$limit} posts per page");
        $this->info("🔄 Force mode: " . ($force ? 'Yes' : 'No'));
        if ($userId) $this->info("👤 User ID: {$userId}");
        if ($accessToken) $this->info("🔑 Access Token: " . substr($accessToken, 0, 20) . "...");
        if ($pagesOnly) $this->info("📄 Pages only mode");
        if ($postsOnly) $this->info("📝 Posts only mode");
        
        $startTime = now();
        $totalSteps = 6;
        $currentStep = 0;
        
        try {
            // Step 0: Validate access tokens
            $this->info("\n🔍 Validating access tokens...");
            $this->validateAccessTokens();
            
            // Step 1: Sync Facebook Fanpages and Posts
            if (!$postsOnly) {
                $currentStep++;
                $this->info("\n📋 Step {$currentStep}/{$totalSteps}: Syncing Facebook Fanpages and Posts...");
                
                $fanpageParams = [
                    '--days' => $days,
                    '--limit' => $limit,
                    '--since' => $since->toDateString(),
                    '--until' => $until->toDateString(),
                ];
                
                if ($userId) $fanpageParams['--user-id'] = $userId;
                if ($accessToken) $fanpageParams['--access-token'] = $accessToken;
                if ($pagesOnly) $fanpageParams['--pages-only'] = true;
                
                $this->call('facebook:sync-fanpage-posts', $fanpageParams);
                $this->info("✅ Fanpages and posts sync completed");
            }
            
            // Step 2: Sync Page Images (profile & cover)
            if (!$postsOnly) {
                $currentStep++;
                $this->info("\n🖼️ Step {$currentStep}/{$totalSteps}: Syncing Page Images (profile & cover)...");
                $this->syncPageImages((int) $limit);
                $this->info("✅ Page images sync completed");
            }
            
            // Step 3: Sync Enhanced Post Insights (for video posts)
            if (!$pagesOnly) {
                $currentStep++;
                $this->info("\n📊 Step {$currentStep}/{$totalSteps}: Syncing Enhanced Post Insights...");
                $this->call('facebook:sync-enhanced-post-insights', [
                    '--since' => $since->toDateString(),
                    '--until' => $until->toDateString(),
                    '--limit' => $limit
                ]);
                $this->info("✅ Enhanced post insights sync completed");
            }
            
            // Step 4: Sync Facebook Ads (if needed)
            if (!$pagesOnly) {
                $currentStep++;
                $this->info("\n💰 Step {$currentStep}/{$totalSteps}: Syncing Facebook Ads...");
                try {
                    $this->call('facebook:sync-ads-with-video-metrics', [
                        '--days' => $days,
                        '--limit' => $limit
                    ]);
                    $this->info("✅ Facebook ads sync completed");
                } catch (\Exception $e) {
                    $this->warn("⚠️ Facebook ads sync failed: " . $e->getMessage());
                }
            }
            
            // Step 5: Sync Page Messaging Insights (daily) AFTER ads to ensure Paid is populated
            if (!$pagesOnly) {
                $currentStep++;
                $this->info("\n💬 Step {$currentStep}/{$totalSteps}: Syncing Page Messaging Insights (daily)...");
                $this->syncPageMessagingInsights($since->toDateString(), $until->toDateString(), (int) $limit);
                $this->info("✅ Page messaging insights sync completed");
            }
            
            // Step 6: Generate Summary Report
            $currentStep++;
            $this->info("\n📈 Step {$currentStep}/{$totalSteps}: Generating Summary Report...");
            $this->generateSummaryReport();
            
            $endTime = now();
            $duration = $startTime->diffInMinutes($endTime);
            
            $this->info("\n🎉 === COMPREHENSIVE SYNC COMPLETED ===");
            $this->info("⏱️ Total duration: {$duration} minutes");
            $this->info("📅 Completed at: " . $endTime->format('Y-m-d H:i:s'));
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("❌ Sync failed: " . $e->getMessage());
            $this->error("💡 Try running with --force option or check your access tokens");
            return 1;
        }
    }

    /**
     * Sync Page messaging insights (period=day) for all pages with tokens
     * Metrics per v23: page_messages_new_conversations_unique, page_messages_total_messaging_connections, page_messages_active_threads_unique
     * We derive paid vs organic using ad actions (messaging_conversation_started_7d) when available in our insights table
     *
     * @param int $days       Number of days back to fetch
     * @param int $limitPages Limit number of pages to process
     */
    private function syncPageMessagingInsights(string $sinceDate, string $untilDate, int $limitPages = 100): void
    {
        // Normalize input dates
        try {
            $since = Carbon::parse($sinceDate)->toDateString();
        } catch (\Throwable $e) {
            $since = now()->subDays(29)->toDateString();
        }
        try {
            $until = Carbon::parse($untilDate)->toDateString();
        } catch (\Throwable $e) {
            $until = now()->toDateString();
        }

        $pages = DB::table('facebook_fanpage')
            ->whereNotNull('access_token')
            ->where('access_token', '!=', '')
            ->limit($limitPages)
            ->get(['page_id', 'access_token']);

        if ($pages->isEmpty()) {
            $this->warn('No pages with access_token to sync messaging insights.');
            return;
        }

        foreach ($pages as $p) {
            $pageId = $p->page_id;
            $token = $p->access_token;

            // Fetch Page Insights daily
            $metrics = [
                'page_messages_new_conversations_unique',
                'page_messages_total_messaging_connections',
                'page_messages_active_threads_unique',
            ];
            $url = sprintf('https://graph.facebook.com/v23.0/%s/insights', $pageId);
            try {
                $response = Http::timeout(30)->get($url, [
                    'metric' => implode(',', $metrics),
                    'period' => 'day',
                    'since' => $since,
                    'until' => $until,
                    'access_token' => $token,
                ]);
            } catch (\Exception $e) {
                $this->warn("Failed to fetch page insights for {$pageId}: {$e->getMessage()}");
                continue;
            }

            if (!$response->successful()) {
                $this->warn("Non-200 when fetching page insights for {$pageId}: " . $response->status());
                continue;
            }

            $data = $response->json('data') ?? [];
            if (!is_array($data) || empty($data)) {
                $this->warn("Empty insights for page {$pageId}");
                continue;
            }

            // Normalize values by date
            $byDate = [];
            foreach ($data as $metric) {
                $name = $metric['name'] ?? '';
                $values = $metric['values'] ?? [];
                foreach ($values as $row) {
                    $endTime = $row['end_time'] ?? null;
                    $value = (int) ($row['value'] ?? 0);
                    if (!$endTime) { continue; }
                    // For period=day, Facebook returns end_time as the end of the period (exclusive),
                    // typically 00:00:00 of the next day in UTC. Map data to the actual day by subtracting 1 day.
                    // Then format as date string (no timezone shift to avoid off-by-one issues).
                    try {
                        $dt = Carbon::parse($endTime);
                        // If the timestamp is exactly at start-of-day (00:00:00), it represents the previous day.
                        if ($dt->format('H:i:s') === '00:00:00') {
                            $dt = $dt->subDay();
                        } else {
                            // Safety: still subtract a day to align with FB Insights convention.
                            $dt = $dt->subDay();
                        }
                        $d = $dt->toDateString();
                    } catch (\Throwable $e) {
                        // Fallback to raw date portion
                        $d = substr((string) $endTime, 0, 10);
                    }
                    $byDate[$d] = $byDate[$d] ?? [
                        'messages_new_conversations' => 0,
                        'messages_total_connections' => 0,
                        'messages_active_threads' => 0,
                    ];
                    if ($name === 'page_messages_new_conversations_unique') {
                        $byDate[$d]['messages_new_conversations'] = $value;
                    } elseif ($name === 'page_messages_total_messaging_connections') {
                        $byDate[$d]['messages_total_connections'] = $value;
                    } elseif ($name === 'page_messages_active_threads_unique') {
                        $byDate[$d]['messages_active_threads'] = $value;
                    }
                }
            }

            if (empty($byDate)) { continue; }

            // Strict mode: do not infer/approximate totals. Log when total is missing while new conversations exist.
            foreach ($byDate as $d => $vals) {
                $hasNew = isset($vals['messages_new_conversations']) && (int) $vals['messages_new_conversations'] > 0;
                $hasTotal = isset($vals['messages_total_connections']) && (int) $vals['messages_total_connections'] > 0;
                if ($hasNew && !$hasTotal) {
                    $this->warn("Missing page_messages_total_messaging_connections for {$pageId} on {$d} while new_conversations>0. Keeping total=0 per docs.");
                }
            }

            // Paid conversations per day: use raw persisted from Ads only (no fallback)
            $paidByDate = DB::table('facebook_page_daily_insights')
                ->where('page_id', $pageId)
                ->whereBetween('date', [$since, $until])
                ->pluck('ads_messaging_conversation_started', 'date');

            foreach ($byDate as $date => $vals) {
                $paid = (int) ($paidByDate[$date] ?? 0);
                $organic = max(($vals['messages_new_conversations'] ?? 0) - $paid, 0);

                DB::table('facebook_page_daily_insights')->updateOrInsert(
                    ['page_id' => $pageId, 'date' => $date],
                    [
                        'messages_new_conversations' => (int) ($vals['messages_new_conversations'] ?? 0),
                        'messages_total_connections' => (int) ($vals['messages_total_connections'] ?? 0),
                        'messages_active_threads' => (int) ($vals['messages_active_threads'] ?? 0),
                        'messages_paid_conversations' => $paid,
                        'messages_organic_conversations' => $organic,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }

            // Update facebook_fanpage snapshot only (no 28d aggregation)
            $latestDay = array_key_last($byDate);
            if ($latestDay) {
                $snap = $byDate[$latestDay];
                $paid = (int) ($paidByDate[$latestDay] ?? 0);
                $organic = max(($snap['messages_new_conversations'] ?? 0) - $paid, 0);

                DB::table('facebook_fanpage')->where('page_id', $pageId)->update([
                    'msg_new_conversations_day' => (int) ($snap['messages_new_conversations'] ?? 0),
                    'msg_paid_conversations_day' => $paid,
                    'msg_organic_conversations_day' => $organic,
                    'messages_last_synced_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Sync page profile picture and cover photo URLs for pages with tokens.
     */
    private function syncPageImages(int $limitPages = 100): void
    {
        $pages = DB::table('facebook_fanpage')
            ->whereNotNull('access_token')
            ->where('access_token', '!=', '')
            ->limit($limitPages)
            ->get(['page_id', 'access_token']);

        if ($pages->isEmpty()) {
            $this->warn('No pages with access_token to sync images.');
            return;
        }

        foreach ($pages as $p) {
            $pageId = $p->page_id;
            $token = $p->access_token;

            try {
                // Fetch picture and cover in one request
                $url = sprintf('https://graph.facebook.com/v23.0/%s', $pageId);
                $response = Http::timeout(20)->get($url, [
                    'fields' => 'picture{url},cover',
                    'access_token' => $token,
                ]);
            } catch (\Exception $e) {
                $this->warn("Failed to fetch page images for {$pageId}: {$e->getMessage()}");
                continue;
            }

            if (!$response->successful()) {
                $this->warn("Non-200 when fetching page images for {$pageId}: " . $response->status());
                continue;
            }

            $body = $response->json();
            $pictureUrl = $body['picture']['data']['url'] ?? null;
            $coverUrl = $body['cover']['source'] ?? null;

            $update = [
                'updated_at' => now(),
            ];
            if ($pictureUrl) {
                $update['profile_picture_url'] = $pictureUrl;
            }
            if ($coverUrl) {
                $update['cover_photo_url'] = $coverUrl;
            }

            if (count($update) > 1) {
                DB::table('facebook_fanpage')->where('page_id', $pageId)->update($update);
            }
        }
    }
    
    /**
     * Generate summary report
     * 
     * Note: created_time = when the post was created on Facebook
     *       created_at = when the record was synced to our database
     */
    private function generateSummaryReport()
    {
        $this->info("\n📊 === DATA SUMMARY REPORT ===");
        
        // Fanpage statistics
        $fanpageCount = DB::table('facebook_fanpage')->count();
        $fanpageWithTokens = DB::table('facebook_fanpage')
            ->whereNotNull('access_token')
            ->where('access_token', '!=', '')
            ->count();
        
        $this->info("📄 Facebook Fanpages:");
        $this->info("   Total: {$fanpageCount}");
        $this->info("   With access tokens: {$fanpageWithTokens}");
        
        // Posts statistics
        $totalPosts = DB::table('post_facebook_fanpage_not_ads')->count();
        $videoPosts = DB::table('post_facebook_fanpage_not_ads')
            ->where('attachments', 'like', '%video%')
            ->count();
        $postsWithInsights = DB::table('post_facebook_fanpage_not_ads')
            ->whereNotNull('insights_synced_at')
            ->count();
        
        $this->info("\n📝 Posts:");
        $this->info("   Total posts: {$totalPosts}");
        $this->info("   Video posts: {$videoPosts}");
        $this->info("   Posts with insights: {$postsWithInsights}");
        
        // Video insights statistics
        $videoInsightsCount = DB::table('facebook_video_insights')->count();
        
        $this->info("\n🎥 Video Insights:");
        $this->info("   Total video insights: {$videoInsightsCount}");
        
        // Facebook ads statistics
        try {
            $adsCount = DB::table('facebook_ads')->count();
            $campaignsCount = DB::table('facebook_campaigns')->count();
            
            $this->info("\n💰 Facebook Ads:");
            $this->info("   Total ads: {$adsCount}");
            $this->info("   Total campaigns: {$campaignsCount}");
        } catch (\Exception $e) {
            $this->warn("   Ads data not available");
        }
        
        // Recent activity - Use created_time (post creation time) not created_at (sync time)
        $recentPosts = DB::table('post_facebook_fanpage_not_ads')
            ->where('created_time', '>=', now()->subDays(7))
            ->count();
        
        $this->info("\n📅 Recent Activity (Last 7 days):");
        $this->info("   New posts: {$recentPosts}");
        
        // Top performing video posts
        $topVideos = DB::table('post_facebook_fanpage_not_ads')
            ->where('attachments', 'like', '%video%')
            ->whereNotNull('post_impressions')
            ->orderBy('post_impressions', 'desc')
            ->limit(3)
            ->get(['post_id', 'post_impressions', 'post_reactions', 'post_video_views']);
        
        if ($topVideos->count() > 0) {
            $this->info("\n🏆 Top Performing Video Posts:");
            foreach ($topVideos as $index => $video) {
                $this->info("   " . ($index + 1) . ". Post: {$video->post_id}");
                $this->info("      Impressions: " . number_format($video->post_impressions ?? 0));
                $this->info("      Reactions: " . number_format($video->post_reactions ?? 0));
                $this->info("      Video Views: " . number_format($video->post_video_views ?? 0));
            }
        }
        
        $this->info("\n✅ Summary report completed");
    }
    
    /**
     * Validate access tokens before starting sync
     */
    private function validateAccessTokens()
    {
        $validTokens = 0;
        $totalTokens = 0;
        
        // Check config token
        $configToken = config('services.facebook.ads_token');
        if ($configToken) {
            $totalTokens++;
            if ($this->isTokenValid($configToken)) {
                $validTokens++;
                $this->info("✅ Config token is valid");
            } else {
                $this->warn("❌ Config token is invalid");
            }
        }
        
        // Check database tokens
        $dbTokens = DB::table('facebook_fanpage')
            ->whereNotNull('access_token')
            ->where('access_token', '!=', '')
            ->pluck('access_token')
            ->unique();
            
        foreach ($dbTokens as $token) {
            $totalTokens++;
            if ($this->isTokenValid($token)) {
                $validTokens++;
            }
        }
        
        $this->info("🔑 Token validation: {$validTokens}/{$totalTokens} tokens are valid");
        
        if ($validTokens === 0) {
            $this->error("❌ No valid access tokens found!");
            $this->error("💡 Please check your .env file and database configuration");
            throw new \Exception("No valid access tokens available");
        }
        
        if ($validTokens < $totalTokens) {
            $this->warn("⚠️ Some tokens are invalid. Consider refreshing them.");
        }
    }
    
    /**
     * Check if a token is valid
     */
    private function isTokenValid(string $token): bool
    {
        try {
            $response = Http::timeout(10)->get('https://graph.facebook.com/v23.0/me', [
                'access_token' => $token
            ]);
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}
