<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\FacebookAdsSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncFacebookAdsWithVideoMetrics extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'facebook:sync-with-video-metrics 
                            {--since= : Start date (Y-m-d format)}
                            {--until= : End date (Y-m-d format)}
                            {--limit=10 : Limit number of ads to process for testing}
                            {--fix-video-metrics : Fix existing video metrics data}';

    /**
     * The console command description.
     */
    protected $description = 'Sync Facebook Ads data with complete video metrics and breakdowns from Facebook Business Manager';

    /**
     * Execute the console command.
     */
    public function handle(FacebookAdsSyncService $syncService): int
    {
        $this->info('Bắt đầu sync Facebook Ads với video metrics đầy đủ từ Facebook Business Manager...');

        $since = $this->option('since') ?: now()->subDays(360)->format('Y-m-d');
        $until = $this->option('until') ?: now()->format('Y-m-d');
        $limit = (int) $this->option('limit');
        $fixVideoMetrics = $this->option('fix-video-metrics');

        $this->info("Time range: {$since} to {$until}");
        $this->info("Limit: {$limit} ads");
        if ($fixVideoMetrics) {
            $this->info("Mode: Fix existing video metrics data");
        }

        try {
            // Progress callback
            $progressCallback = function ($data) {
                $this->info($data['message']);
                $this->table(
                    ['Metric', 'Count'],
                    [
                        ['Businesses', $data['counts']['businesses']],
                        ['Accounts', $data['counts']['accounts']],
                        ['Campaigns', $data['counts']['campaigns']],
                        ['Ad Sets', $data['counts']['adsets']],
                        ['Ads', $data['counts']['ads']],
                        ['Ad Insights', $data['counts']['ad_insights']],
                        ['Breakdowns', $data['counts']['breakdowns'] ?? 0],
                        ['Video Metrics Fixed', $data['counts']['video_metrics_fixed'] ?? 0],
                    ]
                );

                if (!empty($data['errors'])) {
                    $this->error('Errors:');
                    foreach ($data['errors'] as $error) {
                        $errorMsg = is_array($error) ? json_encode($error) : $error;
                        $this->error("- {$errorMsg}");
                    }
                }
            };

            // Sync data với video metrics đầy đủ từ Facebook BM
            $result = $syncService->syncFacebookData($progressCallback, $since, $until, $limit, $fixVideoMetrics);

            $this->info('Sync completed!');
            $this->table(
                ['Metric', 'Count'],
                [
                    ['Businesses', $result['businesses']],
                    ['Accounts', $result['accounts']],
                    ['Campaigns', $result['campaigns']],
                    ['Ad Sets', $result['adsets']],
                    ['Ads', $result['ads']],
                    ['Ad Insights', $result['ad_insights']],
                    ['Breakdowns', $result['breakdowns'] ?? 0],
                    ['Video Metrics Fixed', $result['video_metrics_fixed'] ?? 0],
                ]
            );

            if (!empty($result['errors'])) {
                $this->error('Errors occurred:');
                foreach ($result['errors'] as $error) {
                    $errorMsg = is_array($error) ? json_encode($error) : $error;
                    $this->error("- {$errorMsg}");
                }
            }

            $this->info("Duration: {$result['duration']} seconds");
            
            // Hiển thị thống kê video metrics
            if (isset($result['video_metrics_stats'])) {
                $this->info('Video Metrics Statistics:');
                $this->table(
                    ['Metric', 'Value'],
                    [
                        ['Total Video Views', number_format($result['video_metrics_stats']['total_video_views'] ?? 0)],
                        ['Total Video Plays', number_format($result['video_metrics_stats']['total_video_plays'] ?? 0)],
                        ['Video Plays at 25%', number_format($result['video_metrics_stats']['total_video_plays_at_25'] ?? 0)],
                        ['Video Plays at 50%', number_format($result['video_metrics_stats']['total_video_plays_at_50'] ?? 0)],
                        ['Video Plays at 75%', number_format($result['video_metrics_stats']['total_video_plays_at_75'] ?? 0)],
                        ['Video Plays at 100%', number_format($result['video_metrics_stats']['total_video_plays_at_100'] ?? 0)],
                        ['Thruplays', number_format($result['video_metrics_stats']['total_thruplays'] ?? 0)],
                        ['Video 30s Watched', number_format($result['video_metrics_stats']['total_video_30_sec_watched'] ?? 0)],
                    ]
                );
            }
            
            return 0;

        } catch (\Exception $e) {
            $this->error("Error: {$e->getMessage()}");
            Log::error('Facebook sync error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }
}
