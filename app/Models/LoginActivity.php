<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'location',
        'action',
        'logged_in_at',
        'logged_out_at',
    ];

    protected $casts = [
        'logged_in_at' => 'datetime',
        'logged_out_at' => 'datetime',
    ];

    /**
     * Quan hệ với User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope để lấy login activities gần đây
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope để lấy login thành công
     */
    public function scopeSuccessful($query)
    {
        return $query->where('action', 'login');
    }

    /**
     * Scope để lấy login thất bại
     */
    public function scopeFailed($query)
    {
        return $query->where('action', 'failed');
    }
}
