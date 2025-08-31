<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FacebookAdSet extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'id',
        'name',
        'status',
        'optimization_goal',
        'campaign_id',
        'created_time',
        'updated_time'
    ];
    protected $casts = [
        'created_time' => 'datetime',
        'updated_time' => 'datetime',
    ];

    /**
     * Quan hệ: Ad Set thuộc về Campaign
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(FacebookCampaign::class, 'campaign_id', 'id');
    }

    /**
     * Quan hệ: Ad Set có nhiều Ad
     */
    public function ads(): HasMany
    {
        return $this->hasMany(FacebookAd::class, 'adset_id', 'id');
    }
}



