<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\FacebookAdsService;
use App\Services\FacebookAdsSyncService;
use App\Jobs\SyncFacebookAds;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class FacebookSyncController extends Controller
{
    public function __construct()
    {
    }

    /**
     * Đồng bộ Facebook Ads trực tiếp (không cần job)
     */
    public function syncAdsDirect(Request $request): JsonResponse
    {
        try {
            Log::info('Bắt đầu đồng bộ Facebook Ads trực tiếp');
            
            // Kiểm tra xem có đang chạy sync nào không
            $currentStatus = Cache::get('facebook_sync_status');
            if ($currentStatus && $currentStatus['status'] === 'running') {
                return response()->json([
                    'success' => false,
                    'message' => 'Đang có quá trình đồng bộ chạy. Vui lòng chờ hoàn thành.',
                    'status' => $currentStatus
                ]);
            }
            
            // Reset cache khi bắt đầu đồng bộ mới
            Cache::forget('facebook_sync_stop_requested');
            Cache::forget('facebook_sync_progress');
            
            // Cập nhật trạng thái bắt đầu
            Cache::put('facebook_sync_status', [
                'status' => 'running',
                'started_at' => now()->toISOString(),
                'progress' => 0,
                'message' => 'Đang đồng bộ dữ liệu...'
            ], 3600);
            
            // Tạo progress callback
            $progressCallback = function(array $progress) {
                Cache::put('facebook_sync_progress', $progress, 3600);
                Cache::put('facebook_sync_status', [
                    'status' => 'running',
                    'started_at' => Cache::get('facebook_sync_status')['started_at'],
                    'progress' => $progress['counts']['ads'] ?? 0,
                    'message' => $progress['message'] ?? 'Đang đồng bộ...'
                ], 3600);
            };
            
            // Chạy sync trực tiếp
            $syncService = new FacebookAdsSyncService(new FacebookAdsService());
            $result = $syncService->syncFacebookData($progressCallback);
            
            // Cập nhật trạng thái hoàn thành
            Cache::put('facebook_sync_status', [
                'status' => 'completed',
                'started_at' => Cache::get('facebook_sync_status')['started_at'],
                'completed_at' => now()->toISOString(),
                'progress' => $result['ads'],
                'message' => 'Hoàn thành đồng bộ dữ liệu',
                'result' => $result
            ], 3600);
            
            return response()->json([
                'success' => true,
                'message' => 'Đồng bộ Facebook Ads hoàn thành',
                'result' => $result,
                'status' => 'completed'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Lỗi khi đồng bộ Facebook Ads: ' . $e->getMessage());
            
            // Cập nhật trạng thái lỗi
            Cache::put('facebook_sync_status', [
                'status' => 'error',
                'started_at' => Cache::get('facebook_sync_status')['started_at'] ?? null,
                'error_at' => now()->toISOString(),
                'error' => $e->getMessage(),
                'message' => 'Lỗi khi đồng bộ dữ liệu'
            ], 3600);
            
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Đồng bộ Facebook Ads sử dụng queue job
     */
    public function syncAds(Request $request): JsonResponse
    {
        try {
            Log::info('Bắt đầu đồng bộ Facebook Ads với queue job');
            
            // Kiểm tra xem có đang chạy sync nào không
            $currentStatus = Cache::get('facebook_sync_status');
            if ($currentStatus && $currentStatus['status'] === 'running') {
                return response()->json([
                    'success' => false,
                    'message' => 'Đang có quá trình đồng bộ chạy. Vui lòng chờ hoàn thành.',
                    'status' => $currentStatus
                ]);
            }
            
            // Reset cache khi bắt đầu đồng bộ mới
            Cache::forget('facebook_sync_stop_requested');
            Cache::forget('facebook_sync_progress');
            
            // Clear sạch cache cũ để tránh nhầm lẫn
            Cache::forget('facebook_sync_status');
            Cache::forget('facebook_sync_current_stage');
            Cache::forget('facebook_sync_start_time');
            Cache::forget('facebook_sync_end_time');
            
            // Clear các cache sync theo sync_id cũ
            $oldKeys = Cache::get('facebook_sync_keys', []);
            foreach ($oldKeys as $key) {
                Cache::forget($key);
            }
            Cache::forget('facebook_sync_keys');
            
            // Dispatch job vào queue để xử lý trong background
            $syncId = (string) Str::uuid();
            
            SyncFacebookAds::dispatch($syncId);
            
            // Cập nhật trạng thái bắt đầu
            Cache::put('facebook_sync_status', [
                'status' => 'queued',
                'queued_at' => now()->toISOString(),
                'progress' => 0,
                'message' => 'Đã đưa vào queue, đang chờ xử lý...'
            ], 3600);
            
            return response()->json([
                'success' => true,
                'message' => 'Đã bắt đầu đồng bộ Facebook Ads trong background',
                'sync_id' => $syncId,
                'status' => 'queued'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Lỗi khi bắt đầu đồng bộ Facebook Ads: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy trạng thái đồng bộ hiện tại
     */
    public function getSyncStatus(): JsonResponse
    {
        try {
            $status = Cache::get('facebook_sync_status');
            $progress = Cache::get('facebook_sync_progress');
            
            // Debug: Log để xem cache status
            Log::info('getSyncStatus debug', [
                'stage' => 'getSyncStatus_debug',
                'cache_status' => $status,
                'cache_progress' => $progress,
                'has_status' => !empty($status),
                'has_progress' => !empty($progress),
                'timestamp' => now()->toISOString()
            ]);
            
            // Debug: Thêm thông tin chi tiết về lỗi
            $errorDetails = [];
            if ($status && $status['status'] === 'failed') {
                $errorDetails = [
                    'error_type' => 'sync_failed',
                    'error_message' => $status['message'] ?? 'Không có thông tin lỗi',
                    'error_time' => $status['failed_at'] ?? 'Không có thời gian lỗi',
                    'last_successful_stage' => $status['last_successful_stage'] ?? 'Không có',
                    'failed_stage' => $status['failed_stage'] ?? 'Không có',
                    'error_details' => $status['error_details'] ?? 'Không có chi tiết lỗi'
                ];
            }
            
            if (!$status) {
                return response()->json([
                    'success' => true,
                    'status' => 'idle',
                    'message' => 'Không có quá trình đồng bộ nào đang chạy',
                    'progress' => null,
                    'counts' => [],
                    'debug_info' => [
                        'cache_cleared' => true,
                        'no_status_found' => true
                    ]
                ]);
            }
            
            return response()->json([
                'success' => true,
                'status' => $status,
                'progress' => $progress,
                'last_updated' => now()->toISOString(),
                'debug_info' => [
                    'cache_status_exists' => true,
                    'status_type' => $status['status'] ?? 'unknown',
                    'error_details' => $errorDetails
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy trạng thái sync: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage(),
                'debug_info' => [
                    'exception' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]
            ], 500);
        }
    }

    /**
     * Lấy progress chi tiết của quá trình đồng bộ
     */
    public function getSyncProgress(): JsonResponse
    {
        try {
            $progress = Cache::get('facebook_sync_progress');
            $status = Cache::get('facebook_sync_status');
            
            if (!$progress) {
                return response()->json([
                    'success' => true,
                    'progress' => [
                        'stage' => 'idle',
                        'percent' => 0,
                        'message' => 'Không có quá trình đồng bộ nào đang chạy',
                        'current_step' => 0,
                        'total_steps' => 0
                    ],
                    'counts' => [],
                    'errors' => []
                ]);
            }
            
            return response()->json([
                'success' => true,
                'progress' => $progress,
                'status' => $status,
                'last_updated' => now()->toISOString()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy progress sync: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Dừng quá trình đồng bộ đang chạy
     */
    public function stopSync(): JsonResponse
    {
        try {
            $status = Cache::get('facebook_sync_status');
            
            if (!$status || $status['status'] !== 'running') {
                return response()->json([
                    'success' => false,
                    'message' => 'Không có quá trình đồng bộ nào đang chạy'
                ]);
            }
            
            // Signal job để dừng xử lý
            Cache::put('facebook_sync_stop_requested', true, 60);
            
            // Clear sạch tất cả cache sync để reset hoàn toàn
            Cache::forget('facebook_sync_status');
            Cache::forget('facebook_sync_progress');
            
            // Xóa tất cả cache sync theo sync_id
            $keys = Cache::get('facebook_sync_keys', []);
            foreach ($keys as $key) {
                Cache::forget($key);
            }
            Cache::forget('facebook_sync_keys');
            
            Log::info('Người dùng đã dừng quá trình đồng bộ Facebook Ads - Cache đã được clear sạch');
            
            return response()->json([
                'success' => true,
                'message' => 'Đã dừng quá trình đồng bộ và clear sạch cache',
                'status' => 'stopped'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Lỗi khi dừng sync: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Xóa cache và reset trạng thái đồng bộ
     */
    public function resetSync(): JsonResponse
    {
        try {
            // Clear sạch tất cả cache liên quan đến sync
            Cache::forget('facebook_sync_status');
            Cache::forget('facebook_sync_progress');
            Cache::forget('facebook_sync_stop_requested');
            
            // Clear các cache khác có thể liên quan
            Cache::forget('facebook_sync_current_stage');
            Cache::forget('facebook_sync_start_time');
            Cache::forget('facebook_sync_end_time');
            
            // Xóa tất cả cache sync theo sync_id
            $keys = Cache::get('facebook_sync_keys', []);
            foreach ($keys as $key) {
                Cache::forget($key);
            }
            Cache::forget('facebook_sync_keys');
            
            // Clear các cache có pattern facebook_sync_*
            $this->clearFacebookSyncCache();
            
            Log::info('Đã reset hoàn toàn trạng thái đồng bộ Facebook Ads - Tất cả cache đã được clear sạch');
            
            return response()->json([
                'success' => true,
                'message' => 'Đã reset hoàn toàn trạng thái đồng bộ và clear sạch tất cả cache'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Lỗi khi reset sync: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Clear tất cả cache có pattern facebook_sync_*
     */
    private function clearFacebookSyncCache(): void
    {
        try {
            // Lấy tất cả cache keys
            $keys = Cache::get('facebook_sync_keys', []);
            
            // Thêm các key cơ bản
            $basicKeys = [
                'facebook_sync_status',
                'facebook_sync_progress', 
                'facebook_sync_stop_requested',
                'facebook_sync_current_stage',
                'facebook_sync_start_time',
                'facebook_sync_end_time'
            ];
            
            $allKeys = array_merge($keys, $basicKeys);
            
            // Clear từng key
            foreach ($allKeys as $key) {
                Cache::forget($key);
            }
            
            // Clear facebook_sync_keys cuối cùng
            Cache::forget('facebook_sync_keys');
            
        } catch (\Exception $e) {
            Log::warning('Không thể clear một số cache: ' . $e->getMessage());
        }
    }
    
    /**
     * Clear cache từ bên ngoài (có thể gọi từ Artisan command)
     */
    public static function clearAllSyncCache(): void
    {
        try {
            // Clear tất cả cache liên quan đến sync
            Cache::forget('facebook_sync_status');
            Cache::forget('facebook_sync_progress');
            Cache::forget('facebook_sync_stop_requested');
            Cache::forget('facebook_sync_current_stage');
            Cache::forget('facebook_sync_start_time');
            Cache::forget('facebook_sync_end_time');
            
            // Clear các cache sync theo sync_id
            $keys = Cache::get('facebook_sync_keys', []);
            foreach ($keys as $key) {
                Cache::forget($key);
            }
            Cache::forget('facebook_sync_keys');
            
            Log::info('Đã clear sạch tất cả cache sync Facebook từ bên ngoài');
            
        } catch (\Exception $e) {
            Log::error('Lỗi khi clear cache từ bên ngoài: ' . $e->getMessage());
        }
    }
}


