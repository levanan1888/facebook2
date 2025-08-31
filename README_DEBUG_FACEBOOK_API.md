# Debug Facebook API Response

## 🎯 **Mục đích**

Để kiểm tra và debug response từ Facebook API, xem các trường data mà Facebook trả về cho:
- Ads data
- Post details  
- Post insights
- Ad insights
- Creative data

## 🚀 **Cách sử dụng**

### **1. Test từng bước riêng biệt**

#### **Test lấy Ads data:**
```bash
php artisan facebook:sync-direct --step=ads --adset-id=YOUR_ADSET_ID
```

#### **Test lấy Post details:**
```bash
php artisan facebook:sync-direct --step=post --adset-id=YOUR_ADSET_ID
```

#### **Test lấy Ad insights:**
```bash
php artisan facebook:sync-direct --step=insights --adset-id=YOUR_ADSET_ID
```

#### **Test lấy Creative data:**
```bash
php artisan facebook:sync-direct --step=creative --adset-id=YOUR_ADSET_ID
```

### **2. Test toàn bộ sync (có dd()):**
```bash
php artisan facebook:sync-direct
```

## 📊 **Các dd() đã được thêm vào service**

### **1. DD Ads Response**
```php
dd('=== FACEBOOK ADS RESPONSE ===', [
    'adset_id' => $adSet->id,
    'adset_name' => $adSet->name,
    'total_ads' => $totalAds,
    'ads_response' => $ads,
    'first_ad' => $adsData[0] ?? null,
    'first_ad_creative' => isset($adsData[0]['creative']) ? $adsData[0]['creative'] : null,
    'creative_fields' => isset($adsData[0]['creative']) ? array_keys($adsData[0]['creative']) : [],
    'ad_fields' => isset($adsData[0]) ? array_keys($adsData[0]) : []
]);
```

**Hiển thị:**
- Tổng số ads trong adset
- Response đầy đủ từ Facebook
- Ad đầu tiên và creative của nó
- Các fields có sẵn trong ad và creative

### **2. DD Creative cho Post Extraction**
```php
dd('=== FACEBOOK CREATIVE FOR POST EXTRACTION ===', [
    'ad_id' => $ad['id'] ?? 'N/A',
    'ad_name' => $ad['name'] ?? 'N/A',
    'creative' => $creative,
    'creative_keys' => array_keys($creative),
    'has_object_story_id' => isset($creative['object_story_id']),
    'object_story_id' => $creative['object_story_id'] ?? null,
    'has_effective_object_story_id' => isset($creative['effective_object_story_id']),
    'effective_object_story_id' => $creative['effective_object_story_id'] ?? null,
    'has_object_story_spec' => isset($creative['object_story_spec']),
    'object_story_spec' => $creative['object_story_spec'] ?? null
]);
```

**Hiển thị:**
- Thông tin ad và creative
- Các keys có trong creative
- Có object_story_id hay không
- Có object_story_spec hay không

### **3. DD Post Details Response**
```php
dd('=== FACEBOOK POST DETAILS RESPONSE ===', [
    'post_id' => $postId,
    'post_data' => $postData,
    'has_error' => isset($postData['error']),
    'post_id_returned' => $postData['id'] ?? 'N/A',
    'post_message' => isset($postData['message']) ? substr($postData['message'], 0, 100) . '...' : 'N/A',
    'post_type' => $postData['type'] ?? 'N/A',
    'fields_available' => isset($postData['error']) ? [] : array_keys($postData)
]);
```

**Hiển thị:**
- Post data đầy đủ từ Facebook
- Có lỗi hay không
- Post ID, message, type
- Các fields có sẵn trong post

### **4. DD Post Insights Response**
```php
dd('=== FACEBOOK POST INSIGHTS RESPONSE ===', [
    'post_id' => $postData['id'],
    'ad_id' => $ad['id'],
    'post_insights' => $postInsights,
    'has_error' => isset($postInsights['error']),
    'data_count' => isset($postInsights['data']) ? count($postInsights['data']) : 0,
    'first_record' => $postInsights['data'][0] ?? null,
    'fields_available' => isset($postInsights['data'][0]) ? array_keys($postInsights['data'][0]) : []
]);
```

**Hiển thị:**
- Post insights đầy đủ
- Số lượng records
- Record đầu tiên
- Các fields có sẵn trong insights

