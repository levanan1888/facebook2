<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Available Posts ===\n";

$posts = DB::table('post_facebook_fanpage_not_ads')
    ->select('post_id', 'created_time', 'attachments')
    ->orderBy('created_time', 'desc')
    ->limit(10)
    ->get();

foreach ($posts as $post) {
    $isVideo = strpos($post->attachments, '"media_type":"video"') !== false;
    echo "Post: {$post->post_id} | Date: {$post->created_time} | Video: " . ($isVideo ? 'Yes' : 'No') . "\n";
}

echo "\nTotal posts: " . $posts->count() . "\n";
