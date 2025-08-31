# Facebook Ads Video Metrics Integration

## Tổng quan

Đã cập nhật logic để lấy đầy đủ video metrics từ Facebook Ad Insights theo docs Facebook API v23.0, bao gồm:

- **Video metrics đầy đủ** từ Ad Insights (không phải Post Insights)
- **Breakdown data** theo các permutation được cho phép
- **Post/Page data** từ creative của Ad
- **Conversion metrics** và các metrics khác

## Các thay đổi chính

### 1. FacebookAdsService

#### Video Metrics Fields
Đã cập nhật `getInsightsForAd()` để lấy đầy đủ video metrics:

```php
// Video metrics chính từ Ad Insights
'video_plays,video_plays_at_25_percent,video_plays_at_50_percent,video_plays_at_75_percent,video_plays_at_100_percent,' .
'video_avg_time_watched_actions,video_p25_watched_actions,video_p50_watched_actions,video_p75_watched_actions,video_p95_watched_actions,video_p100_watched_actions,' .
'thruplays,video_avg_time_watched,video_view_time,' .

// Post video metrics (nếu có)
'post_video_views,post_video_views_unique,post_video_avg_time_watched,post_video_complete_views_30s,post_video_views_10s,' .
'post_video_retention_graph,post_video_views_paid,post_video_views_organic,' .

// Additional video metrics
'video_play_actions,video_view_time_actions,video_views_10s,video_views_15s,video_views_30s,' .
'video_views_60s,video_views_auto_played,video_views_clicked_to_play,video_views_unique,' .
'video_views_organic,video_views_paid,video_views_sound_on,video_views_sound_off,' .
'video_views_unique_10s,video_views_unique_15s,video_views_unique_30s,video_views_unique_60s,' .
'video_views_unique_auto_played,video_views_unique_clicked_to_play,video_views_unique_sound_on,video_views_unique_sound_off,' .

// Conversion metrics
'conversions,conversion_values,cost_per_conversion,purchase_roas,' .
'outbound_clicks,unique_outbound_clicks,inline_link_clicks,unique_inline_link_clicks,website_clicks'
```

#### Breakdown Methods
Đã thêm các method breakdown đầy đủ:

- `getInsightsWithAgeGenderBreakdown()`
- `getInsightsWithRegionBreakdown()`
- `getInsightsWithPlatformPositionBreakdown()`
- `getInsightsWithPublisherPlatformBreakdown()`
- `getInsightsWithDevicePlatformBreakdown()`
- `getInsightsWithCountryBreakdown()`
- `getInsightsWithImpressionDeviceBreakdown()`
- `getInsightsWithActionTypeBreakdown()`
- `getInsightsWithActionDeviceBreakdown()`
- `getInsightsWithActionDestinationBreakdown()`
- `getInsightsWithActionTargetIdBreakdown()`
- `getInsightsWithActionReactionBreakdown()`
- `getInsightsWithActionVideoSoundBreakdown()`
- `getInsightsWithActionVideoTypeBreakdown()`
- `getInsightsWithActionCarouselCardIdBreakdown()`
- `getInsightsWithActionCarouselCardNameBreakdown()`
- `getInsightsWithActionCanvasComponentNameBreakdown()`

### 2. FacebookAdsSyncService

#### Process Ad Insights với Video Metrics
Cập nhật `processAdInsightsWithVideoMetrics()` để lưu đầy đủ video metrics:

