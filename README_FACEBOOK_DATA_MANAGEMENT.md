# Quản lý dữ liệu Facebook

Màn hình quản lý dữ liệu Facebook cho phép người dùng xem và phân tích dữ liệu từ các trang Facebook và bài viết một cách trực quan và dễ dàng.

## Tính năng chính

### 1. Chọn Trang Facebook
- Dropdown để chọn giữa nhiều Page theo Page ID
- Hiển thị thông tin cơ bản: tên trang, danh mục, số lượng fan
- Chỉ hiển thị các trang có dữ liệu insights

### 2. Bộ lọc dữ liệu
- **Thời gian**: Từ ngày - Đến ngày
- **Loại bài viết**: Trạng thái, Hình ảnh, Video, Liên kết, Sự kiện, Ưu đãi
- **Tìm kiếm**: Tìm kiếm trong nội dung bài viết
- **Trạng thái**: Lọc theo trạng thái quảng cáo

### 3. Hiển thị bài viết
- **Thông tin cơ bản**: Tiêu đề, ngày đăng, loại bài viết
- **Số liệu tương tác**: Lượt thích, chia sẻ, bình luận, tổng tương tác
- **Thống kê quảng cáo**: Chi phí, hiển thị, click, chuyển đổi
- **Liên kết**: Dẫn đến bài viết trên Facebook và trang Facebook

### 4. Thống kê chi phí
- Bảng thống kê chi phí theo từng bài viết
- Tổng hợp: Tổng chi phí, hiển thị, click, chuyển đổi
- Chỉ số trung bình: CPC, CPM

## Cấu trúc dữ liệu

### Models sử dụng
- `FacebookPage`: Thông tin trang Facebook
- `FacebookPost`: Bài viết trên Facebook
- `FacebookAd`: Quảng cáo Facebook
- `FacebookAdInsight`: Dữ liệu insights của quảng cáo

### Quan hệ dữ liệu
```
FacebookPage (1) → (N) FacebookPost
FacebookPost (1) → (N) FacebookAd
FacebookAd (1) → (N) FacebookAdInsight
```

## Phân quyền

### Permission: `view-facebook-data`
- **Super Admin**: Có tất cả quyền
- **Admin**: Có tất cả quyền
- **Manager**: Có quyền xem và quản lý dữ liệu Facebook
- **User**: Có quyền xem dữ liệu Facebook

### Middleware
- `auth`: Yêu cầu đăng nhập
- `permission.404:view-facebook-data`: Kiểm tra quyền truy cập

## Cài đặt và sử dụng

### 1. Chạy migration và seeder
```bash
php artisan migrate
php artisan db:seed --class=RolePermissionSeeder
```

### 2. Truy cập màn hình
```
/facebook/data-management
```

### 3. Sử dụng API endpoints
```bash
# Lấy danh sách bài viết theo page
GET /facebook/data-management/posts?page_id={page_id}

# Lấy thống kê chi phí
GET /facebook/data-management/spending-stats?page_id={page_id}&date_from={date}&date_to={date}
```

## Cấu hình

### Environment variables
Không cần cấu hình thêm, sử dụng cấu hình database hiện tại.

### Customization
- Thay đổi giao diện: Chỉnh sửa file `resources/views/facebook/data-management/index.blade.php`
- Thay đổi logic: Chỉnh sửa `App\Services\FacebookDataService`
- Thay đổi validation: Chỉnh sửa `App\Http\Requests\FacebookDataFilterRequest`

## Testing

### Chạy test
```bash
php artisan test --filter=FacebookDataManagementTest
```

### Test cases
- Kiểm tra quyền truy cập
- Kiểm tra hiển thị dữ liệu
- Kiểm tra bộ lọc
- Kiểm tra API endpoints
- Kiểm tra tính toán thống kê

## Troubleshooting

### Lỗi thường gặp

1. **Không hiển thị dữ liệu**
   - Kiểm tra bảng `facebook_pages` có dữ liệu
   - Kiểm tra quan hệ giữa các bảng
   - Kiểm tra quyền truy cập

2. **Lỗi permission**
   - Chạy lại seeder: `php artisan db:seed --class=RolePermissionSeeder`
   - Kiểm tra user có role và permission phù hợp

3. **Hiệu suất chậm**
   - Kiểm tra index trên các trường thường query
   - Sử dụng eager loading để tránh N+1 query
   - Cân nhắc cache cho dữ liệu ít thay đổi

### Debug
- Kiểm tra log: `storage/logs/laravel.log`
- Sử dụng `dd()` hoặc `Log::info()` trong service
- Kiểm tra query SQL với `DB::enableQueryLog()`

## Tính năng mở rộng

### Có thể thêm
- Export dữ liệu ra Excel/CSV
- Biểu đồ thống kê trực quan
- So sánh hiệu suất giữa các trang
- Alert khi chi phí vượt ngưỡng
- Lịch sử thay đổi dữ liệu

### Tích hợp
- Webhook để cập nhật real-time
- Notification qua email/SMS
- Dashboard widget
- Mobile app API

## Liên hệ hỗ trợ

Nếu gặp vấn đề hoặc cần hỗ trợ, vui lòng:
1. Kiểm tra log và error message
2. Xem xét cấu hình database
3. Kiểm tra quyền truy cập
4. Liên hệ team phát triển 