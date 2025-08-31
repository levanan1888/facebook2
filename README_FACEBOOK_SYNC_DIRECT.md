# Facebook Sync Direct - Không cần Job

## Tổng quan

Service mới này cho phép đồng bộ dữ liệu Facebook Ads trực tiếp mà không cần sử dụng queue job. Điều này giúp:

- **Đơn giản hóa**: Không cần cấu hình queue worker
- **Test nhanh**: Có thể test trực tiếp từ command line hoặc web
- **Debug dễ dàng**: Xem log và progress real-time
- **API v23**: Sử dụng Facebook Marketing API phiên bản mới nhất

## Cấu trúc API Calls

Service tuân theo cấu trúc chuẩn của Facebook Marketing API:

```
1. GET /me/businesses → Lấy Business Managers
2. GET /{business_id}/client_ad_accounts → Lấy Ad Accounts (client)
3. GET /{business_id}/owned_ad_accounts → Lấy Ad Accounts (owned)
4. GET /{account_id}/campaigns → Lấy Campaigns
5. GET /{campaign_id}/adsets → Lấy Ad Sets
6. GET /{adset_id}/ads → Lấy Ads + Creative
7. GET /{post_id} → Lấy Post Details (nếu có)
8. GET /{post_id}/insights → Lấy Post Insights (nếu có)
9. GET /{ad_id}/insights → Lấy Ad Insights
```

## Cách sử dụng

### 1. Từ Command Line

```bash
# Test sync trực tiếp
php artisan facebook:sync-direct

# Với giới hạn số lượng ads
php artisan facebook:sync-direct --limit=100
```

### 2. Từ Web Interface

Truy cập: `/facebook/sync/test`

- **Sync với Job**: Sử dụng queue job (cách cũ)
- **Sync Trực tiếp**: Chạy sync ngay lập tức

### 3. Từ API

```bash
# Sync trực tiếp
POST /facebook/sync/ads-direct

# Kiểm tra trạng thái
GET /facebook/sync/status
```

## Cấu hình

### Environment Variables

```env
FACEBOOK_ADS_TOKEN=your_access_token
FACEBOOK_API_VERSION=v23.0
FACEBOOK_BATCH_SIZE=100
FACEBOOK_TIMEOUT=30
FACEBOOK_RETRY_ATTEMPTS=3
```

### Config (config/services.php)

```php
'facebook' => [
    'ads_token' => env('FACEBOOK_ADS_TOKEN'),
    'api_version' => env('FACEBOOK_API_VERSION', 'v23.0'),
    'batch_size' => env('FACEBOOK_BATCH_SIZE', 100),
    'timeout' => env('FACEBOOK_TIMEOUT', 30),
    'retry_attempts' => env('FACEBOOK_RETRY_ATTEMPTS', 3),
],
```

## Cải tiến so với phiên bản cũ

### 1. Performance
- **Loại bỏ delay 3 giây** giữa các API calls
- **Rate limiting thông minh**: Chỉ delay 1 giây giữa batch
- **Async processing**: Sử dụng HTTP async cho insights

### 2. Error Handling
- **Retry logic** cho rate limit errors
- **Detailed error logging** với context
- **Graceful degradation**: Tiếp tục xử lý dù có lỗi

### 3. Data Structure
- **API v23**: Sử dụng phiên bản mới nhất
- **Optimized fields**: Chỉ request các trường cần thiết
- **Batch processing**: Xử lý nhiều ads cùng lúc

### 4. Monitoring
- **Real-time progress**: Callback function cho progress
- **Detailed metrics**: Số liệu chi tiết cho mỗi bước
- **Performance tracking**: Thời gian xử lý từng bước

## Cấu trúc Database

Service lưu trữ dữ liệu vào bảng `facebook_ads` với các trường:

### Thông tin cơ bản
- `id`, `name`, `status`, `effective_status`
- `adset_id`, `campaign_id`, `account_id`
- `creative` (JSON), `created_time`, `updated_time`

### Post Data (nếu có)
- `post_id`, `page_id`, `post_message`, `post_type`
- `post_attachments`, `post_permalink_url`
- `post_created_time`, `post_updated_time`

