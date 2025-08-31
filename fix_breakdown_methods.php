<?php

// Script để sửa các method save breakdown trong FacebookAdsSyncService.php

$filePath = 'app/Services/FacebookAdsSyncService.php';
$content = file_get_contents($filePath);

// Pattern để tìm và thay thế trong saveBreakdownData
$oldPattern1 = 'if (isset($breakdownData[\'data\']) && !empty($breakdownData[\'data\'])) {
            foreach ($breakdownData[\'data\'] as $row) {
                \\App\\Models\\FacebookBreakdown::updateOrCreate(
                    [
                        \'ad_insight_id\' => $this->lastProcessedAdInsightId,
                        \'breakdown_type\' => $breakdownType,
                        \'breakdown_value\' => is_array($row[$breakdownType] ?? null)
                            ? (string)($row[$breakdownType][\'id\'] ?? $row[$breakdownType][\'name\'] ?? json_encode($row[$breakdownType]))
                            : (string)($row[$breakdownType] ?? \'unknown\'),
                    ],
                    [
                        \'metrics\' => json_encode($row),
                    ]
                );
            }
            $result[\'breakdowns\']++;
        }';

$newPattern1 = 'if (isset($breakdownData[\'data\']) && !empty($breakdownData[\'data\'])) {
            foreach ($breakdownData[\'data\'] as $row) {
                // Sử dụng method extractBreakdownValue đã được cải thiện
                $breakdownValue = $this->extractBreakdownValue($row, $breakdownType);
                
                // Chỉ lưu nếu có giá trị hợp lệ
                if ($breakdownValue !== null) {
                    \\App\\Models\\FacebookBreakdown::updateOrCreate(
                        [
                            \'ad_insight_id\' => $this->lastProcessedAdInsightId,
                            \'breakdown_type\' => $breakdownType,
                            \'breakdown_value\' => $breakdownValue,
                        ],
                        [
                            \'metrics\' => json_encode($row),
                        ]
                    );
                }
            }
            $result[\'breakdowns\']++;
        }';

// Thay thế trong saveBreakdownData
$content = str_replace($oldPattern1, $newPattern1, $content);

// Thay thế trong saveActionBreakdownData
$content = str_replace($oldPattern1, $newPattern1, $content);

// Thay thế trong saveAssetBreakdownData  
$content = str_replace($oldPattern1, $newPattern1, $content);

// Lưu file
file_put_contents($filePath, $content);

echo "Đã sửa xong các method save breakdown!\n";
echo "Các thay đổi:\n";
echo "1. Sử dụng extractBreakdownValue() thay vì logic cũ\n";
echo "2. Chỉ lưu khi breakdownValue !== null\n";
echo "3. Loại bỏ 'unknown' values\n";
