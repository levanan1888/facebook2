<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\FacebookAd;
use App\Models\FacebookAdInsight;
// Bỏ phụ thuộc bảng post insights
use App\Models\FacebookReportSummary;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReportSummaryService
{
    /**
     * Cập nhật bảng tổng hợp cho tất cả entities
     */
    public function updateAllSummaries(string $date = null): array
    {
        $date = $date ?? date('Y-m-d');
        $results = [];

        try {
            // Cập nhật summary cho từng loại entity
            $results['businesses'] = $this->updateBusinessSummaries($date);
            $results['accounts'] = $this->updateAccountSummaries($date);
            $results['campaigns'] = $this->updateCampaignSummaries($date);
            $results['adsets'] = $this->updateAdSetSummaries($date);
            $results['ads'] = $this->updateAdSummaries($date);
            $results['posts'] = $this->updatePostSummaries($date);

            Log::info('Cập nhật bảng tổng hợp thành công', [
                'date' => $date,
                'results' => $results
            ]);

        } catch (\Exception $e) {
            Log::error('Lỗi cập nhật bảng tổng hợp: ' . $e->getMessage(), [
                'date' => $date,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }

        return $results;
    }

    /**
     * Cập nhật summary cho Business
     */
    private function updateBusinessSummaries(string $date): int
    {
        $businesses = DB::table('facebook_businesses')->get();
        $updated = 0;

        foreach ($businesses as $business) {
            $summary = $this->calculateBusinessSummary($business->id, $date);
            if ($summary) {
                $this->updateOrCreateSummary('business', $business->id, $date, $summary);
                $updated++;
            }
        }

        return $updated;
    }

    /**
     * Cập nhật summary cho Account
     */
    private function updateAccountSummaries(string $date): int
    {
        $accounts = DB::table('facebook_ad_accounts')->get();
        $updated = 0;

        foreach ($accounts as $account) {
            $summary = $this->calculateAccountSummary($account->id, $date);
            if ($summary) {
                $this->updateOrCreateSummary('account', $account->id, $date, $summary);
                $updated++;
            }
        }

        return $updated;
    }

    /**
     * Cập nhật summary cho Campaign
     */
    private function updateCampaignSummaries(string $date): int
    {
        $campaigns = DB::table('facebook_campaigns')->get();
        $updated = 0;

        foreach ($campaigns as $campaign) {
            $summary = $this->calculateCampaignSummary($campaign->id, $date);
            if ($summary) {
                $this->updateOrCreateSummary('campaign', $campaign->id, $date, $summary);
                $updated++;
            }
        }

        return $updated;
    }

    /**
     * Cập nhật summary cho Ad Set
     */
    private function updateAdSetSummaries(string $date): int
    {
        $adSets = DB::table('facebook_ad_sets')->get();
        $updated = 0;

        foreach ($adSets as $adSet) {
            $summary = $this->calculateAdSetSummary($adSet->id, $date);
            if ($summary) {
                $this->updateOrCreateSummary('adset', $adSet->id, $date, $summary);
                $updated++;
            }
        }

        return $updated;
    }

    /**
     * Cập nhật summary cho Ad
     */
    private function updateAdSummaries(string $date): int
    {
        $ads = DB::table('facebook_ads')->get();
        $updated = 0;

        foreach ($ads as $ad) {
            $summary = $this->calculateAdSummary($ad->id, $date);
            if ($summary) {
                $this->updateOrCreateSummary('ad', $ad->id, $date, $summary);
                $updated++;
            }
        }

        return $updated;
    }

    /**
     * Cập nhật summary cho Post
     */
    private function updatePostSummaries(string $date): int
    {
        // Bảng facebook_posts đã loại bỏ
        $posts = collect();
        $updated = 0;

        foreach ($posts as $post) {
            $summary = $this->calculatePostSummary($post->id, $date);
            if ($summary) {
                $this->updateOrCreateSummary('post', $post->id, $date, $summary);
                $updated++;
            }
        }

        return $updated;
    }

    /**
     * Tính toán summary cho Business
     */
    private function calculateBusinessSummary(string $businessId, string $date): ?array
    {
        $summary = DB::table('facebook_ad_insights as ai')
            ->join('facebook_ads as a', 'ai.ad_id', '=', 'a.id')
            ->join('facebook_ad_sets as ads', 'a.adset_id', '=', 'ads.id')
            ->join('facebook_campaigns as c', 'ads.campaign_id', '=', 'c.id')
            ->join('facebook_ad_accounts as aa', 'c.ad_account_id', '=', 'aa.id')
            ->where('aa.business_id', $businessId)
            ->where('ai.date', $date)
            ->selectRaw('
                SUM(ai.spend) as total_spend,
                SUM(ai.reach) as total_reach,
                SUM(ai.impressions) as total_impressions,
                SUM(ai.clicks) as total_clicks,
                AVG(ai.ctr) as avg_ctr,
                AVG(ai.cpc) as avg_cpc,
                AVG(ai.cpm) as avg_cpm,
                AVG(ai.frequency) as avg_frequency,
                SUM(ai.conversions) as total_conversions,
                SUM(ai.conversion_values) as total_conversion_values,
                AVG(ai.cost_per_conversion) as avg_cost_per_conversion
            ')
            ->first();

        if (!$summary || $summary->total_spend == 0) {
            return null;
        }

        return [
            'total_spend' => $summary->total_spend,
            'total_reach' => $summary->total_reach,
            'total_impressions' => $summary->total_impressions,
            'total_clicks' => $summary->total_clicks,
            'avg_ctr' => $summary->avg_ctr,
            'avg_cpc' => $summary->avg_cpc,
            'avg_cpm' => $summary->avg_cpm,
            'avg_frequency' => $summary->avg_frequency,
            'total_conversions' => $summary->total_conversions,
            'total_conversion_values' => $summary->total_conversion_values,
            'avg_cost_per_conversion' => $summary->avg_cost_per_conversion,
        ];
    }

    /**
     * Tính toán summary cho Account
     */
    private function calculateAccountSummary(string $accountId, string $date): ?array
    {
        $summary = DB::table('facebook_ad_insights as ai')
            ->join('facebook_ads as a', 'ai.ad_id', '=', 'a.id')
            ->join('facebook_ad_sets as ads', 'a.adset_id', '=', 'ads.id')
            ->join('facebook_campaigns as c', 'ads.campaign_id', '=', 'c.id')
            ->where('c.ad_account_id', $accountId)
            ->where('ai.date', $date)
            ->selectRaw('
                SUM(ai.spend) as total_spend,
                SUM(ai.reach) as total_reach,
                SUM(ai.impressions) as total_impressions,
                SUM(ai.clicks) as total_clicks,
                AVG(ai.ctr) as avg_ctr,
                AVG(ai.cpc) as avg_cpc,
                AVG(ai.cpm) as avg_cpm,
                AVG(ai.frequency) as avg_frequency,
                SUM(ai.conversions) as total_conversions,
                SUM(ai.conversion_values) as total_conversion_values,
                AVG(ai.cost_per_conversion) as avg_cost_per_conversion
            ')
            ->first();

        if (!$summary || $summary->total_spend == 0) {
            return null;
        }

        return [
            'total_spend' => $summary->total_spend,
            'total_reach' => $summary->total_reach,
            'total_impressions' => $summary->total_impressions,
            'total_clicks' => $summary->total_clicks,
            'avg_ctr' => $summary->avg_ctr,
            'avg_cpc' => $summary->avg_cpc,
            'avg_cpm' => $summary->avg_cpm,
            'avg_frequency' => $summary->avg_frequency,
            'total_conversions' => $summary->total_conversions,
            'total_conversion_values' => $summary->total_conversion_values,
            'avg_cost_per_conversion' => $summary->avg_cost_per_conversion,
        ];
    }

    /**
     * Tính toán summary cho Campaign
     */
    private function calculateCampaignSummary(string $campaignId, string $date): ?array
    {
        $summary = DB::table('facebook_ad_insights as ai')
            ->join('facebook_ads as a', 'ai.ad_id', '=', 'a.id')
            ->join('facebook_ad_sets as ads', 'a.adset_id', '=', 'ads.id')
            ->where('ads.campaign_id', $campaignId)
            ->where('ai.date', $date)
            ->selectRaw('
                SUM(ai.spend) as total_spend,
                SUM(ai.reach) as total_reach,
                SUM(ai.impressions) as total_impressions,
                SUM(ai.clicks) as total_clicks,
                AVG(ai.ctr) as avg_ctr,
                AVG(ai.cpc) as avg_cpc,
                AVG(ai.cpm) as avg_cpm,
                AVG(ai.frequency) as avg_frequency,
                SUM(ai.conversions) as total_conversions,
                SUM(ai.conversion_values) as total_conversion_values,
                AVG(ai.cost_per_conversion) as avg_cost_per_conversion
            ')
            ->first();

        if (!$summary || $summary->total_spend == 0) {
            return null;
        }

        return [
            'total_spend' => $summary->total_spend,
            'total_reach' => $summary->total_reach,
            'total_impressions' => $summary->total_impressions,
            'total_clicks' => $summary->total_clicks,
            'avg_ctr' => $summary->avg_ctr,
            'avg_cpc' => $summary->avg_cpc,
            'avg_cpm' => $summary->avg_cpm,
            'avg_frequency' => $summary->avg_frequency,
            'total_conversions' => $summary->total_conversions,
            'total_conversion_values' => $summary->total_conversion_values,
            'avg_cost_per_conversion' => $summary->avg_cost_per_conversion,
        ];
    }

    /**
     * Tính toán summary cho Ad Set
     */
    private function calculateAdSetSummary(string $adSetId, string $date): ?array
    {
        $summary = DB::table('facebook_ad_insights as ai')
            ->join('facebook_ads as a', 'ai.ad_id', '=', 'a.id')
            ->where('a.adset_id', $adSetId)
            ->where('ai.date', $date)
            ->selectRaw('
                SUM(ai.spend) as total_spend,
                SUM(ai.reach) as total_reach,
                SUM(ai.impressions) as total_impressions,
                SUM(ai.clicks) as total_clicks,
                AVG(ai.ctr) as avg_ctr,
                AVG(ai.cpc) as avg_cpc,
                AVG(ai.cpm) as avg_cpm,
                AVG(ai.frequency) as avg_frequency,
                SUM(ai.conversions) as total_conversions,
                SUM(ai.conversion_values) as total_conversion_values,
                AVG(ai.cost_per_conversion) as avg_cost_per_conversion
            ')
            ->first();

        if (!$summary || $summary->total_spend == 0) {
            return null;
        }

        return [
            'total_spend' => $summary->total_spend,
            'total_reach' => $summary->total_reach,
            'total_impressions' => $summary->total_impressions,
            'total_clicks' => $summary->total_clicks,
            'avg_ctr' => $summary->avg_ctr,
            'avg_cpc' => $summary->avg_cpc,
            'avg_cpm' => $summary->avg_cpm,
            'avg_frequency' => $summary->avg_frequency,
            'total_conversions' => $summary->total_conversions,
            'total_conversion_values' => $summary->total_conversion_values,
            'avg_cost_per_conversion' => $summary->avg_cost_per_conversion,
        ];
    }

    /**
     * Tính toán summary cho Ad
     */
    private function calculateAdSummary(string $adId, string $date): ?array
    {
        $summary = DB::table('facebook_ad_insights as ai')
            ->where('ai.ad_id', $adId)
            ->where('ai.date', $date)
            ->selectRaw('
                SUM(ai.spend) as total_spend,
                SUM(ai.reach) as total_reach,
                SUM(ai.impressions) as total_impressions,
                SUM(ai.clicks) as total_clicks,
                AVG(ai.ctr) as avg_ctr,
                AVG(ai.cpc) as avg_cpc,
                AVG(ai.cpm) as avg_cpm,
                AVG(ai.frequency) as avg_frequency,
                SUM(ai.conversions) as total_conversions,
                SUM(ai.conversion_values) as total_conversion_values,
                AVG(ai.cost_per_conversion) as avg_cost_per_conversion
            ')
            ->first();

        if (!$summary || $summary->total_spend == 0) {
            return null;
        }

        return [
            'total_spend' => $summary->total_spend,
            'total_reach' => $summary->total_reach,
            'total_impressions' => $summary->total_impressions,
            'total_clicks' => $summary->total_clicks,
            'avg_ctr' => $summary->avg_ctr,
            'avg_cpc' => $summary->avg_cpc,
            'avg_cpm' => $summary->avg_cpm,
            'avg_frequency' => $summary->avg_frequency,
            'total_conversions' => $summary->total_conversions,
            'total_conversion_values' => $summary->total_conversion_values,
            'avg_cost_per_conversion' => $summary->avg_cost_per_conversion,
        ];
    }

    /**
     * Tính toán summary cho Post
     */
    private function calculatePostSummary(string $postId, string $date): ?array
    {
        $summary = DB::table('facebook_post_insights as pi')
            ->where('pi.post_id', $postId)
            ->where('pi.date', $date)
            ->selectRaw('
                SUM(pi.spend) as total_spend,
                SUM(pi.reach) as total_reach,
                SUM(pi.impressions) as total_impressions,
                SUM(pi.clicks) as total_clicks,
                AVG(pi.ctr) as avg_ctr,
                AVG(pi.cpc) as avg_cpc,
                AVG(pi.cpm) as avg_cpm,
                AVG(pi.frequency) as avg_frequency,
                SUM(pi.likes) as total_likes,
                SUM(pi.shares) as total_shares,
                SUM(pi.comments) as total_comments,
                SUM(pi.reactions) as total_reactions
            ')
            ->first();

        if (!$summary || $summary->total_spend == 0) {
            return null;
        }

        return [
            'total_spend' => $summary->total_spend,
            'total_reach' => $summary->total_reach,
            'total_impressions' => $summary->total_impressions,
            'total_clicks' => $summary->total_clicks,
            'avg_ctr' => $summary->avg_ctr,
            'avg_cpc' => $summary->avg_cpc,
            'avg_cpm' => $summary->avg_cpm,
            'avg_frequency' => $summary->avg_frequency,
            'total_likes' => $summary->total_likes,
            'total_shares' => $summary->total_shares,
            'total_comments' => $summary->total_comments,
            'total_reactions' => $summary->total_reactions,
        ];
    }

    /**
     * Cập nhật hoặc tạo mới summary record
     */
    private function updateOrCreateSummary(string $entityType, string $entityId, string $date, array $data): void
    {
        $summaryData = [
            'date' => $date,
            'total_spend' => $data['total_spend'] ?? 0,
            'total_impressions' => $data['total_impressions'] ?? 0,
            'total_clicks' => $data['total_clicks'] ?? 0,
            'total_reach' => $data['total_reach'] ?? 0,
            'total_conversions' => $data['total_conversions'] ?? 0,
            'total_conversion_values' => $data['total_conversion_values'] ?? 0,
            'avg_ctr' => $data['avg_ctr'] ?? 0,
            'avg_cpc' => $data['avg_cpc'] ?? 0,
            'avg_cpm' => $data['avg_cpm'] ?? 0,
            'avg_frequency' => $data['avg_frequency'] ?? 0,
            'engagement_rate' => $data['total_likes'] ?? 0,
            'roas' => $data['total_conversion_values'] > 0 ? ($data['total_conversion_values'] / $data['total_spend']) : 0,
        ];

        // Set entity type specific fields
        switch ($entityType) {
            case 'business':
                $summaryData['business_id'] = $entityId;
                break;
            case 'account':
                $summaryData['account_id'] = $entityId;
                break;
            case 'campaign':
                $summaryData['campaign_id'] = $entityId;
                break;
            case 'adset':
                $summaryData['adset_id'] = $entityId;
                break;
            case 'ad':
                $summaryData['ad_id'] = $entityId;
                break;
            case 'post':
                $summaryData['post_id'] = $entityId;
                break;
        }

        FacebookReportSummary::updateOrCreate(
            [
                'date' => $date,
                $entityType . '_id' => $entityId
            ],
            $summaryData
        );
    }
}
