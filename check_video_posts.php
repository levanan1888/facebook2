<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🔍 Checking video posts in database...\n\n";

// Lấy tất cả posts
$allPosts = DB::table('post_facebook_fanpage_not_ads')->get();
echo "📊 Total posts: " . $allPosts->count() . "\n";

// Kiểm tra video posts
$videoPosts = DB::table('post_facebook_fanpage_not_ads')
    ->where('attachments', 'like', '%video%')
    ->get();

echo "🎥 Posts with 'video' in attachments: " . $videoPosts->count() . "\n";

// Kiểm tra từng post
foreach ($allPosts as $post) {
    $attachments = json_decode($post->attachments, true);
    $isVideo = false;
    
    if (is_array($attachments)) {
        foreach ($attachments as $attachment) {
            if (isset($attachment['media_type']) && $attachment['media_type'] === 'video') {
                $isVideo = true;
                break;
            }
            if (isset($attachment['type']) && $attachment['type'] === 'video') {
                $isVideo = true;
                break;
            }
        }
    }
    
    if ($isVideo) {
        echo "✅ Video post found: {$post->post_id}\n";
        echo "   Created: {$post->created_time}\n";
        echo "   Attachments: " . substr($post->attachments, 0, 200) . "...\n\n";
    } else {
        echo "📄 Regular post: {$post->post_id}\n";
        echo "   Created: {$post->created_time}\n";
        echo "   Attachments: " . substr($post->attachments, 0, 100) . "...\n\n";
    }
}

echo "✅ Check completed!\n";
