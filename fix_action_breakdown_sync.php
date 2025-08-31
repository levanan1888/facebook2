<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\FacebookAd;
use App\Models\FacebookBreakdown;
use App\Services\FacebookAdsService;

echo "=== FIX ACTION BREAKDOWN SYNC ===\n\n";

// 1. Kiểm tra actions data hiện có
echo "1. Kiểm tra actions data từ basic insights:\n";
$api = new FacebookAdsService();
$testAd = FacebookAd::first();

if ($testAd) {
    $basicInsights = $api->getInsightsForAdWithBreakdowns($testAd->id, []);
    
    if (isset($basicInsights['data']) && !empty($basicInsights['data'])) {
        $record = $basicInsights['data'][0];
        if (isset($record['actions']) && is_array($record['actions'])) {
            echo "  ✅ Có " . count($record['actions']) . " actions\n";
            
            // Phân loại actions
            $onMetaActions = [];
            $offMetaActions = [];
            
            foreach ($record['actions'] as $action) {
                $actionType = $action['action_type'];
                
                // On-Meta actions (có thể có breakdown)
                if (in_array($actionType, ['link_click', 'post_engagement', 'page_engagement', 'video_view', 'post_reaction', 'like', 'comment'])) {
                    $onMetaActions[] = $action;
                } else {
                    $offMetaActions[] = $action;
                }
            }
            
            echo "  On-Meta actions: " . count($onMetaActions) . "\n";
            echo "  Off-Meta actions: " . count($offMetaActions) . "\n";
            
            echo "  On-Meta action types:\n";
            foreach ($onMetaActions as $action) {
                echo "    - " . $action['action_type'] . ": " . $action['value'] . "\n";
            }
        }
    }
}

// 2. Kiểm tra breakdown data hiện có
echo "\n2. Kiểm tra breakdown data hiện có:\n";
$actionBreakdownTypes = [
    'action_carousel_card_id',
    'action_carousel_card_name',
    'action_device',
    'action_destination',
    'action_target_id',
    'action_reaction',
    'action_video_sound',
    'action_video_type',
    'action_canvas_component_name'
];

foreach ($actionBreakdownTypes as $type) {
    $count = FacebookBreakdown::where('breakdown_type', $type)->count();
    echo "  - $type: $count records\n";
}

// 3. Đề xuất giải pháp
echo "\n3. Đề xuất giải pháp:\n";
echo "  ✅ Actions data đã có sẵn trong basic insights\n";
echo "  ❌ Action breakdown chỉ hoạt động cho on-Meta metrics\n";
echo "  🔧 Cần sửa logic sync để:\n";
echo "     - Lấy actions từ basic insights\n";
echo "     - Chỉ sync action breakdown cho on-Meta actions\n";
echo "     - Bỏ qua action breakdown cho off-Meta actions\n";

echo "\nHoàn thành!\n";
