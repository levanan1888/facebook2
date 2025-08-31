<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FacebookBusiness extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id','name','verification_status','created_time'];
    protected $casts = ['created_time' => 'datetime'];

    /**
     * Quan hệ: Business Manager có nhiều Ad Account
     */
    public function adAccounts(): HasMany
    {
        return $this->hasMany(FacebookAdAccount::class, 'business_id', 'id');
    }
}



