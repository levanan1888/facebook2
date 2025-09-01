# HƯỚNG DẪN SỬ DỤNG FACEBOOK DASHBOARD

## 📋 Tổng quan hệ thống

Facebook Dashboard là hệ thống quản lý và phân tích dữ liệu Facebook Ads toàn diện, giúp doanh nghiệp theo dõi hiệu suất quảng cáo, phân tích đối tượng và tối ưu hóa chiến dịch marketing.

### 🎯 Các tính năng chính:
- **Dashboard Overview**: Tổng quan toàn bộ hệ thống
- **Data Management**: Quản lý dữ liệu chi tiết
- **Analytics**: Phân tích chuyên sâu
- **Hierarchy**: Cấu trúc phân cấp Business > Account > Campaign > Ad
- **AI Summary**: Tóm tắt thông minh bằng AI

---

## 🏠 MÀN HÌNH TỔNG QUAN (OVERVIEW)

### 📊 Vị trí truy cập
- **URL**: `/facebook/overview`
- **Menu**: Facebook Dashboard > Overview

### 🎯 Chức năng chính

#### 1. **Bộ lọc dữ liệu (Filters)**
- **Khoảng thời gian**: Chọn từ ngày - đến ngày (mặc định 36 tháng gần nhất)
- **Business Manager**: Lọc theo Business Manager cụ thể
- **Ad Account**: Lọc theo tài khoản quảng cáo
- **Campaign**: Lọc theo chiến dịch
- **Page**: Lọc theo trang Facebook

#### 2. **Thống kê tổng hợp (Totals)**
Hiển thị số lượng:
- **Business Managers**: Số lượng Business Manager
- **Ad Accounts**: Số lượng tài khoản quảng cáo
- **Campaigns**: Số lượng chiến dịch
- **Ad Sets**: Số lượng bộ quảng cáo
- **Ads**: Số lượng quảng cáo
- **Pages**: Số lượng trang Facebook
- **Posts**: Số lượng bài đăng
- **Ad Insights**: Số lượng bản ghi insights

#### 3. **Biểu đồ hoạt động theo thời gian**
- Hiển thị hoạt động quảng cáo theo ngày
- Metrics: Số lượng ads, posts, campaigns, chi phí
- Thời gian: Từ ngày đầu tiên đến ngày cuối cùng có dữ liệu

#### 4. **Phân bố trạng thái Campaigns**
- Biểu đồ donut hiển thị tỷ lệ trạng thái campaigns
- Trạng thái: ACTIVE, PAUSED, DELETED, etc.

#### 5. **Top 5 Quảng cáo**
- Danh sách 5 quảng cáo có chi phí cao nhất
- Thông tin: Tên quảng cáo, campaign, trạng thái, ngày đồng bộ cuối

#### 6. **Top 5 Posts**
- Danh sách 5 bài đăng có hiệu suất tốt nhất
- Metrics: Chi phí, impressions, clicks, CTR
- Link: Xem chi tiết từng post

#### 7. **Phân tích Actions**
- Tổng hợp các hành động người dùng
- Phân loại theo loại hành động (like, share, comment, etc.)

### 🔧 Cách sử dụng

1. **Chọn khoảng thời gian**:
   - Click vào ô "Từ ngày" và "Đến ngày"
   - Chọn ngày mong muốn
   - Hệ thống sẽ tự động cập nhật dữ liệu

2. **Lọc theo Business/Account/Campaign**:
   - Chọn Business Manager từ dropdown
   - Chọn Ad Account (sẽ lọc theo Business đã chọn)
   - Chọn Campaign (sẽ lọc theo Account đã chọn)

3. **Xem chi tiết**:
   - Click "Xem chi tiết" trên bất kỳ post nào
   - Chuyển đến trang chi tiết post

4. **Tải lại dữ liệu**:
   - Click nút "Làm mới" để tải lại dữ liệu mới nhất

---

## 📋 QUẢN LÝ DỮ LIỆU (DATA MANAGEMENT)

### 📊 Vị trí truy cập
- **URL**: `/facebook/data-management`
- **Menu**: Facebook Dashboard > Data Management

### 🎯 Chức năng chính

