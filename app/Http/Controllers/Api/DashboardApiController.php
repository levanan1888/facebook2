<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\UnifiedDataService;
use App\Services\DashboardReportService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DashboardApiController extends Controller
{
    public function __construct(
        private UnifiedDataService $unifiedDataService,
        private DashboardReportService $dashboardReportService
    ) {}

    /**
     * Lấy dữ liệu thống nhất
     */
    public function getUnifiedData(Request $request): JsonResponse
    {
        $filters = $request->only([
            'date_range',
            'data_source',
            'metric',
            'date_from',
            'date_to'
        ]);

        try {
            $data = $this->unifiedDataService->getUnifiedData($filters);
            
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy dữ liệu thống nhất: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy dữ liệu so sánh
     */
    public function getComparisonData(Request $request): JsonResponse
    {
        $filters = $request->only([
            'date_range',
            'source1',
            'source2',
            'metric'
        ]);

        try {
            $data = $this->unifiedDataService->getUnifiedData($filters);
            
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy dữ liệu so sánh: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy dữ liệu theo bộ lọc
     */
    public function getFilteredData(Request $request): JsonResponse
    {
        $filters = $request->only([
            'date_from',
            'date_to',
            'min_spend',
            'max_spend',
            'data_source',
            'metric'
        ]);

        try {
            $data = $this->unifiedDataService->getFilteredData($filters);
            
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy dữ liệu đã lọc: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy trạng thái các nguồn dữ liệu
     */
    public function getDataSourcesStatus(): JsonResponse
    {
        try {
            $data = $this->unifiedDataService->getUnifiedData();
            
            return response()->json([
                'success' => true,
                'data' => $data['sources'] ?? []
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy trạng thái nguồn dữ liệu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Làm mới cache dữ liệu
     */
    public function refreshCache(): JsonResponse
    {
        try {
            $this->unifiedDataService->clearCache();
            
            return response()->json([
                'success' => true,
                'message' => 'Cache đã được làm mới thành công'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi làm mới cache: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy dữ liệu tổng quan
     */
    public function getOverviewData(Request $request): JsonResponse
    {
        try {
            $data = $this->dashboardReportService->generateOverviewReport();
            
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy dữ liệu tổng quan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy dữ liệu analytics
     */
    public function getAnalyticsData(Request $request): JsonResponse
    {
        try {
            $data = $this->dashboardReportService->generateAnalyticsReport();
            
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy dữ liệu analytics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy dữ liệu hierarchy
     */
    public function getHierarchyData(Request $request): JsonResponse
    {
        try {
            $data = $this->dashboardReportService->generateHierarchyReport();
            
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy dữ liệu hierarchy: ' . $e->getMessage()
            ], 500);
        }
    }
}
