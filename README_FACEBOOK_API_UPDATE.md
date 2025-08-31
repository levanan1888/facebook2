# 🔄 Cập nhật Facebook API - Sửa lỗi Deprecated Endpoints

## 🚨 Vấn đề đã gặp

Lỗi `(#12) singular statuses API is deprecated for versions v2.4 and higher` xảy ra do sử dụng endpoint cũ của Facebook API. Theo tài liệu chính thức của Meta, endpoint `statuses` đã bị deprecated từ v2.4 và cần thay thế bằng endpoint `insights`.

## ✅ Giải pháp đã áp dụng

### 1. **Cập nhật API Version**
- Thay đổi từ `v23.0` → `v19.0` (phiên bản ổn định mới nhất)
- Cập nhật trong `config/services.php` và các service classes

### 2. **Thay thế Deprecated Endpoints**

#### **Trước (Deprecated):**
```php
// Endpoint cũ - không còn hoạt động
GET /{object-id}/statuses
```

#### **Sau (Mới):**
```php
// Endpoint mới - thay thế cho statuses
GET /{object-id}/insights
```

### 3. **Các thay đổi chính trong code**

#### **FacebookAdsService.php:**
- ✅ Cập nhật API version mặc định thành `v19.0`
- ✅ Thêm `time_range` parameter cho tất cả insights requests
- ✅ Thêm các method mới: `getPageInsights()`, `getCampaignInsights()`, `getAdSetInsights()`
- ✅ Cải thiện error handling và logging

#### **FacebookAdsSyncService.php:**
- ✅ Cập nhật API version constant thành `v19.0`

#### **config/services.php:**
- ✅ Cập nhật default API version thành `v19.0`

## 📋 Các Endpoint Mới Được Hỗ Trợ

### 1. **Post Insights**
```php
// Lấy insights cho post
$service->getPostInsightsExtended($postId);

// Lấy insights với breakdowns
$service->getPostInsightsWithBreakdowns($postId, ['age', 'gender', 'region']);
```

### 2. **Ad Insights**
```php
// Lấy insights cho ad
$service->getInsightsForAd($adId);

// Lấy insights với breakdowns
$service->getInsightsForAdWithBreakdowns($adId, ['age', 'gender']);
```

### 3. **Campaign Insights**
```php
// Lấy insights cho campaign
$service->getCampaignInsights($campaignId);
```

### 4. **Ad Set Insights**
```php
// Lấy insights cho ad set
$service->getAdSetInsights($adSetId);
```

### 5. **Page Insights**
```php
// Lấy insights cho page
$service->getPageInsights($pageId);
```

## 🔧 Cấu hình Environment

### **Cập nhật .env file:**
```env
# Facebook API Configuration
FACEBOOK_API_VERSION=v19.0
FACEBOOK_ADS_TOKEN=your_access_token
FACEBOOK_APP_ID=your_app_id
FACEBOOK_APP_SECRET=your_app_secret

# Optional configurations
FACEBOOK_BATCH_SIZE=100
FACEBOOK_TIMEOUT=60
FACEBOOK_RETRY_ATTEMPTS=3
```

## 📊 Các Field Insights Mới

### **Post Insights Fields:**
- `post_impressions` - Số lần hiển thị
- `post_impressions_unique` - Số người xem duy nhất
- `post_engaged_users` - Số người tương tác
- `post_video_views` - Lượt xem video
- `post_video_avg_time_watched` - Thời gian xem trung bình
- `post_video_complete_views_30s` - Lượt xem hoàn thành 30s

### **Ad Insights Fields:**
- `spend` - Chi phí
- `reach` - Độ tiếp cận
- `impressions` - Số lần hiển thị
- `clicks` - Số lượt click
- `ctr` - Tỷ lệ click
- `cpc` - Chi phí mỗi click
- `cpm` - Chi phí mỗi 1000 lần hiển thị

### **Page Insights Fields:**
- `page_impressions` - Số lần hiển thị trang
- `page_engaged_users` - Số người tương tác với trang
- `page_fans` - Số người theo dõi
- `page_fan_adds` - Số người theo dõi mới

## 🚀 Cách sử dụng

