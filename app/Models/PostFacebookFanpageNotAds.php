<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostFacebookFanpageNotAds extends Model
{
    use HasFactory;

    protected $table = 'post_facebook_fanpage_not_ads';

    protected $fillable = [
        'post_id',
        'page_id',
        'message',
        'story',
        'type',
        'status_type',
        'link',
        'picture',
        'full_picture',
        'source',
        'description',
        'caption',
        'name',
        'attachments',
        'properties',
        'is_published',
        'is_hidden',
        'is_expired',
        'created_time',
        'updated_time',
        'post_impressions',
        'post_engaged_users',
        'post_clicks',
        'post_reactions',
        'post_comments',
        'post_shares',
        'post_video_views',
        'post_video_complete_views',
        'insights_data',
        'insights_synced_at',
        'last_synced_at',
    ];

    protected $casts = [
        'attachments' => 'array',
        'properties' => 'array',
        'is_published' => 'boolean',
        'is_hidden' => 'boolean',
        'is_expired' => 'boolean',
        'created_time' => 'datetime',
        'updated_time' => 'datetime',
        'insights_data' => 'array',
        'insights_synced_at' => 'datetime',
        'last_synced_at' => 'datetime',
    ];
}


