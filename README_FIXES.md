# Báo cáo sửa lỗi

## Tổng quan

Tài liệu này ghi lại các thay đổi đã thực hiện để sửa các lỗi trong hệ thống Facebook Dashboard.

## Các vấn đề đã được sửa

### 1. Lỗi redirect sau login

**Vấn đề:** Sau khi đăng nhập, hệ thống đang redirect sang URL `/api/dashboard/data-sources-status` thay vì `/facebook/overview`

**Giải pháp:**
- Tạo middleware `RedirectAfterLogin` để redirect người dùng về trang `facebook/overview` sau khi đăng nhập
- Đăng ký middleware trong `bootstrap/app.php`
- Áp dụng middleware vào route `dashboard`

**Files đã sửa:**
- `app/Http/Middleware/RedirectAfterLogin.php` (mới)
- `bootstrap/app.php`
- `routes/web.php`

### 2. Lỗi JS - "Lỗi khi tải dữ liệu thống nhất"

**Vấn đề:** Trình duyệt hiển thị lỗi JavaScript khi tải dữ liệu thống nhất do bảng `facebook_posts` không tồn tại

**Nguyên nhân:** Bảng `facebook_posts` đã bị xóa trong migration `2025_08_25_160000_drop_legacy_fb_tables.php` nhưng code vẫn đang cố gắng truy cập vào nó

**Giải pháp:**
- Sửa `UnifiedDataService` để sử dụng dữ liệu từ bảng `facebook_ads` thay vì `facebook_posts`
- Sửa `DashboardReportService` để sử dụng dữ liệu từ bảng `facebook_ads` thay vì `FacebookPost`
- Sửa `DashboardApiController` để xử lý lỗi tốt hơn và trả về response hợp lệ
- Sửa các view files để sử dụng `facebook_ads` thay vì `facebook_posts`
- Sửa JavaScript files để sử dụng `facebook_ads` thay vì `facebook_posts`

**Files đã sửa:**
- `app/Services/UnifiedDataService.php`
- `app/Services/DashboardReportService.php`
- `app/Http/Controllers/Api/DashboardApiController.php`
- `resources/views/dashboard/tabs/unified-data.blade.php`
- `resources/views/dashboard/tabs/comparison.blade.php`
- `resources/js/dashboard-unified.js`

## Cấu trúc dữ liệu mới

### Thay thế FacebookPost bằng FacebookAd

**Trước đây:**
```php
// Sử dụng FacebookPost model
$posts = FacebookPost::select([
    'facebook_posts.id as post_id',
    'facebook_posts.message as post_message',
    'facebook_posts.type as post_type',
    'facebook_posts.likes_count as post_likes',
    'facebook_posts.shares_count as post_shares',
    'facebook_posts.comments_count as post_comments'
])->get();
```

**Hiện tại:**
```php
// Sử dụng FacebookAd model với các trường post_*
$posts = FacebookAd::select([
    'facebook_ads.id as ad_id',
    'facebook_ads.post_message as post_message',
    'facebook_ads.post_type as post_type',
    'facebook_ads.post_likes as post_likes',
    'facebook_ads.post_shares as post_shares',
    'facebook_ads.post_comments as post_comments'
])
->whereNotNull('post_id')
->whereNotNull('post_message')
->get();
```

### Các trường tương ứng

| FacebookPost (cũ) | FacebookAd (mới) |
|-------------------|-------------------|
| `id` | `id` |
| `message` | `post_message` |
| `type` | `post_type` |
| `likes_count` | `post_likes` |
| `shares_count` | `post_shares` |
| `comments_count` | `post_comments` |
| `created_time` | `post_created_time` |

## Các vấn đề còn lại

### 1. FacebookAdsSyncService

Service này vẫn đang sử dụng `FacebookPost` model để lưu dữ liệu post. Cần sửa để lưu dữ liệu post vào bảng `facebook_ads` thay thế.

**Files cần sửa:**
- `app/Services/FacebookAdsSyncService.php`

### 2. FacebookDataController

Controller này vẫn đang sử dụng `FacebookPostResource` và `FacebookPost` model.

**Files cần sửa:**
- `app/Http/Controllers/FacebookDataController.php`
- `app/Http/Resources/FacebookPostResource.php`

### 3. Các Model relationships

Một số model vẫn đang có relationship với `FacebookPost` model.

**Files cần sửa:**
- `app/Models/FacebookAd.php`
- `app/Models/FacebookPage.php`
- `app/Models/FacebookReportSummary.php`
- `app/Models/FacebookPostInsight.php`

### 4. Test files

Các file test vẫn đang sử dụng `FacebookPost` model.

**Files cần sửa:**
- `tests/Feature/FacebookDataManagementTest.php`

## Hướng dẫn tiếp theo

### 1. Sửa FacebookAdsSyncService

Cần sửa service này để:
- Không sử dụng `FacebookPost::updateOrCreate()`
- Lưu dữ liệu post vào các trường `post_*` của bảng `facebook_ads`
- Cập nhật logic để sử dụng cấu trúc dữ liệu mới

### 2. Sửa FacebookDataController

Cần sửa controller này để:
- Không sử dụng `FacebookPostResource`
- Lấy dữ liệu từ bảng `facebook_ads` thay vì `facebook_posts`
- Tạo resource mới hoặc sửa đổi resource hiện tại

### 3. Sửa các Model relationships

Cần sửa các model để:
- Không có relationship với `FacebookPost` model
- Sử dụng relationship với bảng `facebook_ads` thay thế

### 4. Cập nhật Test files

Cần cập nhật các file test để:
- Không sử dụng `FacebookPost` model
- Sử dụng `FacebookAd` model với dữ liệu post

## Kết luận

Các vấn đề chính đã được sửa:
- ✅ Lỗi redirect sau login
- ✅ Lỗi JS - "Lỗi khi tải dữ liệu thống nhất"

Tuy nhiên, vẫn còn một số vấn đề cần sửa để hoàn thiện hệ thống:
- ⚠️ FacebookAdsSyncService
- ⚠️ FacebookDataController
- ⚠️ Các Model relationships
- ⚠️ Test files

Các thay đổi này sẽ giúp hệ thống hoạt động ổn định và không còn lỗi khi truy cập dữ liệu post.
