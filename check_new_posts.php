<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== NEW POSTS ANALYSIS ===\n";
echo "Posts from page 116773291373042 (newly synced):\n";

$posts = DB::table('post_facebook_fanpage_not_ads')
    ->where('page_id', '116773291373042')
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

echo "\n=== ALL POSTS BY CREATED_TIME ===\n";
$allPosts = DB::table('post_facebook_fanpage_not_ads')
    ->select('post_id', 'created_time', 'page_id')
    ->orderBy('created_time', 'desc')
    ->limit(10)
    ->get();

foreach($allPosts as $post) {
    echo "Post: {$post->post_id} - Page: {$post->page_id} - Created: {$post->created_time}\n";
}

