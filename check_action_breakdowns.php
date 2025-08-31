<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\FacebookBreakdown;
use App\Models\FacebookAd;
use App\Services\FacebookAdsService;

echo "=== CHECK ACTION BREAKDOWNS ===\n\n";

// Kiểm tra action breakdown types hiện có
echo "1. Kiểm tra action breakdown types trong database:\n";
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

// Kiểm tra một số records mẫu
echo "\n2. Kiểm tra mẫu data:\n";
$sampleBreakdowns = FacebookBreakdown::whereIn('breakdown_type', $actionBreakdownTypes)
    ->limit(5)
    ->get();

foreach ($sampleBreakdowns as $breakdown) {
    echo "  Breakdown ID: " . $breakdown->id . "\n";
    echo "  Type: " . $breakdown->breakdown_type . "\n";
    echo "  Value: " . $breakdown->breakdown_value . "\n";
    
    $metrics = json_decode($breakdown->metrics, true);
    if (is_array($metrics)) {
        echo "  Available keys: " . implode(', ', array_keys($metrics)) . "\n";
    }
    echo "\n";
}

// Test API call để xem có lấy được action breakdown data không
echo "3. Test API call cho action breakdown:\n";
try {
    $api = new FacebookAdsService();
    
    // Lấy một ad để test
    $testAd = FacebookAd::first();
    if ($testAd) {
        echo "  Testing với Ad ID: " . $testAd->id . "\n";
        
        // Test action breakdown call
        $actionBreakdownData = $api->getInsightsWithActionCarouselCardIdBreakdown($testAd->id, 'ad');
        
        if (isset($actionBreakdownData['data']) && !empty($actionBreakdownData['data'])) {
            echo "  ✅ Có action breakdown data!\n";
            echo "  Số records: " . count($actionBreakdownData['data']) . "\n";
            
            // Hiển thị mẫu data
            $sample = $actionBreakdownData['data'][0];
            echo "  Sample data keys: " . implode(', ', array_keys($sample)) . "\n";
            
            if (isset($sample['action_carousel_card_id'])) {
                echo "  action_carousel_card_id: " . $sample['action_carousel_card_id'] . "\n";
            }
            if (isset($sample['carousel_card_id'])) {
                echo "  carousel_card_id: " . $sample['carousel_card_id'] . "\n";
            }
            if (isset($sample['carousel_card_name'])) {
                echo "  carousel_card_name: " . $sample['carousel_card_name'] . "\n";
            }
        } else {
            echo "  ❌ Không có action breakdown data\n";
        }
    } else {
        echo "  ❌ Không tìm thấy ad để test\n";
    }
    
} catch (Exception $e) {
    echo "  ❌ Lỗi API: " . $e->getMessage() . "\n";
}

echo "\nHoàn thành!\n";
