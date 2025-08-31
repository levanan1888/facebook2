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
        // Thêm trường video cho facebook_post_insights (chỉ thiếu thruplays)
        Schema::table('facebook_post_insights', function (Blueprint $table) {
            $table->unsignedBigInteger('thruplays')->default(0)->after('video_p100_watched_actions');
        });

        // Thêm trường video cho facebook_ad_insights
        Schema::table('facebook_ad_insights', function (Blueprint $table) {
            $table->unsignedBigInteger('video_views')->default(0)->after('breakdowns');
            $table->unsignedBigInteger('video_view_time')->default(0)->after('video_views');
            $table->decimal('video_avg_time_watched', 10, 2)->default(0)->after('video_view_time');
            $table->unsignedBigInteger('video_plays')->default(0)->after('video_avg_time_watched');
            $table->unsignedBigInteger('video_plays_at_25')->default(0)->after('video_plays');
            $table->unsignedBigInteger('video_plays_at_50')->default(0)->after('video_plays_at_25');
            $table->unsignedBigInteger('video_plays_at_75')->default(0)->after('video_plays_at_50');
            $table->unsignedBigInteger('video_plays_at_100')->default(0)->after('video_plays_at_75');
            $table->unsignedBigInteger('video_p25_watched_actions')->default(0)->after('video_plays_at_100');
            $table->unsignedBigInteger('video_p50_watched_actions')->default(0)->after('video_p25_watched_actions');
            $table->unsignedBigInteger('video_p75_watched_actions')->default(0)->after('video_p50_watched_actions');
            $table->unsignedBigInteger('video_p95_watched_actions')->default(0)->after('video_p75_watched_actions');
            $table->unsignedBigInteger('video_p100_watched_actions')->default(0)->after('video_p95_watched_actions');
            $table->unsignedBigInteger('thruplays')->default(0)->after('video_p100_watched_actions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Xóa trường video từ facebook_post_insights
        Schema::table('facebook_post_insights', function (Blueprint $table) {
            $table->dropColumn(['thruplays']);
        });

        // Xóa trường video từ facebook_ad_insights
        Schema::table('facebook_ad_insights', function (Blueprint $table) {
            $table->dropColumn([
                'video_views',
                'video_view_time',
                'video_avg_time_watched',
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
                'thruplays'
            ]);
        });
    }
};
