<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\FacebookAdInsight;
use App\Services\FacebookAdsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateFacebookAdInsightFields extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'facebook:update-ad-insight-fields 
                            {--ad-insight-id= : ID của bản ghi Facebook Ad Insight cần update}
                            {--ad-id= : Facebook Ad ID để lấy data mới từ API}
                            {--limit=1 : Số lượng bản ghi để update (mặc định 1)}';

    /**
     * The console command description.
     */
    protected $description = 'Update các trường mới trong bảng facebook_ad_insights với data từ Facebook API';

    /**
     * Execute the console command.
     */
    public function handle(FacebookAdsService $api): int
    {
        $this->info('Bắt đầu update các trường mới trong facebook_ad_insights...');

        $adInsightId = $this->option('ad-insight-id') ? (int) $this->option('ad-insight-id') : null;
        $adId = $this->option('ad-id');
        $limit = (int) $this->option('limit');

        try {
            if ($adInsightId) {
                // Update một bản ghi cụ thể
                $this->updateSingleAdInsight($api, $adInsightId, $adId);
            } else {
                // Update nhiều bản ghi
                $this->updateMultipleAdInsights($api, $limit);
            }

            return 0;

        } catch (\Exception $e) {
            $this->error("Error: {$e->getMessage()}");
            Log::error('Update Facebook Ad Insight fields error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    /**
     * Update một bản ghi cụ thể
     */
    private function updateSingleAdInsight(FacebookAdsService $api, int $adInsightId, ?string $adId = null): void
    {
        $this->info("Updating Ad Insight ID: {$adInsightId}");

        $adInsight = FacebookAdInsight::find($adInsightId);
        if (!$adInsight) {
            $this->error("Không tìm thấy Ad Insight với ID: {$adInsightId}");
            return;
        }

        // Nếu không có ad_id, sử dụng từ ad_insight
        $facebookAdId = $adId ?: $adInsight->ad_id;
        
        $this->updateAdInsightWithApiData($api, $adInsight, $facebookAdId);
    }

    /**
     * Update nhiều bản ghi
     */
    private function updateMultipleAdInsights(FacebookAdsService $api, int $limit): void
    {
        $this->info("Updating {$limit} ad insights...");

        $adInsights = FacebookAdInsight::limit($limit)->get();

        if ($adInsights->isEmpty()) {
            $this->warn("Không có ad insights nào trong database");
            return;
        }

        foreach ($adInsights as $adInsight) {
            $this->info("\n--- Updating Ad Insight: {$adInsight->id} ---");
            $this->updateAdInsightWithApiData($api, $adInsight, $adInsight->ad_id);
        }
    }

    /**
     * Update Ad Insight với data từ Facebook API
     */
    private function updateAdInsightWithApiData(FacebookAdsService $api, FacebookAdInsight $adInsight, string $facebookAdId): void
    {
        try {
            // Lấy data mới từ Facebook API
            $insights = $api->getInsightsForAd($facebookAdId);

            if (isset($insights['error'])) {
                $this->error("API Error: " . json_encode($insights['error']));
                return;
            }

            if (empty($insights['data'])) {
                $this->warn("Không có data insights cho ad {$facebookAdId}");
                return;
            }

            $insight = $insights['data'][0];
            
            // Parse actions để map về các trường quan trọng
            $actions = $insight['actions'] ?? [];
            $actionTotals = [];
            foreach ($actions as $a) {
                $type = $a['action_type'] ?? '';
                $val = (int) ($a['value'] ?? 0);
                if ($type === '') { continue; }
                $actionTotals[$type] = ($actionTotals[$type] ?? 0) + $val;
            }

            // Update các trường mới
            $updateData = [
                // Basic metrics mới
                'unique_ctr' => (float) ($insight['unique_ctr'] ?? 0),
                
                // Conversion metrics
                'conversions' => (int) ($insight['conversions'] ?? (
                    ($actionTotals['lead'] ?? 0)
                    + ($actionTotals['onsite_conversion.lead'] ?? 0)
                    + ($actionTotals['onsite_web_lead'] ?? 0)
                    + ($actionTotals['onsite_conversion.lead_grouped'] ?? 0)
                )),
                'conversion_values' => (float) ($insight['conversion_values'] ?? 0),
                'cost_per_conversion' => (float) ($insight['cost_per_conversion'] ?? 0),
                'purchase_roas' => (float) ($insight['purchase_roas'] ?? 0),
                
                // Click metrics
                'outbound_clicks' => (int) ($insight['outbound_clicks'] ?? 0),
                'unique_outbound_clicks' => (int) ($insight['unique_outbound_clicks'] ?? 0),
                'inline_link_clicks' => (int) ($insight['inline_link_clicks'] ?? ($actionTotals['link_click'] ?? 0)),
                'unique_inline_link_clicks' => (int) ($insight['unique_inline_link_clicks'] ?? 0),
                'website_clicks' => (int) ($insight['website_clicks'] ?? ($actionTotals['link_click'] ?? 0)),
                
                // JSON fields
                'actions' => $insight['actions'] ?? null,
                'action_values' => $insight['action_values'] ?? null,
                'cost_per_action_type' => $insight['cost_per_action_type'] ?? null,
                'cost_per_unique_action_type' => $insight['cost_per_unique_action_type'] ?? null,
            ];

            // Update bản ghi
            $adInsight->update($updateData);

            $this->info("✅ Đã update thành công Ad Insight ID: {$adInsight->id}");
            
            // Hiển thị thông tin đã update
            $this->table(
                ['Field', 'Old Value', 'New Value'],
                [
                    ['unique_ctr', $adInsight->getOriginal('unique_ctr'), $updateData['unique_ctr']],
                    ['conversions', $adInsight->getOriginal('conversions'), $updateData['conversions']],
                    ['conversion_values', $adInsight->getOriginal('conversion_values'), $updateData['conversion_values']],
                    ['cost_per_conversion', $adInsight->getOriginal('cost_per_conversion'), $updateData['cost_per_conversion']],
                    ['purchase_roas', $adInsight->getOriginal('purchase_roas'), $updateData['purchase_roas']],
                    ['outbound_clicks', $adInsight->getOriginal('outbound_clicks'), $updateData['outbound_clicks']],
                    ['unique_outbound_clicks', $adInsight->getOriginal('unique_outbound_clicks'), $updateData['unique_outbound_clicks']],
                    ['inline_link_clicks', $adInsight->getOriginal('inline_link_clicks'), $updateData['inline_link_clicks']],
                    ['unique_inline_link_clicks', $adInsight->getOriginal('unique_inline_link_clicks'), $updateData['unique_inline_link_clicks']],
                    ['website_clicks', $adInsight->getOriginal('website_clicks'), $updateData['website_clicks']],
                ]
            );

            // Log để debug
            Log::info("Updated Facebook Ad Insight fields", [
                'ad_insight_id' => $adInsight->id,
                'ad_id' => $facebookAdId,
                'update_data' => $updateData,
                'insight_keys' => array_keys($insight)
            ]);

        } catch (\Exception $e) {
            $this->error("Lỗi khi update Ad Insight {$adInsight->id}: {$e->getMessage()}");
            Log::error("Update Ad Insight error", [
                'ad_insight_id' => $adInsight->id,
                'ad_id' => $facebookAdId,
                'error' => $e->getMessage()
            ]);
        }
    }
}
