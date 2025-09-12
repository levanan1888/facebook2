<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\FacebookBusiness;
use App\Models\FacebookAdAccount;
use App\Models\FacebookCampaign;
use App\Models\FacebookAdSet;
use App\Models\FacebookAd;

class SyncAdsWithPrerequisites extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'facebook:sync-ads-with-prerequisites 
                            {--since= : Start date (Y-m-d format)}
                            {--until= : End date (Y-m-d format)}
                            {--days=7 : Number of days to sync (if since/until not provided)}
                            {--limit=100 : Number of ads to sync per ad account}
                            {--delay=1 : Delay between requests in seconds}
                            {--force : Force sync even if prerequisites are missing}
                            {--check-only : Only check prerequisites without syncing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Facebook ads only when Business Manager and Ad Account prerequisites are met';

    /**
     * Facebook Graph API base URL
     */
    private $graphApiUrl = 'https://graph.facebook.com/v23.0';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $accessToken = config('services.facebook.ads_token');
        $since = $this->option('since');
        $until = $this->option('until');
        $days = $this->option('days');
        $limit = $this->option('limit');
        $delay = $this->option('delay');
        $force = $this->option('force');
        $checkOnly = $this->option('check-only');

        if (!$accessToken) {
            $this->error('❌ Access token is required');
            $this->info('Usage: php artisan facebook:sync-ads-with-prerequisites --access-token="YOUR_TOKEN"');
            $this->info('Or set FACEBOOK_ADS_TOKEN in .env file');
            return 1;
        }

        // Xử lý date range
        if ($since && $until) {
            $sinceDate = $since;
            $untilDate = $until;
            $this->info("📅 Date range: {$sinceDate} to {$untilDate}");
        } else {
            $sinceDate = Carbon::now()->subDays($days)->format('Y-m-d');
            $untilDate = Carbon::now()->format('Y-m-d');
            $this->info("📅 Days to sync: {$days} (from {$sinceDate} to {$untilDate})");
        }

        $this->info("🚀 Starting Facebook ads sync with prerequisites check...");
        $this->info("📱 Facebook Graph API Version: v23.0");
        $this->info("🔑 Access Token: " . substr($accessToken, 0, 20) . "...");
        $this->info("📊 Ads per ad account: {$limit}");
        $this->info("⏱️  Delay between requests: {$delay}s");
        $this->info("🔄 Force mode: " . ($force ? 'Yes' : 'No'));
        $this->info("🔍 Check only mode: " . ($checkOnly ? 'Yes' : 'No'));

        try {
            // Step 1: Check prerequisites
            $this->info("\n=== 🔍 Step 1: Checking prerequisites ===");
            $prerequisites = $this->checkPrerequisites($force);
            
            if (!$prerequisites['valid'] && !$force) {
                $this->error("❌ Prerequisites not met:");
                foreach ($prerequisites['issues'] as $issue) {
                    $this->error("   - {$issue}");
                }
                $this->info("\n💡 To fix prerequisites, run:");
                $this->info("   php artisan facebook:sync-all-data --days=30");
                $this->info("\nOr use --force to sync anyway (not recommended)");
                return 1;
            }

            $this->info("✅ Prerequisites check passed");
            $this->info("   📊 Business Managers: {$prerequisites['businesses']}");
            $this->info("   💰 Ad Accounts: {$prerequisites['ad_accounts']}");

            if ($checkOnly) {
                $this->info("\n✅ Check completed successfully");
                return 0;
            }

            // Step 2: Sync ads from valid ad accounts
            $this->info("\n=== 💰 Step 2: Syncing ads from valid ad accounts ===");
            $syncResult = $this->syncAdsFromValidAccounts($accessToken, $sinceDate, $untilDate, $limit, $delay);

            // Step 3: Generate summary report
            $this->info("\n=== 📊 Step 3: Summary Report ===");
            $this->generateSummaryReport($syncResult);

            $this->info("\n🎉 === Sync completed successfully ===");
            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Error during sync: " . $e->getMessage());
            Log::error('Facebook ads sync with prerequisites error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    /**
     * Check if prerequisites are met
     */
    private function checkPrerequisites($force = false): array
    {
        $issues = [];
        
        // Check Business Managers
        $businessCount = FacebookBusiness::count();
        if ($businessCount == 0) {
            $issues[] = "No Business Managers found in database";
        }

        // Check Ad Accounts
        $adAccountCount = FacebookAdAccount::count();
        if ($adAccountCount == 0) {
            $issues[] = "No Ad Accounts found in database";
        }

        // Check if Ad Accounts have valid Business Manager relationships
        $orphanAdAccounts = FacebookAdAccount::whereNull('business_id')
            ->orWhere('business_id', '')
            ->count();
        
        if ($orphanAdAccounts > 0) {
            $issues[] = "{$orphanAdAccounts} Ad Accounts without Business Manager relationship";
        }

        // Check if we have any valid ad accounts with business relationships
        $validAdAccounts = FacebookAdAccount::whereNotNull('business_id')
            ->where('business_id', '!=', '')
            ->whereHas('business')
            ->count();

        if ($validAdAccounts == 0) {
            $issues[] = "No valid Ad Accounts with Business Manager relationships found";
        }

        return [
            'valid' => empty($issues),
            'issues' => $issues,
            'businesses' => $businessCount,
            'ad_accounts' => $adAccountCount,
            'valid_ad_accounts' => $validAdAccounts,
            'orphan_ad_accounts' => $orphanAdAccounts
        ];
    }

    /**
     * Sync ads from valid ad accounts
     */
    private function syncAdsFromValidAccounts($accessToken, $sinceDate, $untilDate, $limit, $delay): array
    {
        $result = [
            'ad_accounts_processed' => 0,
            'campaigns_synced' => 0,
            'adsets_synced' => 0,
            'ads_synced' => 0,
            'ad_insights_synced' => 0,
            'errors' => []
        ];

        // Get valid ad accounts with business relationships
        $adAccounts = FacebookAdAccount::whereNotNull('business_id')
            ->where('business_id', '!=', '')
            ->whereHas('business')
            ->with('business')
            ->get();

        if ($adAccounts->isEmpty()) {
            $this->warn("⚠️  No valid ad accounts found with business relationships");
            return $result;
        }

        $this->info("📊 Found {$adAccounts->count()} valid ad accounts to process");

        foreach ($adAccounts as $index => $adAccount) {
            $this->info("\n💰 Processing Ad Account " . ($index + 1) . "/" . $adAccounts->count() . ": {$adAccount->name} ({$adAccount->id})");
            $this->info("   Business: {$adAccount->business->name} ({$adAccount->business->id})");

            try {
                // Sync campaigns for this ad account
                $campaignsResult = $this->syncCampaignsForAccount($adAccount, $accessToken, $sinceDate, $untilDate, $limit, $delay);
                $result['campaigns_synced'] += $campaignsResult['campaigns'];

                // Sync adsets for this ad account
                $adsetsResult = $this->syncAdSetsForAccount($adAccount, $accessToken, $sinceDate, $untilDate, $limit, $delay);
                $result['adsets_synced'] += $adsetsResult['adsets'];

                // Sync ads for this ad account
                $adsResult = $this->syncAdsForAccount($adAccount, $accessToken, $sinceDate, $untilDate, $limit, $delay);
                $result['ads_synced'] += $adsResult['ads'];
                $result['ad_insights_synced'] += $adsResult['insights'];

                $result['ad_accounts_processed']++;

                $this->info("   ✅ Completed: {$campaignsResult['campaigns']} campaigns, {$adsetsResult['adsets']} adsets, {$adsResult['ads']} ads, {$adsResult['insights']} insights");

            } catch (\Exception $e) {
                $errorMsg = "Error processing ad account {$adAccount->id}: " . $e->getMessage();
                $this->error("   ❌ {$errorMsg}");
                $result['errors'][] = $errorMsg;
                Log::error('Ad account sync error', [
                    'ad_account_id' => $adAccount->id,
                    'error' => $e->getMessage()
                ]);
            }

            // Delay between ad accounts
            if ($index < $adAccounts->count() - 1) {
                sleep($delay);
            }
        }

        return $result;
    }

    /**
     * Sync campaigns for a specific ad account
     */
    private function syncCampaignsForAccount($adAccount, $accessToken, $sinceDate, $untilDate, $limit, $delay): array
    {
        $result = ['campaigns' => 0];

        try {
            $url = "{$this->graphApiUrl}/{$adAccount->id}/campaigns";
            $params = [
                'access_token' => $accessToken,
                'fields' => 'id,name,status,effective_status,objective,created_time,updated_time,start_time,stop_time',
                'time_range' => json_encode([
                    'since' => $sinceDate,
                    'until' => $untilDate
                ]),
                'limit' => $limit
            ];

            $response = Http::timeout(30)->get($url, $params);

            if (!$response->successful()) {
                throw new \Exception("Failed to fetch campaigns: " . $response->body());
            }

            $data = $response->json();
            $campaigns = $data['data'] ?? [];

            foreach ($campaigns as $campaign) {
                FacebookCampaign::updateOrCreate(
                    ['id' => $campaign['id']],
                    [
                        'name' => $campaign['name'] ?? null,
                        'status' => $campaign['status'] ?? null,
                        'effective_status' => $campaign['effective_status'] ?? null,
                        'objective' => $campaign['objective'] ?? null,
                        'ad_account_id' => $adAccount->id,
                        'created_time' => isset($campaign['created_time']) ? Carbon::parse($campaign['created_time']) : null,
                        'updated_time' => isset($campaign['updated_time']) ? Carbon::parse($campaign['updated_time']) : null,
                        'start_time' => isset($campaign['start_time']) ? Carbon::parse($campaign['start_time']) : null,
                        'stop_time' => isset($campaign['stop_time']) ? Carbon::parse($campaign['stop_time']) : null,
                    ]
                );
                $result['campaigns']++;
            }

        } catch (\Exception $e) {
            Log::warning("Failed to sync campaigns for ad account {$adAccount->id}: " . $e->getMessage());
        }

        return $result;
    }

    /**
     * Sync adsets for a specific ad account
     */
    private function syncAdSetsForAccount($adAccount, $accessToken, $sinceDate, $untilDate, $limit, $delay): array
    {
        $result = ['adsets' => 0];

        try {
            $url = "{$this->graphApiUrl}/{$adAccount->id}/adsets";
            $params = [
                'access_token' => $accessToken,
                'fields' => 'id,name,status,effective_status,campaign_id,created_time,updated_time,start_time,stop_time',
                'time_range' => json_encode([
                    'since' => $sinceDate,
                    'until' => $untilDate
                ]),
                'limit' => $limit
            ];

            $response = Http::timeout(30)->get($url, $params);

            if (!$response->successful()) {
                throw new \Exception("Failed to fetch adsets: " . $response->body());
            }

            $data = $response->json();
            $adsets = $data['data'] ?? [];

            foreach ($adsets as $adset) {
                FacebookAdSet::updateOrCreate(
                    ['id' => $adset['id']],
                    [
                        'name' => $adset['name'] ?? null,
                        'status' => $adset['status'] ?? null,
                        'effective_status' => $adset['effective_status'] ?? null,
                        'campaign_id' => $adset['campaign_id'] ?? null,
                        'ad_account_id' => $adAccount->id,
                        'created_time' => isset($adset['created_time']) ? Carbon::parse($adset['created_time']) : null,
                        'updated_time' => isset($adset['updated_time']) ? Carbon::parse($adset['updated_time']) : null,
                        'start_time' => isset($adset['start_time']) ? Carbon::parse($adset['start_time']) : null,
                        'stop_time' => isset($adset['stop_time']) ? Carbon::parse($adset['stop_time']) : null,
                    ]
                );
                $result['adsets']++;
            }

        } catch (\Exception $e) {
            Log::warning("Failed to sync adsets for ad account {$adAccount->id}: " . $e->getMessage());
        }

        return $result;
    }

    /**
     * Sync ads for a specific ad account
     */
    private function syncAdsForAccount($adAccount, $accessToken, $sinceDate, $untilDate, $limit, $delay): array
    {
        $result = ['ads' => 0, 'insights' => 0];

        try {
            $url = "{$this->graphApiUrl}/{$adAccount->id}/ads";
            $params = [
                'access_token' => $accessToken,
                'fields' => 'id,name,status,effective_status,adset_id,campaign_id,created_time,updated_time',
                'time_range' => json_encode([
                    'since' => $sinceDate,
                    'until' => $untilDate
                ]),
                'limit' => $limit
            ];

            $response = Http::timeout(30)->get($url, $params);

            if (!$response->successful()) {
                throw new \Exception("Failed to fetch ads: " . $response->body());
            }

            $data = $response->json();
            $ads = $data['data'] ?? [];

            foreach ($ads as $ad) {
                $facebookAd = FacebookAd::updateOrCreate(
                    ['id' => $ad['id']],
                    [
                        'name' => $ad['name'] ?? null,
                        'status' => $ad['status'] ?? null,
                        'effective_status' => $ad['effective_status'] ?? null,
                        'adset_id' => $ad['adset_id'] ?? null,
                        'campaign_id' => $ad['campaign_id'] ?? null,
                        'account_id' => $adAccount->id,
                        'created_time' => isset($ad['created_time']) ? Carbon::parse($ad['created_time']) : null,
                        'updated_time' => isset($ad['updated_time']) ? Carbon::parse($ad['updated_time']) : null,
                    ]
                );
                $result['ads']++;

                // Sync insights for this ad
                try {
                    $insightsResult = $this->syncAdInsights($facebookAd, $accessToken, $sinceDate, $untilDate);
                    $result['insights'] += $insightsResult;
                } catch (\Exception $e) {
                    Log::warning("Failed to sync insights for ad {$ad['id']}: " . $e->getMessage());
                }

                // Delay between ads
                sleep($delay);
            }

        } catch (\Exception $e) {
            Log::warning("Failed to sync ads for ad account {$adAccount->id}: " . $e->getMessage());
        }

        return $result;
    }

    /**
     * Sync insights for a specific ad
     */
    private function syncAdInsights($ad, $accessToken, $sinceDate, $untilDate): int
    {
        $insightsCount = 0;

        try {
            $url = "{$this->graphApiUrl}/{$ad->id}/insights";
            $params = [
                'access_token' => $accessToken,
                'fields' => 'impressions,clicks,spend,reach,frequency,cpm,cpc,cpp,ctr,cost_per_conversion,conversions,conversion_values',
                'time_range' => json_encode([
                    'since' => $sinceDate,
                    'until' => $untilDate
                ]),
                'level' => 'ad'
            ];

            $response = Http::timeout(30)->get($url, $params);

            if (!$response->successful()) {
                throw new \Exception("Failed to fetch insights: " . $response->body());
            }

            $data = $response->json();
            $insights = $data['data'] ?? [];

            foreach ($insights as $insight) {
                DB::table('facebook_ad_insights')->updateOrInsert(
                    [
                        'ad_id' => $ad->id,
                        'date_start' => $insight['date_start'] ?? $sinceDate,
                        'date_stop' => $insight['date_stop'] ?? $untilDate
                    ],
                    [
                        'ad_id' => $ad->id,
                        'date_start' => $insight['date_start'] ?? $sinceDate,
                        'date_stop' => $insight['date_stop'] ?? $untilDate,
                        'impressions' => $insight['impressions'] ?? 0,
                        'clicks' => $insight['clicks'] ?? 0,
                        'spend' => $insight['spend'] ?? 0,
                        'reach' => $insight['reach'] ?? 0,
                        'frequency' => $insight['frequency'] ?? 0,
                        'cpm' => $insight['cpm'] ?? 0,
                        'cpc' => $insight['cpc'] ?? 0,
                        'cpp' => $insight['cpp'] ?? 0,
                        'ctr' => $insight['ctr'] ?? 0,
                        'cost_per_conversion' => $insight['cost_per_conversion'] ?? 0,
                        'conversions' => $insight['conversions'] ?? 0,
                        'conversion_values' => $insight['conversion_values'] ?? 0,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                );
                $insightsCount++;
            }

            // Update last insights sync time
            $ad->update(['last_insights_sync' => now()]);

        } catch (\Exception $e) {
            Log::warning("Failed to sync insights for ad {$ad->id}: " . $e->getMessage());
        }

        return $insightsCount;
    }

    /**
     * Generate summary report
     */
    private function generateSummaryReport($syncResult): void
    {
        $this->info("📊 === SYNC SUMMARY REPORT ===");
        $this->info("💰 Ad Accounts processed: {$syncResult['ad_accounts_processed']}");
        $this->info("📈 Campaigns synced: {$syncResult['campaigns_synced']}");
        $this->info("🎯 Ad Sets synced: {$syncResult['adsets_synced']}");
        $this->info("📢 Ads synced: {$syncResult['ads_synced']}");
        $this->info("📊 Ad Insights synced: {$syncResult['ad_insights_synced']}");

        if (!empty($syncResult['errors'])) {
            $this->warn("\n⚠️  Errors encountered:");
            foreach ($syncResult['errors'] as $error) {
                $this->warn("   - {$error}");
            }
        }

        // Database statistics
        $totalAds = FacebookAd::count();
        $totalInsights = DB::table('facebook_ad_insights')->count();
        $recentAds = FacebookAd::where('created_at', '>=', now()->subDays(7))->count();

        $this->info("\n📈 === DATABASE STATISTICS ===");
        $this->info("📢 Total ads in database: {$totalAds}");
        $this->info("📊 Total ad insights in database: {$totalInsights}");
        $this->info("🆕 Recent ads (last 7 days): {$recentAds}");

        $this->info("\n✅ Summary report completed");
    }
}
