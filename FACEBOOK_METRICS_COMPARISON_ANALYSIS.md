# 📊 Facebook Metrics Comparison Analysis - Ads vs Posts

## 🎯 **Mục Tiêu**
So sánh chi tiết các chỉ số có thể lấy được từ:
- **Ads Posts** (quảng cáo) - từ `SyncFacebookAdsWithVideoMetrics.php`
- **Normal Posts** (bài viết thường) - từ `SyncFacebookFanpageAndPosts.php`

## 📈 **1. ADS POSTS METRICS (Hiện Tại)**

### **A. Basic Metrics (Từ FacebookAdsService.php)**
```php
$fields = [
    'spend', 'reach', 'impressions', 'clicks', 'ctr', 'cpc', 'cpm', 'frequency',
    'unique_clicks', 'unique_ctr', 'actions', 'action_values', 'ad_name', 'ad_id',
    
    // Conversion metrics
    'conversions', 'conversion_values', 'cost_per_conversion', 'purchase_roas',
    
    // Click metrics
    'outbound_clicks', 'unique_outbound_clicks', 'inline_link_clicks', 'unique_inline_link_clicks',
    
    // Cost metrics
    'cost_per_action_type', 'cost_per_unique_action_type',
    
    // Video metrics
    'video_30_sec_watched_actions', 'video_avg_time_watched_actions',
    'video_p25_watched_actions', 'video_p50_watched_actions', 
    'video_p75_watched_actions', 'video_p95_watched_actions', 'video_p100_watched_actions',
    'video_play_actions'
];
```

### **B. Demographics Breakdowns**
```php
$mainBreakdowns = [
    'demographics' => ['age', 'gender'],
    'geographic' => ['country', 'region'],
    'platform' => ['publisher_platform', 'device_platform', 'impression_device']
];
```

### **C. Action Breakdowns**
```php
$actionBreakdowns = [
    'action_device', 'action_destination', 'action_target_id', 'action_reaction',
    'action_video_sound', 'action_video_type', 'action_carousel_card_id',
    'action_carousel_card_name', 'action_canvas_component_name',
    'matched_persona_id', 'matched_persona_name', 'signal_source_bucket',
    'standard_event_content_type', 'conversion_destination'
];
```

### **D. Asset Breakdowns**
```php
$assetBreakdowns = [
    'video_asset', 'image_asset', 'body_asset', 'title_asset',
    'description_asset', 'call_to_action_asset', 'link_url_asset', 'ad_format_asset'
];
```

## 📝 **2. NORMAL POSTS METRICS (Hiện Tại)**

### **A. Basic Metrics (Từ SyncFacebookFanpageAndPosts.php)**
```php
$fields = [
    'id', 'message', 'created_time', 'permalink_url',
    'from{id,name,picture}', 'attachments{media_type,media,url,title,description}',
    'shares', 'comments.limit(10){id,message,from,created_time}',
    'likes.limit(10){id,name}'
];
```

### **B. Insights Metrics**
```php
$insightsMetrics = [
    'post_impressions', 'post_impressions_unique', 'post_impressions_paid',
    'post_impressions_organic', 'post_impressions_viral', 'post_clicks',
    'post_video_views', 'post_video_views_paid', 'post_video_views_organic',
    'post_reactions_like_total', 'post_reactions_love_total', 'post_reactions_wow_total',
    'post_reactions_haha_total', 'post_reactions_sorry_total', 'post_reactions_anger_total'
];
```

## 🔍 **3. FACEBOOK GRAPH API v23.0 ANALYSIS**

### **A. Post Insights Endpoints**
1. **`/{post_id}/insights`** - Basic post insights
2. **`/{video_id}/video_insights`** - Video-specific insights (cho video posts)

### **B. Available Metrics for Normal Posts**

#### **✅ Có Thể Lấy Được:**
```php
// Basic Post Metrics
'post_impressions', 'post_impressions_unique', 'post_impressions_organic',
'post_impressions_viral', 'post_clicks', 'post_video_views',
'post_reactions_like_total', 'post_reactions_love_total',
'post_reactions_wow_total', 'post_reactions_haha_total',
'post_reactions_sorry_total', 'post_reactions_anger_total'

// Video Metrics (cho video posts)
'video_views', 'video_views_autoplayed', 'video_views_clicked_to_play',
'video_views_unique', 'video_avg_time_watched', 'video_complete_views',
'video_retention_graph', 'video_play_actions'

// Engagement Metrics
'post_engaged_users', 'post_engaged_fan', 'post_negative_feedback',
'post_positive_feedback', 'post_impressions_by_story_type'
```

#### **❌ Không Thể Lấy Được (Chỉ có trong Ads):**
```php
// Demographics (chỉ có trong ads)
'age', 'gender', 'country', 'region', 'city'

// Device/Platform (chỉ có trong ads)  
'device_platform', 'publisher_platform', 'impression_device'

// Cost/Spend (chỉ có trong ads)
'spend', 'cpc', 'cpm', 'cost_per_action_type'

// Conversion (chỉ có trong ads)
'conversions', 'conversion_values', 'purchase_roas'

// Advanced Breakdowns (chỉ có trong ads)
'action_breakdowns', 'asset_breakdowns', 'demographic_breakdowns'
```

