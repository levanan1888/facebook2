<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Xóa các bảng alias nếu tồn tại (fb_pages, fb_posts, fb_post_insights)
        foreach (['fb_post_insights', 'fb_posts', 'fb_pages'] as $table) {
            if (Schema::hasTable($table)) {
                Schema::drop($table);
            }
        }
    }

    public function down(): void
    {
        // Không khôi phục các bảng alias
    }
};




