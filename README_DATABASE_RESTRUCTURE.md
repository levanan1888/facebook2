# 🗄️ Tái cấu trúc Database theo chuẩn hóa

## 📋 Tổng quan

Database đã được tái cấu trúc từ cấu trúc không chuẩn hóa (denormalized) sang cấu trúc chuẩn hóa (normalized) để:
- Giảm dữ liệu trùng lặp
- Dễ dàng maintain và mở rộng
- Tối ưu performance cho báo cáo
- Tuân thủ các nguyên tắc database design

## 🏗️ Cấu trúc database mới

### 📊 **Các bảng chính:**

```
facebook_businesses          # Business Managers
├── facebook_ad_accounts     # Ad Accounts
    ├── facebook_campaigns   # Campaigns
        ├── facebook_ad_sets # Ad Sets
            └── facebook_ads # Ads
                ├── facebook_creatives      # Creative content
                ├── facebook_ad_insights    # Ad performance data
                └── facebook_posts          # Posts (nếu là Post Ad)
                    └── facebook_post_insights  # Post performance data
```

### 📋 **Chi tiết từng bảng:**

#### 1. **facebook_businesses** (Business Managers)
```sql
- id (PK): Business ID từ Facebook
- name: Tên business
- verification_status: Trạng thái xác minh
- created_time: Thời gian tạo từ Facebook
- timestamps: Laravel timestamps
```

#### 2. **facebook_ad_accounts** (Ad Accounts)
```sql
- id (PK): Account ID từ Facebook
- account_id: Account ID số
- name: Tên account
- account_status: Trạng thái account
- business_id (FK): Liên kết với Business
- timestamps: Laravel timestamps
```

#### 3. **facebook_campaigns** (Campaigns)
```sql
- id (PK): Campaign ID từ Facebook
- name: Tên campaign
- status: Trạng thái campaign
- objective: Mục tiêu campaign
- start_time, stop_time: Thời gian chạy
- effective_status: Trạng thái hiệu quả
- ad_account_id (FK): Liên kết với Ad Account
- timestamps: Laravel timestamps
```

#### 4. **facebook_ad_sets** (Ad Sets)
```sql
- id (PK): Ad Set ID từ Facebook
- name: Tên ad set
- status: Trạng thái ad set
- optimization_goal: Mục tiêu tối ưu
- campaign_id (FK): Liên kết với Campaign
- timestamps: Laravel timestamps
```

#### 5. **facebook_ads** (Ads) - **Đã được tối ưu**
```sql
- id (PK): Ad ID từ Facebook
- name: Tên ad
- status: Trạng thái ad
- effective_status: Trạng thái hiệu quả
- adset_id (FK): Liên kết với Ad Set
- campaign_id (FK): Liên kết với Campaign
- account_id (FK): Liên kết với Ad Account
- post_id (FK): Liên kết với Post (nếu có)
- page_id (FK): Liên kết với Page (nếu có)
- created_time, updated_time: Thời gian từ Facebook
- last_insights_sync: Lần sync insights cuối
- timestamps: Laravel timestamps
```

#### 6. **facebook_posts** (Posts) - **Bảng mới**
```sql
- id (PK): Post ID từ Facebook
- page_id (FK): Liên kết với Page
- message: Nội dung post
- type: Loại post (photo, video, carousel_album, link)
- status_type: Trạng thái post
- attachments: JSON data đính kèm
- permalink_url: URL post
- created_time, updated_time: Thời gian từ Facebook
- timestamps: Laravel timestamps
```

#### 7. **facebook_creatives** (Creatives) - **Bảng mới**
```sql
- id (PK): Creative ID
- ad_id (FK): Liên kết với Ad
- creative_data: JSON data creative từ Facebook
- link_url: URL link (nếu là link ad)
- link_message: Message link
- link_name: Tên link
- image_hash: Hash hình ảnh
- call_to_action_type: Loại call-to-action
- page_welcome_message: Message chào mừng
- created_time, updated_time: Thời gian từ Facebook
- timestamps: Laravel timestamps
```

#### 8. **facebook_ad_insights** (Ad Performance) - **Bảng mới**
```sql
- id (PK): Auto increment
- ad_id (FK): Liên kết với Ad
- date: Ngày dữ liệu
- spend: Chi phí
- reach: Tiếp cận
- impressions: Hiển thị
- clicks: Lượt click
- unique_clicks: Click duy nhất
- ctr, cpc, cpm, frequency: Metrics hiệu suất
- conversions, conversion_values: Chuyển đổi
- purchase_roas: ROAS
- outbound_clicks, inline_link_clicks: Click metrics
- actions, action_values: JSON data actions
- timestamps: Laravel timestamps
```

#### 9. **facebook_post_insights** (Post Performance) - **Bảng mới**
```sql
- id (PK): Auto increment
- post_id (FK): Liên kết với Post
- date: Ngày dữ liệu
- impressions, reach, clicks: Metrics cơ bản
- likes, shares, comments, reactions: Engagement metrics
- saves, hides, unlikes: Negative metrics
- video_views, video_view_time: Video metrics
- engagement_rate, ctr, cpm, cpc: Performance metrics
- spend, frequency: Cost metrics
- actions, action_values: JSON data actions
- timestamps: Laravel timestamps
```

