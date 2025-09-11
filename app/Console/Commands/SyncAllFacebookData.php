<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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
                            {--force : Force sync even if data exists}';

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
        
        $this->info("📅 Date range: {$days} days");
        $this->info("📊 Limit: {$limit} posts per page");
        $this->info("🔄 Force mode: " . ($force ? 'Yes' : 'No'));
        
        $startTime = now();
        $totalSteps = 4;
        $currentStep = 0;
        
        try {
            // Step 1: Sync Facebook Fanpages
            $currentStep++;
            $this->info("\n📋 Step {$currentStep}/{$totalSteps}: Syncing Facebook Fanpages...");
            $this->call('facebook:sync-fanpage-posts', [
                '--days' => $days,
                '--limit' => $limit
            ]);
            $this->info("✅ Fanpages sync completed");
            
            // Step 2: Sync Enhanced Post Insights (for video posts)
            $currentStep++;
            $this->info("\n📊 Step {$currentStep}/{$totalSteps}: Syncing Enhanced Post Insights...");
            $this->call('facebook:sync-enhanced-post-insights', [
                '--since' => now()->subDays($days)->format('Y-m-d'),
                '--until' => now()->format('Y-m-d'),
                '--limit' => $limit
            ]);
            $this->info("✅ Enhanced post insights sync completed");
            
            // Step 3: Sync Facebook Ads (if needed)
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
            return 1;
        }
    }
    
    /**
     * Generate summary report
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
        
        // Recent activity
        $recentPosts = DB::table('post_facebook_fanpage_not_ads')
            ->where('created_at', '>=', now()->subDays(7))
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
}
