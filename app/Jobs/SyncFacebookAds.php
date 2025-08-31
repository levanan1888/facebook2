<?php
declare(strict_types=1);

namespace App\Jobs;

use App\Services\FacebookAdsService;
use App\Services\FacebookAdsSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SyncFacebookAds implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $syncId;

    public int $timeout = 1200; // 20 minutes

    public function __construct(string $syncId)
    {
        // Debug: dd() để xem job constructor
        // dd([
        //     'stage' => 'job_constructor',
        //     'sync_id' => $syncId,
        //     'job_class' => get_class($this),
        //     'timestamp' => now()->toISOString()
        // ]);
        
        $this->syncId = $syncId;
    }

    public function handle(): void
    {
        $cacheKey = self::cacheKey($this->syncId);

        $api = new FacebookAdsService();
        $syncService = new FacebookAdsSyncService($api);

        $stagePercent = [
            'getBusinessManagers' => 5,
            'upsertBusiness' => 10,
            'getClientAdAccounts' => 15,
            'upsertAdAccount' => 25,
            'getCampaigns' => 35,
            'upsertCampaign' => 45,
            'getAdSetsByCampaign' => 55,
            'upsertAdSet' => 65,
            'getAdsByAdSet' => 75,
            'upsertAd' => 85,
            'upsertAdInsights' => 90,
            'upsertAdSetInsights' => 95,
            'upsertAccountInsights' => 96,
            'completed' => 100,
        ];

        $stageMessage = [
            'getBusinessManagers' => 'Đang lấy Business Managers...',
            'upsertBusiness' => 'Đang lưu Business Managers...',
            'getClientAdAccounts' => 'Đang lấy tài khoản quảng cáo...',
            'upsertAdAccount' => 'Đang lưu tài khoản quảng cáo...',
            'getCampaigns' => 'Đang lấy chiến dịch...',
            'upsertCampaign' => 'Đang lưu chiến dịch...',
            'getAdSetsByCampaign' => 'Đang lấy bộ quảng cáo...',
            'upsertAdSet' => 'Đang lưu bộ quảng cáo...',
            'getAdsByAdSet' => 'Đang lấy quảng cáo...',
            'upsertAd' => 'Đang lưu quảng cáo...',
            'upsertAdInsights' => 'Đang lưu thống kê quảng cáo...',
            'upsertAdSetInsights' => 'Đang lưu thống kê bộ quảng cáo...',
            'upsertAccountInsights' => 'Đang lưu thống kê tài khoản...',
            'completed' => 'Hoàn thành đồng bộ!'
        ];

        $onProgress = function (array $state) use ($cacheKey, $stagePercent, $stageMessage): void {
            // Kiểm tra flag dừng
            if (Cache::get('facebook_sync_stop_requested', false)) {
                throw new \Exception('Đồng bộ đã bị dừng bởi người dùng');
            }
            
            $current = Cache::get($cacheKey, []);
            $stage = $state['stage'] ?? 'running';
            $percent = (int) ($stagePercent[$stage] ?? ($current['percent'] ?? 0));
            $payload = [
                'id' => $current['id'] ?? null,
                'startedAt' => $current['startedAt'] ?? now()->toISOString(),
                'stage' => $stage,
                'counts' => $state['counts'] ?? $current['counts'] ?? [
                    'businesses' => 0,
                    'accounts' => 0,
                    'campaigns' => 0,
                    'adsets' => 0,
                    'ads' => 0,
                    'insights' => 0,
                ],
                'errors' => $state['errors'] ?? $current['errors'] ?? [],
                'percent' => max((int) ($current['percent'] ?? 0), $percent),
                'done' => false,
                'result' => null,
                'updatedAt' => now()->toISOString(),
            ];
            Cache::put($cacheKey, $payload, now()->addHours(2));

            // Update UI-facing caches
            Cache::put('facebook_sync_progress', [
                'stage' => $payload['stage'],
                'percent' => $payload['percent'],
                'message' => $stageMessage[$payload['stage']] ?? $payload['stage'],
                'current_step' => 0,
                'total_steps' => 0,
                'counts' => $payload['counts'],
                'errors' => $payload['errors'],
            ], 300);

            Cache::put('facebook_sync_status', [
                'status' => 'running',
                'started_at' => $payload['startedAt'],
                'progress' => $payload['percent'],
                'message' => $stageMessage[$payload['stage']] ?? 'Đang xử lý...'
            ], 3600);
        };

        try {
            Cache::put('facebook_sync_status', [
                'status' => 'running',
                'started_at' => now()->toISOString(),
                'progress' => 0,
                'message' => 'Đang xử lý...'
            ], 3600);
            $result = $syncService->syncYesterday($onProgress);
            $final = Cache::get($cacheKey, []);
            $final['stage'] = 'completed';
            $final['percent'] = 100;
            $final['done'] = true;
            $final['result'] = $result;
            $final['updatedAt'] = now()->toISOString();
            Cache::put($cacheKey, $final, now()->addHours(2));

            // Cập nhật cache cho UI
            Cache::put('facebook_sync_progress', [
                'stage' => 'completed',
                'percent' => 100,
                'message' => 'Hoàn thành đồng bộ!',
                'current_step' => 0,
                'total_steps' => 0,
                'counts' => $final['counts'] ?? [],
                'errors' => $final['errors'] ?? [],
            ], 300);
            
            Cache::put('facebook_sync_status', [
                'status' => 'completed',
                'started_at' => $final['startedAt'] ?? now()->toISOString(),
                'completed_at' => now()->toISOString(),
                'progress' => 100,
                'message' => 'Hoàn thành đồng bộ!'
            ], 3600);
            
            // Clear stop request flag
            Cache::forget('facebook_sync_stop_requested');
            
            Log::info('SyncFacebookAds job completed successfully');
        } catch (\Throwable $e) {
            Log::error('SyncFacebookAds job failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'sync_id' => $this->syncId
            ]);
            
            $current = Cache::get($cacheKey, []);
            
            // Kiểm tra xem có phải bị dừng bởi người dùng không
            $isStoppedByUser = Cache::get('facebook_sync_stop_requested', false);
            $status = $isStoppedByUser ? 'stopped' : 'failed';
            $message = $isStoppedByUser ? 'Đã dừng bởi người dùng' : 'Đồng bộ thất bại';
            
            // Nếu bị dừng bởi người dùng, clear sạch cache
            if ($isStoppedByUser) {
                Cache::forget('facebook_sync_status');
                Cache::forget('facebook_sync_progress');
                Cache::forget('facebook_sync_stop_requested');
                Cache::forget($cacheKey);
                
                // Clear các cache khác
                $keys = Cache::get('facebook_sync_keys', []);
                foreach ($keys as $key) {
                    Cache::forget($key);
                }
                Cache::forget('facebook_sync_keys');
                
                Log::info('Job đã dừng bởi người dùng - Cache đã được clear sạch');
                return;
            }
            
            // Nếu lỗi thật sự, cập nhật status failed với thông tin chi tiết
            $current['stage'] = $status;
            $current['done'] = true;
            $current['percent'] = $current['percent'] ?? 0;
            $current['errors'] = array_values(array_merge($current['errors'] ?? [], [[
                'stage' => 'exception',
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]]));
            $current['updatedAt'] = now()->toISOString();
            Cache::put($cacheKey, $current, now()->addHours(2));

            // Cập nhật cache cho UI với thông tin lỗi chi tiết
            Cache::put('facebook_sync_progress', [
                'stage' => $status,
                'percent' => $current['percent'] ?? 0,
                'message' => $message,
                'current_step' => 0,
                'total_steps' => 0,
                'counts' => $current['counts'] ?? [],
                'errors' => $current['errors'] ?? [],
                'debug_info' => [
                    'error_type' => 'job_exception',
                    'error_message' => $e->getMessage(),
                    'error_file' => $e->getFile(),
                    'error_line' => $e->getLine(),
                    'sync_id' => $this->syncId
                ]
            ], 300);
            
            Cache::put('facebook_sync_status', [
                'status' => $status,
                'started_at' => $current['startedAt'] ?? now()->toISOString(),
                'failed_at' => now()->toISOString(),
                'progress' => $current['percent'] ?? 0,
                'message' => $message,
                'error_details' => [
                    'exception' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'sync_id' => $this->syncId
                ]
            ], 3600);
            
            Log::error('SyncFacebookAds job failed - Status updated with error details', [
                'status' => $status,
                'sync_id' => $this->syncId,
                'error' => $e->getMessage()
            ]);
        }
    }

    public static function cacheKey(string $syncId): string
    {
        return 'facebook_sync:' . $syncId;
    }
}