#### 10. **facebook_pages** (Pages) - **Bảng mới**
```sql
- id (PK): Page ID từ Facebook
- name: Tên page
- category: Danh mục page
- category_list: Danh sách danh mục
- about: Mô tả page
- fan_count: Số lượng fan
- verification_status: Trạng thái xác minh
- created_time: Thời gian tạo từ Facebook
- timestamps: Laravel timestamps
```

#### 11. **facebook_report_summary** (Bảng tổng hợp) - **Bảng mới**
```sql
- id (PK): Auto increment
- entity_type: Loại entity (business, account, campaign, adset, ad, post)
- entity_id: ID của entity
- date: Ngày dữ liệu
- total_spend, total_reach, total_impressions, total_clicks: Metrics tổng hợp
- avg_ctr, avg_cpc, avg_cpm, avg_frequency: Metrics trung bình
- total_likes, total_shares, total_comments, total_reactions: Engagement tổng hợp
- total_conversions, total_conversion_values: Conversion tổng hợp
- avg_cost_per_conversion, avg_purchase_roas: Performance trung bình
- timestamps: Laravel timestamps
```

## 🔄 **Quá trình migration:**

### 1. **Tạo cấu trúc mới**
```bash
php artisan migrate --path=database/migrations/2025_08_23_000001_restructure_facebook_database_normalized.php
```

### 2. **Migrate dữ liệu**
```bash
php artisan migrate --path=database/migrations/2025_08_23_000002_migrate_data_to_normalized_tables.php
```

### 3. **Cập nhật bảng tổng hợp**
```bash
# Cập nhật cho hôm nay
php artisan facebook:update-summary

# Cập nhật cho ngày cụ thể
php artisan facebook:update-summary --date=2025-08-23

# Cập nhật tất cả ngày trong tháng
php artisan facebook:update-summary --all
```

## 🎯 **Lợi ích của cấu trúc mới:**

### ✅ **Chuẩn hóa dữ liệu:**
- Không còn dữ liệu trùng lặp
- Mỗi bảng có trách nhiệm rõ ràng
- Dễ dàng thêm/sửa/xóa dữ liệu

### ✅ **Performance:**
- Bảng tổng hợp cho báo cáo nhanh
- Indexes tối ưu cho truy vấn
- Có thể cache từng bảng riêng biệt

### ✅ **Maintainability:**
- Dễ dàng thêm metrics mới
- Có thể scale từng bảng độc lập
- Backup/restore từng bảng riêng biệt

### ✅ **Flexibility:**
- Hỗ trợ nhiều loại ads khác nhau
- Dễ dàng mở rộng cho platforms khác
- Có thể thêm dimensions mới

## 📊 **Cách sử dụng:**

### 1. **Truy vấn dữ liệu cơ bản:**
```php
// Lấy ad với insights
$ad = FacebookAd::with(['insights', 'post.insights'])->find($adId);

// Lấy campaign với tổng hợp
$campaign = FacebookCampaign::with(['adSets.ads.insights'])->find($campaignId);
```

### 2. **Truy vấn báo cáo:**
```php
// Lấy summary cho campaign
$summary = DB::table('facebook_report_summary')
    ->where('entity_type', 'campaign')
    ->where('entity_id', $campaignId)
    ->whereBetween('date', [$from, $to])
    ->get();
```

### 3. **Tự động cập nhật:**
```php
// Trong service sync
$summaryService = app(ReportSummaryService::class);
$summaryService->updateAllSummaries(date('Y-m-d'));
```

## 🔧 **Cron Job để tự động cập nhật:**

Thêm vào `app/Console/Kernel.php`:
```php
protected function schedule(Schedule $schedule): void
{
    // Cập nhật bảng tổng hợp hàng ngày lúc 2h sáng
    $schedule->command('facebook:update-summary')
        ->dailyAt('02:00')
        ->withoutOverlapping();
}
```

## 📈 **Monitoring:**

### 1. **Kiểm tra dữ liệu:**
```bash
# Kiểm tra số lượng records
php artisan tinker
>>> DB::table('facebook_ads')->count();
>>> DB::table('facebook_ad_insights')->count();
>>> DB::table('facebook_report_summary')->count();
```

### 2. **Kiểm tra performance:**
```sql
-- Kiểm tra query performance
EXPLAIN SELECT * FROM facebook_report_summary 
WHERE entity_type = 'campaign' AND date = '2025-08-23';
```

## 🚨 **Lưu ý quan trọng:**

1. **Backup trước khi migrate:** Luôn backup database trước khi chạy migration
2. **Test trên staging:** Test đầy đủ trên môi trường staging trước khi deploy production
3. **Monitor performance:** Theo dõi performance sau khi migrate
4. **Update code:** Cập nhật tất cả code sử dụng bảng cũ sang bảng mới

## 📚 **Tài liệu tham khảo:**

- [Database Normalization](https://en.wikipedia.org/wiki/Database_normalization)
- [Laravel Migrations](https://laravel.com/docs/11.x/migrations)
- [Laravel Eloquent Relationships](https://laravel.com/docs/11.x/eloquent-relationships)
