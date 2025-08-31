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
        Schema::table('facebook_ad_insights', function (Blueprint $table) {
            // Xóa các trường thừa - đã có video_p25_watched_actions, video_p50_watched_actions, etc.
            $columnsToDrop = [
                'video_plays_at_25',
                'video_plays_at_50', 
                'video_plays_at_75',
                'video_plays_at_100',
                'video_plays_at_25_percent',
                'video_plays_at_50_percent',
                'video_plays_at_75_percent',
                'video_plays_at_100_percent',
                'video_watch_at_75_percent_actions',
                'video_watch_at_100_percent_actions',
                'video_retention_graph',
                'video_sound_on_actions',
                'video_sound_off_actions',
                'video_quality_actions',
                'video_engagement_rate',
                'video_completion_rate',
                'video_skip_actions',
                'video_mute_actions',
                'video_unmute_actions',
                'video_performance_p25',
                'video_performance_p50',
                'video_performance_p75',
                'video_performance_p95',
                'video_attributed_views',
                'video_attributed_view_time',
            ];

            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('facebook_ad_insights', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facebook_ad_insights', function (Blueprint $table) {
            // Thêm lại các trường đã xóa (nếu cần rollback)
            $table->integer('video_plays_at_25')->nullable();
            $table->integer('video_plays_at_50')->nullable();
            $table->integer('video_plays_at_75')->nullable();
            $table->integer('video_plays_at_100')->nullable();
            $table->integer('video_plays_at_25_percent')->nullable();
            $table->integer('video_plays_at_50_percent')->nullable();
            $table->integer('video_plays_at_75_percent')->nullable();
            $table->integer('video_plays_at_100_percent')->nullable();
            $table->integer('video_watch_at_75_percent_actions')->nullable();
            $table->integer('video_watch_at_100_percent_actions')->nullable();
            $table->json('video_retention_graph')->nullable();
            $table->integer('video_sound_on_actions')->nullable();
            $table->integer('video_sound_off_actions')->nullable();
            $table->integer('video_quality_actions')->nullable();
            $table->decimal('video_engagement_rate', 5, 2)->nullable();
            $table->decimal('video_completion_rate', 5, 2)->nullable();
            $table->integer('video_skip_actions')->nullable();
            $table->integer('video_mute_actions')->nullable();
            $table->integer('video_unmute_actions')->nullable();
            $table->integer('video_performance_p25')->nullable();
            $table->integer('video_performance_p50')->nullable();
            $table->integer('video_performance_p75')->nullable();
            $table->integer('video_performance_p95')->nullable();
            $table->integer('video_attributed_views')->nullable();
            $table->integer('video_attributed_view_time')->nullable();
        });
    }
};
