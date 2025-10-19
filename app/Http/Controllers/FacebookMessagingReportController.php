<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FacebookMessagingReportController extends Controller
{
    public function index(Request $request)
    {
        $since = $request->query('since', now()->subDays(6)->toDateString());
        $until = $request->query('until', now()->toDateString());
        $pageId = $request->query('page_id');

        $pages = DB::table('facebook_fanpage')->select('page_id','name')->orderBy('name')->get();

        $base = DB::table('facebook_page_daily_insights')->whereBetween('date', [$since, $until]);
        if ($pageId) { $base->where('page_id', $pageId); }
        $rows = $base->orderBy('date')->get(['date','page_id','messages_new_conversations','messages_total_connections','messages_paid_conversations','messages_organic_conversations']);

        // If paid is mostly zero, recompute from ad insights as a fallback for reporting view (không ghi DB)
        $recomputePaid = $rows->sum('messages_paid_conversations') === 0;
        $paidByDate = [];
        if ($recomputePaid) {
            $ai = DB::table('facebook_ad_insights')
                ->select('date', DB::raw('COALESCE(SUM(messaging_conversation_started_7d),0) as paid'))
                ->whereBetween('date', [$since, $until]);
            if ($pageId) { $ai->where('page_id', $pageId); }
            $paidByDate = $ai->groupBy('date')->pluck('paid','date');
        }

        $byDate = [];
        foreach ($rows as $r) {
            $d = $r->date;
            // When not filtering by page, there can be multiple rows per date (one per page).
            // Sum metrics by date across all pages, then compute organic after aggregation.
            if (!isset($byDate[$d])) {
                $byDate[$d] = [
                    'date' => $d,
                    'new' => 0,
                    'total' => 0,
                    'paid' => 0,
                    'organic' => 0,
                ];
            }

            $paidForDate = (int) $r->messages_paid_conversations;
            if ($recomputePaid) {
                // Already aggregated by date from ad insights when recomputing
                $paidForDate = (int) ($paidByDate[$d] ?? 0);
            }

            $byDate[$d]['new'] += (int) $r->messages_new_conversations;
            $byDate[$d]['total'] += (int) $r->messages_total_connections;
            // If recomputing, ensure we don't add the same paid value multiple times across pages.
            // In recompute mode, set paid to the aggregated value once.
            if ($recomputePaid) {
                $byDate[$d]['paid'] = $paidForDate;
            } else {
                $byDate[$d]['paid'] += $paidForDate;
            }
        }

        // After aggregation, compute organic per date
        foreach ($byDate as $d => $vals) {
            $byDate[$d]['organic'] = max($vals['new'] - $vals['paid'], 0);
        }

        // Aggregate totals
        $totals = [
            'new' => array_sum(array_column($byDate, 'new')),
            'total' => array_sum(array_column($byDate, 'total')),
            'paid' => array_sum(array_column($byDate, 'paid')),
            'organic' => array_sum(array_column($byDate, 'organic')),
        ];

        return view('facebook.messaging.report', [
            'since' => $since,
            'until' => $until,
            'pageId' => $pageId,
            'pages' => $pages,
            'byDate' => $byDate,
            'totals' => $totals,
        ]);
    }
}


