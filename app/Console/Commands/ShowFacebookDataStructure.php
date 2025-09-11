<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ShowFacebookDataStructure extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'facebook:show-data-structure';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show Facebook data structure and sample data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('📊 === FACEBOOK DATA STRUCTURE OVERVIEW ===');
        
        // 1. Fanpage Statistics
        $this->showFanpageStats();
        
        // 2. Posts Statistics
        $this->showPostsStats();
        
        // 3. Insights Statistics
        $this->showInsightsStats();
        
        // 4. Video Statistics
        $this->showVideoStats();
        
        // 5. Daily Insights Statistics
        $this->showDailyInsightsStats();
        
        // 6. Sample Data
        $this->showSampleData();
        
        $this->info("\n✅ Data structure overview completed!");
    }
    
    private function showFanpageStats()
    {
        $this->info("\n📄 === FANPAGE STATISTICS ===");
        
        $totalPages = DB::table('facebook_fanpage')->count();
        $pagesWithTokens = DB::table('facebook_fanpage')
            ->whereNotNull('access_token')
            ->where('access_token', '!=', '')
            ->count();
        $verifiedPages = DB::table('facebook_fanpage')
            ->where('is_verified', true)
            ->count();
        
        $this->info("Total Pages: {$totalPages}");
        $this->info("Pages with Access Tokens: {$pagesWithTokens}");
        $this->info("Verified Pages: {$verifiedPages}");
        
        // Show sample pages
        $samplePages = DB::table('facebook_fanpage')
            ->select('name', 'page_id', 'fan_count', 'followers_count', 'is_verified')
            ->limit(3)
            ->get();
            
        if ($samplePages->count() > 0) {
            $this->info("\nSample Pages:");
            foreach ($samplePages as $page) {
                $this->info("  - {$page->name} (ID: {$page->page_id})");
                $this->info("    Fans: " . number_format($page->fan_count ?? 0) . 
                           " | Followers: " . number_format($page->followers_count ?? 0) . 
                           " | Verified: " . ($page->is_verified ? 'Yes' : 'No'));
            }
        }
    }
    
    private function showPostsStats()
    {
        $this->info("\n📝 === POSTS STATISTICS ===");
        
        $totalPosts = DB::table('post_facebook_fanpage_not_ads')->count();
        $videoPosts = DB::table('post_facebook_fanpage_not_ads')
            ->where('attachments', 'like', '%video%')
            ->count();
        $postsWithInsights = DB::table('post_facebook_fanpage_not_ads')
            ->whereNotNull('insights_synced_at')
            ->count();
        
        $this->info("Total Posts: {$totalPosts}");
        $this->info("Video Posts: {$videoPosts}");
        $this->info("Posts with Insights: {$postsWithInsights}");
        
        // Posts by type
        $postsByType = DB::table('post_facebook_fanpage_not_ads')
            ->selectRaw("
                CASE 
                    WHEN attachments LIKE '%\"media_type\":\"video\"%' THEN 'Video'
                    WHEN attachments LIKE '%\"media_type\":\"photo\"%' THEN 'Image'
                    WHEN attachments LIKE '%\"media_type\":\"link\"%' THEN 'Link'
                    ELSE 'Text'
                END as post_type,
                COUNT(*) as count
            ")
            ->groupBy('post_type')
            ->get();
            
        if ($postsByType->count() > 0) {
            $this->info("\nPosts by Type:");
            foreach ($postsByType as $type) {
                $this->info("  - {$type->post_type}: {$type->count}");
            }
        }
    }
    
    private function showInsightsStats()
    {
        $this->info("\n📊 === INSIGHTS STATISTICS ===");
        
        $postsWithMetrics = DB::table('post_facebook_fanpage_not_ads')
            ->whereNotNull('post_impressions')
            ->where('post_impressions', '>', 0)
            ->count();
            
        $totalImpressions = DB::table('post_facebook_fanpage_not_ads')
            ->whereNotNull('post_impressions')
            ->sum('post_impressions');
            
        $totalClicks = DB::table('post_facebook_fanpage_not_ads')
            ->whereNotNull('post_clicks')
            ->sum('post_clicks');
            
        $totalReactions = DB::table('post_facebook_fanpage_not_ads')
            ->whereNotNull('post_reactions')
            ->sum('post_reactions');
        
        $this->info("Posts with Metrics: {$postsWithMetrics}");
        $this->info("Total Impressions: " . number_format($totalImpressions ?? 0));
        $this->info("Total Clicks: " . number_format($totalClicks ?? 0));
        $this->info("Total Reactions: " . number_format($totalReactions ?? 0));
        
        if ($totalImpressions > 0) {
            $ctr = round(($totalClicks / $totalImpressions) * 100, 2);
            $engagementRate = round(($totalReactions / $totalImpressions) * 100, 2);
            $this->info("Average CTR: {$ctr}%");
            $this->info("Average Engagement Rate: {$engagementRate}%");
        }
    }
    
    private function showVideoStats()
    {
        $this->info("\n🎥 === VIDEO STATISTICS ===");
        
        $videoInsightsCount = DB::table('facebook_video_insights')->count();
        $totalVideoViews = DB::table('post_facebook_fanpage_not_ads')
            ->whereNotNull('post_video_views')
            ->sum('post_video_views');
        $totalCompleteViews = DB::table('post_facebook_fanpage_not_ads')
            ->whereNotNull('post_video_complete_views')
            ->sum('post_video_complete_views');
        
        $this->info("Video Insights Records: {$videoInsightsCount}");
        $this->info("Total Video Views: " . number_format($totalVideoViews ?? 0));
        $this->info("Total Complete Views: " . number_format($totalCompleteViews ?? 0));
        
        if ($totalVideoViews > 0) {
            $completionRate = round(($totalCompleteViews / $totalVideoViews) * 100, 2);
            $this->info("Average Completion Rate: {$completionRate}%");
        }
    }
    
    private function showDailyInsightsStats()
    {
        $this->info("\n📅 === DAILY INSIGHTS STATISTICS ===");
        
        $dailyInsightsCount = DB::table('facebook_daily_insights')->count();
        $dailyVideoInsightsCount = DB::table('facebook_daily_video_insights')->count();
        
        $this->info("Daily Insights Records: {$dailyInsightsCount}");
        $this->info("Daily Video Insights Records: {$dailyVideoInsightsCount}");
        
        if ($dailyInsightsCount > 0) {
            $dateRange = DB::table('facebook_daily_insights')
                ->selectRaw('MIN(date) as min_date, MAX(date) as max_date')
                ->first();
            $this->info("Date Range: {$dateRange->min_date} to {$dateRange->max_date}");
        }
    }
    
    private function showSampleData()
    {
        $this->info("\n📋 === SAMPLE DATA ===");
        
        // Top performing posts
        $topPosts = DB::table('post_facebook_fanpage_not_ads')
            ->select('post_id', 'post_impressions', 'post_clicks', 'post_reactions', 'created_time')
            ->whereNotNull('post_impressions')
            ->where('post_impressions', '>', 0)
            ->orderBy('post_impressions', 'desc')
            ->limit(3)
            ->get();
            
        if ($topPosts->count() > 0) {
            $this->info("\nTop Performing Posts:");
            foreach ($topPosts as $index => $post) {
                $this->info("  " . ($index + 1) . ". Post: {$post->post_id}");
                $this->info("     Impressions: " . number_format($post->post_impressions ?? 0));
                $this->info("     Clicks: " . number_format($post->post_clicks ?? 0));
                $this->info("     Reactions: " . number_format($post->post_reactions ?? 0));
                $this->info("     Created: {$post->created_time}");
            }
        }
        
        // Recent activity
        $recentPosts = DB::table('post_facebook_fanpage_not_ads')
            ->where('created_time', '>=', now()->subDays(7)->format('Y-m-d'))
            ->count();
            
        $this->info("\nRecent Activity (Last 7 days):");
        $this->info("  New Posts: {$recentPosts}");
        
        // Database tables info
        $this->info("\nDatabase Tables:");
        $tables = [
            'facebook_fanpage' => 'Fanpage information',
            'post_facebook_fanpage_not_ads' => 'Posts and basic metrics',
            'facebook_daily_insights' => 'Daily post insights',
            'facebook_daily_video_insights' => 'Daily video insights',
            'facebook_video_insights' => 'Video insights summary'
        ];
        
        foreach ($tables as $table => $description) {
            $count = DB::table($table)->count();
            $this->info("  - {$table}: {$count} records ({$description})");
        }
    }
}
