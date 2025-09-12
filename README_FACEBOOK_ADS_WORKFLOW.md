# Facebook Ads Sync Workflow

## Tổng quan

Hệ thống đã được tách biệt thành 2 luồng riêng biệt để tối ưu hóa việc đồng bộ Facebook ads:

1. **Sync Ads Structure**: Chỉ đồng bộ cấu trúc ads (campaigns, adsets, ads) - KHÔNG có insights
2. **Sync Insights**: Đồng bộ insights và breakdowns cho ads đã có

## Workflow mới

### Bước 1: Sync Ads Structure (Không có Insights)

```bash
# Sử dụng command mới với prerequisites check
php artisan facebook:sync-ads-with-prerequisites --since=2025-01-01 --until=2025-01-31

# Hoặc sử dụng command cũ (đã được sửa)
php artisan facebook:sync-with-video-metrics --since=2025-01-01 --until=2025-01-31
```

**Những gì được sync:**
- ✅ Business Managers
- ✅ Ad Accounts  
- ✅ Campaigns
- ✅ Ad Sets
- ✅ Ads (cơ bản)
- ❌ Ad Insights (KHÔNG sync)
- ❌ Breakdowns (KHÔNG sync)
- ❌ Video Metrics (KHÔNG sync)

### Bước 2: Sync Insights cho Ads đã có

```bash
# Sync insights cơ bản
php artisan facebook:sync-insights-only --since=2025-01-01 --until=2025-01-31

# Sync insights + breakdowns
php artisan facebook:sync-insights-only --since=2025-01-01 --until=2025-01-31 --with-breakdowns

# Sync video metrics cho posts
php artisan facebook:sync-enhanced-post-insights --since=2025-01-01 --until=2025-01-31
```

**Những gì được sync:**
- ✅ Ad Insights (impressions, clicks, spend, etc.)
- ✅ Breakdowns (age, gender, placement, etc.)
- ✅ Video Metrics (30s watched, thruplays, etc.)

## Lợi ích của Workflow mới

### 1. **Tách biệt rõ ràng**
- Ads structure và insights được xử lý riêng biệt
- Dễ debug và troubleshoot
- Có thể retry insights mà không cần sync lại ads

### 2. **Tối ưu hiệu suất**
- Sync ads structure nhanh hơn (không cần gọi insights API)
- Có thể sync insights theo batch nhỏ hơn
- Giảm rate limiting từ Facebook API

### 3. **Linh hoạt hơn**
- Có thể sync insights nhiều lần cho cùng một ads
- Có thể sync insights cho date range khác nhau
- Dễ dàng thêm metrics mới mà không ảnh hưởng ads structure

### 4. **Dễ maintain**
- Code rõ ràng, dễ hiểu
- Mỗi command có trách nhiệm riêng biệt
- Dễ test và debug

## Các Command có sẵn

### 1. `facebook:sync-ads-with-prerequisites`
- **Mục đích**: Sync ads structure với prerequisites check
- **Tính năng**: Kiểm tra Business Manager và Ad Account trước khi sync
- **Sử dụng**: Khi muốn đảm bảo có đủ prerequisites

### 2. `facebook:sync-with-video-metrics` 
- **Mục đích**: Sync ads structure (đã được sửa để không sync insights)
- **Tính năng**: Sync toàn bộ cấu trúc ads
- **Sử dụng**: Khi muốn sync toàn bộ từ đầu

### 3. `facebook:sync-insights-only`
- **Mục đích**: Sync insights cho ads đã có
- **Tính năng**: Sync insights và breakdowns
- **Sử dụng**: Sau khi đã có ads structure

### 4. `facebook:sync-enhanced-post-insights`
- **Mục đích**: Sync video metrics cho posts
- **Tính năng**: Sync video insights chi tiết
- **Sử dụng**: Khi cần video metrics

## Ví dụ sử dụng

### Scenario 1: Sync mới hoàn toàn
```bash
# Bước 1: Sync ads structure
php artisan facebook:sync-ads-with-prerequisites --since=2025-01-01 --until=2025-01-31

# Bước 2: Sync insights
php artisan facebook:sync-insights-only --since=2025-01-01 --until=2025-01-31 --with-breakdowns

# Bước 3: Sync video metrics
php artisan facebook:sync-enhanced-post-insights --since=2025-01-01 --until=2025-01-31
```

### Scenario 2: Chỉ cập nhật insights
```bash
# Chỉ sync insights cho ads đã có
php artisan facebook:sync-insights-only --since=2025-01-15 --until=2025-01-31 --with-breakdowns
```

### Scenario 3: Sync hàng ngày
```bash
# Sync ads mới (nếu có)
php artisan facebook:sync-ads-with-prerequisites --days=1

# Sync insights cho ngày hôm qua
php artisan facebook:sync-insights-only --day=2025-01-30 --with-breakdowns
```

## Lưu ý quan trọng

### 1. **Thứ tự thực hiện**
- Luôn sync ads structure trước
- Sau đó mới sync insights
- Video metrics có thể sync song song với insights

### 2. **Rate Limiting**
- Facebook có giới hạn API calls
- Sử dụng `--delay` để tránh rate limit
- Sync insights theo batch nhỏ hơn

### 3. **Error Handling**
- Mỗi command có error handling riêng
- Có thể retry từng bước riêng biệt
- Log chi tiết cho từng bước

### 4. **Database Consistency**
- Ads được lưu trước, insights sau
- Có thể có ads không có insights (tạm thời)
- `last_insights_sync` chỉ được set khi sync insights thành công

## Troubleshooting

### Lỗi "No ads found for insights sync"
- Kiểm tra xem đã sync ads structure chưa
- Chạy `facebook:sync-ads-with-prerequisites` trước

### Lỗi "Prerequisites not met"
- Chạy sync toàn bộ để tạo prerequisites
- Hoặc dùng `--force` (không khuyến khích)

### Insights không được sync
- Kiểm tra `last_insights_sync` trong bảng `facebook_ads`
- Chạy `facebook:sync-insights-only` riêng

### Video metrics thiếu
- Chạy `facebook:sync-enhanced-post-insights`
- Kiểm tra xem ads có post_id không

## Migration từ Workflow cũ

Nếu bạn đang sử dụng workflow cũ (sync tất cả cùng lúc):

1. **Backup database** trước khi thay đổi
2. **Test** với một vài ads trước
3. **Chạy từng bước** theo thứ tự mới
4. **Verify** kết quả sau mỗi bước

## Kết luận

Workflow mới giúp:
- **Tách biệt** rõ ràng giữa ads structure và insights
- **Tối ưu** hiệu suất và giảm rate limiting
- **Linh hoạt** hơn trong việc sync và retry
- **Dễ maintain** và debug hơn

Sử dụng workflow mới để có trải nghiệm tốt hơn khi làm việc với Facebook Ads API.