### **5. DD Ad Insights Response**
```php
dd('=== FACEBOOK AD INSIGHTS RESPONSE ===', [
    'ad_id' => $ad['id'],
    'ad_name' => $ad['name'],
    'ad_insights' => $adInsights,
    'ad_insights_data' => $adInsightsData,
    'has_error' => isset($adInsightsData['error']),
    'data_count' => isset($adInsightsData['data']) ? count($adInsightsData['data']) : 0,
    'first_record' => $adInsightsData['data'][0] ?? null,
    'fields_available' => isset($adInsightsData['data'][0]) ? array_keys($adInsightsData['data'][0]) : []
]);
```

**Hiển thị:**
- Ad insights đầy đủ
- Số lượng records
- Record đầu tiên
- Các fields có sẵn trong insights

### **6. DD Creative Data Response**
```php
dd('=== FACEBOOK CREATIVE DATA RESPONSE ===', [
    'ad_id' => $ad['id'] ?? 'N/A',
    'ad_name' => $ad['name'] ?? 'N/A',
    'creative' => $creative,
    'creative_keys' => array_keys($creative),
    'has_object_story_spec' => isset($creative['object_story_spec']),
    'object_story_spec' => $creative['object_story_spec'] ?? null,
    'has_link_data' => isset($creative['object_story_spec']['link_data']),
    'link_data' => $creative['object_story_spec']['link_data'] ?? null
]);
```

**Hiển thị:**
- Creative data đầy đủ
- Các keys có trong creative
- Có object_story_spec hay không
- Có link_data hay không

## 🔍 **Cách debug hiệu quả**

### **1. Bắt đầu với Ads data**
```bash
php artisan facebook:sync-direct --step=ads --adset-id=YOUR_ADSET_ID
```
Xem response ads để hiểu cấu trúc cơ bản.

### **2. Kiểm tra Creative**
```bash
php artisan facebook:sync-direct --step=creative --adset-id=YOUR_ADSET_ID
```
Xem creative data để hiểu loại ad (post, link, standard).

### **3. Test Post details (nếu có)**
```bash
php artisan facebook:sync-direct --step=post --adset-id=YOUR_ADSET_ID
```
Xem post data nếu là post ad.

### **4. Test Insights**
```bash
php artisan facebook:sync-direct --step=insights --adset-id=YOUR_ADSET_ID
```
Xem insights data để hiểu metrics.

### **5. Test toàn bộ (có dd())**
```bash
php artisan facebook:sync-direct
```
Xem toàn bộ flow với dd() ở mỗi bước.

## 📋 **Các trường cần chú ý**

### **Ads Response:**
- `id`, `name`, `status`, `effective_status`
- `creative` - chứa thông tin về loại ad
- `created_time`, `updated_time`

### **Creative Data:**
- `object_story_id` - ID của post (nếu có)
- `object_story_spec` - Chi tiết về story
- `link_data` - Thông tin link (nếu là link ad)

### **Post Details:**
- `id`, `message`, `type`, `status_type`
- `attachments`, `permalink_url`
- `created_time`, `updated_time`

### **Post Insights:**
- `impressions`, `reach`, `clicks`
- `likes`, `shares`, `comments`
- `actions`, `action_values`

### **Ad Insights:**
- `spend`, `reach`, `impressions`, `clicks`
- `ctr`, `cpc`, `cpm`
- `actions`, `action_values`, `purchase_roas`

## ⚠️ **Lưu ý quan trọng**

1. **dd() sẽ dừng execution** - chỉ dùng để debug
2. **Xóa dd() sau khi debug xong** để service chạy bình thường
3. **Kiểm tra permissions** của Facebook token
4. **Rate limiting** - Facebook có giới hạn API calls
5. **Logs** - kiểm tra `storage/logs/laravel.log` để xem chi tiết

## 🎯 **Kết quả mong đợi**

Sau khi debug, bạn sẽ hiểu rõ:
- Facebook trả về những trường nào
- Cấu trúc data của từng loại ad
- Cách extract post ID từ creative
- Các metrics có sẵn trong insights
- Cách xử lý từng loại ad khác nhau

Điều này giúp bạn tối ưu hóa service và đảm bảo lưu đúng data vào database!

