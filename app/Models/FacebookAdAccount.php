<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FacebookAdAccount extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'id',
        'account_id',
        'name',
        'account_status',
        'business_id',
        'created_time',
        'updated_time'
    ];
    protected $casts = [
        'created_time' => 'datetime',
        'updated_time' => 'datetime',
    ];

    /**
     * Quan hệ: Ad Account thuộc về Business
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(FacebookBusiness::class, 'business_id', 'id');
    }

    /**
     * Quan hệ: Ad Account có nhiều Campaigns
     */
    public function campaigns(): HasMany
    {
        return $this->hasMany(FacebookCampaign::class, 'ad_account_id', 'id');
    }

    /**
     * Quan hệ: Ad Account có nhiều Ads
     */
    public function ads(): HasMany
    {
        return $this->hasMany(FacebookAd::class, 'account_id', 'id');
    }
}



