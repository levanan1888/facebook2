<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\FacebookBreakdown;
use Illuminate\Support\Facades\Log;

echo "=== UPDATE EXISTING BREAKDOWNS ===\n\n";

// Function để extract breakdown value từ metrics
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

// Lấy tất cả breakdown records có giá trị 'unknown'
$unknownBreakdowns = FacebookBreakdown::where('breakdown_value', 'unknown')->get();

echo "Tìm thấy " . $unknownBreakdowns->count() . " records có giá trị 'unknown'\n\n";

$updatedCount = 0;
$skippedCount = 0;

foreach ($unknownBreakdowns as $breakdown) {
    echo "Processing breakdown ID: " . $breakdown->id . " (Type: " . $breakdown->breakdown_type . ")\n";
    
    // Parse metrics JSON
    $metrics = $breakdown->metrics;
    if (is_string($metrics)) {
        $metrics = json_decode($metrics, true);
    }
    
    if (!is_array($metrics)) {
        echo "  ❌ Không thể parse metrics JSON\n";
        $skippedCount++;
        continue;
    }
    
    // Extract breakdown value từ metrics
    $newValue = extractBreakdownValue($metrics, $breakdown->breakdown_type);
    
    if ($newValue !== null) {
        // Cập nhật breakdown value
        $breakdown->breakdown_value = $newValue;
        $breakdown->save();
        
        echo "  ✅ Updated: 'unknown' -> '$newValue'\n";
        $updatedCount++;
    } else {
        echo "  ⚠️  Không tìm thấy giá trị phù hợp trong metrics\n";
        echo "  Available keys: " . implode(', ', array_keys($metrics)) . "\n";
        $skippedCount++;
    }
}

echo "\n=== SUMMARY ===\n";
echo "Total records processed: " . $unknownBreakdowns->count() . "\n";
echo "Successfully updated: " . $updatedCount . "\n";
echo "Skipped: " . $skippedCount . "\n";

// Kiểm tra kết quả
$remainingUnknown = FacebookBreakdown::where('breakdown_value', 'unknown')->count();
echo "Remaining 'unknown' records: " . $remainingUnknown . "\n";

if ($remainingUnknown > 0) {
    echo "\nRemaining breakdown types:\n";
    $remainingTypes = FacebookBreakdown::where('breakdown_value', 'unknown')
        ->selectRaw('breakdown_type, COUNT(*) as count')
        ->groupBy('breakdown_type')
        ->get();
    
    foreach ($remainingTypes as $type) {
        echo "  - " . $type->breakdown_type . ": " . $type->count . " records\n";
    }
}
