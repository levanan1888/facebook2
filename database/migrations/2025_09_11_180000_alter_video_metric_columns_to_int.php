<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Đổi các cột video metrics về INT UNSIGNED để hiển thị đúng số lớn (không còn 1/0)
        $columns = [
            'video_plays',
            'video_plays_at_25',
            'video_plays_at_50',
            'video_plays_at_75',
            'video_plays_at_100',
            'video_p25_watched_actions',
            'video_p50_watched_actions',
            'video_p75_watched_actions',
            'video_p95_watched_actions',
            'video_p100_watched_actions',
            'video_30_sec_watched',
            'thruplays',
        ];

        foreach ($columns as $col) {
            try {
                DB::statement("ALTER TABLE facebook_ad_insights MODIFY COLUMN `$col` INT UNSIGNED DEFAULT 0");
            } catch (\Throwable $e) {
                // Bỏ qua nếu cột không tồn tại
            }
        }
    }

    public function down(): void
    {
        // Không cần down cụ thể; để nguyên kiểu INT
    }
};


