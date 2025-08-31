<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SyncFacebookAds;
use App\Services\FacebookAdsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class SyncController extends Controller
{
    public function start(Request $request): JsonResponse
    {
        $api = new FacebookAdsService();
        if (!$api->isConfigured()) {
            return response()->json([
                'error' => 'Vui lòng cấu hình FACEBOOK_ADS_TOKEN trong .env',
            ], 400);
        }

        $syncId = (string) Str::uuid();
        $cacheKey = SyncFacebookAds::cacheKey($syncId);

        Cache::put($cacheKey, [
            'id' => $syncId,
            'stage' => 'queued',
            'percent' => 0,
            'counts' => [
                'businesses' => 0,
                'accounts' => 0,
                'campaigns' => 0,
                'adsets' => 0,
                'ads' => 0,
                'insights' => 0,
            ],
            'errors' => [],
            'done' => false,
            'result' => null,
            'startedAt' => now()->toISOString(),
            'updatedAt' => now()->toISOString(),
        ], now()->addHours(2));

        SyncFacebookAds::dispatch($syncId);

        return response()->json([
            'id' => $syncId,
            'status' => 'queued',
        ]);
    }

    public function status(string $id): JsonResponse
    {
        $cacheKey = SyncFacebookAds::cacheKey($id);
        $state = Cache::get($cacheKey);
        if (!$state) {
            return response()->json([
                'error' => 'Không tìm thấy tiến trình',
            ], 404);
        }

        return response()->json($state);
    }
}

