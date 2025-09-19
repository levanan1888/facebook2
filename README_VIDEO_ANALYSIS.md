# Hệ thống Phân tích Video Facebook bằng Gemini AI

## Tổng quan

Hệ thống này cho phép phân tích video Facebook tự động bằng Gemini AI API, tập trung vào lĩnh vực Y khoa và Quảng cáo. Hệ thống thực hiện phân tích toàn diện về nội dung video, tuân thủ quy định, và đưa ra khuyến nghị cải tiến.

## Tính năng chính

### 1. Phân tích Video Tự động
- **Tóm tắt video**: Nội dung chính, thông điệp cốt lõi, bối cảnh thời gian
- **Phân tích y khoa**: Yếu tố y khoa, độ chính xác, bằng chứng khoa học, rủi ro
- **Phân tích quảng cáo**: Thông điệp, chiến lược marketing, sử dụng media
- **Đánh giá tuân thủ**: Mức độ tuân thủ quy định quảng cáo y tế
- **Khuyến nghị**: Điểm mạnh, điểm yếu, đề xuất cải tiến

### 2. Kiểm tra Lỗi Video Metrics
- Tỷ lệ completion thấp
- Tỷ lệ thruplay thấp
- Thời gian xem trung bình ngắn
- Tỷ lệ xem 30s thấp

### 3. Giao diện Thân thiện
- Nút "Phân tích AI (Gemini)" trên trang chi tiết bài viết
- Hiển thị kết quả có cấu trúc với màu sắc phân biệt
- Debug mode để xem JSON gốc
- Cảnh báo video metrics

## Cài đặt và Cấu hình

### 1. Cấu hình Gemini API Key

Thêm vào file `.env`:
```env
GEMINI_API_KEY=your_gemini_api_key_here
```

### 2. Cài đặt Dependencies

Các package cần thiết đã có sẵn trong Laravel:
- `guzzlehttp/guzzle` (cho HTTP requests)
- `illuminate/support` (cho Laravel helpers)

### 3. Routes

Routes đã được cấu hình trong `routes/api.php`:
```php
Route::post('analyze-video', [FacebookAnalysisController::class, 'analyzeVideo'])
    ->name('analyze-video');
```

## Cách sử dụng

### 1. Truy cập trang chi tiết bài viết
- Đi đến trang quản lý dữ liệu Facebook
- Chọn một bài viết có video
- Click vào "Chi tiết" để xem trang chi tiết

### 2. Phân tích video
- Trong section "Phân tích Video Chi tiết"
- Click nút "Phân tích AI (Gemini)"
- Chờ kết quả phân tích (có thể mất 30-60 giây)

### 3. Xem kết quả
- **Tóm tắt Video**: Nội dung chính và thông điệp
- **Phân tích Y khoa**: Yếu tố y khoa và rủi ro
- **Phân tích Quảng cáo**: Chiến lược marketing
- **Tuân thủ & Rủi ro**: Mức độ tuân thủ quy định
- **Kết luận & Khuyến nghị**: Điểm mạnh/yếu và cải tiến

## Cấu trúc API

### Request
```json
{
  "post_id": "string",
  "page_id": "string", 
  "video_url": "string (optional)",
  "post_data": {
    "message": "string",
    "type": "string",
    "created_time": "string",
    "video_urls": ["array"],
    "primary_video_url": "string",
    "video_metrics": {
      "views": "number",
      "plays": "number",
      "p25": "number",
      "p50": "number", 
      "p75": "number",
      "p95": "number",
      "p100": "number",
      "thruplays": "number",
      "video_30s": "number",
      "avg_time": "number",
      "view_time": "number"
    }
  }
}
```

### Response
```json
{
  "success": true,
  "analysis": {
    "summary": {
      "main_content": "string",
      "core_message": "string", 
      "duration_context": "string"
    },
    "medical_analysis": {
      "elements": ["array"],
      "accuracy_evidence": "string",
      "risks": "string"
    },
    "advertising_analysis": {
      "message": "string",
      "strategy": "string",
      "media_usage": "string"
    },
    "compliance_risk": {
      "compliance_level": "string",
      "misleading_signs": "string"
    },
    "conclusion_recommendations": {
      "strengths": "string",
      "weaknesses": "string", 
      "improvements": "string"
    }
  },
  "video_url": "string",
  "video_errors": ["array"],
  "raw_analysis": "string"
}
```

## Giới hạn và Lưu ý

### 1. Giới hạn Gemini API
- **Định dạng video**: mp4, webm, mov, avi, flv, mpg, wmv, 3gpp
- **Thời lượng**: ≤ 45 phút (có audio), ≤ 60 phút (không audio)
- **Kích thước**: Theo giới hạn của Gemini API
- **Tốc độ xử lý**: 1 FPS (có thể tùy chỉnh)

### 2. Chi phí
- Mỗi giây video ≈ 300 tokens (độ phân giải mặc định)
- Mỗi giây video ≈ 100 tokens (độ phân giải thấp)
- Âm thanh: 32 tokens/giây

### 3. Bảo mật
- API key được lưu trong environment variables
- Có safety settings để lọc nội dung không phù hợp
- CSRF protection cho web requests

## Troubleshooting

### 1. Lỗi "Gemini API key chưa được cấu hình"
- Kiểm tra file `.env` có `GEMINI_API_KEY`
- Restart server sau khi thêm API key

### 2. Lỗi "Không thể lấy video từ Facebook"
- Kiểm tra post có video không
- Kiểm tra video URL có hợp lệ không
- Kiểm tra quyền truy cập video

### 3. Lỗi "Gemini API không phản hồi"
- Kiểm tra kết nối internet
- Kiểm tra API key có hợp lệ
- Kiểm tra quota API

### 4. Video quá lớn
- Cắt video thành đoạn nhỏ hơn
- Sử dụng độ phân giải thấp
- Chọn đoạn quan trọng nhất

## Phát triển và Mở rộng

### 1. Thêm tính năng mới
- Phân tích cảm xúc
- Phân tích đối tượng mục tiêu
- So sánh với video khác
- Lưu lịch sử phân tích

### 2. Tối ưu hiệu suất
- Cache kết quả phân tích
- Batch processing
- Async processing
- CDN cho video

### 3. Tích hợp khác
- Webhook notifications
- Email reports
- Dashboard analytics
- API documentation

## Liên hệ và Hỗ trợ

Nếu gặp vấn đề hoặc cần hỗ trợ, vui lòng:
1. Kiểm tra logs trong `storage/logs/laravel.log`
2. Kiểm tra console browser để xem lỗi JavaScript
3. Kiểm tra network tab để xem API calls
4. Liên hệ team phát triển

---

**Lưu ý**: Hệ thống này sử dụng Gemini AI API của Google. Vui lòng tuân thủ các điều khoản sử dụng và chính sách bảo mật của Google.
