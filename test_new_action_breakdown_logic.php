<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\FacebookAd;
use App\Models\FacebookBreakdown;
use App\Services\FacebookAdsService;

echo "=== TEST NEW ACTION BREAKDOWN LOGIC ===\n\n";

try {
    $api = new FacebookAdsService();
    $testAd = FacebookAd::first();
    
    if (!$testAd) {
        echo "❌ Không tìm thấy ad để test\n";
        exit;
    }
    
    echo "Testing với Ad ID: " . $testAd->id . "\n";
    echo "Ad Name: " . ($testAd->name ?? 'N/A') . "\n\n";
    
    // Test action_device breakdown
    echo "1. Test action_device breakdown với logic mới:\n";
    $deviceResult = $api->getInsightsWithActionDeviceBreakdown($testAd->id, 'ad');
    
    if (isset($deviceResult['data']) && !empty($deviceResult['data'])) {
        $sample = $deviceResult['data'][0];
        echo "  ✅ Có action_device data!\n";
        
        // Simulate logic mới
        if (isset($sample['actions']) && is_array($sample['actions'])) {
            echo "  ✅ Có actions array với " . count($sample['actions']) . " items\n";
            
            $breakdownGroups = [];
            
            foreach ($sample['actions'] as $action) {
                $breakdownValue = $action['action_device'] ?? null;
                
                if ($breakdownValue !== null) {
                    if (!isset($breakdownGroups[$breakdownValue])) {
                        $breakdownGroups[$breakdownValue] = [
                            'breakdown_value' => $breakdownValue,
                            'actions' => [],
                            'metrics' => $sample
                        ];
                    }
                    $breakdownGroups[$breakdownValue]['actions'][] = $action;
                }
            }
            
            echo "  ✅ Tìm thấy " . count($breakdownGroups) . " breakdown groups:\n";
            foreach ($breakdownGroups as $device => $group) {
                echo "    - $device: " . count($group['actions']) . " actions\n";
            }
        }
    }
    
    // Test 2: Kiểm tra breakdown data hiện có
    echo "\n2. Kiểm tra breakdown data hiện có:\n";
    $actionBreakdownTypes = [
        'action_device',
        'action_destination', 
        'action_carousel_card_id',
        'action_carousel_card_name'
    ];
    
    foreach ($actionBreakdownTypes as $type) {
        $count = FacebookBreakdown::where('breakdown_type', $type)->count();
        echo "  - $type: $count records\n";
        
        if ($count > 0) {
            $sample = FacebookBreakdown::where('breakdown_type', $type)->first();
            echo "    Sample value: " . $sample->breakdown_value . "\n";
        }
    }
    
    // Test 3: Simulate sync với logic mới
    echo "\n3. Simulate sync với logic mới:\n";
    if (isset($deviceResult['data']) && !empty($deviceResult['data'])) {
        $row = $deviceResult['data'][0];
        
        if (isset($row['actions']) && is_array($row['actions'])) {
            $breakdownGroups = [];
            
            foreach ($row['actions'] as $action) {
                $breakdownValue = $action['action_device'] ?? null;
                
                if ($breakdownValue !== null) {
                    if (!isset($breakdownGroups[$breakdownValue])) {
                        $breakdownGroups[$breakdownValue] = [
                            'breakdown_value' => $breakdownValue,
                            'actions' => [],
                            'metrics' => $row
                        ];
                    }
                    $breakdownGroups[$breakdownValue]['actions'][] = $action;
                }
            }
            
            echo "  ✅ Sẽ tạo " . count($breakdownGroups) . " breakdown records:\n";
            foreach ($breakdownGroups as $device => $group) {
                echo "    - action_device: $device\n";
                echo "      Actions: " . count($group['actions']) . "\n";
                foreach ($group['actions'] as $action) {
                    echo "        * " . $action['action_type'] . ": " . $action['value'] . "\n";
                }
            }
        }
    }
    
} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage() . "\n";
}

echo "\nHoàn thành!\n";
