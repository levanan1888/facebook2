<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FacebookPost extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'id', 'page_id', 'message', 'type', 'status_type',
        'attachments', 'permalink_url', 'created_time', 'updated_time',
        'likes_count', 'shares_count', 'comments_count', 'reactions_count',
        'engagement_updated_at'
    ];

    protected $casts = [
        'attachments' => 'array',
        'created_time' => 'datetime',
        'updated_time' => 'datetime',
        'engagement_updated_at' => 'datetime',
    ];

    /**
     * Relationship với Page
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(FacebookPage::class, 'page_id', 'id');
    }

    /**
     * Relationship với Ads
     */
    public function ads(): HasMany
    {
        return $this->hasMany(FacebookAd::class, 'post_id', 'id');
    }

    /**
     * Relationship với Post Insights
     */
    public function insights(): HasMany
    {
        return $this->hasMany(FacebookPostInsight::class, 'post_id', 'id');
    }

    /**
     * Scope: Lọc theo loại post
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: Lọc theo page
     */
    public function scopeByPage($query, string $pageId)
    {
        return $query->where('page_id', $pageId);
    }

    /**
     * Scope: Lọc theo thời gian tạo
     */
    public function scopeCreatedBetween($query, $from, $to)
    {
        return $query->whereBetween('created_time', [$from, $to]);
    }

    /**
     * Lấy insights theo ngày
     */
    public function getInsightsByDate($date)
    {
        return $this->insights()->where('date', $date)->first();
    }

    /**
     * Lấy tổng hợp insights trong khoảng thời gian
     */
    public function getInsightsSummary($from, $to)
    {
        return $this->insights()
            ->whereBetween('date', [$from, $to])
            ->selectRaw('
                SUM(impressions) as total_impressions,
                SUM(reach) as total_reach,
                SUM(clicks) as total_clicks,
                SUM(likes) as total_likes,
                SUM(shares) as total_shares,
                SUM(comments) as total_comments,
                SUM(spend) as total_spend,
                AVG(engagement_rate) as avg_engagement_rate,
                AVG(ctr) as avg_ctr,
                AVG(cpm) as avg_cpm,
                AVG(cpc) as avg_cpc
            ')
            ->first();
    }
}
