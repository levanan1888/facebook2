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
        return "Vai trò: Bạn là lãnh đạo marketing (CMO) 10+ năm kinh nghiệm. Mục tiêu: đánh giá hiệu quả chiến dịch và đặc biệt là tác động của nội dung video lên người dùng, bằng ngôn ngữ đơn giản dễ hiểu cho cả người không chuyên.\n\nBối cảnh: Page {$pageId}, giai đoạn {$period}. Input JSON (đầy đủ tham số từ màn post detail và overview): {$json}.{$breakdownsInfo}\n\nPhạm vi phân tích (nếu có dữ liệu) – PHẢI đề cập đầy đủ, có số liệu cụ thể:\n- Hiệu quả tổng quan: spend, impressions, reach, clicks, CTR, CPC, CPM, conversions, conversion_values, ROAS.\n- Video & mức độ xem: video_views, video_plays, p25/p50/p75/p95/p100, 30s, avg_time, view_time (quy đổi tỉ lệ rõ ràng 50%/70%/80%/100%).\n- Phân khúc: thiết bị, khu vực/tỉnh, quốc gia, giới tính–độ tuổi, vị trí/nền tảng (publisher_platform, platform_position, impression_device). Nêu top tốt nhất và kém nhất.\n- Hành vi: actions theo thời gian; chỉ ra xu hướng tăng/giảm.\n- Chất lượng dữ liệu: unknown/missing/outliers.\n\nĐầu ra (tiếng Việt, rõ ràng, yêu cầu đủ 5 phần):\n1) **Kết luận nhanh về hiệu quả** (có/không, vì sao).\n2) **Tác động video** (50/70/80/100%, avg_time, drop-off).\n3) **Insight theo phân khúc** (top/worst từng nhóm, có số liệu).\n4) **Khuyến nghị hành động** (7–10 mục, cụ thể, khả thi).\n5) **KPI theo dõi tuần tới** (3–5 KPI).\n\nQuy tắc trình bày: gạch đầu dòng '*' hoặc '-', dùng **đậm** cho ý chính; không dùng thuật ngữ khó; diễn giải số thành câu dễ hiểu (ví dụ: \"100 người xem có ~28 người xem ≥70%\"). Không bịa số liệu; nếu thiếu số, nói rõ là thiếu.";
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