#### 1. **Danh sách Posts**
- Hiển thị tất cả bài đăng từ Facebook
- Thông tin: ID, loại, thời gian tạo, nội dung, link
- Metrics: Likes, shares, comments, reactions

#### 2. **Thống kê hiệu suất**
- **Chi phí tổng**: Tổng chi phí quảng cáo
- **Impressions**: Số lần hiển thị
- **Clicks**: Số lần click
- **Video Views**: Số lượt xem video
- **CTR**: Tỷ lệ click
- **CPC**: Chi phí mỗi click
- **CPM**: Chi phí mỗi 1000 impressions

#### 3. **Phân tích video**
- **Video Views**: Số lượt xem video
- **Video Plays**: Số lần phát video
- **75% Watched**: Số người xem 75% video
- **100% Watched**: Số người xem hết video

#### 4. **Chuyển đổi**
- **Conversions**: Số chuyển đổi
- **Conversion Values**: Giá trị chuyển đổi
- **ROAS**: Return on Ad Spend

### 🔧 Cách sử dụng

1. **Xem danh sách posts**:
   - Scroll xuống để xem tất cả posts
   - Mỗi post hiển thị thông tin cơ bản và metrics

2. **Xem chi tiết post**:
   - Click "Xem chi tiết" trên bất kỳ post nào
   - Chuyển đến trang chi tiết với đầy đủ thông tin

3. **Lọc và tìm kiếm**:
   - Sử dụng bộ lọc theo thời gian
   - Tìm kiếm theo nội dung post

4. **Xem biểu đồ**:
   - Mỗi post có biểu đồ hiệu suất
   - Biểu đồ video metrics (nếu có video)

---

## 📊 CHI TIẾT BÀI ĐĂNG (POST DETAIL)

### 📊 Vị trí truy cập
- **URL**: `/facebook/data-management/post-detail/{postId}/{pageId}`
- **Từ**: Click "Xem chi tiết" trên bất kỳ post nào

### 🎯 Chức năng chính

#### 1. **Thông tin cơ bản**
- **Post ID**: ID duy nhất của bài đăng
- **Loại**: Loại bài đăng (post, video, photo, etc.)
- **Thời gian tạo**: Ngày giờ tạo bài đăng
- **Nội dung**: Nội dung bài đăng
- **Link**: Link đến bài đăng gốc trên Facebook

#### 2. **Thống kê tổng hợp**
- **Chi phí**: Tổng chi phí quảng cáo
- **Impressions**: Số lần hiển thị
- **Clicks**: Số lần click
- **Video Views**: Số lượt xem video

#### 3. **Dữ liệu theo ngày**
- Biểu đồ hiển thị metrics theo thời gian
- Có thể chọn khoảng thời gian cụ thể
- Metrics: Spend, Impressions, Clicks, Reach, CTR, CPC, CPM

#### 4. **Phân tích Actions**
- **Summary**: Tổng hợp các hành động
- **Daily Actions**: Hành động theo ngày
- **Detailed Actions**: Chi tiết từng loại hành động

#### 5. **Breakdowns**
- **Thiết bị**: Phân tích theo thiết bị (Desktop, Mobile, Tablet)
- **Khu vực**: Phân tích theo khu vực địa lý
- **Độ tuổi**: Phân tích theo nhóm tuổi
- **Giới tính**: Phân tích theo giới tính
- **Vị trí**: Phân tích theo vị trí hiển thị

#### 6. **Chiến dịch quảng cáo**
- Danh sách các chiến dịch sử dụng post này
- Thông tin: Tên chiến dịch, trạng thái, chi phí, hiệu suất

### 🔧 Cách sử dụng

1. **Xem thông tin cơ bản**:
   - Scroll lên đầu trang để xem thông tin post
   - Click vào các link để mở Facebook

2. **Phân tích theo thời gian**:
   - Chọn khoảng thời gian mong muốn
   - Xem biểu đồ thay đổi metrics

3. **Xem breakdowns**:
   - Chọn tab breakdowns mong muốn
   - Xem phân tích chi tiết theo từng tiêu chí

4. **Xem actions**:
   - Chọn tab actions để xem hành động người dùng
   - Phân tích chi tiết từng loại hành động

5. **Xem chiến dịch**:
   - Click "Xem chiến dịch" để xem danh sách campaigns
   - Phân tích hiệu suất từng chiến dịch

