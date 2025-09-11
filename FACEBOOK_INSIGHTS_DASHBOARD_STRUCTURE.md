# 📊 Facebook Insights Dashboard Structure

## 🎯 Tổng quan hệ thống

Hệ thống Facebook Insights được thiết kế để theo dõi và phân tích hiệu suất của các bài đăng Facebook, đặc biệt tập trung vào video posts với dữ liệu insights chi tiết theo ngày.

## 🗄️ Cấu trúc Database

### 1. **facebook_fanpage** - Thông tin Fanpage
```sql
- id (Primary Key)
- page_id (Facebook Page ID - Unique)
- name (Tên fanpage)
- about (Mô tả fanpage)
- website (Website)
- phone (Số điện thoại)
- email (Email)
- location (Vị trí - Text)
- cover_photo_url (URL ảnh bìa)
- profile_picture_url (URL ảnh đại diện)
- access_token (Page Access Token)
- category (Danh mục)
- category_list (Danh sách danh mục)
- is_published (Trạng thái xuất bản)
- is_verified (Trạng thái xác minh)
- fan_count (Số fan)
- followers_count (Số người theo dõi)
- likes_count (Số lượt thích)
- last_synced_at (Thời gian đồng bộ cuối)
- created_at, updated_at
```

### 2. **post_facebook_fanpage_not_ads** - Bài đăng Fanpage
```sql
- id (Primary Key)
- post_id (Facebook Post ID)
- page_id (Foreign Key → facebook_fanpage.page_id)
- message (Nội dung bài đăng)
- attachments (Đính kèm - JSON)
- type (Loại bài đăng)
- status_type (Trạng thái)
- permalink_url (URL bài đăng)
- link (Link)
- picture (Ảnh)
- full_picture (Ảnh đầy đủ)
- source (Nguồn)
- properties (Thuộc tính - JSON)
- actions (Hành động - JSON)
- privacy (Quyền riêng tư - JSON)
- place (Địa điểm - JSON)
- coordinates (Tọa độ - JSON)
- targeting (Mục tiêu - JSON)
- feed_targeting (Mục tiêu feed - JSON)
- promotion_status (Trạng thái quảng cáo)
- scheduled_publish_time (Thời gian lên lịch)
- backdated_time (Thời gian lùi)
- call_to_action (Lời gọi hành động - JSON)
- parent_id (ID bài đăng cha)
- timeline_visibility (Hiển thị timeline)
- is_hidden (Ẩn)
- is_expired (Hết hạn)
- is_published (Đã xuất bản)
- is_popular (Phổ biến)
- is_spherical (Hình cầu)
- is_instagram_eligible (Đủ điều kiện Instagram)
- is_eligible_for_promotion (Đủ điều kiện quảng cáo)
- created_time (Thời gian tạo)
- updated_time (Thời gian cập nhật)
- created_at, updated_at

-- Metrics columns
- post_impressions (Lượt hiển thị)
- post_impressions_unique (Lượt hiển thị duy nhất)
- post_impressions_organic (Hiển thị hữu cơ)
- post_impressions_viral (Hiển thị lan truyền)
- post_clicks (Lượt click)
- post_engaged_users (Người dùng tương tác)
- post_reactions (Tổng reactions)
- post_reactions_like_total (Lượt thích)
- post_reactions_love_total (Lượt yêu thích)
- post_reactions_wow_total (Lượt wow)
- post_reactions_haha_total (Lượt haha)
- post_reactions_sorry_total (Lượt sorry)
- post_reactions_anger_total (Lượt tức giận)
- post_video_views (Lượt xem video)
- post_video_views_unique (Lượt xem video duy nhất)
- post_video_complete_views (Lượt xem hoàn thành)
- post_video_avg_time_watched (Thời gian xem trung bình)
- post_video_view_total_time (Tổng thời gian xem)
- insights_data (Dữ liệu insights - JSON)
- insights_synced_at (Thời gian đồng bộ insights)
```

