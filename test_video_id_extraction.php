<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== VIDEO ID EXTRACTION TEST ===\n";

// Get a video post
$videoPost = DB::table('post_facebook_fanpage_not_ads')
    ->where('attachments', 'like', '%video%')
    ->select('post_id', 'attachments')
    ->first();

if (!$videoPost) {
    echo "No video posts found\n";
    exit;
}

echo "Testing with post: {$videoPost->post_id}\n";
echo "Attachments: " . substr($videoPost->attachments, 0, 200) . "...\n\n";

// Test the extraction method
function extractVideoId($attachments)
{
    if (!$attachments) return null;
    
    $attachmentsData = json_decode($attachments, true);
    if (!isset($attachmentsData['data'])) return null;

    echo "Attachments data structure:\n";
    print_r($attachmentsData);

        foreach ($attachmentsData['data'] as $attachment) {
            echo "\nProcessing attachment:\n";
            print_r($attachment);
            
            if (isset($attachment['media_type']) && $attachment['media_type'] === 'video') {
                echo "Found video attachment\n";
                
                // Priority 1: Try to get video ID from Facebook URL (most reliable)
                if (isset($attachment['url'])) {
                    echo "Facebook URL: {$attachment['url']}\n";
                    if (preg_match('/\/videos\/(\d+)/', $attachment['url'], $matches)) {
                        echo "Found video ID from Facebook URL: {$matches[1]}\n";
                        return $matches[1];
                    }
                }
                
                // Priority 2: Try to get video ID from target.id
                if (isset($attachment['target']['id'])) {
                    echo "Found video ID from target.id: {$attachment['target']['id']}\n";
                    return $attachment['target']['id'];
                }
                
                // Priority 3: Try to get video ID from media.id
                if (isset($attachment['media']['id'])) {
                    echo "Found video ID from media.id: {$attachment['media']['id']}\n";
                    return $attachment['media']['id'];
                }
                
                // Priority 4: Try to get video ID from media source URL
                if (isset($attachment['media']['source'])) {
                    $url = $attachment['media']['source'];
                    echo "Media source URL: {$url}\n";
                    
                    // Try different patterns for video ID
                    if (preg_match('/\/videos\/(\d+)/', $url, $matches)) {
                        echo "Found video ID from /videos/ pattern: {$matches[1]}\n";
                        return $matches[1];
                    }
                    // Try to extract from Facebook CDN URL
                    if (preg_match('/vs=([a-f0-9]+)/', $url, $matches)) {
                        echo "Found video ID from vs= pattern: {$matches[1]}\n";
                        return $matches[1];
                    }
                }
            }
        }

    return null;
}

$videoId = extractVideoId($videoPost->attachments);
echo "\nFinal video ID: " . ($videoId ?: 'NOT FOUND') . "\n";
