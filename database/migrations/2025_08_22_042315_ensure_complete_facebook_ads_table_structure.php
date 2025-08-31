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
            // Đảm bảo có đủ tất cả các trường cần thiết
            
            // 1. Trường cơ bản của Ad
            if (!Schema::hasColumn('facebook_ads', 'id')) {
                $table->string('id', 50)->primary();
            }
            if (!Schema::hasColumn('facebook_ads', 'name')) {
                $table->string('name')->nullable();
            }
            if (!Schema::hasColumn('facebook_ads', 'status')) {
                $table->string('status')->nullable();
            }
            if (!Schema::hasColumn('facebook_ads', 'effective_status')) {
                $table->string('effective_status')->nullable();
            }
            if (!Schema::hasColumn('facebook_ads', 'adset_id')) {
                $table->string('adset_id', 50)->nullable();
            }
            if (!Schema::hasColumn('facebook_ads', 'campaign_id')) {
                $table->string('campaign_id', 50)->nullable();
            }
            if (!Schema::hasColumn('facebook_ads', 'account_id')) {
                $table->string('account_id', 50)->nullable();
            }
            if (!Schema::hasColumn('facebook_ads', 'creative')) {
                $table->json('creative')->nullable();
            }
            if (!Schema::hasColumn('facebook_ads', 'created_time')) {
                $table->timestamp('created_time')->nullable();
            }
            if (!Schema::hasColumn('facebook_ads', 'updated_time')) {
                $table->timestamp('updated_time')->nullable();
            }
            
            // 2. Trường Post (nếu là Post Ad)
            if (!Schema::hasColumn('facebook_ads', 'post_id')) {
                $table->string('post_id', 50)->nullable();
            }
            if (!Schema::hasColumn('facebook_ads', 'page_id')) {
                $table->string('page_id', 50)->nullable();
            }
            if (!Schema::hasColumn('facebook_ads', 'post_message')) {
                $table->text('post_message')->nullable();
            }
            if (!Schema::hasColumn('facebook_ads', 'post_type')) {
                $table->string('post_type')->nullable();
            }
            if (!Schema::hasColumn('facebook_ads', 'post_status_type')) {
                $table->string('post_status_type')->nullable();
            }
            if (!Schema::hasColumn('facebook_ads', 'post_attachments')) {
                $table->json('post_attachments')->nullable();
            }
            if (!Schema::hasColumn('facebook_ads', 'post_permalink_url')) {
                $table->text('post_permalink_url')->nullable();
            }
            if (!Schema::hasColumn('facebook_ads', 'post_created_time')) {
                $table->timestamp('post_created_time')->nullable();
            }
            if (!Schema::hasColumn('facebook_ads', 'post_updated_time')) {
                $table->timestamp('post_updated_time')->nullable();
            }
            
            // 3. Trường Creative (cho Link Ads)
            if (!Schema::hasColumn('facebook_ads', 'creative_link_url')) {
                $table->text('creative_link_url')->nullable();
            }
            if (!Schema::hasColumn('facebook_ads', 'creative_link_message')) {
                $table->text('creative_link_message')->nullable();
            }
            if (!Schema::hasColumn('facebook_ads', 'creative_link_name')) {
                $table->string('creative_link_name')->nullable();
            }
            if (!Schema::hasColumn('facebook_ads', 'creative_image_hash')) {
                $table->string('creative_image_hash')->nullable();
            }
            if (!Schema::hasColumn('facebook_ads', 'creative_call_to_action_type')) {
                $table->string('creative_call_to_action_type')->nullable();
            }
            if (!Schema::hasColumn('facebook_ads', 'creative_page_welcome_message')) {
                $table->text('creative_page_welcome_message')->nullable();
            }
            
            // 4. Trường Post Insights
            if (!Schema::hasColumn('facebook_ads', 'post_impressions')) {
                $table->integer('post_impressions')->default(0);
            }
            if (!Schema::hasColumn('facebook_ads', 'post_reach')) {
                $table->integer('post_reach')->default(0);
            }
            if (!Schema::hasColumn('facebook_ads', 'post_clicks')) {
                $table->integer('post_clicks')->default(0);
            }
            if (!Schema::hasColumn('facebook_ads', 'post_unique_clicks')) {
                $table->integer('post_unique_clicks')->default(0);
            }
            if (!Schema::hasColumn('facebook_ads', 'post_likes')) {
                $table->integer('post_likes')->default(0);
            }
            if (!Schema::hasColumn('facebook_ads', 'post_shares')) {
                $table->integer('post_shares')->default(0);
            }
            if (!Schema::hasColumn('facebook_ads', 'post_comments')) {
                $table->integer('post_comments')->default(0);
            }
            if (!Schema::hasColumn('facebook_ads', 'post_reactions')) {
                $table->integer('post_reactions')->default(0);
            }
            if (!Schema::hasColumn('facebook_ads', 'post_saves')) {
                $table->integer('post_saves')->default(0);
            }
            if (!Schema::hasColumn('facebook_ads', 'post_hides')) {
                $table->integer('post_hides')->default(0);
            }
            if (!Schema::hasColumn('facebook_ads', 'post_hide_all_clicks')) {
                $table->integer('post_hide_all_clicks')->default(0);
            }
            if (!Schema::hasColumn('facebook_ads', 'post_unlikes')) {
                $table->integer('post_unlikes')->default(0);
            }
            if (!Schema::hasColumn('facebook_ads', 'post_negative_feedback')) {
                $table->integer('post_negative_feedback')->default(0);
            }
            if (!Schema::hasColumn('facebook_ads', 'post_video_views')) {
                $table->integer('post_video_views')->default(0);
            }
            if (!Schema::hasColumn('facebook_ads', 'post_video_view_time')) {
                $table->integer('post_video_view_time')->default(0);
            }
            if (!Schema::hasColumn('facebook_ads', 'post_video_avg_time_watched')) {
                $table->decimal('post_video_avg_time_watched', 10, 2)->default(0);
            }
            if (!Schema::hasColumn('facebook_ads', 'post_video_p25_watched_actions')) {
                $table->integer('post_video_p25_watched_actions')->default(0);
            }
            if (!Schema::hasColumn('facebook_ads', 'post_video_p50_watched_actions')) {
                $table->integer('post_video_p50_watched_actions')->default(0);
            }
            if (!Schema::hasColumn('facebook_ads', 'post_video_p75_watched_actions')) {
                $table->integer('post_video_p75_watched_actions')->default(0);
            }
            if (!Schema::hasColumn('facebook_ads', 'post_video_p95_watched_actions')) {
                $table->integer('post_video_p95_watched_actions')->default(0);
            }
            if (!Schema::hasColumn('facebook_ads', 'post_video_p100_watched_actions')) {
                $table->integer('post_video_p100_watched_actions')->default(0);
            }
            if (!Schema::hasColumn('facebook_ads', 'post_engagement_rate')) {
                $table->decimal('post_engagement_rate', 10, 4)->default(0);
            }
            if (!Schema::hasColumn('facebook_ads', 'post_ctr')) {
                $table->decimal('post_ctr', 10, 4)->default(0);
            }
            if (!Schema::hasColumn('facebook_ads', 'post_cpm')) {
                $table->decimal('post_cpm', 10, 2)->default(0);
            }
            if (!Schema::hasColumn('facebook_ads', 'post_cpc')) {
                $table->decimal('post_cpc', 10, 2)->default(0);
            }
            if (!Schema::hasColumn('facebook_ads', 'post_spend')) {
                $table->decimal('post_spend', 12, 2)->default(0);
            }
            if (!Schema::hasColumn('facebook_ads', 'post_frequency')) {
                $table->decimal('post_frequency', 10, 4)->default(0);
            }
            if (!Schema::hasColumn('facebook_ads', 'post_actions')) {
                $table->json('post_actions')->nullable();
            }
            if (!Schema::hasColumn('facebook_ads', 'post_action_values')) {
                $table->json('post_action_values')->nullable();
            }
            if (!Schema::hasColumn('facebook_ads', 'post_cost_per_action_type')) {
                $table->json('post_cost_per_action_type')->nullable();
            }
            if (!Schema::hasColumn('facebook_ads', 'post_cost_per_unique_action_type')) {
                $table->json('post_cost_per_unique_action_type')->nullable();
            }
            if (!Schema::hasColumn('facebook_ads', 'post_breakdowns')) {
                $table->json('post_breakdowns')->nullable();
            }
            
            // 5. Trường Ad Insights
            if (!Schema::hasColumn('facebook_ads', 'ad_spend')) {
                $table->decimal('ad_spend', 12, 2)->default(0);
            }
            if (!Schema::hasColumn('facebook_ads', 'ad_reach')) {
                $table->integer('ad_reach')->default(0);
            }
            if (!Schema::hasColumn('facebook_ads', 'ad_impressions')) {
                $table->integer('ad_impressions')->default(0);
            }
            if (!Schema::hasColumn('facebook_ads', 'ad_clicks')) {
                $table->integer('ad_clicks')->default(0);
            }
            if (!Schema::hasColumn('facebook_ads', 'ad_ctr')) {
                $table->decimal('ad_ctr', 10, 4)->default(0);
            }
            if (!Schema::hasColumn('facebook_ads', 'ad_cpc')) {
                $table->decimal('ad_cpc', 10, 2)->default(0);
            }
            if (!Schema::hasColumn('facebook_ads', 'ad_cpm')) {
                $table->decimal('ad_cpm', 10, 2)->default(0);
            }
            if (!Schema::hasColumn('facebook_ads', 'ad_frequency')) {
                $table->decimal('ad_frequency', 10, 4)->default(0);
            }
            if (!Schema::hasColumn('facebook_ads', 'ad_unique_clicks')) {
                $table->integer('ad_unique_clicks')->default(0);
            }
            if (!Schema::hasColumn('facebook_ads', 'ad_actions')) {
                $table->json('ad_actions')->nullable();
            }
            if (!Schema::hasColumn('facebook_ads', 'ad_action_values')) {
                $table->json('ad_action_values')->nullable();
            }
            if (!Schema::hasColumn('facebook_ads', 'ad_purchase_roas')) {
                $table->decimal('ad_purchase_roas', 10, 4)->default(0);
            }
            if (!Schema::hasColumn('facebook_ads', 'ad_conversions')) {
                $table->integer('ad_conversions')->default(0);
            }
            if (!Schema::hasColumn('facebook_ads', 'ad_conversion_values')) {
                $table->decimal('ad_conversion_values', 12, 2)->default(0);
            }
            if (!Schema::hasColumn('facebook_ads', 'ad_cost_per_conversion')) {
                $table->decimal('ad_cost_per_conversion', 10, 2)->default(0);
            }
            if (!Schema::hasColumn('facebook_ads', 'ad_outbound_clicks')) {
                $table->integer('ad_outbound_clicks')->default(0);
            }
            if (!Schema::hasColumn('facebook_ads', 'ad_unique_outbound_clicks')) {
                $table->integer('ad_unique_outbound_clicks')->default(0);
            }
            if (!Schema::hasColumn('facebook_ads', 'ad_inline_link_clicks')) {
                $table->integer('ad_inline_link_clicks')->default(0);
            }
            if (!Schema::hasColumn('facebook_ads', 'ad_unique_inline_link_clicks')) {
                $table->integer('ad_unique_inline_link_clicks')->default(0);
            }
            if (!Schema::hasColumn('facebook_ads', 'ad_website_clicks')) {
                $table->integer('ad_website_clicks')->default(0);
            }
            
            // 6. Trường metadata
            if (!Schema::hasColumn('facebook_ads', 'post_metadata')) {
                $table->json('post_metadata')->nullable();
            }
            if (!Schema::hasColumn('facebook_ads', 'insights_metadata')) {
                $table->json('insights_metadata')->nullable();
            }
            if (!Schema::hasColumn('facebook_ads', 'last_insights_sync')) {
                $table->timestamp('last_insights_sync')->nullable();
            }
            
            // 7. Timestamps Laravel
            if (!Schema::hasColumn('facebook_ads', 'created_at')) {
                $table->timestamps();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facebook_ads', function (Blueprint $table) {
            // Không cần drop columns vì migration này chỉ đảm bảo structure
        });
    }
};
