<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Migrate dữ liệu từ bảng facebook_ads cũ sang các bảng mới đã chuẩn hóa
     */
    public function up(): void
    {
        // 1. Migrate dữ liệu posts
        $this->migratePostsData();
        
        // 2. Migrate dữ liệu creatives
        $this->migrateCreativesData();
        
        // 3. Migrate dữ liệu post insights
        $this->migratePostInsightsData();
        
        // 4. Migrate dữ liệu ad insights
        $this->migrateAdInsightsData();
        
        // 5. Clean up bảng facebook_ads (xóa các cột không cần thiết)
        $this->cleanupFacebookAdsTable();
    }

    /**
     * Migrate dữ liệu posts
     */
    private function migratePostsData(): void
    {
        $adsWithPosts = DB::table('facebook_ads')
            ->whereNotNull('post_id')
            ->select([
                'post_id as id',
                'page_id',
                'post_message as message',
                'post_type as type',
                'post_status_type as status_type',
                'post_attachments as attachments',
                'post_permalink_url as permalink_url',
                'post_created_time as created_time',
                'post_updated_time as updated_time',
                'created_at',
                'updated_at'
            ])
            ->get();

        foreach ($adsWithPosts as $post) {
            DB::table('facebook_posts')->updateOrInsert(
                ['id' => $post->id],
                [
                    'id' => $post->id,
                    'page_id' => $post->page_id,
                    'message' => $post->message,
                    'type' => $post->type,
                    'status_type' => $post->status_type,
                    'attachments' => $post->attachments,
                    'permalink_url' => $post->permalink_url,
                    'created_time' => $post->created_time,
                    'updated_time' => $post->updated_time,
                    'created_at' => $post->created_at,
                    'updated_at' => $post->updated_at,
                ]
            );
        }
    }

    /**
     * Migrate dữ liệu creatives
     */
    private function migrateCreativesData(): void
    {
        $adsWithCreatives = DB::table('facebook_ads')
            ->whereNotNull('creative')
            ->select([
                'id as ad_id',
                'creative',
                'creative_link_url as link_url',
                'creative_link_message as link_message',
                'creative_link_name as link_name',
                'creative_image_hash as image_hash',
                'creative_call_to_action_type as call_to_action_type',
                'creative_page_welcome_message as page_welcome_message',
                'created_time',
                'updated_time',
                'created_at',
                'updated_at'
            ])
            ->get();

        foreach ($adsWithCreatives as $creative) {
            // Tạo creative_id từ ad_id + timestamp
            $creativeId = $creative->ad_id . '_' . time();
            
            DB::table('facebook_creatives')->updateOrInsert(
                ['ad_id' => $creative->ad_id],
                [
                    'id' => $creativeId,
                    'ad_id' => $creative->ad_id,
                    'creative_data' => $creative->creative,
                    'link_url' => $creative->link_url,
                    'link_message' => $creative->link_message,
                    'link_name' => $creative->link_name,
                    'image_hash' => $creative->image_hash,
                    'call_to_action_type' => $creative->call_to_action_type,
                    'page_welcome_message' => $creative->page_welcome_message,
                    'created_time' => $creative->created_time,
                    'updated_time' => $creative->updated_time,
                    'created_at' => $creative->created_at,
                    'updated_at' => $creative->updated_at,
                ]
            );
        }
    }

    /**
     * Migrate dữ liệu post insights
     */
    private function migratePostInsightsData(): void
    {
        $adsWithPostInsights = DB::table('facebook_ads')
            ->whereNotNull('post_id')
            ->whereNotNull('last_insights_sync')
            ->select([
                'post_id',
                'last_insights_sync',
                'post_impressions',
                'post_reach',
                'post_clicks',
                'post_unique_clicks',
                'post_likes',
                'post_shares',
                'post_comments',
                'post_reactions',
                'post_saves',
                'post_hides',
                'post_hide_all_clicks',
                'post_unlikes',
                'post_negative_feedback',
                'post_video_views',
                'post_video_view_time',
                'post_video_avg_time_watched',
                'post_video_p25_watched_actions',
                'post_video_p50_watched_actions',
                'post_video_p75_watched_actions',
                'post_video_p95_watched_actions',
                'post_video_p100_watched_actions',
                'post_engagement_rate',
                'post_ctr',
                'post_cpm',
                'post_cpc',
                'post_spend',
                'post_frequency',
                'post_actions',
                'post_action_values',
                'post_cost_per_action_type',
                'post_cost_per_unique_action_type',
                'post_breakdowns',
                'created_at',
                'updated_at'
            ])
            ->get();

        foreach ($adsWithPostInsights as $insight) {
            $date = $insight->last_insights_sync ? date('Y-m-d', strtotime($insight->last_insights_sync)) : date('Y-m-d');
            
            DB::table('facebook_post_insights')->updateOrInsert(
                ['post_id' => $insight->post_id, 'date' => $date],
                [
                    'post_id' => $insight->post_id,
                    'date' => $date,
                    'impressions' => $insight->post_impressions ?? 0,
                    'reach' => $insight->post_reach ?? 0,
                    'clicks' => $insight->post_clicks ?? 0,
                    'unique_clicks' => $insight->post_unique_clicks ?? 0,
                    'likes' => $insight->post_likes ?? 0,
                    'shares' => $insight->post_shares ?? 0,
                    'comments' => $insight->post_comments ?? 0,
                    'reactions' => $insight->post_reactions ?? 0,
                    'saves' => $insight->post_saves ?? 0,
                    'hides' => $insight->post_hides ?? 0,
                    'hide_all_clicks' => $insight->post_hide_all_clicks ?? 0,
                    'unlikes' => $insight->post_unlikes ?? 0,
                    'negative_feedback' => $insight->post_negative_feedback ?? 0,
                    'video_views' => $insight->post_video_views ?? 0,
                    'video_view_time' => $insight->post_video_view_time ?? 0,
                    'video_avg_time_watched' => $insight->post_video_avg_time_watched ?? 0,
                    'video_p25_watched_actions' => $insight->post_video_p25_watched_actions ?? 0,
                    'video_p50_watched_actions' => $insight->post_video_p50_watched_actions ?? 0,
                    'video_p75_watched_actions' => $insight->post_video_p75_watched_actions ?? 0,
                    'video_p95_watched_actions' => $insight->post_video_p95_watched_actions ?? 0,
                    'video_p100_watched_actions' => $insight->post_video_p100_watched_actions ?? 0,
                    'engagement_rate' => $insight->post_engagement_rate ?? 0,
                    'ctr' => $insight->post_ctr ?? 0,
                    'cpm' => $insight->post_cpm ?? 0,
                    'cpc' => $insight->post_cpc ?? 0,
                    'spend' => $insight->post_spend ?? 0,
                    'frequency' => $insight->post_frequency ?? 0,
                    'actions' => $insight->post_actions,
                    'action_values' => $insight->post_action_values,
                    'cost_per_action_type' => $insight->post_cost_per_action_type,
                    'cost_per_unique_action_type' => $insight->post_cost_per_unique_action_type,
                    'breakdowns' => $insight->post_breakdowns,
                    'created_at' => $insight->created_at,
                    'updated_at' => $insight->updated_at,
                ]
            );
        }
    }

    /**
     * Migrate dữ liệu ad insights
     */
    private function migrateAdInsightsData(): void
    {
        $adsWithInsights = DB::table('facebook_ads')
            ->whereNotNull('last_insights_sync')
            ->select([
                'id as ad_id',
                'last_insights_sync',
                'ad_spend',
                'ad_reach',
                'ad_impressions',
                'ad_clicks',
                'ad_ctr',
                'ad_cpc',
                'ad_cpm',
                'ad_frequency',
                'ad_unique_clicks',
                'ad_actions',
                'ad_action_values',
                'ad_purchase_roas',
                'created_at',
                'updated_at'
            ])
            ->get();

        foreach ($adsWithInsights as $insight) {
            $date = $insight->last_insights_sync ? date('Y-m-d', strtotime($insight->last_insights_sync)) : date('Y-m-d');
            
            DB::table('facebook_ad_insights')->updateOrInsert(
                ['ad_id' => $insight->ad_id, 'date' => $date],
                [
                    'ad_id' => $insight->ad_id,
                    'date' => $date,
                    'spend' => $insight->ad_spend ?? 0,
                    'reach' => $insight->ad_reach ?? 0,
                    'impressions' => $insight->ad_impressions ?? 0,
                    'clicks' => $insight->ad_clicks ?? 0,
                    'unique_clicks' => $insight->ad_unique_clicks ?? 0,
                    'ctr' => $insight->ad_ctr ?? 0,
                    'cpc' => $insight->ad_cpc ?? 0,
                    'cpm' => $insight->ad_cpm ?? 0,
                    'frequency' => $insight->ad_frequency ?? 0,
                    'purchase_roas' => $insight->ad_purchase_roas ?? 0,
                    'actions' => $insight->ad_actions,
                    'action_values' => $insight->ad_action_values,
                    'created_at' => $insight->created_at,
                    'updated_at' => $insight->updated_at,
                ]
            );
        }
    }

    /**
     * Clean up bảng facebook_ads - xóa các cột không cần thiết
     */
    private function cleanupFacebookAdsTable(): void
    {
        Schema::table('facebook_ads', function (Blueprint $table) {
            // Xóa các cột post-related
            $table->dropColumn([
                'post_message', 'post_type', 'post_status_type', 'post_attachments',
                'post_permalink_url', 'post_created_time', 'post_updated_time',
                'post_impressions', 'post_reach', 'post_clicks', 'post_unique_clicks',
                'post_likes', 'post_shares', 'post_comments', 'post_reactions',
                'post_saves', 'post_hides', 'post_hide_all_clicks', 'post_unlikes',
                'post_negative_feedback', 'post_video_views', 'post_video_view_time',
                'post_video_avg_time_watched', 'post_video_p25_watched_actions',
                'post_video_p50_watched_actions', 'post_video_p75_watched_actions',
                'post_video_p95_watched_actions', 'post_video_p100_watched_actions',
                'post_engagement_rate', 'post_ctr', 'post_cpm', 'post_cpc',
                'post_spend', 'post_frequency', 'post_actions', 'post_action_values',
                'post_cost_per_action_type', 'post_cost_per_unique_action_type',
                'post_breakdowns', 'post_metadata'
            ]);

            // Xóa các cột creative-related
            $table->dropColumn([
                'creative', 'creative_link_url', 'creative_link_message',
                'creative_link_name', 'creative_image_hash', 'creative_call_to_action_type',
                'creative_page_welcome_message'
            ]);

            // Xóa các cột insights-related
            $table->dropColumn([
                'ad_spend', 'ad_reach', 'ad_impressions', 'ad_clicks', 'ad_ctr',
                'ad_cpc', 'ad_cpm', 'ad_frequency', 'ad_unique_clicks', 'ad_actions',
                'ad_action_values', 'ad_purchase_roas', 'ad_conversions',
                'ad_conversion_values', 'ad_cost_per_conversion', 'ad_outbound_clicks',
                'ad_unique_outbound_clicks', 'ad_inline_link_clicks',
                'ad_unique_inline_link_clicks', 'ad_website_clicks', 'insights_metadata'
            ]);

            // Giữ lại các cột cơ bản
            // id, name, status, effective_status, adset_id, campaign_id, account_id,
            // post_id, page_id, created_time, updated_time, last_insights_sync, timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Không thể rollback migration này vì đã xóa dữ liệu
        // Cần restore từ backup nếu cần
    }
};