---

## 📈 PHÂN TÍCH (ANALYTICS)

### 📊 Vị trí truy cập
- **URL**: `/facebook/analytics`
- **Menu**: Facebook Dashboard > Analytics

### 🎯 Chức năng chính

#### 1. **Thống kê hàng ngày**
- Biểu đồ hiển thị metrics theo ngày
- Khoảng thời gian: 30 ngày gần nhất
- Metrics: Spend, Impressions, Clicks, Reach, CTR, CPC, CPM

#### 2. **Hiệu suất theo Campaign**
- Danh sách campaigns có hiệu suất tốt nhất
- Sắp xếp theo chi phí giảm dần
- Giới hạn: 20 campaigns

#### 3. **Hiệu suất theo loại Post**
- Phân tích hiệu suất theo loại bài đăng
- Thống kê: Số lượng ads, tổng chi phí

### 🔧 Cách sử dụng

1. **Xem biểu đồ hàng ngày**:
   - Scroll xuống để xem biểu đồ
   - Hover để xem chi tiết từng ngày

2. **Phân tích campaigns**:
   - Xem danh sách campaigns
   - So sánh hiệu suất giữa các campaigns

3. **Phân tích post types**:
   - Xem hiệu suất theo loại bài đăng
   - Tối ưu hóa loại nội dung

---

## 🏗️ CẤU TRÚC PHÂN CẤP (HIERARCHY)

### 📊 Vị trí truy cập
- **URL**: `/facebook/hierarchy`
- **Menu**: Facebook Dashboard > Hierarchy

### 🎯 Chức năng chính

#### 1. **Business Managers**
- Danh sách tất cả Business Managers
- Số lượng Ad Accounts trong mỗi Business

#### 2. **Ad Accounts**
- Danh sách tài khoản quảng cáo
- Số lượng Campaigns trong mỗi Account

#### 3. **Campaigns**
- Danh sách chiến dịch
- Số lượng Ad Sets và Ads trong mỗi Campaign

#### 4. **Ad Sets**
- Danh sách bộ quảng cáo
- Số lượng Ads trong mỗi Ad Set

#### 5. **Ads**
- Danh sách quảng cáo
- Thông tin insights cho mỗi ad

### 🔧 Cách sử dụng

1. **Xem cấu trúc tổng thể**:
   - Scroll để xem toàn bộ cấu trúc
   - Click để mở rộng/thu gọn từng cấp

2. **Tìm kiếm**:
   - Sử dụng search để tìm nhanh
   - Lọc theo tên hoặc ID

3. **Xem chi tiết**:
   - Click vào bất kỳ item nào để xem chi tiết
   - Xem metrics và insights

---

## 🤖 TÓM TẮT AI (AI SUMMARY)

### 📊 Vị trí truy cập
- **Từ Overview**: Click nút "AI Summary"
- **API**: `/facebook/overview/ai-summary`

### 🎯 Chức năng chính

#### 1. **Phân tích tổng quan**
- Tóm tắt hiệu suất tổng thể
- So sánh với các giai đoạn trước
- Đưa ra nhận định về xu hướng

#### 2. **Phân tích breakdowns**
- Phân tích hiệu suất theo thiết bị
- Phân tích theo khu vực địa lý
- Phân tích theo nhóm đối tượng

#### 3. **Đề xuất tối ưu**
- Đề xuất cải thiện hiệu suất
- Gợi ý điều chỉnh targeting
- Khuyến nghị về ngân sách

### 🔧 Cách sử dụng

1. **Tạo báo cáo AI**:
   - Click "AI Summary" từ Overview
   - Chọn khoảng thời gian mong muốn
   - Đợi AI phân tích và tạo báo cáo

2. **Đọc báo cáo**:
   - Xem tổng quan hiệu suất
   - Đọc phân tích chi tiết
   - Thực hiện các đề xuất

3. **Xuất báo cáo**:
   - Copy nội dung báo cáo
   - Chia sẻ với team

---

## 🔄 ĐỒNG BỘ DỮ LIỆU

### 📊 Vị trí truy cập
- **URL**: `/facebook/sync`
- **Menu**: Facebook Dashboard > Sync

### 🎯 Chức năng chính

