<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Xóa các bảng không còn cần thiết vì đã gộp vào facebook_ads
        Schema::dropIfExists('facebook_post_insights');
        Schema::dropIfExists('facebook_posts');
        Schema::dropIfExists('facebook_pages');
        Schema::dropIfExists('facebook_insights');
    }

    public function down(): void
    {
        // Không cần rollback vì đây là cleanup migration
        // Các bảng này đã được thay thế bằng cấu trúc mới trong facebook_ads
    }
};
