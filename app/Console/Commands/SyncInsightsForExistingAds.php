<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\FacebookAdsSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
            
            // Persist raw paid per day only; organic/paid totals are derived later by SyncAllFacebookData
            $this->info('Persisting raw Ads paid per day (ads_messaging_conversation_started)...');
            $this->persistRawPaidPerDay($since, $until);
            $this->info('Done saving raw paid per day.');
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

                // Cache media (images/thumbs) to public storage for longevity
                $cached = $this->cacheAdPostMedia($details, $pageId);
                if (is_array($details)) {
                    $details['cached_urls'] = array_filter($cached);
                }

                // Parse created_time from Facebook API (ISO 8601 format with timezone)
                // Always convert to UTC to ensure consistency
                $createdTime = null;
                $updatedTime = null;
                
                if (isset($details['created_time'])) {
                    try {
                        $createdTime = \Carbon\Carbon::parse($details['created_time'])->setTimezone('Asia/Ho_Chi_Minh');
                    } catch (\Exception $e) {
                        $this->warn("⚠️ Failed to parse created_time for post {$postId}: " . $e->getMessage());
                    }
                }
                
                if (isset($details['updated_time'])) {
                    try {
                        $updatedTime = \Carbon\Carbon::parse($details['updated_time'])->setTimezone('Asia/Ho_Chi_Minh');
                    } catch (\Exception $e) {
                        $this->warn("⚠️ Failed to parse updated_time for post {$postId}: " . $e->getMessage());
                    }
                }

                \Illuminate\Support\Facades\DB::table('facebook_post_ads')->insert([
                    'page_id' => $pageId,
                    'post_id' => $postId,
                    'time_range' => 'lifetime',
                    'message' => $details['message'] ?? null,
                    'type' => $details['type'] ?? null,
                    'permalink_url' => $details['permalink_url'] ?? null,
                    'created_time' => $createdTime,
                    'updated_time' => $updatedTime,
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
    private function cacheAdPostMedia(array $details, string $pageId): array
    {
        $out = [];
        $candidates = [];
        if (!empty($details['picture'])) $candidates['picture_local'] = (string) $details['picture'];
        if (!empty($details['full_picture'])) $candidates['full_picture_local'] = (string) $details['full_picture'];
        if (!empty($details['attachments']['data'][0]['media']['image']['src'])) {
            $candidates['video_thumbnail_local'] = (string) $details['attachments']['data'][0]['media']['image']['src'];
        }
        foreach ($candidates as $key => $url) {
            $local = $this->downloadToPublicSimple($url, $pageId);
            if ($local) $out[$key] = $local;
        }
        return $out;
    }

    private function downloadToPublicSimple(string $url, string $pageId): ?string
    {
        try {
            $hash = sha1($url);
            $ext = pathinfo(parse_url($url, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION) ?: 'jpg';
            $relPath = "facebook_media/{$pageId}/{$hash}.{$ext}";
            $disk = \Illuminate\Support\Facades\Storage::disk('public');
            if ($disk->exists($relPath)) return $disk->url($relPath);
            $resp = \Illuminate\Support\Facades\Http::timeout(15)->get($url);
            if (!$resp->successful()) return null;
            $disk->put($relPath, $resp->body());
            return $disk->url($relPath);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Align facebook_page_daily_insights with Ads paid conversations and backfill missing days.
     * Same logic as in SyncFacebookAdsWithVideoMetrics, colocated here to run after insights-only sync.
     */
    private function persistRawPaidPerDay(string $since, string $until): void
    {
        $start = Carbon::parse($since)->startOfDay();
        $end = Carbon::parse($until)->startOfDay();

        $pageIds = DB::table('facebook_ad_insights')
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->distinct()->pluck('page_id')->toArray();

        $morePageIds = DB::table('facebook_page_daily_insights')
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->distinct()->pluck('page_id')->toArray();

        $pages = array_values(array_unique(array_merge($pageIds, $morePageIds)));
        if (empty($pages)) { return; }

        $dates = [];
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $dates[] = $d->toDateString();
        }

        foreach ($pages as $pageId) {
            $paidByDate = DB::table('facebook_ad_insights')
                ->select('date', DB::raw('COALESCE(SUM(messaging_conversation_started_7d),0) as paid'))
                ->where('page_id', $pageId)
                ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->groupBy('date')
                ->pluck('paid', 'date');

            $nonZero = 0; $totalPaid = 0;

            foreach ($dates as $date) {
                $paid = (int) ($paidByDate[$date] ?? 0);
                if ($paid > 0) { $nonZero++; $totalPaid += $paid; }
                $exists = DB::table('facebook_page_daily_insights')
                    ->where('page_id', $pageId)
                    ->where('date', $date)
                    ->exists();

                if ($exists) {
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

            // Summary per page id for quick diagnostics
            \Log::info('ads_messaging_conversation_started persisted', [
                'page_id' => $pageId,
                'since' => $start->toDateString(),
                'until' => $end->toDateString(),
                'non_zero_days' => $nonZero,
                'total_paid' => $totalPaid,
            ]);
        }
    }
}


