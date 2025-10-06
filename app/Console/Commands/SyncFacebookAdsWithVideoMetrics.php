<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\FacebookAdsSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SyncFacebookAdsWithVideoMetrics extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'facebook:sync-with-video-metrics 
                            {--since= : Start date (Y-m-d format)}
                            {--until= : End date (Y-m-d format)}
                            {--limit=10 : Limit number of ads to process for testing}
                            {--fix-video-metrics : Fix existing video metrics data}
                            {--day= : Sync a single day (Y-m-d), overrides since/until}
                            {--last30 : Sync last 30 days (overrides since/until)}';

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

        // Support quick flag --last30
        if ($this->option('day')) {
            $since = $this->option('day');
            $until = $since;
        } elseif ($this->option('last30')) {
            $since = now()->subDays(29)->format('Y-m-d');
            $until = now()->format('Y-m-d');
        } else {
            // Mặc định: chỉ đồng bộ trong tháng hiện tại nếu không truyền tham số
            $since = $this->option('since') ?: now()->startOfMonth()->format('Y-m-d');
            $until = $this->option('until') ?: now()->format('Y-m-d');
        }
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
            
            // Consolidate messaging insights so UI totals don't drift
            $this->info('Consolidating daily messaging insights with Ads paid conversations...');
            $this->consolidateMessagingInsights($since, $until);
            $this->info('Consolidation completed.');
            
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

    /**
     * Align facebook_page_daily_insights with Ads paid conversations and backfill missing days.
     * - For each page_id seen in the date window, ensure one row/day exists in facebook_page_daily_insights
     * - Persist raw ads_messaging_conversation_started per day from Ads
     *   (Total/new/organic will be handled in SyncAllFacebookData::syncPageMessagingInsights)
     */
    private function consolidateMessagingInsights(string $since, string $until): void
    {
        $start = Carbon::parse($since)->startOfDay();
        $end = Carbon::parse($until)->startOfDay();

        // Collect page_ids that have either ads insights or existing page daily rows in range
        $pageIds = DB::table('facebook_ad_insights')
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->distinct()->pluck('page_id')->toArray();

        $morePageIds = DB::table('facebook_page_daily_insights')
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->distinct()->pluck('page_id')->toArray();

        $pages = array_values(array_unique(array_merge($pageIds, $morePageIds)));
        if (empty($pages)) {
            return;
        }

        // Precompute date list
        $dates = [];
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $dates[] = $d->toDateString();
        }

        foreach ($pages as $pageId) {
            // Ads paid per day
            $paidByDate = DB::table('facebook_ad_insights')
                ->select('date', DB::raw('COALESCE(SUM(messaging_conversation_started_7d),0) as paid'))
                ->where('page_id', $pageId)
                ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->groupBy('date')
                ->pluck('paid', 'date');

            foreach ($dates as $date) {
                $paid = (int) ($paidByDate[$date] ?? 0);
                // Ensure daily row exists (do not compute organic/paid totals here)
                $existing = DB::table('facebook_page_daily_insights')
                    ->where('page_id', $pageId)
                    ->where('date', $date)
                    ->first();

                if ($existing) {
                    DB::table('facebook_page_daily_insights')
                        ->where('page_id', $pageId)
                        ->where('date', $date)
                        ->update([
                            'ads_messaging_conversation_started' => $paid,
                            'updated_at' => now(),
                        ]);
                } else {
                    DB::table('facebook_page_daily_insights')->insert([
                        'page_id' => $pageId,
                        'date' => $date,
                        'messages_new_conversations' => 0,
                        'messages_total_connections' => 0,
                        'messages_active_threads' => 0,
                        'ads_messaging_conversation_started' => $paid,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
