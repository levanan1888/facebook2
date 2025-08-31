<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TEST BREAKDOWN EXTRACTION ===\n\n";

// Mẫu data từ response thực tế
$testData = [
    [
        'action_type' => 'link_click',
        'value' => 25,
        'carousel_card_id' => 3,
        'carousel_card_name' => 'Sản phẩm A - Giảm 50%'
    ],
    [
        'action_type' => 'post_engagement',
        'value' => 10,
        'device' => 'mobile',
        'destination' => 'website'
    ],
    [
        'action_type' => 'video_view',
        'value' => 100,
        'video_sound' => 'on',
        'video_type' => 'feed'
    ]
];

// Test các breakdown types
$breakdownTypes = [
    'action_carousel_card_id',
    'action_carousel_card_name', 
    'action_device',
    'action_destination',
    'action_video_sound',
    'action_video_type'
];

foreach ($testData as $index => $row) {
    echo "Test Row $index:\n";
    echo "  Data: " . json_encode($row) . "\n";
    
    foreach ($breakdownTypes as $breakdownType) {
        $value = extractBreakdownValue($row, $breakdownType);
        echo "  $breakdownType: " . ($value ?? 'null') . "\n";
    }
    echo "\n";
}

// Function tương tự như trong service
function extractBreakdownValue(array $data, string $breakdownType): ?string
{
    // Nếu có trường breakdown chính
    if (isset($data[$breakdownType])) {
        $value = $data[$breakdownType];
        
        // Nếu là array, lấy id hoặc name
        if (is_array($value)) {
            return (string)($value['id'] ?? $value['name'] ?? json_encode($value));
        }
        
        // Nếu là string hoặc number
        return (string)$value;
    }
    
    switch ($breakdownType) {
        case 'action_carousel_card_id':
            return $data['carousel_card_id'] ?? $data['card_id'] ?? null;
            
        case 'action_carousel_card_name':
            return $data['carousel_card_name'] ?? $data['card_name'] ?? null;
            
        case 'action_canvas_component_name':
            return $data['canvas_component_name'] ?? $data['component_name'] ?? null;
            
        case 'action_device':
            return $data['action_device'] ?? $data['device'] ?? $data['device_type'] ?? null;
            
        case 'action_destination':
            return $data['action_destination'] ?? $data['destination'] ?? $data['target'] ?? null;
            
        case 'action_target_id':
            return $data['action_target_id'] ?? $data['target_id'] ?? $data['object_id'] ?? null;
            
        case 'action_reaction':
            return $data['action_reaction'] ?? $data['reaction'] ?? $data['reaction_type'] ?? null;
            
        case 'action_video_sound':
            return $data['action_video_sound'] ?? $data['video_sound'] ?? $data['sound'] ?? null;
            
        case 'action_video_type':
            return $data['action_video_type'] ?? $data['video_type'] ?? $data['type'] ?? null;
            
        default:
            // Tìm kiếm các trường có thể liên quan
            foreach ($data as $key => $value) {
                if (strpos($key, str_replace('action_', '', $breakdownType)) !== false) {
                    return (string)$value;
                }
            }
            
            return null;
    }
}
