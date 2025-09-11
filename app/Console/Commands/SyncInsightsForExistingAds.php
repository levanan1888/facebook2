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
}


