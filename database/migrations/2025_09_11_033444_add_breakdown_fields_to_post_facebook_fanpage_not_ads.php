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
        Schema::table('post_facebook_fanpage_not_ads', function (Blueprint $table) {
            // Thêm các trường breakdown mới
            $table->string('permalink_url')->nullable()->comment('Post permalink URL');
            $table->json('from_data')->nullable()->comment('Post author data');
            $table->json('shares_data')->nullable()->comment('Shares breakdown data');
            $table->json('comments_data')->nullable()->comment('Comments breakdown data');
            $table->json('likes_data')->nullable()->comment('Likes breakdown data');
            
            // Thêm các trường insights breakdown
            $table->integer('post_impressions_unique')->default(0)->comment('Unique post impressions');
            $table->integer('post_impressions_paid')->default(0)->comment('Paid post impressions');
            $table->integer('post_impressions_paid_unique')->default(0)->comment('Unique paid post impressions');
            $table->integer('post_impressions_organic')->default(0)->comment('Organic post impressions');
            $table->integer('post_impressions_organic_unique')->default(0)->comment('Unique organic post impressions');
            $table->integer('post_impressions_viral')->default(0)->comment('Viral post impressions');
            $table->integer('post_impressions_viral_unique')->default(0)->comment('Unique viral post impressions');
            $table->integer('post_clicks_unique')->default(0)->comment('Unique post clicks');
            $table->integer('post_video_views_paid')->default(0)->comment('Paid video views');
            $table->integer('post_video_views_organic')->default(0)->comment('Organic video views');
            
            // Thêm các trường reactions breakdown
            $table->integer('post_reactions_like_total')->default(0)->comment('Total like reactions');
            $table->integer('post_reactions_love_total')->default(0)->comment('Total love reactions');
            $table->integer('post_reactions_wow_total')->default(0)->comment('Total wow reactions');
            $table->integer('post_reactions_haha_total')->default(0)->comment('Total haha reactions');
            $table->integer('post_reactions_sorry_total')->default(0)->comment('Total sorry reactions');
            $table->integer('post_reactions_anger_total')->default(0)->comment('Total anger reactions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('post_facebook_fanpage_not_ads', function (Blueprint $table) {
            $table->dropColumn([
                'permalink_url', 'from_data', 'shares_data', 'comments_data', 'likes_data',
                'post_impressions_unique', 'post_impressions_paid', 'post_impressions_paid_unique',
                'post_impressions_organic', 'post_impressions_organic_unique', 'post_impressions_viral',
                'post_impressions_viral_unique', 'post_clicks_unique', 'post_video_views_paid',
                'post_video_views_organic', 'post_reactions_like_total', 'post_reactions_love_total',
                'post_reactions_wow_total', 'post_reactions_haha_total', 'post_reactions_sorry_total',
                'post_reactions_anger_total'
            ]);
        });
    }
};
