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
            // Thêm các fields video metrics còn thiếu theo docs Facebook
            if (!Schema::hasColumn('facebook_ad_insights', 'video_plays_at_25_percent')) {
                $table->integer('video_plays_at_25_percent')->nullable()->after('video_plays');
            }
            if (!Schema::hasColumn('facebook_ad_insights', 'video_plays_at_50_percent')) {
                $table->integer('video_plays_at_50_percent')->nullable()->after('video_plays_at_25_percent');
            }
            if (!Schema::hasColumn('facebook_ad_insights', 'video_plays_at_75_percent')) {
                $table->integer('video_plays_at_75_percent')->nullable()->after('video_plays_at_50_percent');
            }
            if (!Schema::hasColumn('facebook_ad_insights', 'video_plays_at_100_percent')) {
                $table->integer('video_plays_at_100_percent')->nullable()->after('video_plays_at_75_percent');
            }
            
            if (!Schema::hasColumn('facebook_ad_insights', 'video_p95_watched_actions')) {
                $table->integer('video_p95_watched_actions')->nullable()->after('video_p75_watched_actions');
            }
            
            if (!Schema::hasColumn('facebook_ad_insights', 'video_view_time')) {
                $table->integer('video_view_time')->nullable()->after('video_avg_time_watched');
            }
            
            // Post video metrics
            if (!Schema::hasColumn('facebook_ad_insights', 'post_video_views')) {
                $table->integer('post_video_views')->nullable()->after('video_view_time');
            }
            if (!Schema::hasColumn('facebook_ad_insights', 'post_video_views_unique')) {
                $table->integer('post_video_views_unique')->nullable()->after('post_video_views');
            }
            if (!Schema::hasColumn('facebook_ad_insights', 'post_video_avg_time_watched')) {
                $table->float('post_video_avg_time_watched')->nullable()->after('post_video_views_unique');
            }
            if (!Schema::hasColumn('facebook_ad_insights', 'post_video_complete_views_30s')) {
                $table->integer('post_video_complete_views_30s')->nullable()->after('post_video_avg_time_watched');
            }
            if (!Schema::hasColumn('facebook_ad_insights', 'post_video_views_10s')) {
                $table->integer('post_video_views_10s')->nullable()->after('post_video_complete_views_30s');
            }
            if (!Schema::hasColumn('facebook_ad_insights', 'post_video_retention_graph')) {
                $table->json('post_video_retention_graph')->nullable()->after('post_video_views_10s');
            }
            if (!Schema::hasColumn('facebook_ad_insights', 'post_video_views_paid')) {
                $table->integer('post_video_views_paid')->nullable()->after('post_video_retention_graph');
            }
            if (!Schema::hasColumn('facebook_ad_insights', 'post_video_views_organic')) {
                $table->integer('post_video_views_organic')->nullable()->after('post_video_views_paid');
            }
            
            // Unique metrics
            if (!Schema::hasColumn('facebook_ad_insights', 'unique_ctr')) {
                $table->float('unique_ctr')->nullable()->after('unique_clicks');
            }
            if (!Schema::hasColumn('facebook_ad_insights', 'unique_link_clicks_ctr')) {
                $table->float('unique_link_clicks_ctr')->nullable()->after('unique_ctr');
            }
            if (!Schema::hasColumn('facebook_ad_insights', 'unique_impressions')) {
                $table->integer('unique_impressions')->nullable()->after('unique_link_clicks_ctr');
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
                'video_plays', 'video_plays_at_25_percent', 'video_plays_at_50_percent', 
                'video_plays_at_75_percent', 'video_plays_at_100_percent',
                'video_avg_time_watched_actions', 'video_p25_watched_actions', 'video_p50_watched_actions',
                'video_p75_watched_actions', 'video_p95_watched_actions', 'video_p100_watched_actions',
                'thruplays', 'video_avg_time_watched', 'video_view_time',
                'post_video_views', 'post_video_views_unique', 'post_video_avg_time_watched',
                'post_video_complete_views_30s', 'post_video_views_10s', 'post_video_retention_graph',
                'post_video_views_paid', 'post_video_views_organic',
                'unique_ctr', 'unique_link_clicks_ctr', 'unique_impressions'
            ]);
        });
    }
};
