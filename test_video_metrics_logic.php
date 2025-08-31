<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\FacebookAdInsight;
use Illuminate\Support\Facades\DB;

echo "=== Test Logic Video Metrics và Lưu vào Database ===\n";

// Simulate data từ Facebook API response trong terminal
$insight = [
    'spend' => '120177',
    'reach' => '2514',
    'impressions' => '2886',
    'clicks' => '124',
    'ctr' => '4.296604',
    'cpc' => '969.169355',
    'cpm' => '41641.372141',
    'frequency' => '1.147971',
    'unique_clicks' => '103',
    'unique_ctr' => '4.097056',
    'ad_id' => '120227235120090106',
    'actions' => [
        [
            'action_type' => 'video_view',
            'value' => '604'
        ]
    ],
    'video_30_sec_watched_actions' => [
        [
            'action_type' => 'video_view',
            'value' => '47'
        ]
    ],
    'video_avg_time_watched_actions' => [
        [
            'action_type' => 'video_view',
            'value' => '3'
        ]
    ],
    'video_p25_watched_actions' => [
        [
            'action_type' => 'video_view',
            'value' => '300'
        ]
    ],
    'video_p50_watched_actions' => [
        [
            'action_type' => 'video_view',
            'value' => '138'
        ]
    ],
    'video_p75_watched_actions' => [
        [
            'action_type' => 'video_view',
            'value' => '80'
        ]
    ],
    'video_p95_watched_actions' => [
        [
            'action_type' => 'video_view',
            'value' => '47'
        ]
    ],
    'video_p100_watched_actions' => [
        [
            'action_type' => 'video_view',
            'value' => '46'
        ]
    ],
    'date_start' => '2022-08-28',
    'date_stop' => '2025-08-28'
];

// Logic extract video metrics
$videoAvgTime = 0;
if (isset($insight['video_avg_time_watched_actions']) && is_array($insight['video_avg_time_watched_actions'])) {
    foreach ($insight['video_avg_time_watched_actions'] as $action) {
        if (isset($action['action_type']) && $action['action_type'] === 'video_view') {
            $videoAvgTime = (int)($action['value'] ?? 0);
            break; // Chỉ lấy giá trị đầu tiên
        }
    }
}

// Logic extract video_views
$videoViews = 0;
if (isset($insight['actions']) && is_array($insight['actions'])) {
    foreach ($insight['actions'] as $action) {
        if (isset($action['action_type']) && $action['action_type'] === 'video_view') {
            $videoViews += (int)($action['value'] ?? 0);
        }
    }
}

// Tính toán video_view_time
$videoViewTime = 0;
if ($videoAvgTime > 0 && $videoViews > 0) {
    $videoViewTime = $videoAvgTime * $videoViews;
}

// Extract các metrics khác
$videoP25 = 0;
if (isset($insight['video_p25_watched_actions']) && is_array($insight['video_p25_watched_actions'])) {
    foreach ($insight['video_p25_watched_actions'] as $action) {
        if (isset($action['action_type']) && $action['action_type'] === 'video_view') {
            $videoP25 = (int)($action['value'] ?? 0);
            break;
        }
    }
}

$videoP50 = 0;
if (isset($insight['video_p50_watched_actions']) && is_array($insight['video_p50_watched_actions'])) {
    foreach ($insight['video_p50_watched_actions'] as $action) {
        if (isset($action['action_type']) && $action['action_type'] === 'video_view') {
            $videoP50 = (int)($action['value'] ?? 0);
            break;
        }
    }
}

$videoP75 = 0;
if (isset($insight['video_p75_watched_actions']) && is_array($insight['video_p75_watched_actions'])) {
    foreach ($insight['video_p75_watched_actions'] as $action) {
        if (isset($action['action_type']) && $action['action_type'] === 'video_view') {
            $videoP75 = (int)($action['value'] ?? 0);
            break;
        }
    }
}

$videoP100 = 0;
if (isset($insight['video_p100_watched_actions']) && is_array($insight['video_p100_watched_actions'])) {
    foreach ($insight['video_p100_watched_actions'] as $action) {
        if (isset($action['action_type']) && $action['action_type'] === 'video_view') {
            $videoP100 = (int)($action['value'] ?? 0);
            break;
        }
    }
}