### 3. **facebook_daily_insights** - Insights theo ngày
```sql
- id (Primary Key)
- post_id (Foreign Key → post_facebook_fanpage_not_ads.id)
- metric_name (Tên metric: post_impressions, post_clicks, etc.)
- date (Ngày - Y-m-d format)
- metric_value (Giá trị metric cho ngày)
- created_at, updated_at

-- Indexes
- INDEX(post_id, date)
- INDEX(metric_name, date)
- UNIQUE(post_id, metric_name, date)
```

### 4. **facebook_daily_video_insights** - Video insights theo ngày
```sql
- id (Primary Key)
- post_id (Foreign Key → post_facebook_fanpage_not_ads.id)
- video_id (Facebook Video ID)
- metric_name (Tên metric video: video_views, video_complete_views, etc.)
- date (Ngày - Y-m-d format)
- metric_value (Giá trị metric cho ngày)
- created_at, updated_at

-- Indexes
- INDEX(post_id, date)
- INDEX(video_id, date)
- INDEX(metric_name, date)
- UNIQUE(post_id, video_id, metric_name, date)
```

### 5. **facebook_video_insights** - Video insights tổng hợp
```sql
- id (Primary Key)
- post_id (Foreign Key → post_facebook_fanpage_not_ads.id)
- video_id (Facebook Video ID)
- video_views (Lượt xem video)
- video_views_unique (Lượt xem video duy nhất)
- video_views_autoplayed (Lượt xem tự động)
- video_views_clicked_to_play (Lượt xem click để phát)
- video_views_organic (Lượt xem hữu cơ)
- video_views_paid (Lượt xem trả phí)
- video_views_sound_on (Lượt xem có âm thanh)
- video_views_sound_off (Lượt xem không âm thanh)
- video_complete_views (Lượt xem hoàn thành)
- video_complete_views_unique (Lượt xem hoàn thành duy nhất)
- video_complete_views_organic (Lượt xem hoàn thành hữu cơ)
- video_complete_views_paid (Lượt xem hoàn thành trả phí)
- video_avg_time_watched (Thời gian xem trung bình)
- video_view_total_time (Tổng thời gian xem)
- video_retention_graph (Biểu đồ giữ chân - JSON)
- video_views_by_distribution_type (Lượt xem theo loại phân phối - JSON)
- video_views_by_region_id (Lượt xem theo vùng - JSON)
- video_views_by_age_bucket_and_gender (Lượt xem theo độ tuổi và giới tính - JSON)
- synced_at (Thời gian đồng bộ)
- created_at, updated_at
```

## 📈 Cấu trúc Biểu đồ Dashboard

### 1. **Tổng quan Fanpage**
```javascript
// Biểu đồ cột - Số liệu tổng quan
{
  title: "Tổng quan Fanpage",
  charts: [
    {
      type: "bar",
      data: {
        labels: ["Tổng Fanpage", "Có Access Token", "Đã xác minh"],
        datasets: [{
          label: "Số lượng",
          data: [totalPages, pagesWithTokens, verifiedPages]
        }]
      }
    }
  ]
}
```

### 2. **Phân tích Posts**
```javascript
// Biểu đồ tròn - Loại posts
{
  title: "Phân bố loại Posts",
  charts: [
    {
      type: "doughnut",
      data: {
        labels: ["Video Posts", "Image Posts", "Text Posts", "Link Posts"],
        datasets: [{
          data: [videoCount, imageCount, textCount, linkCount]
        }]
      }
    }
  ]
}

// Biểu đồ đường - Posts theo thời gian
{
  title: "Posts theo thời gian",
  charts: [
    {
      type: "line",
      data: {
        labels: ["Ngày 1", "Ngày 2", "Ngày 3", ...],
        datasets: [{
          label: "Tổng posts",
          data: [dailyPostCounts]
        }]
      }
    }
  ]
}
```

