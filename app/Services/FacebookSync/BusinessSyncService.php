<?php

declare(strict_types=1);

namespace App\Services\FacebookSync;

use App\Models\FacebookBusiness;
use App\Services\FacebookAdsService;
use Illuminate\Support\Facades\Log;

class BusinessSyncService
{
    public function __construct(private FacebookAdsService $api)
    {
    }

    /**
     * Đồng bộ Business Managers
     */
    public function syncBusinesses(callable $onProgress = null): array
    {
        try {
            if ($onProgress) {
                $onProgress('Đang lấy Business Managers...');
            }
            
            $businesses = $this->api->getBusinessManagers();
            
            if (isset($businesses['error'])) {
                throw new \Exception('Lỗi API: ' . $businesses['error']);
            }

            $syncedCount = 0;
            foreach ($businesses['data'] ?? [] as $businessData) {
                $business = FacebookBusiness::updateOrCreate(
                    ['id' => $businessData['id']],
                    [
                        'name' => $businessData['name'] ?? '',
                        'verification_status' => $businessData['verification_status'] ?? 'UNKNOWN',
                    ]
                );
                $syncedCount++;
            }

            if ($onProgress) {
                $onProgress("Đã đồng bộ {$syncedCount} Business Managers");
            }
            
            return [
                'count' => $syncedCount,
                'businesses' => FacebookBusiness::all()
            ];
            
        } catch (\Exception $e) {
            Log::error('Lỗi đồng bộ Business Managers: ' . $e->getMessage());
            throw $e;
        }
    }
}