$video30Sec = 0;
if (isset($insight['video_30_sec_watched_actions']) && is_array($insight['video_30_sec_watched_actions'])) {
    foreach ($insight['video_30_sec_watched_actions'] as $action) {
        if (isset($action['action_type']) && $action['action_type'] === 'video_view') {
            $video30Sec = (int)($action['value'] ?? 0);
            break;
        }
    }
}

echo "=== Video Metrics Extracted ===\n";
echo "Video Views: {$videoViews}\n";
echo "Video Avg Time Watched: {$videoAvgTime} giây\n";
echo "Video View Time: {$videoViewTime} giây\n";
echo "Video View Time (phút): " . round($videoViewTime / 60, 2) . " phút\n";
echo "Video P25: {$videoP25}\n";
echo "Video P50: {$videoP50}\n";
echo "Video P75: {$videoP75}\n";
echo "Video P100: {$videoP100}\n";
echo "Video 30s Watched: {$video30Sec}\n";

// Lưu vào database
echo "\n=== Lưu vào Database ===\n";

try {
    // Tìm hoặc tạo mới FacebookAdInsight
    $adInsight = FacebookAdInsight::updateOrCreate(
        [
            'ad_id' => $insight['ad_id'],
            'date' => $insight['date_start']
        ],
        [
            'spend' => (float) ($insight['spend'] ?? 0),
            'reach' => (int) ($insight['reach'] ?? 0),
            'impressions' => (int) ($insight['impressions'] ?? 0),
            'clicks' => (int) ($insight['clicks'] ?? 0),
            'ctr' => (float) ($insight['ctr'] ?? 0),
            'cpc' => (float) ($insight['cpc'] ?? 0),
            'cpm' => (float) ($insight['cpm'] ?? 0),
            'frequency' => (float) ($insight['frequency'] ?? 0),
            'unique_clicks' => (int) ($insight['unique_clicks'] ?? 0),
            'unique_ctr' => (float) ($insight['unique_ctr'] ?? 0),
            
            // Video metrics
            'video_views' => $videoViews,
            'video_plays' => $videoViews, // Sử dụng video_views làm video_plays
            'video_avg_time_watched' => (float) $videoAvgTime,
            'video_view_time' => $videoViewTime,
            'video_30_sec_watched' => $video30Sec,
            'video_p25_watched_actions' => $videoP25,
            'video_p50_watched_actions' => $videoP50,
            'video_p75_watched_actions' => $videoP75,
            'video_p95_watched_actions' => 0, // Không có trong data
            'video_p100_watched_actions' => $videoP100,
            
            // Actions và breakdowns
            'actions' => json_encode($insight['actions'] ?? []),
            'action_values' => null,
            'cost_per_action_type' => null,
            'cost_per_unique_action_type' => null,
            
            // Các trường khác
            'conversions' => 0,
            'conversion_values' => 0.0,
            'cost_per_conversion' => 0.0,
            'purchase_roas' => 0.0,
            'outbound_clicks' => 0,
            'unique_outbound_clicks' => 0,
            'inline_link_clicks' => 0,
            'unique_inline_link_clicks' => 0,
            'website_clicks' => 0,
            'reactions' => 0,
            'saves' => 0,
            'hides' => 0,
            'hide_all_clicks' => 0,
            'unlikes' => 0,
            'negative_feedback' => 0,
            'engagement_rate' => 0.0,
            'thruplays' => 0,
            'breakdowns' => null,
        ]
    );

    echo "✅ Đã lưu thành công vào database!\n";
    echo "ID: {$adInsight->id}\n";
    echo "Ad ID: {$adInsight->ad_id}\n";
    echo "Date: {$adInsight->date}\n";
    
    // Kiểm tra lại data đã lưu
    echo "\n=== Kiểm tra Data đã lưu ===\n";
    $savedData = FacebookAdInsight::find($adInsight->id);
    echo "Video Views: {$savedData->video_views}\n";
    echo "Video Avg Time Watched: {$savedData->video_avg_time_watched}\n";
    echo "Video View Time: {$savedData->video_view_time}\n";
    echo "Video P25: {$savedData->video_p25_watched_actions}\n";
    echo "Video P50: {$savedData->video_p50_watched_actions}\n";
    echo "Video P75: {$savedData->video_p75_watched_actions}\n";
    echo "Video P100: {$savedData->video_p100_watched_actions}\n";
    echo "Video 30s: {$savedData->video_30_sec_watched}\n";
    
} catch (Exception $e) {
    echo "❌ Lỗi khi lưu vào database: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Test hoàn thành ===\n";
?>
