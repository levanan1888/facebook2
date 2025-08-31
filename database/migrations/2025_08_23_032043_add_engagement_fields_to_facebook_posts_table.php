<?php

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
        Schema::table('facebook_posts', function (Blueprint $table) {
            // Thêm các trường engagement metrics
            $table->unsignedBigInteger('likes_count')->default(0)->after('updated_time');
            $table->unsignedBigInteger('shares_count')->default(0)->after('likes_count');
            $table->unsignedBigInteger('comments_count')->default(0)->after('shares_count');
            $table->unsignedBigInteger('reactions_count')->default(0)->after('comments_count');
            $table->timestamp('engagement_updated_at')->nullable()->after('reactions_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facebook_posts', function (Blueprint $table) {
            // Xóa các trường engagement metrics
            $table->dropColumn([
                'likes_count',
                'shares_count', 
                'comments_count',
                'reactions_count',
                'engagement_updated_at'
            ]);
        });
    }
};
