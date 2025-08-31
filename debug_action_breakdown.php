<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\FacebookAd;
use App\Services\FacebookAdsService;

echo "=== DEBUG ACTION BREAKDOWN ===\n\n";

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
        }
    }
    
    // Test 2: Thử với breakdowns parameter thay vì action_breakdowns
    echo "2. Test với breakdowns parameter:\n";
    
    $url = "https://graph.facebook.com/v23.0/{$testAd->id}/insights";
    $fields = 'impressions,reach,clicks,ctr,cpc,cpm,spend,frequency,actions,action_values';
    
    $params = [
        'access_token' => config('services.facebook.ads_token'),
        'fields' => $fields,
        'breakdowns' => 'action_carousel_card_id', // Thử với breakdowns thay vì action_breakdowns
        'time_range' => json_encode([
            'since' => date('Y-m-d', strtotime('-30 days')),
            'until' => date('Y-m-d')
        ])
    ];
    
    $response = \Illuminate\Support\Facades\Http::get($url, $params);
    $responseData = $response->json();
    
    echo "Response với breakdowns:\n";
    echo json_encode($responseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
    
} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage() . "\n";
}

echo "\nHoàn thành!\n";
