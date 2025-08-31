<?php

namespace App\Services;

use App\Models\DashboardReport;
use App\Models\FacebookBusiness;
use App\Models\FacebookAdAccount;
use App\Models\FacebookCampaign;
use App\Models\FacebookAdSet;
use App\Models\FacebookAd;
use App\Models\FacebookPage;
use App\Models\FacebookPost;
use App\Models\FacebookInsight;
use Illuminate\Support\Facades\DB;

class DashboardReportService
{
    /**
     * Tạo báo cáo overview
     */
    public function generateOverviewReport(): array
    {
        $data = [
            'totals' => $this->getTotals(),
            'last7Days' => $this->getLast7DaysData(),
            'topCampaigns' => $this->getTopCampaigns(),
            'topPosts' => $this->getTopPosts(),
            'statusStats' => $this->getStatusStats(),
            'performanceStats' => $this->getPerformanceStats(),
        ];

        DashboardReport::updateReport('overview', $data);
        return $data;
    }

    /**
     * Tạo báo cáo analytics
     */
    public function generateAnalyticsReport(): array
    {
        $data = [
            'totalSpend' => FacebookInsight::sum('spend'),
            'totalImpressions' => FacebookInsight::sum('impressions'),
            'totalClicks' => FacebookInsight::sum('clicks'),
            'totalReach' => FacebookInsight::sum('reach'),
            'avgCTR' => FacebookInsight::avg('ctr'),
            'avgCPC' => FacebookInsight::avg('cpc'),
            'avgCPM' => FacebookInsight::avg('cpm'),
        ];

        DashboardReport::updateReport('analytics', $data);
        return $data;
    }

    /**
     * Tạo báo cáo hierarchy
     */
    public function generateHierarchyReport(): array
    {
        $data = [
            'businesses' => FacebookBusiness::withCount('adAccounts')->get(),
            'totalAccounts' => FacebookAdAccount::count(),
            'totalCampaigns' => FacebookCampaign::count(),
            'totalAdSets' => FacebookAdSet::count(),
            'totalAds' => FacebookAd::count(),
        ];

        DashboardReport::updateReport('hierarchy', $data);
        return $data;
    }

    /**
     * Lấy dữ liệu tổng quan
     */
    private function getTotals(): array
    {
        return [
            'businesses' => FacebookBusiness::count(),
            'accounts' => FacebookAdAccount::count(),
            'campaigns' => FacebookCampaign::count(),
            'adsets' => FacebookAdSet::count(),
            'ads' => FacebookAd::count(),
            'pages' => FacebookPage::count(),
            'posts' => FacebookPost::count(),
            'insights' => FacebookInsight::count(),
        ];
    }

    /**
     * Lấy dữ liệu 7 ngày gần nhất
     */
    private function getLast7DaysData(): array
    {
        return collect(range(6, 0))->map(function ($daysAgo) {
            $date = now()->subDays($daysAgo)->format('Y-m-d');
            return [
                'date' => $date,
                'ads' => FacebookAd::whereDate('created_time', $date)->count(),
                'posts' => FacebookPost::whereDate('created_time', $date)->count(),
                'campaigns' => FacebookCampaign::whereDate('created_at', $date)->count(),
                'spend' => FacebookInsight::whereDate('date', $date)->sum('spend'),
            ];
        })->toArray();
    }

    /**
     * Lấy top campaigns
     */
    private function getTopCampaigns(): array
    {
        return FacebookCampaign::with('adAccount')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get(['id', 'name', 'status', 'objective'])
            ->toArray();
    }

    /**
     * Lấy top posts
     */
    private function getTopPosts(): array
    {
        return FacebookPost::with('page')
            ->orderByRaw('(likes_count + shares_count + comments_count) DESC')
            ->limit(5)
            ->get(['id', 'post_id', 'page_id', 'message', 'likes_count', 'shares_count', 'comments_count', 'created_time'])
            ->toArray();
    }

    /**
     * Lấy thống kê trạng thái
     */
    private function getStatusStats(): array
    {
        return [
            'campaigns' => FacebookCampaign::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray(),
            'ads' => FacebookAd::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray(),
        ];
    }

    /**
     * Lấy thống kê hiệu suất
     */
    private function getPerformanceStats(): array
    {
        return [
            'avgCTR' => FacebookInsight::avg('ctr'),
            'avgCPC' => FacebookInsight::avg('cpc'),
            'avgCPM' => FacebookInsight::avg('cpm'),
            'totalImpressions' => FacebookInsight::sum('impressions'),
            'totalClicks' => FacebookInsight::sum('clicks'),
            'totalReach' => FacebookInsight::sum('reach'),
        ];
    }

    /**
     * Lấy báo cáo từ cache hoặc tạo mới
     */
    public function getReport(string $type): array
    {
        $cachedReport = DashboardReport::getReport($type);
        
        if ($cachedReport) {
            return $cachedReport;
        }

        // Tạo báo cáo mới nếu không có cache
        switch ($type) {
            case 'overview':
                return $this->generateOverviewReport();
            case 'analytics':
                return $this->generateAnalyticsReport();
            case 'hierarchy':
                return $this->generateHierarchyReport();
            default:
                return [];
        }
    }
}
