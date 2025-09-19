<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class SyncAllFacebookData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'facebook:sync-all-data 
                            {--days=30 : Number of days to sync}
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
        
        $days = $this->option('days');
        $limit = $this->option('limit');
        $force = $this->option('force');
        $userId = $this->option('user-id');
        $accessToken = $this->option('access-token');
        $pagesOnly = $this->option('pages-only');
        $postsOnly = $this->option('posts-only');
        
        $this->info("📅 Date range: {$days} days");
        $this->info("📊 Limit: {$limit} posts per page");
        $this->info("🔄 Force mode: " . ($force ? 'Yes' : 'No'));
        if ($userId) $this->info("👤 User ID: {$userId}");
        if ($accessToken) $this->info("🔑 Access Token: " . substr($accessToken, 0, 20) . "...");
        if ($pagesOnly) $this->info("📄 Pages only mode");
        if ($postsOnly) $this->info("📝 Posts only mode");
        
        $startTime = now();
        $totalSteps = 4;
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
                    '--limit' => $limit
                ];
                
                if ($userId) $fanpageParams['--user-id'] = $userId;
                if ($accessToken) $fanpageParams['--access-token'] = $accessToken;
                if ($pagesOnly) $fanpageParams['--pages-only'] = true;
                
                $this->call('facebook:sync-fanpage-posts', $fanpageParams);
                $this->info("✅ Fanpages and posts sync completed");
            }
            
            // Step 2: Sync Enhanced Post Insights (for video posts)
            if (!$pagesOnly) {
                $currentStep++;
                $this->info("\n📊 Step {$currentStep}/{$totalSteps}: Syncing Enhanced Post Insights...");
                $this->call('facebook:sync-enhanced-post-insights', [
                    '--since' => now()->subDays($days)->format('Y-m-d'),
                    '--until' => now()->format('Y-m-d'),
                    '--limit' => $limit
                ]);
                $this->info("✅ Enhanced post insights sync completed");
            }
            
            // Step 3: Sync Facebook Ads (if needed)
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
            
            // Step 4: Generate Summary Report
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
