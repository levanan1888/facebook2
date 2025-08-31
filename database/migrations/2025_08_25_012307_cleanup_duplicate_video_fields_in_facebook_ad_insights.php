<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Dọn dẹp các trường video trùng lặp trong facebook_ad_insights
     * Giữ lại các trường có ý nghĩa và loại bỏ trùng lặp
     */
    public function up(): void
    {
        Schema::table('facebook_ad_insights', function (Blueprint $table) {
            // 1. Xóa các trường trùng lặp video_plays_at_XX_percent (giữ lại video_plays_at_XX)
            if (Schema::hasColumn('facebook_ad_insights', 'video_plays_at_25_percent')) {
                $table->dropColumn('video_plays_at_25_percent');
            }
            if (Schema::hasColumn('facebook_ad_insights', 'video_plays_at_50_percent')) {
                $table->dropColumn('video_plays_at_50_percent');
            }
            if (Schema::hasColumn('facebook_ad_insights', 'video_plays_at_75_percent')) {
                $table->dropColumn('video_plays_at_75_percent');
            }
            if (Schema::hasColumn('facebook_ad_insights', 'video_plays_at_100_percent')) {
                $table->dropColumn('video_plays_at_100_percent');
            }
            
            // 2. Xóa trường video_view_time trùng lặp (giữ lại 1 cái)
            // Kiểm tra xem có bao nhiêu cột video_view_time
            $columns = Schema::getColumnListing('facebook_ad_insights');
            $videoViewTimeCount = 0;
            foreach ($columns as $column) {
                if ($column === 'video_view_time') {
                    $videoViewTimeCount++;
                }
            }
            
            // Nếu có nhiều hơn 1 cột video_view_time, xóa cột thứ 2
            if ($videoViewTimeCount > 1) {
                // Xóa cột video_view_time thứ 2 (nếu có)
                // Laravel không hỗ trợ xóa cột cụ thể nên cần tạo migration riêng
            }
            
            // 3. Xóa các trường post video trùng lặp (giữ lại video metrics chính)
            if (Schema::hasColumn('facebook_ad_insights', 'post_video_views')) {
                $table->dropColumn('post_video_views');
            }
            if (Schema::hasColumn('facebook_ad_insights', 'post_video_views_unique')) {
                $table->dropColumn('post_video_views_unique');
            }
            if (Schema::hasColumn('facebook_ad_insights', 'post_video_avg_time_watched')) {
                $table->dropColumn('post_video_avg_time_watched');
            }
            if (Schema::hasColumn('facebook_ad_insights', 'post_video_complete_views_30s')) {
                $table->dropColumn('post_video_complete_views_30s');
            }
            if (Schema::hasColumn('facebook_ad_insights', 'post_video_views_10s')) {
                $table->dropColumn('post_video_views_10s');
            }
            if (Schema::hasColumn('facebook_ad_insights', 'post_video_retention_graph')) {
                $table->dropColumn('post_video_retention_graph');
            }
            if (Schema::hasColumn('facebook_ad_insights', 'post_video_views_paid')) {
                $table->dropColumn('post_video_views_paid');
            }
            if (Schema::hasColumn('facebook_ad_insights', 'post_video_views_organic')) {
                $table->dropColumn('post_video_views_organic');
            }
            
            // 4. Thêm trường video_30_sec_watched nếu chưa có
            if (!Schema::hasColumn('facebook_ad_insights', 'video_30_sec_watched')) {
                $table->integer('video_30_sec_watched')->nullable()->after('thruplays');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facebook_ad_insights', function (Blueprint $table) {
            // Không cần rollback vì đây là dọn dẹp trùng lặp
        });
    }
};
