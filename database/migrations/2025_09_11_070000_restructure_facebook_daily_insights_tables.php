
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Daily post insights: align with post_facebook_fanpage_not_ads metrics
        Schema::table('facebook_daily_insights', function (Blueprint $table) {
            if (!Schema::hasColumn('facebook_daily_insights', 'post_impressions')) {
                $table->unsignedBigInteger('post_impressions')->default(0);
                $table->unsignedBigInteger('post_impressions_unique')->default(0);
                $table->unsignedBigInteger('post_impressions_paid')->default(0);
                $table->unsignedBigInteger('post_impressions_paid_unique')->default(0);
                $table->unsignedBigInteger('post_impressions_organic')->default(0);
                $table->unsignedBigInteger('post_impressions_organic_unique')->default(0);
                $table->unsignedBigInteger('post_impressions_viral')->default(0);
                $table->unsignedBigInteger('post_impressions_viral_unique')->default(0);

                $table->unsignedBigInteger('post_clicks')->default(0);
                $table->unsignedBigInteger('post_clicks_unique')->default(0)->nullable();
                $table->unsignedBigInteger('post_engaged_users')->default(0);

                $table->unsignedBigInteger('post_reactions')->default(0);
                $table->unsignedBigInteger('post_comments')->default(0);
                $table->unsignedBigInteger('post_shares')->default(0);
                $table->unsignedBigInteger('post_reactions_like_total')->default(0);
                $table->unsignedBigInteger('post_reactions_love_total')->default(0);
                $table->unsignedBigInteger('post_reactions_wow_total')->default(0);
                $table->unsignedBigInteger('post_reactions_haha_total')->default(0);
                $table->unsignedBigInteger('post_reactions_sorry_total')->default(0);
                $table->unsignedBigInteger('post_reactions_anger_total')->default(0);

                $table->unsignedBigInteger('post_video_views')->default(0);
                $table->unsignedBigInteger('post_video_complete_views')->default(0);

                $table->json('insights_data')->nullable();
            }
        });

        // Daily video insights: store per-day values
        Schema::table('facebook_daily_video_insights', function (Blueprint $table) {
            if (!Schema::hasColumn('facebook_daily_video_insights', 'video_views')) {
                $table->unsignedBigInteger('video_views')->default(0);
                $table->unsignedBigInteger('video_complete_views')->default(0);
                $table->unsignedBigInteger('video_10s_views')->default(0)->nullable();
                $table->unsignedBigInteger('video_15s_views')->default(0)->nullable();
                $table->unsignedBigInteger('video_30s_views')->default(0)->nullable();
                $table->unsignedBigInteger('video_avg_time_watched')->default(0)->nullable();
                $table->json('insights_data')->nullable();
            }
        });
    }

    public function down(): void
    {
        // We won't drop columns on down to avoid losing data; no-op
    }
};


