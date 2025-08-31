<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FacebookPage extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'id', 'name', 'category', 'category_list', 'about',
        'fan_count', 'verification_status', 'created_time'
    ];

    protected $casts = [
        'created_time' => 'datetime',
    ];

    /**
     * Relationship với Posts
     */
    public function posts(): HasMany
    {
        return $this->hasMany(FacebookPost::class, 'page_id', 'id');
    }

    /**
     * Relationship với Ads
     */
    public function ads(): HasMany
    {
        return $this->hasMany(FacebookAd::class, 'page_id', 'id');
    }

    /**
     * Scope: Lọc theo category
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope: Lọc theo verification status
     */
    public function scopeByVerificationStatus($query, string $status)
    {
        return $query->where('verification_status', $status);
    }
}
