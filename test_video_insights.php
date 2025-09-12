<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== VIDEO INSIGHTS ANALYSIS ===\n";

// Check current video insights count
$videoInsightsCount = DB::table('facebook_video_insights')->count();
echo "Current video insights count: {$videoInsightsCount}\n";

// Check video posts
$videoPosts = DB::table('post_facebook_fanpage_not_ads')
    ->where('attachments', 'like', '%video%')
    ->select('id', 'post_id', 'page_id', 'created_time', 'attachments')
    ->orderBy('created_time', 'desc')
    ->limit(5)
    ->get();

echo "\n=== VIDEO POSTS ===\n";
foreach($videoPosts as $post) {
    echo "Post: {$post->post_id} - Page: {$post->page_id} - Created: {$post->created_time}\n";
    
    // Check if this post has video insights
    $hasInsights = DB::table('facebook_video_insights')
        ->where('post_id', $post->post_id)
        ->exists();
    echo "  -> Has video insights: " . ($hasInsights ? 'Yes' : 'No') . "\n";
    
    // Try to extract video ID from attachments
    $attachmentsData = json_decode($post->attachments, true);
    if (isset($attachmentsData['data'])) {
        foreach ($attachmentsData['data'] as $attachment) {
            if (isset($attachment['media_type']) && $attachment['media_type'] === 'video') {
                echo "  -> Media type: video\n";
                if (isset($attachment['media']['source'])) {
                    $url = $attachment['media']['source'];
                    if (preg_match('/\/videos\/(\d+)/', $url, $matches)) {
                        echo "  -> Video ID: {$matches[1]}\n";
                    } else {
                        echo "  -> Video URL: {$url}\n";
                    }
                }
                break;
            }
        }
    }
    echo "\n";
}

// Check daily video insights
$dailyVideoInsightsCount = DB::table('facebook_daily_video_insights')->count();
echo "Daily video insights count: {$dailyVideoInsightsCount}\n";

// Check recent video insights
$recentVideoInsights = DB::table('facebook_video_insights')
    ->orderBy('created_at', 'desc')
    ->limit(3)
    ->get();

echo "\n=== RECENT VIDEO INSIGHTS ===\n";
foreach($recentVideoInsights as $insight) {
    echo "Post: {$insight->post_id} - Video: {$insight->video_id} - Created: {$insight->created_at}\n";
    echo "  -> Views: {$insight->video_views_unique}\n";
    echo "  -> Complete views: {$insight->video_complete_views}\n";
}

