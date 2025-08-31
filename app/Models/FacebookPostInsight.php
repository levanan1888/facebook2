<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacebookPostInsight extends Model
{
    protected $fillable = [
        'post_id', 'date', 'impressions', 'reach', 'clicks', 'unique_clicks',
        'likes', 'shares', 'comments', 'reactions', 'saves', 'hides',
        'hide_all_clicks', 'unlikes', 'negative_feedback',
        'video_views', 'video_view_time', 'video_avg_time_watched',
        'video_p25_watched_actions', 'video_p50_watched_actions',
        'video_p75_watched_actions', 'video_p95_watched_actions',
        'video_p100_watched_actions', 'engagement_rate', 'ctr', 'cpm',
        'cpc', 'spend', 'frequency', 'actions', 'action_values',
        'cost_per_action_type', 'cost_per_unique_action_type', 'breakdowns'
    ];

    protected $casts = [
        'date' => 'date',
        'actions' => 'array',
        'action_values' => 'array',
        'cost_per_action_type' => 'array',
        'cost_per_unique_action_type' => 'array',
        'breakdowns' => 'array',
    ];

    /**
     * Relationship với Post
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(FacebookPost::class, 'post_id', 'id');
    }

    /**
     * Scope: Lọc theo ngày
     */
    public function scopeByDate($query, $date)
    {
        return $query->where('date', $date);
    }

    /**
     * Scope: Lọc theo khoảng thời gian
     */
    public function scopeBetweenDates($query, $from, $to)
    {
        return $query->whereBetween('date', [$from, $to]);
    }

    /**
     * Scope: Có dữ liệu insights
     */
    public function scopeHasData($query)
    {
        return $query->where(function ($q) {
            $q->where('impressions', '>', 0)
              ->orWhere('reach', '>', 0)
              ->orWhere('clicks', '>', 0)
              ->orWhere('spend', '>', 0);
        });
    }
}
