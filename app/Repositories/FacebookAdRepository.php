<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\FacebookAd;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class FacebookAdRepository
{
    public function __construct(private FacebookAd $model)
    {
    }

    /**
     * Lấy tổng quan dữ liệu ads
     */
    public function getOverviewData(array $filters = []): array
    {
        $query = $this->model->query();

        if (!empty($filters['account_id'])) {
            $query->where('account_id', $filters['account_id']);
        }

        if (!empty($filters['campaign_id'])) {
            $query->where('campaign_id', $filters['campaign_id']);
        }

        return [
            'total_ads' => $query->count(),
            'ads_with_posts' => $query->whereNotNull('post_id')->count(),
            'ads_with_insights' => $query->whereNotNull('last_insights_sync')->count(),
            'total_spend' => $query->sum('ad_spend'),
            'total_impressions' => $query->sum('ad_impressions'),
            'total_clicks' => $query->sum('ad_clicks'),
        ];
    }

    /**
     * Lấy dữ liệu theo ngày
     */
    public function getDataByDateRange(string $from, string $to, array $filters = []): Collection
    {
        $query = $this->model->query()
            ->whereBetween('last_insights_sync', [$from, $to]);

        if (!empty($filters['account_id'])) {
            $query->where('account_id', $filters['account_id']);
        }

        if (!empty($filters['campaign_id'])) {
            $query->where('campaign_id', $filters['campaign_id']);
        }

        return $query->get();
    }

    /**
     * Lấy ads theo account
     */
    public function getByAccountId(string $accountId): Collection
    {
        return $this->model->where('account_id', $accountId)->get();
    }

    /**
     * Lấy ads theo campaign
     */
    public function getByCampaignId(string $campaignId): Collection
    {
        return $this->model->where('campaign_id', $campaignId)->get();
    }

    /**
     * Lấy ads có post
     */
    public function getAdsWithPosts(array $filters = []): Collection
    {
        $query = $this->model->withPost();

        if (!empty($filters['page_id'])) {
            $query->byPage($filters['page_id']);
        }

        if (!empty($filters['post_type'])) {
            $query->byPostType($filters['post_type']);
        }

        return $query->get();
    }

    /**
     * Lấy ads có insights
     */
    public function getAdsWithInsights(array $filters = []): Collection
    {
        $query = $this->model->withInsights();

        if (!empty($filters['account_id'])) {
            $query->where('account_id', $filters['account_id']);
        }

        return $query->get();
    }

    /**
     * Tạo hoặc cập nhật ad
     */
    public function createOrUpdate(array $data): FacebookAd
    {
        return $this->model->updateOrCreate(
            ['id' => $data['id']],
            $data
        );
    }

    /**
     * Xóa ads cũ
     */
    public function deleteOldAds(Carbon $before): int
    {
        return $this->model->where('updated_time', '<', $before)->delete();
    }
}
