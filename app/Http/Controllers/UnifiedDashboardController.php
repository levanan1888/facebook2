<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\FacebookAd;
use App\Models\FacebookAdInsight;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UnifiedDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $data = $this->getCrossPlatformOverviewData($request);

        return view('unified.dashboard', compact('data'));
    }

    /**
     * Tổng quan đa nền tảng: Facebook (thật), Google/TikTok (placeholder) cho UI tổng hợp.
     */
    private function getCrossPlatformOverviewData(Request $request): array
    {
        $from = $request->get('from') ?: now()->subDays(29)->toDateString();
        $to = $request->get('to') ?: now()->toDateString();

        // Sử dụng dữ liệu từ facebook_ad_insights thay vì các cột không tồn tại
        $fb = [
            'spend' => (float) \App\Models\FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
                ->whereBetween('facebook_ad_insights.date', [$from, $to])
                ->sum('facebook_ad_insights.spend'),
            'impressions' => (int) \App\Models\FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
                ->whereBetween('facebook_ad_insights.date', [$from, $to])
                ->sum('facebook_ad_insights.impressions'),
            'clicks' => (int) \App\Models\FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
                ->whereBetween('facebook_ad_insights.date', [$from, $to])
                ->sum('facebook_ad_insights.clicks'),
            'reach' => (int) \App\Models\FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
                ->whereBetween('facebook_ad_insights.date', [$from, $to])
                ->sum('facebook_ad_insights.reach'),
            'ctr' => (float) \App\Models\FacebookAdInsight::join('facebook_ads', 'facebook_ad_insights.ad_id', '=', 'facebook_ads.id')
                ->whereBetween('facebook_ad_insights.date', [$from, $to])
                ->avg('facebook_ad_insights.ctr'),
        ];

        $google = ['spend' => 0, 'impressions' => 0, 'clicks' => 0, 'reach' => 0, 'ctr' => 0];
        $tiktok = ['spend' => 0, 'impressions' => 0, 'clicks' => 0, 'reach' => 0, 'ctr' => 0];

        $totals = [
            'spend' => $fb['spend'] + $google['spend'] + $tiktok['spend'],
            'impressions' => $fb['impressions'] + $google['impressions'] + $tiktok['impressions'],
            'clicks' => $fb['clicks'] + $google['clicks'] + $tiktok['clicks'],
            'reach' => $fb['reach'] + $google['reach'] + $tiktok['reach'],
        ];

        $series = collect(range(6, 0))->map(function (int $d) use ($from, $to) {
            $date = now()->subDays($d)->toDateString();
            $spend = ($date >= $from && $date <= $to)
                ? (float) \App\Models\FacebookAdInsight::whereDate('date', $date)->sum('spend')
                : 0;
            return ['date' => $date, 'spend' => $spend];
        });

        return [
            'filters' => ['from' => $from, 'to' => $to],
            'platforms' => ['facebook' => $fb, 'google' => $google, 'tiktok' => $tiktok],
            'totals' => $totals,
            'series' => $series,
        ];
    }
}


