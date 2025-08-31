<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Gỡ foreign keys từ facebook_ads trước khi drop bảng posts/pages
        if (Schema::hasTable('facebook_ads')) {
            Schema::table('facebook_ads', function (Blueprint $table) {
                try { $table->dropForeign(['post_id']); } catch (\Throwable $e) {}
                try { $table->dropForeign(['page_id']); } catch (\Throwable $e) {}
            });
        }

        // Xóa các bảng cũ nếu còn tồn tại
        if (Schema::hasTable('facebook_post_insights')) {
            Schema::drop('facebook_post_insights');
        }

        if (Schema::hasTable('facebook_posts')) {
            Schema::drop('facebook_posts');
        }

        if (Schema::hasTable('facebook_pages')) {
            Schema::drop('facebook_pages');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Không khôi phục các bảng cũ để tránh phục hồi cấu trúc thừa
    }
};


