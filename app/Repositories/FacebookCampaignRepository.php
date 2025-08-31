<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\FacebookCampaign;
use Illuminate\Database\Eloquent\Collection;

class FacebookCampaignRepository
{
    public function __construct(private FacebookCampaign $model)
    {
    }

    /**
     * Lấy campaigns theo account
     */
    public function getByAccountId(string $accountId): Collection
    {
        return $this->model->where('ad_account_id', $accountId)->get();
    }

    /**
     * Lấy campaigns với thống kê
     */
    public function getWithStats(array $filters = []): Collection
    {
        $query = $this->model->query();

        if (!empty($filters['account_id'])) {
            $query->where('ad_account_id', $filters['account_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->get();
    }

    /**
     * Tạo hoặc cập nhật campaign
     */
    public function createOrUpdate(array $data): FacebookCampaign
    {
        return $this->model->updateOrCreate(
            ['id' => $data['id']],
            $data
        );
    }

    /**
     * Lấy campaigns theo business
     */
    public function getByBusinessId(string $businessId): Collection
    {
        return $this->model->whereHas('adAccount', function ($query) use ($businessId) {
            $query->where('business_id', $businessId);
        })->get();
    }
}