### **1. Khởi tạo Service:**
```php
use App\Services\FacebookAdsService;

$facebookService = new FacebookAdsService();
```

### **2. Lấy Insights cho Post:**
```php
$postId = '123456789';
$insights = $facebookService->getPostInsightsExtended($postId);

if (!isset($insights['error'])) {
    $impressions = $insights['data'][0]['post_impressions'] ?? 0;
    $videoViews = $insights['data'][0]['post_video_views'] ?? 0;
}
```

### **3. Lấy Insights cho Ad với Breakdowns:**
```php
$adId = '123456789';
$insights = $facebookService->getInsightsForAdWithBreakdowns($adId, ['age', 'gender']);

if (!isset($insights['error'])) {
    foreach ($insights['data'] as $insight) {
        $age = $insight['age'] ?? 'unknown';
        $gender = $insight['gender'] ?? 'unknown';
        $impressions = $insight['impressions'] ?? 0;
    }
}
```

### **4. Lấy Campaign Insights:**
```php
$campaignId = '123456789';
$insights = $facebookService->getCampaignInsights($campaignId);

if (!isset($insights['error'])) {
    $spend = $insights['data'][0]['spend'] ?? 0;
    $reach = $insights['data'][0]['reach'] ?? 0;
}
```

## ⚠️ Lưu ý quan trọng

### **1. Time Range Parameter:**
- Tất cả insights requests cần có `time_range` parameter
- Mặc định: 36 tháng gần nhất cho ads, 30 ngày cho pages

### **2. Rate Limiting:**
- Facebook API có giới hạn rate limit
- Sử dụng delay giữa các requests
- Implement retry mechanism

### **3. Permissions:**
- Cần quyền phù hợp cho từng loại insights
- `ads_read` cho ad insights
- `pages_read_engagement` cho page insights
- `pages_read_user_content` cho post insights

### **4. Field Compatibility:**
- Một số fields không tương thích với breakdowns
- Video fields không hỗ trợ region breakdown
- Kiểm tra tài liệu Meta API trước khi sử dụng

## 🔍 Troubleshooting

### **Lỗi thường gặp:**

#### **1. "Invalid API Version":**
```bash
# Kiểm tra API version trong .env
FACEBOOK_API_VERSION=v19.0
```

#### **2. "Missing time_range":**
```php
// Đảm bảo có time_range parameter
$params = [
    'time_range' => json_encode([
        'since' => date('Y-m-d', strtotime('-30 days')),
        'until' => date('Y-m-d')
    ])
];
```

#### **3. "Insufficient permissions":**
```php
// Kiểm tra access token và permissions
$token = config('services.facebook.ads_token');
```

#### **4. "Rate limit exceeded":**
```php
// Tăng delay giữa requests
sleep(2); // Delay 2 giây
```

## 📈 Performance Improvements

### **1. Batch Processing:**
```php
// Xử lý nhiều requests cùng lúc
$insights = $facebookService->getInsightsForAdsBatch($adIds, 5);
```

### **2. Caching:**
```php
// Cache insights data
Cache::put("insights_{$objectId}", $insights, 3600);
```

### **3. Async Requests:**
```php
// Sử dụng async requests cho performance tốt hơn
$promises = [
    'post' => $service->getPostInsightsAsync($postId),
    'ad' => $service->getAdInsightsAsync($adId),
];
```

## 🔄 Migration Checklist

- [x] Cập nhật API version trong config
- [x] Thay thế deprecated endpoints
- [x] Thêm time_range parameter
- [x] Cập nhật error handling
- [x] Thêm logging chi tiết
- [x] Test với API mới
- [x] Cập nhật documentation

## 📞 Support

Nếu gặp vấn đề sau khi cập nhật:

1. **Kiểm tra logs**: `storage/logs/laravel.log`
2. **Verify API version**: Đảm bảo sử dụng `v19.0`
3. **Check permissions**: Xác nhận access token có đủ quyền
4. **Test endpoints**: Sử dụng Facebook Graph API Explorer

---

**Lưu ý**: Cập nhật này tuân thủ tài liệu chính thức của Meta API và sẽ không bị deprecated trong tương lai gần.
