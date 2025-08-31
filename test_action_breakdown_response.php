<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\FacebookAd;
use App\Services\FacebookAdsService;

echo "=== TEST ACTION BREAKDOWN RESPONSE ===\n\n";

try {
    $api = new FacebookAdsService();
    $testAd = FacebookAd::first();
    
    if (!$testAd) {
        echo "❌ Không tìm thấy ad để test\n";
        exit;
    }
    
    echo "Testing với Ad ID: " . $testAd->id . "\n";
    echo "Ad Name: " . ($testAd->name ?? 'N/A') . "\n\n";
    
    // Test action breakdown với response chi tiết
    echo "1. Test action_carousel_card_id breakdown:\n";
    $result = $api->getInsightsWithActionCarouselCardIdBreakdown($testAd->id, 'ad');
    
    echo "Response structure:\n";
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
    
    if (isset($result['data']) && !empty($result['data'])) {
        echo "Data records:\n";
        foreach ($result['data'] as $index => $record) {
            echo "Record " . ($index + 1) . ":\n";
            echo json_encode($record, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
            
            // Kiểm tra có action_carousel_card_id không
            if (isset($record['action_carousel_card_id'])) {
                echo "  ✅ Có action_carousel_card_id: " . $record['action_carousel_card_id'] . "\n";
            } else {
                echo "  ❌ Không có action_carousel_card_id\n";
            }
            
            // Kiểm tra có actions array không
            if (isset($record['actions']) && is_array($record['actions'])) {
                echo "  ✅ Có actions array với " . count($record['actions']) . " items\n";
                foreach ($record['actions'] as $actionIndex => $action) {
                    echo "    Action " . ($actionIndex + 1) . ": " . json_encode($action) . "\n";
                }
            } else {
                echo "  ❌ Không có actions array\n";
            }
        }
    }
    
    // Test 2: Thử với action_device breakdown
    echo "\n2. Test action_device breakdown:\n";
    $deviceResult = $api->getInsightsWithActionDeviceBreakdown($testAd->id, 'ad');
    
    if (isset($deviceResult['data']) && !empty($deviceResult['data'])) {
        echo "  ✅ Có action_device data!\n";
        $sample = $deviceResult['data'][0];
        echo "  Sample record:\n";
        echo json_encode($sample, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
        
        if (isset($sample['action_device'])) {
            echo "  ✅ Có action_device value: " . $sample['action_device'] . "\n";
        } else {
            echo "  ❌ Không có action_device value\n";
        }
    } else {
        echo "  ❌ Không có action_device data\n";
        if (isset($deviceResult['error'])) {
            echo "  Error: " . json_encode($deviceResult['error']) . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage() . "\n";
}

echo "\nHoàn thành!\n";