### Creative Data (cho Link Ads)
- `creative_link_url`, `creative_link_message`
- `creative_image_hash`, `creative_call_to_action_type`

### Insights Data
- **Post Insights**: `post_impressions`, `post_reach`, `post_likes`, etc.
- **Ad Insights**: `ad_spend`, `ad_impressions`, `ad_clicks`, etc.
- **Metadata**: `post_metadata`, `insights_metadata`

## Troubleshooting

### 1. Rate Limit Errors
```bash
# Kiểm tra log
tail -f storage/logs/laravel.log | grep "rate limit"

# Giảm batch size
FACEBOOK_BATCH_SIZE=50
```

### 2. Token Expired
```bash
# Kiểm tra token
curl "https://graph.facebook.com/v23.0/me?access_token=YOUR_TOKEN"

# Cập nhật token trong .env
FACEBOOK_ADS_TOKEN=new_token
```

### 3. Permission Issues
```bash
# Kiểm tra permissions
curl "https://graph.facebook.com/v23.0/me/permissions?access_token=YOUR_TOKEN"

# Cần permissions: ads_read, business_management
```

### 4. Memory Issues
```bash
# Tăng memory limit
php -d memory_limit=512M artisan facebook:sync-direct

# Giảm batch size
FACEBOOK_BATCH_SIZE=25
```

## Monitoring & Logging

### Progress Tracking
```php
$progressCallback = function(array $progress) {
    Log::info("Sync Progress", $progress);
    // Gửi notification, update UI, etc.
};
```

### Performance Metrics
```php
$result = [
    'businesses' => 0,
    'accounts' => 0,
    'campaigns' => 0,
    'adsets' => 0,
    'ads' => 0,
    'duration' => 0, // Thời gian xử lý
    'errors' => [], // Danh sách lỗi
];
```

### Error Logging
```php
Log::error('Sync Error', [
    'stage' => 'getCampaigns',
    'account_id' => $accountId,
    'error' => $error,
    'time' => now(),
]);
```

## Best Practices

### 1. Production Use
- **Không nên** chạy sync trực tiếp trong production
- **Sử dụng** queue job cho production
- **Monitor** memory usage và execution time

### 2. Development & Testing
- **Ideal** cho development và testing
- **Quick feedback** về API responses
- **Easy debugging** với detailed logs

### 3. Batch Size Optimization
- **Start small**: 25-50 ads per batch
- **Monitor rate limits**: Tăng batch size dần dần
- **Balance**: Giữa performance và rate limits

### 4. Error Handling
- **Log everything**: Không bỏ qua lỗi nào
- **Continue on errors**: Xử lý các ads khác dù có lỗi
- **Retry logic**: Cho rate limit errors

## Migration từ Job-based Sync

### 1. Cập nhật Service
```php
// Thay vì dispatch job
SyncFacebookAds::dispatch($syncId);

// Sử dụng sync trực tiếp
$syncService = new FacebookAdsSyncService(new FacebookAdsService());
$result = $syncService->syncFacebookData($progressCallback);
```

### 2. Cập nhật Controller
```php
public function syncAdsDirect(Request $request): JsonResponse
{
    $syncService = new FacebookAdsSyncService(new FacebookAdsService());
    $result = $syncService->syncFacebookData($progressCallback);
    
    return response()->json([
        'success' => true,
        'result' => $result
    ]);
}
```

### 3. Cập nhật Routes
```php
Route::post('facebook/sync/ads-direct', [FacebookSyncController::class, 'syncAdsDirect'])
    ->name('facebook.sync.ads-direct');
```

## Kết luận

Facebook Sync Direct service cung cấp một cách tiếp cận đơn giản và hiệu quả để đồng bộ dữ liệu Facebook Ads. Với API v23, performance tối ưu, và error handling mạnh mẽ, service này là lựa chọn lý tưởng cho development, testing, và các use case đơn giản.

Tuy nhiên, cho production environments với khối lượng dữ liệu lớn, vẫn nên sử dụng queue job để đảm bảo reliability và scalability.

