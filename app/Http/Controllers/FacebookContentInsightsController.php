<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FacebookContentInsightsController extends Controller
{
    /**
     * Display content insights overview
     */
    public function index(Request $request)
    {
        $pageId = $request->get('page_id');
        $dateRange = $request->get('date_range', '30'); // days
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        // Calculate date range
        if ($startDate && $endDate) {
            $since = Carbon::parse($startDate);
            $until = Carbon::parse($endDate);
        } else {
            $until = now();
            $since = $until->copy()->subDays((int) $dateRange);
        }

        // Get pages
        $pages = DB::table('facebook_fanpage')
            ->whereNotNull('access_token')
            ->where('access_token', '!=', '')
            ->get(['page_id', 'name']);

        $selectedPage = null;
        if ($pageId) {
            $selectedPage = $pages->firstWhere('page_id', $pageId);
        }

        // Get content insights data
        $insightsData = $this->getContentInsightsData($pageId, $since, $until);
        
        // Get top performing content
        $topContent = $this->getTopPerformingContent($pageId, $since, $until);

        return view('facebook.content-insights', compact(
            'pages',
            'selectedPage',
            'insightsData',
            'topContent',
            'since',
            'until',
            'pageId',
            'dateRange'
        ));
    }

    /**
     * Get content insights data
     */
    private function getContentInsightsData(?string $pageId, Carbon $since, Carbon $until)
    {
        $query = DB::table('facebook_page_daily_insights')
            ->whereBetween('date', [$since->toDateString(), $until->toDateString()]);

        if ($pageId) {
            $query->where('page_id', $pageId);
        }

        $data = $query->select([
            'page_id',
            'date',
            'content_views',
            'content_views_organic',
            'content_views_paid',
            'content_impressions',
            'content_impressions_organic',
            'content_impressions_paid',
            'content_views_3_seconds',
            'content_views_1_minute',
            'content_interactions',
            'content_viewers'
        ])->get();

        // Calculate totals
        $totals = [
            'content_views' => $data->sum('content_views'),
            'content_views_organic' => $data->sum('content_views_organic'),
            'content_views_paid' => $data->sum('content_views_paid'),
            'content_impressions' => $data->sum('content_impressions'),
            'content_impressions_organic' => $data->sum('content_impressions_organic'),
            'content_impressions_paid' => $data->sum('content_impressions_paid'),
            'content_views_3_seconds' => $data->sum('content_views_3_seconds'),
            'content_views_1_minute' => $data->sum('content_views_1_minute'),
            'content_interactions' => $data->sum('content_interactions'),
            'content_viewers' => $data->sum('content_viewers'),
        ];

        // Calculate percentages
        $totals['organic_percentage'] = $totals['content_views'] > 0 
            ? round(($totals['content_views_organic'] / $totals['content_views']) * 100, 1)
            : 0;
        
        $totals['paid_percentage'] = $totals['content_views'] > 0 
            ? round(($totals['content_views_paid'] / $totals['content_views']) * 100, 1)
            : 0;

        // Get daily data for chart
        $dailyData = $data->groupBy('date')->map(function ($dayData) {
            return [
                'date' => $dayData->first()->date,
                'views' => $dayData->sum('content_views'),
                'views_organic' => $dayData->sum('content_views_organic'),
                'views_paid' => $dayData->sum('content_views_paid'),
                'impressions' => $dayData->sum('content_impressions'),
                'impressions_organic' => $dayData->sum('content_impressions_organic'),
                'impressions_paid' => $dayData->sum('content_impressions_paid'),
            ];
        })->sortBy('date');

        return [
            'totals' => $totals,
            'daily' => $dailyData,
            'raw_data' => $data
        ];
    }

    /**
     * Get top performing content
     */
    private function getTopPerformingContent(?string $pageId, Carbon $since, Carbon $until)
    {
        // This would typically come from posts table
        // For now, return empty array as we need to implement post insights sync
        return collect();
    }

    /**
     * Get content insights API data
     */
    public function api(Request $request)
    {
        $pageId = $request->get('page_id');
        $since = Carbon::parse($request->get('since', now()->subDays(30)));
        $until = Carbon::parse($request->get('until', now()));

        $insightsData = $this->getContentInsightsData($pageId, $since, $until);

        return response()->json([
            'success' => true,
            'data' => $insightsData
        ]);
    }

    /**
     * Sync content insights data
     */
    public function syncContentInsights(Request $request)
    {
        try {
            $pageId = $request->input('page_id');
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            if (!$pageId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Page ID is required'
                ], 400);
            }

            // Run the sync command
            $command = "php artisan facebook:sync-content-insights --page-id={$pageId}";
            
            if ($startDate && $endDate) {
                $command .= " --since={$startDate} --until={$endDate}";
            } else {
                $command .= " --days=30";
            }

            $output = shell_exec($command . ' 2>&1');

            return response()->json([
                'success' => true,
                'message' => 'Content insights synced successfully',
                'output' => $output
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage()
            ], 500);
        }
    }
}