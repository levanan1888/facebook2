<?php

$filePath = 'app/Services/FacebookAdsService.php';
$content = file_get_contents($filePath);

// Sửa lỗi field thruplays không hợp lệ
$content = str_replace(
    'action_values,thruplays',
    'action_values',
    $content
);

// Lưu file
file_put_contents($filePath, $content);

echo "Đã sửa lỗi field thruplays!\n";
echo "Loại bỏ 'thruplays' khỏi fields vì không hợp lệ với Facebook API\n";
