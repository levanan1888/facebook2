<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacebookBreakdown extends Model
{
    protected $fillable = [
        'ad_insight_id',
        'breakdown_type',
        'breakdown_value',
        'metrics'
    ];

    protected $casts = [
        'metrics' => 'array'
    ];

    public function adInsight(): BelongsTo
    {
        return $this->belongsTo(FacebookAdInsight::class, 'ad_insight_id');
    }
}





