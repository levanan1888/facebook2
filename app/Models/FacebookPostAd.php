<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacebookPostAd extends Model
{
    protected $table = 'facebook_post_ads';

    protected $fillable = [
        'page_id',
        'post_id',
        'time_range',
        'message',
        'type',
        'attachment_type',
        'attachment_image',
        'attachment_source',
        'permalink_url',
        'created_time',
        'updated_time',
        'from_id',
        'from_name',
        'from_picture',
        'likes_count',
        'comments_count',
        'shares_count',
        'reactions_count',
        'raw',
    ];

    protected $casts = [
        'raw' => 'array',
        'created_time' => 'datetime',
        'updated_time' => 'datetime',
    ];
}


