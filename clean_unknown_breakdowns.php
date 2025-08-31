<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\FacebookBreakdown;

echo "=== CLEAN UNKNOWN BREAKDOWNS ===\n\n";

// Đếm số records unknown trước khi xóa
$unknownCount = FacebookBreakdown::where('breakdown_value', 'unknown')->count();
echo "Tìm thấy " . $unknownCount . " records có giá trị 'unknown'\n";

if ($unknownCount > 0) {
    echo "Bắt đầu xóa các records 'unknown'...\n";
    
    // Xóa tất cả records có breakdown_value = 'unknown'
    $deletedCount = FacebookBreakdown::where('breakdown_value', 'unknown')->delete();
    
    echo "Đã xóa " . $deletedCount . " records 'unknown'\n";
    
    // Kiểm tra lại
    $remainingUnknown = FacebookBreakdown::where('breakdown_value', 'unknown')->count();
    echo "Còn lại " . $remainingUnknown . " records 'unknown'\n";
} else {
    echo "Không có records 'unknown' để xóa\n";
}

// Hiển thị thống kê tổng quan
$totalBreakdowns = FacebookBreakdown::count();
echo "\nTổng số breakdown records: " . $totalBreakdowns . "\n";

if ($totalBreakdowns > 0) {
    echo "\nThống kê theo breakdown_type:\n";
    $breakdownTypes = FacebookBreakdown::selectRaw('breakdown_type, COUNT(*) as count')
        ->groupBy('breakdown_type')
        ->orderBy('count', 'desc')
        ->get();
    
    foreach ($breakdownTypes as $type) {
        echo "  - " . $type->breakdown_type . ": " . $type->count . " records\n";
    }
}

echo "\nHoàn thành!\n";
