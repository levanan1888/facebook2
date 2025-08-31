<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacebookReportSummary extends Model
{
    protected $table = 'facebook_report_summary';

    protected $fillable = [
        'date',
        'business_id',
        'account_id',
        'campaign_id',
        'adset_id',
        'ad_id',
        'post_id',
        'page_id',
        'total_spend',
        'total_impressions',
        'total_clicks',
        'total_reach',
        'total_conversions',
        'total_conversion_values',
        'avg_ctr',
        'avg_cpc',
        'avg_cpm',
        'avg_frequency',
        'ads_count',
        'posts_count',
        'campaigns_count',
        'pages_count',
        'engagement_rate',
        'roas',
    ];

    protected $casts = [
        'date' => 'date',
        'total_spend' => 'float',
        'total_impressions' => 'integer',
        'total_clicks' => 'integer',
        'total_reach' => 'integer',
        'total_conversions' => 'integer',
        'total_conversion_values' => 'float',
        'avg_ctr' => 'float',
        'avg_cpc' => 'float',
        'avg_cpm' => 'float',
        'avg_frequency' => 'float',
        'ads_count' => 'integer',
        'posts_count' => 'integer',
        'campaigns_count' => 'integer',
        'pages_count' => 'integer',
        'engagement_rate' => 'float',
        'roas' => 'float',
    ];

    /**
     * Relationship với Business
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(FacebookBusiness::class, 'business_id');
    }

    /**
     * Relationship với Ad Account
     */
    public function adAccount(): BelongsTo
    {
        return $this->belongsTo(FacebookAdAccount::class, 'account_id');
    }

    /**
     * Relationship với Campaign
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(FacebookCampaign::class, 'campaign_id');
    }

    /**
     * Relationship với Ad Set
     */
    public function adSet(): BelongsTo
    {
        return $this->belongsTo(FacebookAdSet::class, 'adset_id');
    }

    /**
     * Relationship với Ad
     */
    public function ad(): BelongsTo
    {
        return $this->belongsTo(FacebookAd::class, 'ad_id');
    }

    /**
     * Relationship với Post
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(FacebookPost::class, 'post_id');
    }

    /**
     * Relationship với Page
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(FacebookPage::class, 'page_id');
    }

    /**
     * Scope để lọc theo ngày
     */
    public function scopeByDate($query, string $date)
    {
        return $query->where('date', $date);
    }

    /**
     * Scope để lọc theo khoảng ngày
     */
    public function scopeBetweenDates($query, string $from, string $to)
    {
        return $query->whereBetween('date', [$from, $to]);
    }

    /**
     * Scope để lọc theo Business
     */
    public function scopeByBusiness($query, string $businessId)
    {
        return $query->where('business_id', $businessId);
    }

    /**
     * Scope để lọc theo Account
     */
    public function scopeByAccount($query, string $accountId)
    {
        return $query->where('account_id', $accountId);
    }

    /**
     * Scope để lọc theo Campaign
     */
    public function scopeByCampaign($query, string $campaignId)
    {
        return $query->where('campaign_id', $campaignId);
    }

    /**
     * Scope để lọc theo Ad Set
     */
    public function scopeByAdSet($query, string $adsetId)
    {
        return $query->where('adset_id', $adsetId);
    }

    /**
     * Scope để lọc theo Ad
     */
    public function scopeByAd($query, string $adId)
    {
        return $query->where('ad_id', $adId);
    }

    /**
     * Scope để lọc theo Post
     */
    public function scopeByPost($query, string $postId)
    {
        return $query->where('post_id', $postId);
    }

    /**
     * Scope để lọc theo Page
     */
    public function scopeByPage($query, string $pageId)
    {
        return $query->where('page_id', $pageId);
    }

    /**
     * Scope để lọc có dữ liệu
     */
    public function scopeHasData($query)
    {
        return $query->where('total_spend', '>', 0)
                    ->orWhere('total_impressions', '>', 0)
                    ->orWhere('total_clicks', '>', 0);
    }
}
