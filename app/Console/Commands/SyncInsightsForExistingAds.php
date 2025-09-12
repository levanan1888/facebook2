<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\FacebookAdsSyncService;
use Illuminate\Console\Command;

class SyncInsightsForExistingAds extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'facebook:sync-insights-only 
                            {--since= : Start date (Y-m-d)}
                            {--until= : End date (Y-m-d)}
                            {--day= : A single day (Y-m-d), overrides since/until}
                            {--last30 : Sync last 30 days}
                            {--limit= : Limit number of ads}
                            {--with-breakdowns : Also fetch and save breakdowns}';

    /**
     * The console command description.
     */
    protected $description = 'Sync only Ad Insights (and breakdowns/video metrics) for existing Ads in DB, without fetching BM/Accounts/Campaigns/AdSets';

    public function handle(FacebookAdsSyncService $service): int
    {
        if ($this->option('day')) {
            $since = (string) $this->option('day');
            $until = $since;
        } elseif ($this->option('last30')) {
            $since = now()->subDays(29)->toDateString();
            $until = now()->toDateString();
        } else {
            $since = (string) ($this->option('since') ?: now()->startOfMonth()->toDateString());
            $until = (string) ($this->option('until') ?: now()->toDateString());
        }
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;
        $withBreakdowns = (bool) $this->option('with-breakdowns');

        $this->info("Sync Ad Insights for existing Ads: {$since} → {$until}" . ($limit ? ", limit {$limit}" : ''));

        $progress = function (array $data) {
            if (!empty($data['message'])) {
                $this->info($data['message']);
            }
            if (!empty($data['counts'])) {
                $c = $data['counts'];
                $this->table(
                    ['Metric', 'Count'],
                    [
                        ['Ads processed', $c['ads'] ?? 0],
                        ['Ad Insights', $c['ad_insights'] ?? 0],
                        ['Breakdowns', $c['breakdowns'] ?? 0],
                    ]
                );
            }
        };

        try {
            $result = $service->syncInsightsForExistingAds($progress, $since, $until, $limit, $withBreakdowns);
            // Bổ sung: lưu thông tin post gắn với ads vào bảng facebook_post_ads (time=lifetime)
            $this->info('>>> calling saveAdPostsLifetime');
            $this->saveAdPostsLifetime($since, $until);
            $this->info('<<< finished saveAdPostsLifetime');
            $this->info('Completed.');
            $this->table(
                ['Metric', 'Count'],
                [
                    ['Ads processed', $result['ads'] ?? 0],
                    ['Ad Insights', $result['ad_insights'] ?? 0],
                    ['Breakdowns', $result['breakdowns'] ?? 0],
                ]
            );
            return 0;
        } catch (\Throwable $e) {
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Lưu thông tin các post gắn với ads vào bảng facebook_post_ads (lifetime)
     */
    private function saveAdPostsLifetime(string $since, string $until): void
    {
        try {
          
            if (!\Illuminate\Support\Facades\Schema::hasTable('facebook_post_ads')) {
                $this->warn('facebook_post_ads table not found. Run migrations to enable saving post data.');
                return;
            }

            // Lấy danh sách cặp (page_id, post_id) từ bảng insights theo khoảng ngày
            $pairs = \App\Models\FacebookAdInsight::query()
                ->select(['page_id','post_id'])
                ->whereNotNull('post_id')
                ->when($since && $until, function($q) use ($since,$until){
                    $q->whereBetween('date', [$since, $until]);
                })
                ->groupBy('page_id','post_id')
                ->get();
            $this->info('Pairs to process: ' . $pairs->count());

            $api = app(\App\Services\FacebookAdsService::class);

            $saved = 0; $skipped = 0;
            foreach ($pairs as $p) {
                $postId = (string) $p->post_id;
                $pageId = (string) ($p->page_id ?? '');
                if (!$postId || !$pageId) { continue; }

                // Bỏ qua nếu đã có
                $exists = \Illuminate\Support\Facades\DB::table('facebook_post_ads')
                    ->where('page_id', $pageId)
                    ->where('post_id', $postId)
                    ->exists();
                if ($exists) { $skipped++; continue; }

                // Gọi API lấy chi tiết post
                $details = $api->getPostDetails($postId);
                if (!is_array($details) || isset($details['error'])) { continue; }

                \Illuminate\Support\Facades\DB::table('facebook_post_ads')->insert([
                    'page_id' => $pageId,
                    'post_id' => $postId,
                    'time_range' => 'lifetime',
                    'message' => $details['message'] ?? null,
                    'type' => $details['type'] ?? null,
                    'permalink_url' => $details['permalink_url'] ?? null,
                    'created_time' => isset($details['created_time']) ? \Carbon\Carbon::parse($details['created_time']) : null,
                    'updated_time' => isset($details['updated_time']) ? \Carbon\Carbon::parse($details['updated_time']) : null,
                    'raw' => json_encode($details),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $saved++;
            }

            $this->info("Saved post details for ads to facebook_post_ads (lifetime). saved={$saved}, skipped_existing={$skipped}");
        } catch (\Throwable $e) {
            $this->warn('Could not save post details for ads: ' . $e->getMessage());
        }
    }
}


