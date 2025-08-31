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
        Schema::table('facebook_ads', function (Blueprint $table) {
            // Thêm trường cho post
            $table->string('post_id')->nullable()->index();
            $table->string('page_id')->nullable()->index();
            $table->text('post_message')->nullable();
            $table->string('post_type')->nullable();
            $table->string('post_status_type')->nullable();
            $table->json('post_attachments')->nullable();
            $table->string('post_permalink_url')->nullable();
            $table->timestamp('post_created_time')->nullable();
            $table->timestamp('post_updated_time')->nullable();
            
            // Thêm trường cho post insights
            $table->integer('post_impressions')->default(0);
            $table->integer('post_reach')->default(0);
            $table->integer('post_clicks')->default(0);
            $table->integer('post_unique_clicks')->default(0);
            $table->integer('post_likes')->default(0);
            $table->integer('post_shares')->default(0);
            $table->integer('post_comments')->default(0);
            $table->integer('post_reactions')->default(0);
            $table->integer('post_saves')->default(0);
            $table->integer('post_hides')->default(0);
            $table->integer('post_hide_all_clicks')->default(0);
            $table->integer('post_unlikes')->default(0);
            $table->integer('post_negative_feedback')->default(0);
            $table->integer('post_video_views')->default(0);
            $table->integer('post_video_view_time')->default(0);
            $table->decimal('post_video_avg_time_watched', 10, 2)->default(0);
            $table->integer('post_video_p25_watched_actions')->default(0);
            $table->integer('post_video_p50_watched_actions')->default(0);
            $table->integer('post_video_p75_watched_actions')->default(0);
            $table->integer('post_video_p95_watched_actions')->default(0);
            $table->integer('post_video_p100_watched_actions')->default(0);
            $table->decimal('post_engagement_rate', 8, 4)->default(0);
            $table->decimal('post_ctr', 8, 4)->default(0);
            $table->decimal('post_cpm', 10, 2)->default(0);
            $table->decimal('post_cpc', 10, 2)->default(0);
            $table->decimal('post_spend', 12, 2)->default(0);
            $table->decimal('post_frequency', 8, 4)->default(0);
            $table->json('post_actions')->nullable();
            $table->json('post_action_values')->nullable();
            $table->json('post_cost_per_action_type')->nullable();
            $table->json('post_cost_per_unique_action_type')->nullable();
            $table->json('post_breakdowns')->nullable();
            
            // Thêm trường cho ad insights
            $table->decimal('ad_spend', 12, 2)->default(0);
            $table->integer('ad_reach')->default(0);
            $table->integer('ad_impressions')->default(0);
            $table->integer('ad_clicks')->default(0);
            $table->decimal('ad_ctr', 8, 4)->default(0);
            $table->decimal('ad_cpc', 10, 2)->default(0);
            $table->decimal('ad_cpm', 10, 2)->default(0);
            $table->decimal('ad_frequency', 8, 4)->default(0);
            $table->integer('ad_unique_clicks')->default(0);
            $table->json('ad_actions')->nullable();
            $table->json('ad_action_values')->nullable();
            $table->decimal('ad_purchase_roas', 8, 4)->default(0);
            
            // Thêm trường metadata
            $table->json('post_metadata')->nullable(); // Lưu thông tin bổ sung về post
            $table->json('insights_metadata')->nullable(); // Lưu thông tin bổ sung về insights
            $table->timestamp('last_insights_sync')->nullable(); // Thời gian sync insights cuối cùng
            
            // Thêm trường cho creative link ads
            $table->string('creative_link_url')->nullable(); // URL của link ad
            $table->text('creative_link_message')->nullable(); // Message của link ad
            $table->string('creative_link_name')->nullable(); // Tên của link ad
            $table->string('creative_image_hash')->nullable(); // Hash của hình ảnh
            $table->string('creative_call_to_action_type')->nullable(); // Loại call to action
            $table->text('creative_page_welcome_message')->nullable(); // Welcome message
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facebook_ads', function (Blueprint $table) {
            // Xóa các trường đã thêm
            $table->dropColumn([
                'post_id', 'page_id', 'post_message', 'post_type', 'post_status_type',
                'post_attachments', 'post_permalink_url', 'post_created_time', 'post_updated_time',
                'post_impressions', 'post_reach', 'post_clicks', 'post_unique_clicks',
                'post_likes', 'post_shares', 'post_comments', 'post_reactions',
                'post_saves', 'post_hides', 'post_hide_all_clicks', 'post_unlikes',
                'post_negative_feedback', 'post_video_views', 'post_video_view_time',
                'post_video_avg_time_watched', 'post_video_p25_watched_actions',
                'post_video_p50_watched_actions', 'post_video_p75_watched_actions',
                'post_video_p95_watched_actions', 'post_video_p100_watched_actions',
                'post_engagement_rate', 'post_ctr', 'post_cpm', 'post_cpc',
                'post_spend', 'post_frequency', 'post_actions', 'post_action_values',
                'post_cost_per_action_type', 'post_cost_per_unique_action_type', 'post_breakdowns',
                'ad_spend', 'ad_reach', 'ad_impressions', 'ad_clicks', 'ad_ctr',
                'ad_cpc', 'ad_cpm', 'ad_frequency', 'ad_unique_clicks', 'ad_actions',
                'ad_action_values', 'ad_purchase_roas', 'post_metadata', 'insights_metadata',
                'last_insights_sync', 'creative_link_url', 'creative_link_message',
                'creative_link_name', 'creative_image_hash', 'creative_call_to_action_type',
                'creative_page_welcome_message'
            ]);
        });
    }
};
