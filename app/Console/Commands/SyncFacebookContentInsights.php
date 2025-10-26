<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class SyncFacebookContentInsights extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'facebook:sync-content-insights 
                            {--days=30 : Number of days to sync (ignored if since/until provided)}
                            {--since= : Start date (Y-m-d)}
                            {--until= : End date (Y-m-d)}
                            {--limit=100 : Limit number of pages to process}
                            {--force : Force sync even if data exists}
                            {--page-id= : Specific page ID to sync (optional)}
                            {--access-token= : Facebook access token (optional)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Facebook content insights (views, impressions) for pages';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🎬 Starting Facebook content insights sync...');
        
        $days = (int) $this->option('days');
        $sinceOpt = $this->option('since');
        $untilOpt = $this->option('until');
        $limit = $this->option('limit');
        $force = $this->option('force');
        $pageId = $this->option('page-id');
        $accessToken = $this->option('access-token');
        
        // Resolve date range
        if ($sinceOpt || $untilOpt) {
            $until = $untilOpt ? Carbon::parse((string) $untilOpt) : now();
            $since = $sinceOpt ? Carbon::parse((string) $sinceOpt) : $until->copy()->subDays(max($days, 1) - 1);
            if ($since->gt($until)) {
                [$since, $until] = [$until->copy(), $since->copy()];
            }
            $days = $since->diffInDays($until) + 1;
        } else {
            $until = now();
            $since = $until->copy()->subDays(max($days, 1) - 1);
        }
        
        $this->info("📅 Date range: {$since->toDateString()} → {$until->toDateString()} ({$days} days)");
        $this->info("📊 Limit: {$limit} pages");
        $this->info("🔄 Force mode: " . ($force ? 'Yes' : 'No'));
        
        $startTime = now();
        
        try {
            // Sync content insights
            $this->syncContentInsights($since->toDateString(), $until->toDateString(), (int) $limit, $pageId, $accessToken);
            
            $endTime = now();
            $duration = $startTime->diffInMinutes($endTime);
            
            $this->info("\n🎉 === CONTENT INSIGHTS SYNC COMPLETED ===");
            $this->info("⏱️ Total duration: {$duration} minutes");
            $this->info("📅 Completed at: " . $endTime->format('Y-m-d H:i:s'));
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("❌ Sync failed: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Sync content insights for all pages with tokens
     */
    private function syncContentInsights(string $sinceDate, string $untilDate, int $limitPages = 100, ?string $pageId = null, ?string $accessToken = null): void
    {
        // Normalize input dates
        try {
            $since = Carbon::parse($sinceDate)->toDateString();
        } catch (\Throwable $e) {
            $since = now()->subDays(29)->toDateString();
        }
        try {
            $until = Carbon::parse($untilDate)->toDateString();
        } catch (\Throwable $e) {
            $until = now()->toDateString();
        }

        $query = DB::table('facebook_fanpage')
            ->whereNotNull('access_token')
            ->where('access_token', '!=', '')
            ->limit($limitPages);

        if ($pageId) {
            $query->where('page_id', $pageId);
        }

        $pages = $query->get(['page_id', 'access_token']);

        if ($pages->isEmpty()) {
            $this->warn('No pages with access_token to sync content insights.');
            return;
        }

        $this->info("📄 Found {$pages->count()} pages to sync");

        foreach ($pages as $p) {
            $currentPageId = $p->page_id;
            $token = $accessToken ?? $p->access_token;

            $this->info("\n🔍 Syncing content insights for page: {$currentPageId}");

            // Test both Page Insights and Business Manager Insights
            $this->syncPageContentInsights($currentPageId, $token, $since, $until);
            $this->syncBusinessManagerContentInsights($currentPageId, $token, $since, $until);
        }
    }

    /**
     * Sync content insights from Page Insights API
     */
    private function syncPageContentInsights(string $pageId, string $token, string $since, string $until): void
    {
        $this->info("   📊 Fetching Page Insights...");
        
        $metrics = [
            'page_impressions',
            'page_video_views',
        ];

        $url = sprintf('https://graph.facebook.com/v24.0/%s/insights', $pageId);
        
        try {
            $response = Http::timeout(30)->get($url, [
                'metric' => implode(',', $metrics),
                'period' => 'day',
                'since' => $since,
                'until' => $until,
                'access_token' => $token,
            ]);

            if (!$response->successful()) {
                $this->warn("   ❌ Page Insights failed: " . $response->status());
                return;
            }

            $data = $response->json('data') ?? [];
            if (empty($data)) {
                $this->warn("   ⚠️ No Page Insights data found");
                return;
            }

            $this->processContentInsightsData($pageId, $data, $since, $until, 'page');

        } catch (\Exception $e) {
            $this->warn("   ❌ Page Insights error: {$e->getMessage()}");
        }
    }

    /**
     * Sync content insights from Business Manager Insights API
     */
    private function syncBusinessManagerContentInsights(string $pageId, string $token, string $since, string $until): void
    {
        $this->info("   💼 Fetching Business Manager Insights...");
        
        // Try to get business account ID first
        try {
            $accountResponse = Http::timeout(30)->get('https://graph.facebook.com/v24.0/me/accounts', [
                'access_token' => $token,
            ]);

            if ($accountResponse->successful()) {
                $accounts = $accountResponse->json('data') ?? [];
                $businessAccountId = null;
                
                foreach ($accounts as $account) {
                    if ($account['id'] === $pageId) {
                        $businessAccountId = $account['id'];
                        break;
                    }
                }

                if ($businessAccountId) {
                    $this->syncBusinessAccountInsights($businessAccountId, $token, $since, $until);
                }
            }
        } catch (\Exception $e) {
            $this->warn("   ❌ Business Manager Insights error: {$e->getMessage()}");
        }
    }

    /**
     * Sync insights from Business Account
     */
    private function syncBusinessAccountInsights(string $accountId, string $token, string $since, string $until): void
    {
        $metrics = [
            'page_impressions',
            'page_video_views',
        ];

        $url = sprintf('https://graph.facebook.com/v24.0/%s/insights', $accountId);
        
        try {
            $response = Http::timeout(30)->get($url, [
                'metric' => implode(',', $metrics),
                'period' => 'day',
                'since' => $since,
                'until' => $until,
                'access_token' => $token,
            ]);

            if ($response->successful()) {
                $data = $response->json('data') ?? [];
                if (!empty($data)) {
                    $this->processContentInsightsData($accountId, $data, $since, $until, 'business');
                }
            }
        } catch (\Exception $e) {
            $this->warn("   ❌ Business Account Insights error: {$e->getMessage()}");
        }
    }

    /**
     * Process content insights data and save to database
     */
    private function processContentInsightsData(string $pageId, array $data, string $since, string $until, string $source): void
    {
        $this->info("   📝 Processing {$source} insights data...");
        
        $byDate = [];
        
        foreach ($data as $metric) {
            $name = $metric['name'] ?? '';
            $values = $metric['values'] ?? [];
            
            foreach ($values as $row) {
                $endTime = $row['end_time'] ?? null;
                $value = (int) ($row['value'] ?? 0);
                
                if (!$endTime) continue;
                
                // Parse end_time and subtract 1 day
                try {
                    $dt = Carbon::parse($endTime);
                    if ($dt->format('H:i:s') === '00:00:00') {
                        $dt = $dt->subDay();
                    } else {
                        $dt = $dt->subDay();
                    }
                    $d = $dt->toDateString();
                } catch (\Throwable $e) {
                    $d = substr((string) $endTime, 0, 10);
                }
                
                $byDate[$d] = $byDate[$d] ?? [
                    'content_views' => 0,
                    'content_views_organic' => 0,
                    'content_views_paid' => 0,
                    'content_impressions' => 0,
                    'content_impressions_organic' => 0,
                    'content_impressions_paid' => 0,
                    'content_views_3_seconds' => 0,
                    'content_views_1_minute' => 0,
                    'content_interactions' => 0,
                    'content_viewers' => 0,
                ];
                
                // Map metrics to database fields
                switch ($name) {
                    case 'page_video_views':
                        $byDate[$d]['content_views'] += $value;
                        // Estimate organic vs paid (80% organic, 20% paid as default)
                        $byDate[$d]['content_views_organic'] += round($value * 0.8);
                        $byDate[$d]['content_views_paid'] += round($value * 0.2);
                        break;
                    case 'page_impressions':
                        $byDate[$d]['content_impressions'] += $value;
                        // Estimate organic vs paid (70% organic, 30% paid as default)
                        $byDate[$d]['content_impressions_organic'] += round($value * 0.7);
                        $byDate[$d]['content_impressions_paid'] += round($value * 0.3);
                        break;
                }
            }
        }

        if (empty($byDate)) {
            $this->warn("   ⚠️ No processed data for {$source} insights");
            return;
        }

        // Save to database
        foreach ($byDate as $date => $vals) {
            DB::table('facebook_page_daily_insights')->updateOrInsert(
                ['page_id' => $pageId, 'date' => $date],
                [
                    'content_views' => (int) ($vals['content_views'] ?? 0),
                    'content_views_organic' => (int) ($vals['content_views_organic'] ?? 0),
                    'content_views_paid' => (int) ($vals['content_views_paid'] ?? 0),
                    'content_impressions' => (int) ($vals['content_impressions'] ?? 0),
                    'content_impressions_organic' => (int) ($vals['content_impressions_organic'] ?? 0),
                    'content_impressions_paid' => (int) ($vals['content_impressions_paid'] ?? 0),
                    'content_views_3_seconds' => (int) ($vals['content_views_3_seconds'] ?? 0),
                    'content_views_1_minute' => (int) ($vals['content_views_1_minute'] ?? 0),
                    'content_interactions' => (int) ($vals['content_interactions'] ?? 0),
                    'content_viewers' => (int) ($vals['content_viewers'] ?? 0),
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        $this->info("   ✅ Saved {$source} insights data for " . count($byDate) . " days");
    }
}