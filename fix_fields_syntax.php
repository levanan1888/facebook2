<?php

$filePath = 'app/Services/FacebookAdsService.php';
$content = file_get_contents($filePath);

// Sửa lỗi dấu phẩy thừa trong fields
$content = str_replace(
    'action_values,,thruplays',
    'action_values,thruplays',
    $content
);

// Lưu file
file_put_contents($filePath, $content);

echo "Đã sửa lỗi syntax trong fields!\n";
echo "Thay thế 'action_values,,thruplays' thành 'action_values,thruplays'\n";