```php
// Video metrics trực tiếp từ API
'video_plays' => (int) ($insight['video_plays'] ?? 0),
'video_plays_at_25_percent' => (int) ($insight['video_plays_at_25_percent'] ?? 0),
'video_plays_at_50_percent' => (int) ($insight['video_plays_at_50_percent'] ?? 0),
'video_plays_at_75_percent' => (int) ($insight['video_plays_at_75_percent'] ?? 0),
'video_plays_at_100_percent' => (int) ($insight['video_plays_at_100_percent'] ?? 0),
'video_avg_time_watched_actions' => (int) ($insight['video_avg_time_watched_actions'] ?? 0),
'video_p25_watched_actions' => (int) ($insight['video_p25_watched_actions'] ?? 0),
'video_p50_watched_actions' => (int) ($insight['video_p50_watched_actions'] ?? 0),
'video_p75_watched_actions' => (int) ($insight['video_p75_watched_actions'] ?? 0),
'video_p95_watched_actions' => (int) ($insight['video_p95_watched_actions'] ?? 0),
'video_p100_watched_actions' => (int) ($insight['video_p100_watched_actions'] ?? 0),
'thruplays' => (int) ($insight['thruplays'] ?? 0),
'video_avg_time_watched' => (float) ($insight['video_avg_time_watched'] ?? 0),
'video_view_time' => (int) ($insight['video_view_time'] ?? 0),

// Post video metrics
'post_video_views' => (int) ($insight['post_video_views'] ?? 0),
'post_video_views_unique' => (int) ($insight['post_video_views_unique'] ?? 0),
'post_video_avg_time_watched' => (float) ($insight['post_video_avg_time_watched'] ?? 0),
'post_video_complete_views_30s' => (int) ($insight['post_video_complete_views_30s'] ?? 0),
'post_video_views_10s' => (int) ($insight['post_video_views_10s'] ?? 0),
'post_video_retention_graph' => isset($insight['post_video_retention_graph']) ? json_encode($insight['post_video_retention_graph']) : null,
'post_video_views_paid' => (int) ($insight['post_video_views_paid'] ?? 0),
'post_video_views_organic' => (int) ($insight['post_video_views_organic'] ?? 0),
```

#### Process Breakdowns
Cập nhật `processAdBreakdowns()` để lấy đầy đủ breakdown data:

```php
// Age/Gender breakdown
$ageGenderBreakdown = $this->api->getInsightsWithAgeGenderBreakdown($ad['id']);

// Region breakdown
$regionBreakdown = $this->api->getInsightsWithRegionBreakdown($ad['id']);

// Platform position breakdown
$platformPositionBreakdown = $this->api->getInsightsWithPlatformPositionBreakdown($ad['id']);

// Publisher platform breakdown
$publisherPlatformBreakdown = $this->api->getInsightsWithPublisherPlatformBreakdown($ad['id']);

// Device platform breakdown
$devicePlatformBreakdown = $this->api->getInsightsWithDevicePlatformBreakdown($ad['id']);

// Country breakdown
$countryBreakdown = $this->api->getInsightsWithCountryBreakdown($ad['id']);

// Impression device breakdown
$impressionDeviceBreakdown = $this->api->getInsightsWithImpressionDeviceBreakdown($ad['id']);

// Action breakdowns
$actionTypeBreakdown = $this->api->getInsightsWithActionTypeBreakdown($ad['id']);
$actionDeviceBreakdown = $this->api->getInsightsWithActionDeviceBreakdown($ad['id']);
$actionDestinationBreakdown = $this->api->getInsightsWithActionDestinationBreakdown($ad['id']);
$actionTargetIdBreakdown = $this->api->getInsightsWithActionTargetIdBreakdown($ad['id']);
$actionReactionBreakdown = $this->api->getInsightsWithActionReactionBreakdown($ad['id']);
$actionVideoSoundBreakdown = $this->api->getInsightsWithActionVideoSoundBreakdown($ad['id']);
$actionVideoTypeBreakdown = $this->api->getInsightsWithActionVideoTypeBreakdown($ad['id']);
$actionCarouselCardIdBreakdown = $this->api->getInsightsWithActionCarouselCardIdBreakdown($ad['id']);
$actionCarouselCardNameBreakdown = $this->api->getInsightsWithActionCarouselCardNameBreakdown($ad['id']);
$actionCanvasComponentNameBreakdown = $this->api->getInsightsWithActionCanvasComponentNameBreakdown($ad['id']);
```

### 3. Database Schema

#### FacebookAdInsight Model
Đã cập nhật fillable fields để bao gồm tất cả video metrics:

```php
protected $fillable = [
    // ... existing fields ...
    
    // Video metrics fields - đầy đủ theo docs Facebook
    'video_plays', 'video_plays_at_25_percent', 'video_plays_at_50_percent', 'video_plays_at_75_percent', 'video_plays_at_100_percent',
    'video_avg_time_watched_actions', 'video_p25_watched_actions', 'video_p50_watched_actions', 'video_p75_watched_actions', 'video_p95_watched_actions', 'video_p100_watched_actions',
    'thruplays', 'video_avg_time_watched', 'video_view_time',
    'post_video_views', 'post_video_views_unique', 'post_video_avg_time_watched', 'post_video_complete_views_30s', 'post_video_views_10s',
    'post_video_retention_graph', 'post_video_views_paid', 'post_video_views_organic'
];
```

