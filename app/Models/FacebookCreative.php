<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacebookCreative extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'id', 'ad_id', 'creative_data', 'link_url', 'link_message', 'link_name',
        'image_hash', 'call_to_action_type', 'page_welcome_message',
        'created_time', 'updated_time'
    ];
    protected $casts = [
        'creative_data' => 'array',
        'created_time' => 'datetime',
        'updated_time' => 'datetime',
    ];

    /**
     * Relationship với FacebookAd
     */
    public function ad(): BelongsTo
    {
        return $this->belongsTo(FacebookAd::class, 'ad_id', 'id');
    }
}