### 3. **Metrics Performance**
```javascript
// Biểu đồ cột - Top performing posts
{
  title: "Top Posts theo Impressions",
  charts: [
    {
      type: "bar",
      data: {
        labels: ["Post 1", "Post 2", "Post 3", ...],
        datasets: [{
          label: "Impressions",
          data: [impressions1, impressions2, impressions3, ...]
        }]
      }
    }
  ]
}

// Biểu đồ kết hợp - Engagement metrics
{
  title: "Engagement Metrics",
  charts: [
    {
      type: "line",
      data: {
        labels: ["Ngày 1", "Ngày 2", "Ngày 3", ...],
        datasets: [
          {
            label: "Impressions",
            data: [dailyImpressions],
            borderColor: "rgb(75, 192, 192)"
          },
          {
            label: "Clicks",
            data: [dailyClicks],
            borderColor: "rgb(255, 99, 132)"
          },
          {
            label: "Reactions",
            data: [dailyReactions],
            borderColor: "rgb(54, 162, 235)"
          }
        ]
      }
    }
  ]
}
```

### 4. **Video Analytics**
```javascript
// Biểu đồ cột - Video performance
{
  title: "Video Performance",
  charts: [
    {
      type: "bar",
      data: {
        labels: ["Video 1", "Video 2", "Video 3", ...],
        datasets: [
          {
            label: "Views",
            data: [videoViews1, videoViews2, videoViews3, ...]
          },
          {
            label: "Complete Views",
            data: [completeViews1, completeViews2, completeViews3, ...]
          }
        ]
      }
    }
  ]
}

// Biểu đồ đường - Video retention
{
  title: "Video Retention Rate",
  charts: [
    {
      type: "line",
      data: {
        labels: ["0s", "10s", "30s", "60s", "90s", "100%"],
        datasets: [{
          label: "Retention %",
          data: [100, 85, 70, 55, 40, 25]
        }]
      }
    }
  ]
}
```

### 5. **Daily Insights Trends**
```javascript
// Biểu đồ đường - Trends theo ngày
{
  title: "Daily Insights Trends",
  charts: [
    {
      type: "line",
      data: {
        labels: ["2025-09-01", "2025-09-02", "2025-09-03", ...],
        datasets: [
          {
            label: "Daily Impressions",
            data: [dailyImpressionsData],
            borderColor: "rgb(75, 192, 192)"
          },
          {
            label: "Daily Clicks",
            data: [dailyClicksData],
            borderColor: "rgb(255, 99, 132)"
          },
          {
            label: "Daily Video Views",
            data: [dailyVideoViewsData],
            borderColor: "rgb(54, 162, 235)"
          }
        ]
      }
    }
  ]
}
```

## 🔧 Commands để lấy dữ liệu

### 1. **Sync dữ liệu cơ bản**
```bash
# Sync fanpages và posts
php artisan facebook:sync-fanpage-posts --days=30 --limit=100

# Sync insights cho video posts
php artisan facebook:sync-enhanced-post-insights --days=30 --limit=50

# Sync insights theo ngày
php artisan facebook:sync-enhanced-post-insights --days=30 --daily --limit=50
```

### 2. **Sync tự động**
```bash
# Sync tất cả dữ liệu
php artisan facebook:sync-all-data --days=7 --limit=50
```

## 📊 Queries mẫu để tạo biểu đồ

### 1. **Tổng quan Fanpage**
```sql
SELECT 
  COUNT(*) as total_pages,
  COUNT(CASE WHEN access_token IS NOT NULL AND access_token != '' THEN 1 END) as pages_with_tokens,
  COUNT(CASE WHEN is_verified = 1 THEN 1 END) as verified_pages
FROM facebook_fanpage;
```

### 2. **Posts theo loại**
```sql
SELECT 
  CASE 
    WHEN attachments LIKE '%"media_type":"video"%' THEN 'Video'
    WHEN attachments LIKE '%"media_type":"photo"%' THEN 'Image'
    WHEN attachments LIKE '%"media_type":"link"%' THEN 'Link'
    ELSE 'Text'
  END as post_type,
  COUNT(*) as count
FROM post_facebook_fanpage_not_ads
GROUP BY post_type;
```

