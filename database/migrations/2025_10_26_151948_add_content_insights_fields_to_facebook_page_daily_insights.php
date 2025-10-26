<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facebook_page_daily_insights', function (Blueprint $table) {
            // Check if columns don't exist before adding them
            if (!Schema::hasColumn('facebook_page_daily_insights', 'content_views')) {
                $table->unsignedBigInteger('content_views')->default(0)->comment('Tổng lượt xem nội dung');
            }
            if (!Schema::hasColumn('facebook_page_daily_insights', 'content_views_organic')) {
                $table->unsignedBigInteger('content_views_organic')->default(0)->comment('Lượt xem từ nguồn tự nhiên');
            }
            if (!Schema::hasColumn('facebook_page_daily_insights', 'content_views_paid')) {
                $table->unsignedBigInteger('content_views_paid')->default(0)->comment('Lượt xem từ quảng cáo');
            }
            if (!Schema::hasColumn('facebook_page_daily_insights', 'content_impressions')) {
                $table->unsignedBigInteger('content_impressions')->default(0)->comment('Tổng số hiển thị nội dung');
            }
            if (!Schema::hasColumn('facebook_page_daily_insights', 'content_impressions_organic')) {
                $table->unsignedBigInteger('content_impressions_organic')->default(0)->comment('Số hiển thị từ nguồn tự nhiên');
            }
            if (!Schema::hasColumn('facebook_page_daily_insights', 'content_impressions_paid')) {
                $table->unsignedBigInteger('content_impressions_paid')->default(0)->comment('Số hiển thị từ quảng cáo');
            }
            if (!Schema::hasColumn('facebook_page_daily_insights', 'content_views_3_seconds')) {
                $table->unsignedBigInteger('content_views_3_seconds')->default(0)->comment('Lượt xem trong tối thiểu 3 giây');
            }
            if (!Schema::hasColumn('facebook_page_daily_insights', 'content_views_1_minute')) {
                $table->unsignedBigInteger('content_views_1_minute')->default(0)->comment('Lượt xem trong tối thiểu 1 phút');
            }
            if (!Schema::hasColumn('facebook_page_daily_insights', 'content_interactions')) {
                $table->unsignedBigInteger('content_interactions')->default(0)->comment('Lượt tương tác với nội dung');
            }
            if (!Schema::hasColumn('facebook_page_daily_insights', 'content_viewers')) {
                $table->unsignedBigInteger('content_viewers')->default(0)->comment('Số người xem nội dung');
            }
        });
    }

    public function down(): void
    {
        Schema::table('facebook_page_daily_insights', function (Blueprint $table) {
            $table->dropColumn([
                'content_views',
                'content_views_organic',
                'content_views_paid',
                'content_impressions',
                'content_impressions_organic',
                'content_impressions_paid',
                'content_views_3_seconds',
                'content_views_1_minute',
                'content_interactions',
                'content_viewers',
            ]);
        });
    }
};