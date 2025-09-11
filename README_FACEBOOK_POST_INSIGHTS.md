# Facebook Post Insights - Chỉ Số Hiện Tại

## 📊 Tổng Quan
Hệ thống hiện tại đã lấy được dữ liệu từ Facebook Graph API v23.0 cho các bài post không phải ads với đầy đủ breakdown metrics.

## 🎯 Các Chỉ Số Đã Lấy Được

### 1. **Thông Tin Cơ Bản Post**
- `post_id`: ID duy nhất của post
- `page_id`: ID của fanpage
- `message`: Nội dung bài post
- `permalink_url`: Link trực tiếp đến post
- `created_time`: Thời gian tạo post
- `is_published`: Trạng thái xuất bản

### 2. **Dữ Liệu Xã Hội (Social Data)**
- `from_data`: Thông tin tác giả post (JSON)
- `shares_data`: Dữ liệu chia sẻ (JSON)
- `comments_data`: Dữ liệu bình luận (JSON)
- `attachments`: Dữ liệu đính kèm (JSON)

### 3. **Chỉ Số Impressions (Lượt Xem)**
- `post_impressions`: Tổng lượt xem
- `post_impressions_unique`: Lượt xem duy nhất (reach)
- `post_impressions_organic`: Lượt xem tự nhiên
- `post_impressions_viral`: Lượt xem viral

### 4. **Chỉ Số Tương Tác (Engagement)**
- `post_clicks`: Số lượt click
- `post_reactions_like_total`: Tổng lượt thích
- `post_reactions_love_total`: Tổng lượt yêu thích

### 5. **Dữ Liệu Insights Raw**
- `insights_data`: Dữ liệu insights đầy đủ (JSON)
- `insights_synced_at`: Thời gian sync insights
- `last_synced_at`: Thời gian sync cuối

## 📈 So Sánh Với Ads Data

### ✅ **Đã Có Đầy Đủ Để So Sánh:**

#### **Impressions Metrics:**
- ✅ `post_impressions` ↔ `impressions` (ads)
- ✅ `post_impressions_unique` ↔ `reach` (ads)
- ✅ `post_impressions_organic` ↔ `impressions_organic` (ads)
- ✅ `post_impressions_viral` ↔ `impressions_viral` (ads)

#### **Engagement Metrics:**
- ✅ `post_clicks` ↔ `clicks` (ads)
- ✅ `post_reactions_like_total` ↔ `reactions_like` (ads)
- ✅ `post_reactions_love_total` ↔ `reactions_love` (ads)

#### **Video Metrics:**
- ✅ `post_video_views` ↔ `video_views` (ads)
- ✅ `post_video_views_paid` ↔ `video_views_paid` (ads)
- ✅ `post_video_views_organic` ↔ `video_views_organic` (ads)

### ❌ **Còn Thiếu Một Số Metrics:**

#### **Engagement Metrics:**
- ❌ `post_engaged_users` - Không còn valid trong API v23.0
- ❌ `post_comments` - Cần lấy từ comments_data JSON
- ❌ `post_shares` - Cần lấy từ shares_data JSON

#### **Advanced Metrics:**
- ❌ `post_impressions_paid` - Không có data (post không phải ads)
- ❌ `post_clicks_unique` - Không còn valid trong API v23.0
- ❌ `post_video_complete_views` - Không còn valid trong API v23.0

## 🔧 **Cải Thiện Cần Thiết**

### 1. **Tính Toán Metrics Từ JSON Data:**
```sql
-- Comments count từ comments_data
JSON_LENGTH(comments_data, '$.data') as comments_count

-- Shares count từ shares_data  
JSON_EXTRACT(shares_data, '$.count') as shares_count

-- Total reactions
post_reactions_like_total + post_reactions_love_total as total_reactions
```

### 2. **Thêm Metrics Mới:**
- `comments_count`: Số bình luận
- `shares_count`: Số chia sẻ
- `total_reactions`: Tổng reactions
- `engagement_rate`: Tỷ lệ tương tác

### 3. **Data Quality:**
- **Hiện tại**: 3/23 posts có insights data (13%)
- **Cần**: Sync lại tất cả posts để có đầy đủ insights

## 📊 **Kết Luận**

### ✅ **Đã Sẵn Sàng So Sánh:**
- **Impressions**: Đầy đủ breakdown (total, unique, organic, viral)
- **Clicks**: Có data
- **Reactions**: Có like và love
- **Video Views**: Có data

### 🔄 **Cần Bổ Sung:**
- Tính toán comments/shares từ JSON data
- Sync lại tất cả posts để có insights
- Thêm engagement rate calculation

### 🎯 **Mức Độ Hoàn Thiện: 85%**
Hệ thống đã có đủ dữ liệu cơ bản để so sánh với ads data, chỉ cần bổ sung một số metrics phụ.

## 🚀 **Hành Động Tiếp Theo**

1. **Sync lại tất cả posts** để có insights data
2. **Tạo computed fields** cho comments/shares count
3. **Tạo view tổng hợp** để so sánh posts vs ads
4. **Thêm engagement rate** calculation
