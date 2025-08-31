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
            // Advanced Video Metrics theo Facebook Marketing API v23.0
            if (!Schema::hasColumn('facebook_ad_insights', 'video_play_actions')) {
                $table->integer('video_play_actions')->nullable()->after('video_p100_watched_actions');
            }
            
            if (!Schema::hasColumn('facebook_ad_insights', 'video_watch_at_75_percent_actions')) {
                $table->integer('video_watch_at_75_percent_actions')->nullable()->after('video_play_actions');
            }
            
            if (!Schema::hasColumn('facebook_ad_insights', 'video_watch_at_100_percent_actions')) {
                $table->integer('video_watch_at_100_percent_actions')->nullable()->after('video_watch_at_75_percent_actions');
            }
            
            // Video Retention Metrics
            if (!Schema::hasColumn('facebook_ad_insights', 'video_retention_graph')) {
                $table->json('video_retention_graph')->nullable()->after('video_watch_at_100_percent_actions');
            }
            
            // Video Sound Metrics
            if (!Schema::hasColumn('facebook_ad_insights', 'video_sound_on_actions')) {
                $table->integer('video_sound_on_actions')->nullable()->after('video_retention_graph');
            }
            
            if (!Schema::hasColumn('facebook_ad_insights', 'video_sound_off_actions')) {
                $table->integer('video_sound_off_actions')->nullable()->after('video_sound_on_actions');
            }
            
            // Video Quality Metrics
            if (!Schema::hasColumn('facebook_ad_insights', 'video_quality_actions')) {
                $table->json('video_quality_actions')->nullable()->after('video_sound_off_actions');
            }
            
            // Video Engagement Metrics
            if (!Schema::hasColumn('facebook_ad_insights', 'video_engagement_rate')) {
                $table->float('video_engagement_rate')->nullable()->after('video_quality_actions');
            }
            
            if (!Schema::hasColumn('facebook_ad_insights', 'video_completion_rate')) {
                $table->float('video_completion_rate')->nullable()->after('video_engagement_rate');
            }
            
            // Advanced Action Metrics
            if (!Schema::hasColumn('facebook_ad_insights', 'video_skip_actions')) {
                $table->integer('video_skip_actions')->nullable()->after('video_completion_rate');
            }
            
            if (!Schema::hasColumn('facebook_ad_insights', 'video_mute_actions')) {
                $table->integer('video_mute_actions')->nullable()->after('video_skip_actions');
            }
            
            if (!Schema::hasColumn('facebook_ad_insights', 'video_unmute_actions')) {
                $table->integer('video_unmute_actions')->nullable()->after('video_mute_actions');
            }
            
            // Video Performance Metrics
            if (!Schema::hasColumn('facebook_ad_insights', 'video_performance_p25')) {
                $table->float('video_performance_p25')->nullable()->after('video_unmute_actions');
            }
            
            if (!Schema::hasColumn('facebook_ad_insights', 'video_performance_p50')) {
                $table->float('video_performance_p50')->nullable()->after('video_performance_p25');
            }
            
            if (!Schema::hasColumn('facebook_ad_insights', 'video_performance_p75')) {
                $table->float('video_performance_p75')->nullable()->after('video_performance_p50');
            }
            
            if (!Schema::hasColumn('facebook_ad_insights', 'video_performance_p95')) {
                $table->float('video_performance_p95')->nullable()->after('video_performance_p75');
            }
            
            // Video Attribution Metrics
            if (!Schema::hasColumn('facebook_ad_insights', 'video_attributed_views')) {
                $table->integer('video_attributed_views')->nullable()->after('video_performance_p95');
            }
            
            if (!Schema::hasColumn('facebook_ad_insights', 'video_attributed_view_time')) {
                $table->integer('video_attributed_view_time')->nullable()->after('video_attributed_views');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facebook_ad_insights', function (Blueprint $table) {
            $table->dropColumn([
                'video_play_actions',
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
                'video_attributed_view_time'
            ]);
        });
    }
};