## 🎯 **4. COMPARISON MATRIX**

| Metric Category | Ads Posts | Normal Posts | Video Posts | Status |
|----------------|-----------|--------------|-------------|---------|
| **Basic Impressions** | ✅ | ✅ | ✅ | **Available** |
| **Reach/Unique** | ✅ | ✅ | ✅ | **Available** |
| **Clicks** | ✅ | ✅ | ✅ | **Available** |
| **Reactions** | ✅ | ✅ | ✅ | **Available** |
| **Comments/Shares** | ✅ | ✅ | ✅ | **Available** |
| **Video Views** | ✅ | ✅ | ✅ | **Available** |
| **Video Retention** | ✅ | ❌ | ✅ | **Video Only** |
| **Demographics** | ✅ | ❌ | ❌ | **Ads Only** |
| **Device/Platform** | ✅ | ❌ | ❌ | **Ads Only** |
| **Cost/Spend** | ✅ | ❌ | ❌ | **Ads Only** |
| **Conversions** | ✅ | ❌ | ❌ | **Ads Only** |

## 🚀 **5. RECOMMENDATIONS**

### **A. Bổ Sung Metrics Cho Normal Posts**

#### **1. Video Posts (Có thể lấy thêm)**
```php
// Thêm vào SyncFacebookFanpageAndPosts.php
$videoMetrics = [
    'video_views_autoplayed', 'video_views_clicked_to_play',
    'video_views_unique', 'video_avg_time_watched', 'video_complete_views',
    'video_retention_graph', 'video_play_actions'
];
```

#### **2. Enhanced Engagement**
```php
$enhancedMetrics = [
    'post_engaged_users', 'post_engaged_fan', 'post_negative_feedback',
    'post_positive_feedback', 'post_impressions_by_story_type'
];
```

### **B. Database Schema Updates**

#### **1. Thêm Video Metrics Table**
```sql
CREATE TABLE facebook_video_insights (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    post_id VARCHAR(255) NOT NULL,
    video_id VARCHAR(255),
    video_views_autoplayed INT DEFAULT 0,
    video_views_clicked_to_play INT DEFAULT 0,
    video_views_unique INT DEFAULT 0,
    video_avg_time_watched DECIMAL(10,2) DEFAULT 0,
    video_complete_views INT DEFAULT 0,
    video_retention_graph JSON,
    video_play_actions JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### **2. Thêm Enhanced Post Metrics**
```sql
ALTER TABLE post_facebook_fanpage_not_ads 
ADD COLUMN post_engaged_users INT DEFAULT 0,
ADD COLUMN post_engaged_fan INT DEFAULT 0,
ADD COLUMN post_negative_feedback INT DEFAULT 0,
ADD COLUMN post_positive_feedback INT DEFAULT 0,
ADD COLUMN post_impressions_by_story_type JSON;
```

### **C. Code Implementation**

#### **1. Enhanced Post Sync Command**
```php
// Thêm vào SyncFacebookFanpageAndPosts.php
private function getEnhancedPostInsights($postId, $accessToken) {
    $url = "{$this->graphApiUrl}/{$postId}/insights";
    $params = [
        'access_token' => $accessToken,
        'metric' => implode(',', [
            'post_impressions', 'post_impressions_unique', 'post_impressions_organic',
            'post_impressions_viral', 'post_clicks', 'post_video_views',
            'post_reactions_like_total', 'post_reactions_love_total',
            'post_engaged_users', 'post_engaged_fan', 'post_negative_feedback',
            'post_positive_feedback', 'post_impressions_by_story_type'
        ])
    ];
    
    return Http::timeout(30)->get($url, $params);
}
```

#### **2. Video Insights Command**
```php
// Tạo command mới: SyncFacebookVideoInsights.php
private function getVideoInsights($videoId, $accessToken) {
    $url = "{$this->graphApiUrl}/{$videoId}/video_insights";
    $params = [
        'access_token' => $accessToken,
        'metric' => implode(',', [
            'video_views', 'video_views_autoplayed', 'video_views_clicked_to_play',
            'video_views_unique', 'video_avg_time_watched', 'video_complete_views',
            'video_retention_graph', 'video_play_actions'
        ])
    ];
    
    return Http::timeout(30)->get($url, $params);
}
```

## 📊 **6. FINAL COMPARISON SUMMARY**

### **✅ Metrics Available for Both:**
- Impressions (total, unique, organic, viral)
- Clicks
- Reactions (like, love, wow, haha, sorry, anger)
- Comments & Shares
- Video Views (basic)

### **✅ Metrics Available for Video Posts Only:**
- Video Retention Graph
- Video Completion Rates
- Video Play Actions
- Video Average Time Watched

### **❌ Metrics Available for Ads Only:**
- Demographics (age, gender, country, region)
- Device/Platform breakdowns
- Cost/Spend metrics
- Conversion tracking
- Advanced action breakdowns

### **🎯 Conclusion:**
**Normal posts có thể lấy được ~70% metrics so với ads posts**, thiếu chủ yếu demographics và cost metrics. Video posts có thể lấy thêm video-specific metrics.

## 🚀 **Next Steps:**
1. Implement enhanced post insights
2. Add video insights for video posts
3. Create comparison dashboard
4. Set up automated sync for all metrics
