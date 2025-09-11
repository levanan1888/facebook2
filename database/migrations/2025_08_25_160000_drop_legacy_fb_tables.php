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
        // Không cần gỡ foreign keys vì chúng không được tạo ra nữa
        // Foreign keys cho post_id và page_id đã được loại bỏ khỏi các migration khác

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


