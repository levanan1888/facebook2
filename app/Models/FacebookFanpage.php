<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacebookFanpage extends Model
{
    use HasFactory;

    protected $table = 'facebook_fanpage';

    protected $fillable = [
        'page_id',
        'name',
        'access_token',
        'category',
        'category_list',
        'about',
        'website',
        'phone',
        'email',
        'location',
        'cover_photo_url',
        'profile_picture_url',
        'is_published',
        'is_verified',
        'fan_count',
        'followers_count',
        'likes_count',
        'last_synced_at',
    ];

    protected $casts = [
        'category_list' => 'array',
        'is_published' => 'boolean',
        'is_verified' => 'boolean',
        'fan_count' => 'integer',
        'followers_count' => 'integer',
        'likes_count' => 'integer',
        'last_synced_at' => 'datetime',
    ];
}


