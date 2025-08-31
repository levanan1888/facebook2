<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacebookAd extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'id', 'name', 'status', 'effective_status', 'adset_id', 'campaign_id', 'account_id', 
        'post_id', 'page_id', 'created_time', 'updated_time', 'last_insights_sync'
    ];
    protected $casts = [
        'created_time' => 'datetime',
        'updated_time' => 'datetime',
        'last_insights_sync' => 'datetime',
    ];

    /**
     * Relationship với Ad Account
     */
    public function adAccount()
    {
        return $this->belongsTo(FacebookAdAccount::class, 'account_id', 'id');
    }

    /**
     * Relationship với Ad Set
     */
    public function adSet()
    {
        return $this->belongsTo(FacebookAdSet::class, 'adset_id', 'id');
    }

    /**
     * Relationship với Campaign
     */
    public function campaign()
    {
        return $this->belongsTo(FacebookCampaign::class, 'campaign_id', 'id');
    }

    /**
     * Relationship với Post
     */
    public function post()
    {
        // Bảng posts đã bị loại bỏ; giữ stub để tránh lỗi gọi quan hệ cũ
        return $this->belongsTo(\App\Models\FacebookPost::class, 'post_id', 'id');
    }

    /**
     * Relationship với Page
     */
    public function page()
    {
        // Bảng pages đã bị loại bỏ; giữ stub để tránh lỗi gọi quan hệ cũ
        return $this->belongsTo(\App\Models\FacebookPage::class, 'page_id', 'id');
    }

    /**
     * Relationship với Ad Insights
     */
    public function insights()
    {
        return $this->hasMany(FacebookAdInsight::class, 'ad_id', 'id');
    }

    /**
     * Relationship với Creative
     */
    public function creative()
    {
        return $this->hasOne(FacebookCreative::class, 'ad_id', 'id');
    }

    // Scopes
    /**
     * Scope: Lấy ads có post
     */
    public function scopeWithPost($query)
    {
        return $query->whereNotNull('post_id');
    }

    /**
     * Scope: Lấy ads có insights
     */
    public function scopeWithInsights($query)
    {
        return $query->whereNotNull('last_insights_sync');
    }

    /**
     * Scope: Lọc theo page
     */
    public function scopeByPage($query, $pageId)
    {
        return $query->where('page_id', $pageId);
    }
}



