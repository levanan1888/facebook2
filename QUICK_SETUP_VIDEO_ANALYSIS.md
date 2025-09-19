# Hướng dẫn Cài đặt Nhanh - Video Analysis với Gemini AI

## 🚀 Cài đặt trong 5 phút

### Bước 1: Cấu hình Gemini API Key
```bash
# Thêm vào file .env
echo "GEMINI_API_KEY=your_gemini_api_key_here" >> .env
```

### Bước 2: Lấy Gemini API Key
1. Truy cập [Google AI Studio](https://aistudio.google.com/)
2. Đăng nhập với Google account
3. Tạo API key mới
4. Copy API key và paste vào file `.env`

### Bước 3: Restart Server
```bash
# Nếu đang chạy Laravel server
# Dừng server (Ctrl+C) và chạy lại
php artisan serve
```

### Bước 4: Test Hệ thống
1. Truy cập trang quản lý dữ liệu Facebook
2. Chọn một bài viết có video
3. Click "Chi tiết" để xem trang chi tiết
4. Trong section "Phân tích Video Chi tiết"
5. Click nút "Phân tích AI (Gemini)"
6. Chờ kết quả (30-60 giây)

## ✅ Kiểm tra Cài đặt

### Test Script
```bash
php test_video_analysis.php
```

### Kiểm tra API Status
Truy cập: `http://localhost:8000/api/facebook/check-gemini-status`

## 🎯 Tính năng Chính

### 1. Phân tích Video Tự động
- **Tóm tắt**: Nội dung chính, thông điệp cốt lõi
- **Y khoa**: Yếu tố y khoa, độ chính xác, rủi ro
- **Quảng cáo**: Thông điệp, chiến lược marketing
- **Tuân thủ**: Mức độ tuân thủ quy định
- **Khuyến nghị**: Điểm mạnh/yếu, cải tiến

### 2. Kiểm tra Lỗi Video
- Tỷ lệ completion thấp
- Tỷ lệ thruplay thấp  
- Thời gian xem ngắn
- Hook đầu video yếu

### 3. Giao diện Thân thiện
- Kết quả có cấu trúc với màu sắc
- Debug mode cho JSON gốc
- Cảnh báo video metrics

## 🔧 Troubleshooting

### Lỗi "Gemini API key chưa được cấu hình"
```bash
# Kiểm tra file .env
cat .env | grep GEMINI_API_KEY

# Nếu không có, thêm vào
echo "GEMINI_API_KEY=your_key_here" >> .env
```

### Lỗi "Không thể lấy video từ Facebook"
- Kiểm tra post có video không
- Kiểm tra video URL có hợp lệ không
- Kiểm tra quyền truy cập video

### Lỗi "Gemini API không phản hồi"
- Kiểm tra kết nối internet
- Kiểm tra API key có hợp lệ
- Kiểm tra quota API

## 📊 Giới hạn

### Video
- **Định dạng**: mp4, webm, mov, avi, flv, mpg, wmv, 3gpp
- **Thời lượng**: ≤ 45 phút (có audio), ≤ 60 phút (không audio)
- **Tốc độ**: 1 FPS (có thể tùy chỉnh)

### Chi phí
- Mỗi giây video ≈ 300 tokens (HD) hoặc 100 tokens (SD)
- Âm thanh: 32 tokens/giây

## 🎉 Hoàn thành!

Hệ thống đã sẵn sàng sử dụng. Bạn có thể:

1. **Phân tích video** trên trang chi tiết bài viết
2. **Xem kết quả** có cấu trúc và màu sắc
3. **Kiểm tra lỗi** video metrics
4. **Debug** với JSON gốc

## 📚 Tài liệu Chi tiết

Xem `README_VIDEO_ANALYSIS.md` để biết thêm chi tiết về:
- Cấu trúc API
- Phát triển và mở rộng
- Troubleshooting chi tiết
- Best practices

---

**Lưu ý**: Cần có Gemini API key hợp lệ để sử dụng tính năng này.
