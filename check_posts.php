<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== POSTS ANALYSIS ===\n";
echo "Total posts: " . DB::table('post_facebook_fanpage_not_ads')->count() . "\n";
echo "Posts with video: " . DB::table('post_facebook_fanpage_not_ads')->where('attachments', 'like', '%video%')->count() . "\n";

echo "\n=== SAMPLE POSTS ===\n";
$posts = DB::table('post_facebook_fanpage_not_ads')
    ->select('post_id', 'created_time', 'attachments')
    ->orderBy('created_time', 'desc')
    ->limit(5)
    ->get();

foreach($posts as $post) {
    echo "Post: {$post->post_id} - Created: {$post->created_time}\n";
    if(strpos($post->attachments, 'video') !== false) {
        echo "  -> Has video\n";
    }
}

echo "\n=== DATE RANGE CHECK ===\n";
$since = '2025-09-10';
$until = '2025-09-12';
echo "Looking for posts between {$since} and {$until}\n";

$postsInRange = DB::table('post_facebook_fanpage_not_ads')
    ->whereBetween('created_time', [$since, $until])
    ->count();
echo "Posts in range: {$postsInRange}\n";

echo "\n=== RECENT POSTS (last 30 days) ===\n";
$recentPosts = DB::table('post_facebook_fanpage_not_ads')
    ->where('created_time', '>=', now()->subDays(30)->format('Y-m-d'))
    ->select('post_id', 'created_time')
    ->orderBy('created_time', 'desc')
    ->limit(3)
    ->get();

foreach($recentPosts as $post) {
    echo "Post: {$post->post_id} - Created: {$post->created_time}\n";
}