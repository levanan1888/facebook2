<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GeminiService
{
    /**
     * Gọi Gemini để tạo nhận định marketing cấp quản lý
     */
    public function generateMarketingSummary(string $pageId, ?string $since, ?string $until, array $metrics): string
    {
        $apiKey = env('GEMINI_API_KEY') ?: config('services.gemini.api_key');
        if (!$apiKey) {
            return 'Chưa cấu hình GEMINI_API_KEY trong .env';
        }

        $prompt = $this->buildPrompt($pageId, $since, $until, $metrics);

        try {
            // Attempt: 1.5-flash with retry + timeouts
            $text = $this->callGemini('gemini-1.5-flash', $apiKey, $prompt);
            if ($text === null) {
                // Fallback: gemini-pro
                $text = $this->callGemini('gemini-pro', $apiKey, $prompt);
            }
            if ($text !== null) {
                return $text;
            }
            // If all failed, build a local quick summary as graceful fallback
            return $this->buildLocalFallbackSummary($metrics, $since, $until);
        } catch (\Throwable $e) {
            // Graceful fallback instead of surfacing cURL 28
            return $this->buildLocalFallbackSummary($metrics, $since, $until);
        }
    }

    private function buildPrompt(string $pageId, ?string $since, ?string $until, array $metrics): string
    {
        $period = ($since && $until) ? "Từ {$since} đến {$until}" : '7-30 ngày gần đây';
        
        // Xử lý data breakdowns từ frontend nếu có
        $frontendBreakdowns = $metrics['frontend_breakdowns'] ?? [];
        $breakdownsInfo = '';
        
        if (!empty($frontendBreakdowns)) {
            $breakdownsInfo = "\n\n**Dữ liệu breakdowns tổng hợp từ frontend:**\n";
            if (!empty($frontendBreakdowns['breakdowns'])) {
                $breakdownsInfo .= "- Phân tích breakdowns: " . count($frontendBreakdowns['breakdowns']) . " loại\n";
            }
            if (!empty($frontendBreakdowns['actions'])) {
                $breakdownsInfo .= "- Actions summary: " . count($frontendBreakdowns['actions']['summary'] ?? []) . " loại\n";
            }
            if (!empty($frontendBreakdowns['stats'])) {
                $breakdownsInfo .= "- Stats tổng hợp: spend, impressions, clicks, CTR\n";
            }
            if (!empty($frontendBreakdowns['totals'])) {
                $breakdownsInfo .= "- Tổng số: " . ($frontendBreakdowns['totals']['businesses'] ?? 0) . " businesses, " . 
                                 ($frontendBreakdowns['totals']['accounts'] ?? 0) . " accounts, " . 
                                 ($frontendBreakdowns['totals']['campaigns'] ?? 0) . " campaigns, " . 
                                 ($frontendBreakdowns['totals']['posts'] ?? 0) . " posts\n";
            }
            if (!empty($frontendBreakdowns['last7Days'])) {
                $breakdownsInfo .= "- Hoạt động 7 ngày gần nhất: " . count($frontendBreakdowns['last7Days']) . " ngày có dữ liệu\n";
            }
            if (!empty($frontendBreakdowns['statusStats'])) {
                $breakdownsInfo .= "- Trạng thái campaigns: " . count($frontendBreakdowns['statusStats']['campaigns'] ?? []) . " trạng thái\n";
            }
        }
        
        $json = json_encode($metrics, JSON_UNESCAPED_UNICODE);
        return "Vai trò: Chuyên gia phân tích marketing khắt khe và tàn nhẫn. Mục tiêu: đánh giá toàn diện kết hợp nội dung video và hiệu suất thực tế, không khoan nhượng với những điểm yếu.\n\nBối cảnh: Page {$pageId}, giai đoạn {$period}. Dữ liệu: {$json}.{$breakdownsInfo}\n\nNGUYÊN TẮC KHẮT KHE:\n- Phân tích sâu, không chấp nhận câu trả lời chung chung\n- Kết hợp nội dung video với hiệu suất thực tế\n- Chỉ trích thẳng thắn, không nể nang\n- Đưa ra đánh giá cuối cùng dựa trên bằng chứng cụ thể\n\nCHỈ SỐ QUAN TRỌNG - BẮT BUỘC PHẢI TÍNH TOÁN VÀ PHÂN TÍCH:\n- Engagement Rate: >3% = tốt, <1% = thất bại\n- Video Retention: p25 <50% = thất bại, p50 <70% = kém\n- CTR: <1% = kém, >3% = tốt\n- Video Completion: <20% = không hấp dẫn\n- Conversion Rate: <2% = landing page yếu\n- Video Drop-off Rate: >80% = nội dung nhàm chán\n- Cost per Engagement: >50k VND = đắt đỏ\n\nPHÂN TÍCH BẮT BUỘC:\n1) **Nội dung video**: Phân tích thông điệp, hình ảnh, âm thanh, CTA - có thu hút không?\n2) **Hiệu suất thực tế**: Tính toán từng chỉ số, so sánh với ngưỡng, chỉ ra điểm yếu cụ thể\n3) **Kết hợp đánh giá**: Nội dung tốt nhưng hiệu suất kém = vấn đề gì? Nội dung kém nhưng hiệu suất tốt = tại sao?\n4) **Đánh giá cuối cùng**: Thành công hay thất bại, lý do cụ thể, điểm số 1-10\n\nĐầu ra (4 phần chi tiết):\n1) **Phân tích nội dung video** (thông điệp, hình ảnh, CTA - có thu hút không?)\n2) **Hiệu suất thực tế** (tính toán từng chỉ số, so sánh ngưỡng, chỉ ra thất bại cụ thể)\n3) **Kết hợp đánh giá** (nội dung vs hiệu suất, vấn đề gì, tại sao)\n4) **Kết luận cuối cùng** (thành công/thất bại, điểm số 1-10, lý do cụ thể)\n\nQuy tắc: Ngôn ngữ khắt khe: 'thất bại hoàn toàn', 'nội dung nhàm chán', 'lãng phí tiền bạc', 'cần làm lại từ đầu'. PHẢI có số liệu cụ thể, không chấp nhận 'khá tốt' hay 'cần cải thiện' chung chung.";
    }

    /**
     * Gọi Gemini với retry/timeout. Trả về text hoặc null nếu lỗi.
     */
    private function callGemini(string $model, string $apiKey, string $prompt): ?string
    {
        // Use header x-goog-api-key per official REST spec
        $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/' . $model . ':generateContent';
        $response = Http::retry(2, 1000)
            ->connectTimeout(10)
            ->timeout(25)
            ->withHeaders([
                'x-goog-api-key' => $apiKey,
                'Content-Type' => 'application/json',
            ])
            ->asJson()
            ->post($endpoint, [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ]
            ]);

        if (!$response->ok()) {
            return null;
        }
        $data = $response->json();
        // Parse multiple possible response shapes safely
        $text = $data['candidates'][0]['content']['parts'][0]['text']
            ?? $data['candidates'][0]['content']['parts'][0]['raw_text']
            ?? null;
        if (is_string($text) && trim($text) !== '') {
            return $text;
        }
        return null;
    }

    /**
     * Fallback nội bộ khi AI không phản hồi: tạo nhận định đơn giản dựa trên số liệu.
     */
    private function buildLocalFallbackSummary(array $metrics, ?string $since, ?string $until): string
    {
        $period = ($since && $until) ? "{$since} → {$until}" : 'giai đoạn gần đây';
        $summary = $metrics['summary'] ?? ($metrics['page_summary'] ?? []);
        $video = $metrics['video'] ?? [];
        $totalSpend = (float)($summary['total_spend'] ?? 0);
        $impressions = (int)($summary['total_impressions'] ?? 0);
        $clicks = (int)($summary['total_clicks'] ?? 0);
        $conversions = (int)($summary['total_conversions'] ?? 0);
        $avgCtr = (float)($summary['avg_ctr'] ?? ($impressions > 0 ? ($clicks / max(1,$impressions)) : 0));
        $avgCpc = (float)($summary['avg_cpc'] ?? ($clicks > 0 ? ($totalSpend / $clicks) : 0));

        $lines = [];
        $lines[] = '* **Insight:** Chi phí hiện tại ' . number_format((int)$totalSpend) . ' VND, CTR ~ ' . number_format($avgCtr * 100, 2) . '%, CPC ~ ' . number_format((int)$avgCpc) . ' VND.';
        $lines[] = '  **Hành động:** Tăng chất lượng nội dung (A/B headline/creative), tối ưu đối tượng và lịch chạy để hạ CPC, nâng CTR.';
        if ($impressions > 0 && $clicks > 0 && $conversions === 0) {
            $lines[] = '* **Insight:** Có click nhưng ít/nhiều chuyển đổi.';
            $lines[] = '  **Hành động:** Rà soát landing page, gắn tracking (Pixel/GA4), tối ưu form/CTA, kiểm thử phễu.';
        }
        // Video quick facts if available
        $vViews = (int)($video['views'] ?? 0);
        $vPlays = (int)($video['plays'] ?? 0);
        $v25 = (int)($video['p25'] ?? 0);
        $v50 = (int)($video['p50'] ?? 0);
        $v75 = (int)($video['p75'] ?? 0);
        $v95 = (int)($video['p95'] ?? 0);
        $v100 = (int)($video['p100'] ?? 0);
        $v30s = (int)($video['video_30s'] ?? 0);
        $vAvg = (float)($video['avg_time'] ?? 0);
        if ($vViews > 0 || $vPlays > 0) {
            $lines[] = '* **Video:** Views ' . number_format($vViews) . ', Plays ' . number_format($vPlays) . ', avg_time ~ ' . number_format($vAvg, 2) . 's.';
            $lines[] = '  - Hoàn thành: 25% ' . number_format($v25) . ', 50% ' . number_format($v50) . ', 75% ' . number_format($v75) . ', 95% ' . number_format($v95) . ', 100% ' . number_format($v100) . ', 30s ' . number_format($v30s) . '.';
        }
        $lines[] = '* **Insight:** Thời gian ' . $period . ' thiếu phản hồi từ AI, đang dùng nhận định nhanh nội bộ.';
        $lines[] = '  **Hành động:** Dùng nhận định nhanh này, thử lại AI sau.';

        return implode("\n", $lines);
    }
}


