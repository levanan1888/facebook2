<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\FacebookAd;
use App\Services\FacebookAdsService;

echo "=== TEST ALTERNATIVE ACTION DATA ===\n\n";

try {
    $api = new FacebookAdsService();
    
    // Lấy một ad để test
    $testAd = FacebookAd::first();
    if (!$testAd) {
        echo "❌ Không tìm thấy ad để test\n";
        exit;
    }
    
    echo "Testing với Ad ID: " . $testAd->id . "\n";
    echo "Ad Name: " . ($testAd->name ?? 'N/A') . "\n\n";
    
    // Test 1: Lấy actions từ basic insights
    echo "1. Test lấy actions từ basic insights:\n";
    $basicInsights = $api->getInsightsForAdWithBreakdowns($testAd->id, []);
    
    if (isset($basicInsights['data']) && !empty($basicInsights['data'])) {
        $record = $basicInsights['data'][0];
        if (isset($record['actions']) && is_array($record['actions'])) {
            echo "  ✅ Có actions data!\n";
            echo "  Actions:\n";
            foreach ($record['actions'] as $action) {
                echo "    - " . $action['action_type'] . ": " . $action['value'] . "\n";
                
                // Kiểm tra có carousel data không
                if (isset($action['carousel_card_id'])) {
                    echo "      Carousel Card ID: " . $action['carousel_card_id'] . "\n";
                }
                if (isset($action['carousel_card_name'])) {
                    echo "      Carousel Card Name: " . $action['carousel_card_name'] . "\n";
                }
            }
        } else {
            echo "  ❌ Không có actions data\n";
        }
    }
    
    echo "\n2. Test lấy ad details để xem creative info:\n";
    $adDetails = $api->getAdDetails($testAd->id);
    
    if (isset($adDetails['creative']) && !empty($adDetails['creative'])) {
        echo "  ✅ Có creative data!\n";
        echo "  Creative ID: " . ($adDetails['creative']['id'] ?? 'N/A') . "\n";
        
        if (isset($adDetails['creative']['object_story_spec'])) {
            echo "  Object Story Spec:\n";
            echo json_encode($adDetails['creative']['object_story_spec'], JSON_PRETTY_PRINT) . "\n";
        }
    } else {
        echo "  ❌ Không có creative data\n";
    }
    
    // Test 3: Thử với engagement breakdown (có thể có action data)
    echo "\n3. Test engagement breakdown:\n";
    $engagementData = $api->getAdEngagementWithBreakdowns($testAd->id);
    
    if (isset($engagementData['data']) && !empty($engagementData['data'])) {
        echo "  ✅ Có engagement data!\n";
        echo "  Số records: " . count($engagementData['data']) . "\n";
        
        $sample = $engagementData['data'][0];
        echo "  Sample keys: " . implode(', ', array_keys($sample)) . "\n";
        
        if (isset($sample['actions'])) {
            echo "  Actions data:\n";
            foreach ($sample['actions'] as $action) {
                echo "    - " . $action['action_type'] . ": " . $action['value'] . "\n";
            }
        }
    } else {
        echo "  ❌ Không có engagement data\n";
        if (isset($engagementData['error'])) {
            echo "  Error: " . json_encode($engagementData['error']) . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage() . "\n";
}

echo "\nHoàn thành!\n";
