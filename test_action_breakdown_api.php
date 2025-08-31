<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\FacebookAd;
use App\Services\FacebookAdsService;

echo "=== TEST ACTION BREAKDOWN API ===\n\n";

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
    
    // Test 1: Basic insights không có breakdown
    echo "1. Test basic insights (không có breakdown):\n";
    $basicInsights = $api->getInsightsForAdWithBreakdowns($testAd->id, []);
    if (isset($basicInsights['data']) && !empty($basicInsights['data'])) {
        echo "  ✅ Có basic insights data\n";
        echo "  Số records: " . count($basicInsights['data']) . "\n";
        $sample = $basicInsights['data'][0];
        echo "  Sample keys: " . implode(', ', array_keys($sample)) . "\n";
    } else {
        echo "  ❌ Không có basic insights data\n";
        if (isset($basicInsights['error'])) {
            echo "  Error: " . json_encode($basicInsights['error']) . "\n";
        }
    }
    
    echo "\n2. Test action breakdown types:\n";
    
    $actionBreakdownTypes = [
        'action_carousel_card_id',
        'action_carousel_card_name',
        'action_device',
        'action_destination'
    ];
    
    foreach ($actionBreakdownTypes as $breakdownType) {
        echo "  Testing $breakdownType:\n";
        
        try {
            $methodName = 'getInsightsWith' . str_replace('_', '', ucwords($breakdownType, '_')) . 'Breakdown';
            
            if (method_exists($api, $methodName)) {
                $result = $api->$methodName($testAd->id, 'ad');
                
                if (isset($result['data']) && !empty($result['data'])) {
                    echo "    ✅ Có data (" . count($result['data']) . " records)\n";
                    $sample = $result['data'][0];
                    echo "    Sample keys: " . implode(', ', array_keys($sample)) . "\n";
                    
                    // Kiểm tra có breakdown value không
                    if (isset($sample[$breakdownType])) {
                        echo "    Breakdown value: " . $sample[$breakdownType] . "\n";
                    } else {
                        echo "    ❌ Không có breakdown value\n";
                    }
                } else {
                    echo "    ❌ Không có data\n";
                    if (isset($result['error'])) {
                        echo "    Error: " . json_encode($result['error']) . "\n";
                    }
                }
            } else {
                echo "    ❌ Method $methodName không tồn tại\n";
            }
        } catch (Exception $e) {
            echo "    ❌ Exception: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
    // Test 3: Thử với action breakdown tổng hợp
    echo "3. Test action breakdown tổng hợp:\n";
    try {
        $actionResult = $api->getInsightsWithActionBreakdown($testAd->id, 'ad', ['action_type', 'action_device']);
        
        if (isset($actionResult['data']) && !empty($actionResult['data'])) {
            echo "  ✅ Có action breakdown data\n";
            echo "  Số records: " . count($actionResult['data']) . "\n";
            $sample = $actionResult['data'][0];
            echo "  Sample keys: " . implode(', ', array_keys($sample)) . "\n";
        } else {
            echo "  ❌ Không có action breakdown data\n";
            if (isset($actionResult['error'])) {
                echo "  Error: " . json_encode($actionResult['error']) . "\n";
            }
        }
    } catch (Exception $e) {
        echo "  ❌ Exception: " . $e->getMessage() . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Lỗi chính: " . $e->getMessage() . "\n";
}

echo "\nHoàn thành!\n";