#### 1. **Đồng bộ Business Managers**
- Lấy danh sách Business Managers
- Cập nhật thông tin cơ bản

#### 2. **Đồng bộ Ad Accounts**
- Lấy danh sách tài khoản quảng cáo
- Cập nhật thông tin account

#### 3. **Đồng bộ Campaigns**
- Lấy danh sách chiến dịch
- Cập nhật trạng thái và metrics

#### 4. **Đồng bộ Ads**
- Lấy danh sách quảng cáo
- Cập nhật thông tin creative

#### 5. **Đồng bộ Insights**
- Lấy dữ liệu hiệu suất
- Cập nhật metrics theo ngày

### 🔧 Cách sử dụng

1. **Bắt đầu đồng bộ**:
   - Click "Start Sync" để bắt đầu
   - Chọn loại dữ liệu cần đồng bộ
   - Đợi quá trình hoàn thành

2. **Theo dõi tiến trình**:
   - Xem progress bar
   - Kiểm tra log để biết trạng thái

3. **Xử lý lỗi**:
   - Kiểm tra error log
   - Thử lại nếu cần thiết

---

## ⚙️ CÀI ĐẶT VÀ CẤU HÌNH

### 🔐 Xác thực Facebook
1. **Tạo Facebook App**:
   - Truy cập Facebook Developers
   - Tạo app mới
   - Lấy App ID và App Secret

2. **Cấu hình permissions**:
   - Thêm permissions cần thiết
   - Submit app để review

3. **Cấu hình webhook**:
   - Thiết lập webhook URL
   - Xác minh webhook

### 🔧 Cấu hình hệ thống
1. **Database**:
   - Chạy migrations
   - Import dữ liệu mẫu (nếu có)

2. **Environment**:
   - Cấu hình .env file
   - Thiết lập Facebook credentials

3. **Cron Jobs**:
   - Thiết lập cron để đồng bộ tự động
   - Cấu hình thời gian chạy

---

## 🚨 XỬ LÝ LỖI THƯỜNG GẶP

### ❌ Lỗi "Cannot access offset of type Carbon"
**Nguyên nhân**: Dữ liệu date được cast thành Carbon object nhưng được sử dụng như array key
**Giải pháp**: Đã được sửa trong code, đảm bảo date được convert thành string

### ❌ Lỗi "Attempt to read property on array"
**Nguyên nhân**: Dữ liệu trả về dạng array nhưng code cố gắng truy cập như object
**Giải pháp**: Đã được sửa trong view, sử dụng array syntax `$data['key']`

### ❌ Lỗi "Facebook API rate limit"
**Nguyên nhân**: Gọi API quá nhiều trong thời gian ngắn
**Giải pháp**: 
- Giảm tần suất đồng bộ
- Implement retry mechanism
- Sử dụng batch processing

### ❌ Lỗi "Permission denied"
**Nguyên nhân**: App không có đủ permissions
**Giải pháp**:
- Kiểm tra permissions trong Facebook App
- Submit app để review
- Yêu cầu thêm permissions cần thiết

---

## 📞 HỖ TRỢ VÀ LIÊN HỆ

### 🆘 Khi cần hỗ trợ
1. **Kiểm tra logs**: Xem error logs trong `/storage/logs`
2. **Kiểm tra documentation**: Đọc lại hướng dẫn này
3. **Liên hệ developer**: Gửi email với thông tin lỗi chi tiết

### 📧 Thông tin liên hệ
- **Email**: support@example.com
- **Documentation**: https://docs.example.com
- **GitHub**: https://github.com/example/facebook-dashboard

---

## 📝 GHI CHÚ QUAN TRỌNG

### ⚠️ Lưu ý bảo mật
- Không chia sẻ Facebook credentials
- Sử dụng HTTPS cho production
- Backup dữ liệu thường xuyên

### 🔄 Cập nhật hệ thống
- Kiểm tra cập nhật Facebook API định kỳ
- Test trước khi deploy lên production
- Backup trước khi update

### 📊 Tối ưu hiệu suất
- Sử dụng cache cho queries nặng
- Implement pagination cho danh sách lớn
- Monitor memory usage

---

*Hướng dẫn này được cập nhật lần cuối: {{ date('d/m/Y') }}*