## Sử dụng

### 1. Command mới
```bash
# Sync với video metrics đầy đủ
php artisan facebook:sync-with-video-metrics

# Sync với time range tùy chỉnh
php artisan facebook:sync-with-video-metrics --since=2024-01-01 --until=2024-01-31

# Sync với limit để test
php artisan facebook:sync-with-video-metrics --limit=5
```

### 2. Sử dụng trong code
```php
use App\Services\FacebookAdsSyncService;

$syncService = app(FacebookAdsSyncService::class);

// Sync với progress callback
$result = $syncService->syncFacebookData(
    function($data) {
        echo $data['message'] . "\n";
        echo "Ads processed: " . $data['counts']['ads'] . "\n";
    },
    '2024-01-01',
    '2024-01-31'
);
```

## Video Metrics Available

### Ad Insights Video Metrics
- `video_plays` - Tổng số lần video được phát
- `video_plays_at_25_percent` - Video plays at 25%
- `video_plays_at_50_percent` - Video plays at 50%
- `video_plays_at_75_percent` - Video plays at 75%
- `video_plays_at_100_percent` - Video plays at 100%
- `video_avg_time_watched_actions` - Average time watched
- `video_p25_watched_actions` - 25% watched actions
- `video_p50_watched_actions` - 50% watched actions
- `video_p75_watched_actions` - 75% watched actions
- `video_p95_watched_actions` - 95% watched actions
- `video_p100_watched_actions` - 100% watched actions
- `thruplays` - Thruplays
- `video_avg_time_watched` - Average time watched
- `video_view_time` - Total view time

### Post Video Metrics
- `post_video_views` - Post video views
- `post_video_views_unique` - Unique post video views
- `post_video_avg_time_watched` - Average time watched
- `post_video_complete_views_30s` - Complete views 30s
- `post_video_views_10s` - Views 10s
- `post_video_retention_graph` - Retention graph
- `post_video_views_paid` - Paid video views
- `post_video_views_organic` - Organic video views

## Breakdown Types Available

### Demographics
- `age_gender` - Age and gender breakdown

### Geographic
- `region` - Region breakdown
- `country` - Country breakdown

### Platform
- `platform_position` - Platform position
- `publisher_platform` - Publisher platform
- `device_platform` - Device platform
- `impression_device` - Impression device

### Actions
- `action_type` - Action type
- `action_device` - Action device
- `action_destination` - Action destination
- `action_target_id` - Action target ID
- `action_reaction` - Action reaction
- `action_video_sound` - Action video sound
- `action_video_type` - Action video type
- `action_carousel_card_id` - Action carousel card ID
- `action_carousel_card_name` - Action carousel card name
- `action_canvas_component_name` - Action canvas component name

## Lưu ý

1. **API Version**: Sử dụng Facebook API v23.0
2. **Rate Limiting**: Có delay giữa các API calls để tránh rate limit
3. **Error Handling**: Có logging đầy đủ cho debugging
4. **Video Metrics**: Chỉ có sẵn cho video ads
5. **Breakdowns**: Một số breakdowns không hỗ trợ video metrics (như region)

## Troubleshooting

### Lỗi thường gặp

1. **"Video metrics not available"**
   - Kiểm tra xem ad có phải là video ad không
   - Kiểm tra quyền truy cập API

2. **"Breakdown not supported"**
   - Một số breakdowns không được hỗ trợ trong API v23.0
   - Kiểm tra docs Facebook để biết breakdowns được hỗ trợ

3. **"Rate limit exceeded"**
   - Tăng delay giữa các API calls
   - Giảm số lượng ads xử lý đồng thời

### Debug

```bash
# Xem logs
tail -f storage/logs/laravel.log

# Test API response
php artisan tinker
>>> $api = app(\App\Services\FacebookAdsService::class);
>>> $result = $api->getInsightsForAd('ad_id_here');
>>> dd($result);
```