### 3. **Top Posts theo Impressions**
```sql
SELECT 
  post_id,
  post_impressions,
  post_clicks,
  post_reactions,
  created_time
FROM post_facebook_fanpage_not_ads
WHERE post_impressions > 0
ORDER BY post_impressions DESC
LIMIT 10;
```

### 4. **Daily Insights Trends**
```sql
SELECT 
  date,
  SUM(CASE WHEN metric_name = 'post_impressions' THEN metric_value ELSE 0 END) as daily_impressions,
  SUM(CASE WHEN metric_name = 'post_clicks' THEN metric_value ELSE 0 END) as daily_clicks,
  SUM(CASE WHEN metric_name = 'post_engaged_users' THEN metric_value ELSE 0 END) as daily_engaged_users
FROM facebook_daily_insights
WHERE date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
GROUP BY date
ORDER BY date;
```

### 5. **Video Performance**
```sql
SELECT 
  p.post_id,
  p.post_video_views,
  p.post_video_complete_views,
  p.post_video_avg_time_watched,
  v.video_views,
  v.video_complete_views,
  v.video_avg_time_watched
FROM post_facebook_fanpage_not_ads p
LEFT JOIN facebook_video_insights v ON p.id = v.post_id
WHERE p.attachments LIKE '%"media_type":"video"%'
ORDER BY p.post_video_views DESC;
```

## 🎯 KPI Metrics cần theo dõi

### 1. **Reach & Impressions**
- Total Impressions
- Unique Impressions
- Organic vs Viral Reach
- Impressions per Post

### 2. **Engagement**
- Total Reactions
- Reaction Rate (Reactions/Impressions)
- Click-through Rate (CTR)
- Engaged Users

### 3. **Video Performance**
- Video Views
- Video Completion Rate
- Average Watch Time
- Video Retention Curve

### 4. **Daily Trends**
- Daily Impressions
- Daily Engagement
- Daily Video Views
- Growth Rate

## 📱 Dashboard Layout

```
┌─────────────────────────────────────────────────────────────┐
│                    FACEBOOK INSIGHTS DASHBOARD              │
├─────────────────────────────────────────────────────────────┤
│  [Tổng quan] [Posts] [Video] [Daily Trends] [Settings]     │
├─────────────────────────────────────────────────────────────┤
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐           │
│  │ Total Pages │ │ Total Posts │ │ Video Posts │           │
│  │     10      │ │     156     │ │      23     │           │
│  └─────────────┘ └─────────────┘ └─────────────┘           │
├─────────────────────────────────────────────────────────────┤
│  ┌─────────────────────────────────────────────────────────┐ │
│  │              Posts Performance Chart                    │ │
│  │  [Bar Chart - Top Posts by Impressions]                │ │
│  └─────────────────────────────────────────────────────────┘ │
├─────────────────────────────────────────────────────────────┤
│  ┌─────────────────┐ ┌─────────────────┐                   │
│  │ Engagement Rate │ │ Video Views     │                   │
│  │     [Gauge]     │ │   [Line Chart]  │                   │
│  └─────────────────┘ └─────────────────┘                   │
└─────────────────────────────────────────────────────────────┘
```

## 🚀 Next Steps

1. **Tạo Dashboard Controller** để xử lý dữ liệu
2. **Implement Chart.js** hoặc **Chart.js** để hiển thị biểu đồ
3. **Tạo API endpoints** để lấy dữ liệu cho dashboard
4. **Thiết kế responsive UI** cho mobile và desktop
5. **Thêm real-time updates** với WebSocket hoặc polling
6. **Export reports** PDF/Excel cho báo cáo

---

*File này được tạo tự động bởi hệ thống Facebook Insights Sync*
*Cập nhật lần cuối: 2025-09-11*
