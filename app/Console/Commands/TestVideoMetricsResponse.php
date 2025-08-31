<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\FacebookAdsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestVideoMetricsResponse extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'facebook:test-video-metrics 
                            {--ad-id= : Facebook Ad ID để test}
                            {--limit=5 : Số lượng ads để test}';

    /**
     * The console command description.
     */
    protected $description = 'Test lấy response data video metrics từ Facebook API';

    /**
     * Execute the console command.
     */
    public function handle(FacebookAdsService $api): int
    {
        $this->info('Bắt đầu test video metrics response từ Facebook API...');

        $adId = $this->option('ad-id');
        $limit = (int) $this->option('limit');

        try {
            if ($adId) {
                // Test với một ad cụ thể
                $this->testSingleAd($api, $adId);
            } else {
                // Test với nhiều ads
                $this->testMultipleAds($api, $limit);
            }

            return 0;

        } catch (\Exception $e) {
            $this->error("Error: {$e->getMessage()}");
            Log::error('Test video metrics error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    /**
     * Test với một ad cụ thể
     */
    private function testSingleAd(FacebookAdsService $api, string $adId): void
    {
        $this->info("Testing Ad ID: {$adId}");

        // Lấy insights với video metrics
        $insights = $api->getInsightsForAd($adId);

        if (isset($insights['error'])) {
            $this->error("API Error: " . json_encode($insights['error']));
            return;
        }

        if (empty($insights['data'])) {
            $this->warn("Không có data insights cho ad này");
            return;
        }

        $this->displayInsightsData($insights['data'][0], $adId);
    }

    /**
     * Test với nhiều ads
     */
    private function testMultipleAds(FacebookAdsService $api, int $limit): void
    {
        $this->info("Testing {$limit} ads...");

        // Lấy danh sách ads từ database
        $ads = \App\Models\FacebookAd::limit($limit)->get();

        if ($ads->isEmpty()) {
            $this->warn("Không có ads nào trong database");
            return;
        }

        foreach ($ads as $ad) {
            $this->info("\n--- Testing Ad: {$ad->id} ---");
            
            $insights = $api->getInsightsForAd($ad->id);

            if (isset($insights['error'])) {
                $this->error("API Error for Ad {$ad->id}: " . json_encode($insights['error']));
                continue;
            }

            if (empty($insights['data'])) {
                $this->warn("Không có data insights cho ad {$ad->id}");
                continue;
            }

            $this->displayInsightsData($insights['data'][0], $ad->id);
        }
    }

    /**
     * Hiển thị data insights
     */
    private function displayInsightsData(array $insight, string $adId): void
    {
        $this->info("=== Video Metrics Analysis for Ad {$adId} ===");

        // Hiển thị tất cả fields có sẵn
        $this->info("Available fields:");
        foreach (array_keys($insight) as $field) {
            $this->line("- {$field}");
        }

        // Video metrics cụ thể
        $videoFields = [
            'video_views',
            'video_play_actions',
            'video_p25_watched_actions',
            'video_p50_watched_actions',
            'video_p75_watched_actions',
            'video_p95_watched_actions',
            'video_p100_watched_actions',
            'video_30_sec_watched_actions',
            'video_avg_time_watched_actions',
            'video_thruplay_watched_actions',
            'video_view_time',
            'video_avg_time_watched'
        ];

        $this->info("\n=== Video Metrics Values ===");
        foreach ($videoFields as $field) {
            $value = $insight[$field] ?? 'NOT_FOUND';
            $this->line("{$field}: " . (is_array($value) ? json_encode($value) : $value));
        }

        // Actions analysis
        if (isset($insight['actions']) && is_array($insight['actions'])) {
            $this->info("\n=== Actions Analysis ===");
            foreach ($insight['actions'] as $action) {
                if (isset($action['action_type']) && str_contains($action['action_type'], 'video')) {
                    $this->line("Action: {$action['action_type']} = {$action['value']}");
                }
            }
        }

        // Video avg time watched actions analysis
        if (isset($insight['video_avg_time_watched_actions'])) {
            $this->info("\n=== Video Avg Time Watched Actions ===");
            $avgTimeActions = $insight['video_avg_time_watched_actions'];
            
            if (is_array($avgTimeActions)) {
                foreach ($avgTimeActions as $action) {
                    $this->line("Action: {$action['action_type']} = {$action['value']}");
                }
            } else {
                $this->line("Value: {$avgTimeActions}");
            }
        }

        // Video view time analysis
        if (isset($insight['video_view_time'])) {
            $this->info("\n=== Video View Time ===");
            $viewTime = $insight['video_view_time'];
            $this->line("Value: " . (is_array($viewTime) ? json_encode($viewTime) : $viewTime));
        }

        // Raw data để debug
        $this->info("\n=== Raw Insight Data ===");
        $this->line(json_encode($insight, JSON_PRETTY_PRINT));
    }
}
