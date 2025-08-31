<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tái cấu trúc database theo chuẩn hóa
     * Tách bảng facebook_ads lớn thành các bảng chuyên biệt
     */
    public function up(): void
    {
        // 1. Tạo bảng facebook_posts (tách từ facebook_ads)
        if (!Schema::hasTable('facebook_posts')) {
            Schema::create('facebook_posts', function (Blueprint $table) {
                $table->string('id')->primary(); // post_id từ Facebook
                $table->string('page_id')->index();
                $table->text('message')->nullable();
                $table->string('type')->nullable(); // photo, video, carousel_album, link
                $table->string('status_type')->nullable();
                $table->json('attachments')->nullable();
                $table->text('permalink_url')->nullable();
                $table->timestamp('created_time')->nullable();
                $table->timestamp('updated_time')->nullable();
                $table->timestamps();
                
                // Indexes
                $table->index(['page_id', 'created_time']);
                $table->index(['type', 'created_time']);
            });
        }

        // 2. Tạo bảng facebook_creatives (tách từ facebook_ads)
        if (!Schema::hasTable('facebook_creatives')) {
            Schema::create('facebook_creatives', function (Blueprint $table) {
                $table->string('id')->primary(); // creative_id từ Facebook
                $table->string('ad_id')->index();
                $table->json('creative_data')->nullable(); // Toàn bộ data creative từ Facebook
                $table->text('link_url')->nullable();
                $table->text('link_message')->nullable();
                $table->string('link_name')->nullable();
                $table->string('image_hash')->nullable();
                $table->string('call_to_action_type')->nullable();
                $table->text('page_welcome_message')->nullable();
                $table->timestamp('created_time')->nullable();
                $table->timestamp('updated_time')->nullable();
                $table->timestamps();
            });
        }

        // 3. Tạo bảng facebook_post_insights (tách từ facebook_ads)
        if (!Schema::hasTable('facebook_post_insights')) {
            Schema::create('facebook_post_insights', function (Blueprint $table) {
                $table->id();
                $table->string('post_id')->index();
                $table->date('date')->nullable();
                
                // Metrics cơ bản
                $table->integer('impressions')->default(0);
                $table->integer('reach')->default(0);
                $table->integer('clicks')->default(0);
                $table->integer('unique_clicks')->default(0);
                
                // Engagement metrics
                $table->integer('likes')->default(0);
                $table->integer('shares')->default(0);
                $table->integer('comments')->default(0);
                $table->integer('reactions')->default(0);
                $table->integer('saves')->default(0);
                $table->integer('hides')->default(0);
                $table->integer('hide_all_clicks')->default(0);
                $table->integer('unlikes')->default(0);
                $table->integer('negative_feedback')->default(0);
                
                // Video metrics (nếu có)
                $table->integer('video_views')->default(0);
                $table->integer('video_view_time')->default(0);
                $table->decimal('video_avg_time_watched', 10, 2)->default(0);
                $table->integer('video_p25_watched_actions')->default(0);
                $table->integer('video_p50_watched_actions')->default(0);
                $table->integer('video_p75_watched_actions')->default(0);
                $table->integer('video_p95_watched_actions')->default(0);
                $table->integer('video_p100_watched_actions')->default(0);
                
                // Performance metrics
                $table->decimal('engagement_rate', 10, 4)->default(0);
                $table->decimal('ctr', 10, 4)->default(0);
                $table->decimal('cpm', 10, 2)->default(0);
                $table->decimal('cpc', 10, 2)->default(0);
                $table->decimal('spend', 12, 2)->default(0);
                $table->decimal('frequency', 10, 4)->default(0);
                
                // JSON fields cho data phức tạp
                $table->json('actions')->nullable();
                $table->json('action_values')->nullable();
                $table->json('cost_per_action_type')->nullable();
                $table->json('cost_per_unique_action_type')->nullable();
                $table->json('breakdowns')->nullable();
                
                $table->timestamps();
                
                // Indexes
                $table->index(['post_id', 'date']);
                $table->unique(['post_id', 'date']);
            });
        }

        // 4. Tạo bảng facebook_ad_insights (tách từ facebook_ads)
        if (!Schema::hasTable('facebook_ad_insights')) {
            Schema::create('facebook_ad_insights', function (Blueprint $table) {
                $table->id();
                $table->string('ad_id')->index();
                $table->date('date')->nullable();
                
                // Metrics cơ bản
                $table->decimal('spend', 12, 2)->default(0);
                $table->integer('reach')->default(0);
                $table->integer('impressions')->default(0);
                $table->integer('clicks')->default(0);
                $table->integer('unique_clicks')->default(0);
                
                // Conversion metrics
                $table->integer('conversions')->default(0);
                $table->decimal('conversion_values', 12, 2)->default(0);
                $table->decimal('cost_per_conversion', 10, 2)->default(0);
                
                // Click metrics
                $table->integer('outbound_clicks')->default(0);
                $table->integer('unique_outbound_clicks')->default(0);
                $table->integer('inline_link_clicks')->default(0);
                $table->integer('unique_inline_link_clicks')->default(0);
                $table->integer('website_clicks')->default(0);
                
                // Performance metrics
                $table->decimal('ctr', 10, 4)->default(0);
                $table->decimal('cpc', 10, 2)->default(0);
                $table->decimal('cpm', 10, 2)->default(0);
                $table->decimal('frequency', 10, 4)->default(0);
                
                // JSON fields cho data phức tạp
                $table->json('actions')->nullable();
                $table->json('action_values')->nullable();
                $table->json('cost_per_action_type')->nullable();
                $table->json('cost_per_unique_action_type')->nullable();
                $table->json('breakdowns')->nullable();
                
                $table->timestamps();
                
                // Indexes
                $table->index(['ad_id', 'date']);
                $table->unique(['ad_id', 'date']);
            });
        }

        // 5. Tạo bảng facebook_pages (nếu chưa có)
        if (!Schema::hasTable('facebook_pages')) {
            Schema::create('facebook_pages', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->string('name')->nullable();
                $table->string('category')->nullable();
                $table->string('category_list')->nullable();
                $table->text('about')->nullable();
                $table->string('fan_count')->nullable();
                $table->string('verification_status')->nullable();
                $table->timestamp('created_time')->nullable();
                $table->timestamps();
            });
        }

        // 6. Cập nhật bảng facebook_ads (chỉ giữ thông tin cơ bản)
        Schema::table('facebook_ads', function (Blueprint $table) {
            // Thêm foreign key constraints một cách an toàn
            if (!Schema::hasColumn('facebook_ads', 'post_id')) {
                $table->string('post_id')->nullable()->index();
            }
            if (!Schema::hasColumn('facebook_ads', 'page_id')) {
                $table->string('page_id')->nullable()->index();
            }
        });

        // 7. Cập nhật bảng facebook_ad_sets
        Schema::table('facebook_ad_sets', function (Blueprint $table) {
            // Thêm foreign key nếu chưa có
            if (!Schema::hasColumn('facebook_ad_sets', 'campaign_id')) {
                $table->string('campaign_id')->nullable()->index();
            }
        });

        // 8. Cập nhật bảng facebook_campaigns
        Schema::table('facebook_campaigns', function (Blueprint $table) {
            // Thêm foreign key nếu chưa có
            if (!Schema::hasColumn('facebook_campaigns', 'ad_account_id')) {
                $table->string('ad_account_id')->nullable()->index();
            }
        });

        // 9. Cập nhật bảng facebook_ad_accounts
        Schema::table('facebook_ad_accounts', function (Blueprint $table) {
            // Thêm foreign key nếu chưa có
            if (!Schema::hasColumn('facebook_ad_accounts', 'business_id')) {
                $table->string('business_id')->nullable()->index();
            }
        });

        // 10. Tạo bảng tổng hợp cho báo cáo
        if (!Schema::hasTable('facebook_report_summary')) {
            Schema::create('facebook_report_summary', function (Blueprint $table) {
                $table->id();
                $table->date('date');
                $table->string('business_id')->nullable()->index();
                $table->string('account_id')->nullable()->index();
                $table->string('campaign_id')->nullable()->index();
                $table->string('adset_id')->nullable()->index();
                $table->string('ad_id')->nullable()->index();
                $table->string('post_id')->nullable()->index();
                $table->string('page_id')->nullable()->index();
                
                // Aggregated metrics
                $table->decimal('total_spend', 12, 2)->default(0);
                $table->integer('total_reach')->default(0);
                $table->integer('total_impressions')->default(0);
                $table->integer('total_clicks')->default(0);
                $table->integer('total_conversions')->default(0);
                $table->decimal('total_conversion_values', 12, 2)->default(0);
                $table->decimal('avg_ctr', 10, 4)->default(0);
                $table->decimal('avg_cpc', 10, 2)->default(0);
                $table->decimal('avg_cpm', 10, 2)->default(0);
                $table->decimal('avg_frequency', 10, 4)->default(0);
                $table->decimal('engagement_rate', 10, 4)->default(0);
                $table->decimal('roas', 10, 4)->default(0);
                
                // Counts
                $table->integer('ads_count')->default(0);
                $table->integer('posts_count')->default(0);
                $table->integer('campaigns_count')->default(0);
                $table->integer('pages_count')->default(0);
                
                $table->timestamps();
                
                // Indexes
                $table->index(['date', 'business_id']);
                $table->index(['date', 'account_id']);
                $table->index(['date', 'campaign_id']);
                $table->index(['date', 'adset_id']);
                $table->index(['date', 'ad_id']);
                $table->index(['date', 'post_id']);
                $table->index(['date', 'page_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop tables theo thứ tự ngược lại
        Schema::dropIfExists('facebook_report_summary');
        Schema::dropIfExists('facebook_ad_insights');
        Schema::dropIfExists('facebook_post_insights');
        Schema::dropIfExists('facebook_creatives');
        Schema::dropIfExists('facebook_posts');
        Schema::dropIfExists('facebook_pages');
    }
};
