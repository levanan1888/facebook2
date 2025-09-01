<?php
/**
 * Test script để kiểm tra logic xử lý dữ liệu thời gian
 * Chạy: php test_time_chart_logic.php
 */

// Dữ liệu test với timestamp giống nhau (trường hợp xấu)
$testDataSameTimestamp = [
    ['date' => '2024-08-31T00:00:00.000000Z', 'impressions' => 1000, 'clicks' => 50, 'spend' => 50000],
    ['date' => '2024-08-31T00:00:00.000000Z', 'impressions' => 1200, 'clicks' => 60, 'spend' => 60000],
    ['date' => '2024-08-31T00:00:00.000000Z', 'impressions' => 800, 'clicks' => 40, 'spend' => 40000],
];

// Dữ liệu test với timestamp khác nhau (trường hợp tốt)
$testDataDifferentTimestamps = [
    ['date' => '2024-08-28T00:00:00.000000Z', 'impressions' => 1000, 'clicks' => 50, 'spend' => 50000],
    ['date' => '2024-08-29T00:00:00.000000Z', 'impressions' => 1200, 'clicks' => 60, 'spend' => 60000],
    ['date' => '2024-08-30T00:00:00.000000Z', 'impressions' => 800, 'clicks' => 40, 'spend' => 40000],
    ['date' => '2024-08-31T00:00:00.000000Z', 'impressions' => 1500, 'clicks' => 75, 'spend' => 75000],
];

echo "=== TEST LOGIC XỬ LÝ DỮ LIỆU THỜI GIAN ===\n\n";

echo "1. Test với dữ liệu có timestamp giống nhau:\n";
echo "Input: " . json_encode($testDataSameTimestamp, JSON_PRETTY_PRINT) . "\n";

// Logic xử lý (tương tự như trong JavaScript)
$uniqueTimestamps = array_unique(array_column($testDataSameTimestamp, 'date'));
echo "Số timestamp duy nhất: " . count($uniqueTimestamps) . "\n";

if (count($uniqueTimestamps) === 1) {
    echo "→ Tất cả dữ liệu có cùng timestamp, cần tạo điểm thời gian giả lập\n";
    echo "→ Sẽ tạo 7 điểm thời gian: 6 ngày trước, 5 ngày trước, ..., hôm qua, hôm nay\n";
} else {
    echo "→ Dữ liệu có timestamp khác nhau, xử lý bình thường\n";
}

echo "\n2. Test với dữ liệu có timestamp khác nhau:\n";
echo "Input: " . json_encode($testDataDifferentTimestamps, JSON_PRETTY_PRINT) . "\n";

$uniqueTimestamps2 = array_unique(array_column($testDataDifferentTimestamps, 'date'));
echo "Số timestamp duy nhất: " . count($uniqueTimestamps2) . "\n";

if (count($uniqueTimestamps2) === 1) {
    echo "→ Tất cả dữ liệu có cùng timestamp, cần tạo điểm thời gian giả lập\n";
} else {
    echo "→ Dữ liệu có timestamp khác nhau, xử lý bình thường\n";
    echo "→ Sẽ nhóm theo ngày và hiển thị theo thứ tự thời gian\n";
}

echo "\n=== KẾT LUẬN ===\n";
echo "Logic mới sẽ:\n";
echo "- Phát hiện khi tất cả dữ liệu có cùng timestamp\n";
echo "- Tự động tạo 7 điểm thời gian giả lập để hiển thị xu hướng\n";
echo "- Hiển thị thông báo cho người dùng biết dữ liệu được tạo giả lập\n";
echo "- Vẫn xử lý bình thường khi có timestamp khác nhau\n";
echo "- Tạo ra biểu đồ có ý nghĩa thay vì hiển thị tất cả dữ liệu chồng lên nhau\n";
