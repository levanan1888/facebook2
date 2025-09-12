# Facebook Ads Sync with Prerequisites

Command này chỉ đồng bộ Facebook ads khi đã có đủ Business Manager và Ad Account trong database.

## Tính năng

- ✅ Kiểm tra prerequisites trước khi đồng bộ
- ✅ Chỉ đồng bộ ads từ các Ad Account có Business Manager hợp lệ
- ✅ Đồng bộ campaigns, adsets, ads và insights
- ✅ Báo cáo chi tiết về tiến độ và kết quả
- ✅ Xử lý lỗi và retry logic
- ✅ Tùy chọn kiểm tra prerequisites mà không đồng bộ

## Cách sử dụng

### 1. Kiểm tra prerequisites
```bash
php artisan facebook:sync-ads-with-prerequisites --check-only
```

### 2. Đồng bộ ads với prerequisites
```bash
# Đồng bộ 7 ngày gần nhất
php artisan facebook:sync-ads-with-prerequisites

# Đồng bộ với date range cụ thể
php artisan facebook:sync-ads-with-prerequisites --since=2025-01-01 --until=2025-01-31

# Đồng bộ với giới hạn số lượng ads
php artisan facebook:sync-ads-with-prerequisites --limit=50

# Đồng bộ với delay giữa các requests
php artisan facebook:sync-ads-with-prerequisites --delay=2

# Force sync ngay cả khi prerequisites không đủ (không khuyến khích)
php artisan facebook:sync-ads-with-prerequisites --force
```

### 3. Các tùy chọn

| Tùy chọn | Mô tả | Mặc định |
|----------|-------|----------|
| `--since` | Ngày bắt đầu (Y-m-d) | 7 ngày trước |
| `--until` | Ngày kết thúc (Y-m-d) | Hôm nay |
| `--days` | Số ngày đồng bộ | 7 |
| `--limit` | Số ads tối đa mỗi ad account | 100 |
| `--delay` | Delay giữa các requests (giây) | 1 |
| `--force` | Force sync ngay cả khi thiếu prerequisites | false |
| `--check-only` | Chỉ kiểm tra prerequisites, không đồng bộ | false |

## Prerequisites cần thiết

Command sẽ kiểm tra các điều kiện sau:

1. **Business Managers**: Phải có ít nhất 1 Business Manager trong database
2. **Ad Accounts**: Phải có ít nhất 1 Ad Account trong database  
3. **Relationships**: Ad Accounts phải có quan hệ với Business Manager
4. **Valid Data**: Không có Ad Account "orphan" (không có business_id)

## Quy trình đồng bộ

1. **Kiểm tra Prerequisites**: Xác minh Business Manager và Ad Account
2. **Đồng bộ Campaigns**: Lấy campaigns từ các Ad Account hợp lệ
3. **Đồng bộ Ad Sets**: Lấy adsets từ campaigns
4. **Đồng bộ Ads**: Lấy ads từ adsets
5. **Đồng bộ Insights**: Lấy insights cho từng ad
6. **Báo cáo**: Hiển thị thống kê kết quả

## Xử lý lỗi

- **Rate Limiting**: Tự động retry khi gặp rate limit
- **Permission Errors**: Skip các ad account không có quyền
- **Network Errors**: Log lỗi và tiếp tục với ad account khác
- **Data Validation**: Kiểm tra dữ liệu trước khi lưu

## Báo cáo kết quả

Command sẽ hiển thị:

- Số lượng Ad Accounts đã xử lý
- Số campaigns, adsets, ads đã đồng bộ
- Số insights đã lấy được
- Thống kê database tổng quan
- Danh sách lỗi (nếu có)

## Ví dụ output

```
🚀 Starting Facebook ads sync with prerequisites check...
📱 Facebook Graph API Version: v23.0
🔑 Access Token: EAABwzLixnjYBO...
📅 Days to sync: 7 (from 2025-01-04 to 2025-01-11)
📊 Ads per ad account: 100
⏱️  Delay between requests: 1s
🔄 Force mode: No
🔍 Check only mode: No

=== 🔍 Step 1: Checking prerequisites ===
✅ Prerequisites check passed
   📊 Business Managers: 2
   💰 Ad Accounts: 5

=== 💰 Step 2: Syncing ads from valid ad accounts ===
📊 Found 5 valid ad accounts to process

💰 Processing Ad Account 1/5: My Ad Account (act_123456789)
   Business: My Business (123456789)
   ✅ Completed: 3 campaigns, 8 adsets, 15 ads, 15 insights

=== 📊 Step 3: Summary Report ===
📊 === SYNC SUMMARY REPORT ===
💰 Ad Accounts processed: 5
📈 Campaigns synced: 12
🎯 Ad Sets synced: 28
📢 Ads synced: 45
📊 Ad Insights synced: 45

🎉 === Sync completed successfully ===
```

## Lưu ý quan trọng

1. **Access Token**: Cần có Facebook Ads API access token hợp lệ
2. **Permissions**: Token cần có quyền đọc ads, campaigns, insights
3. **Rate Limits**: Facebook có giới hạn API calls, sử dụng `--delay` để tránh
4. **Data Quality**: Chỉ đồng bộ từ các Ad Account có quan hệ Business Manager hợp lệ
5. **Error Handling**: Command sẽ tiếp tục chạy ngay cả khi có lỗi với một số ads

## Troubleshooting

### Lỗi "Prerequisites not met"
```bash
# Chạy sync toàn bộ để tạo prerequisites
php artisan facebook:sync-all-data --days=30

# Hoặc force sync (không khuyến khích)
php artisan facebook:sync-ads-with-prerequisites --force
```

### Lỗi "No valid ad accounts found"
- Kiểm tra xem Ad Accounts có business_id không
- Chạy lại sync Business Manager và Ad Accounts

### Lỗi "Access token required"
- Kiểm tra FACEBOOK_ADS_TOKEN trong .env
- Hoặc truyền token qua command line

## Liên quan

- `facebook:sync-all-data`: Đồng bộ toàn bộ dữ liệu Facebook
- `facebook:sync-fanpage-posts`: Đồng bộ fanpage và posts
- `facebook:sync-enhanced-post-insights`: Đồng bộ post insights nâng cao
