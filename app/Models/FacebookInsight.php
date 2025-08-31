<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacebookInsight extends Model
{
    protected $fillable = [
        'level','ref_id','date','spend','reach','impressions','clicks','ctr','cpc','cpm','frequency','unique_clicks','actions','action_values','purchase_roas'
    ];
    protected $casts = [
        'date' => 'date',
        'actions' => 'array',
        'action_values' => 'array',
        'purchase_roas' => 'array',
        'spend' => 'decimal:2',
        'ctr' => 'decimal:4',
        'cpc' => 'decimal:4',
        'cpm' => 'decimal:4',
        'frequency' => 'decimal:4',
    ];
}



