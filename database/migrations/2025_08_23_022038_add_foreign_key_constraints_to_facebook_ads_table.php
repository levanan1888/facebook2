<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Thêm foreign key constraints cho bảng facebook_ads
     */
    public function up(): void
    {
        Schema::table('facebook_ads', function (Blueprint $table) {
            // Đảm bảo kiểu dữ liệu khớp trước khi thêm foreign key
            $table->string('account_id', 50)->nullable()->change();
            $table->string('campaign_id', 50)->nullable()->change();
            $table->string('adset_id', 50)->nullable()->change();
            $table->string('post_id', 50)->nullable()->change();
            $table->string('page_id', 50)->nullable()->change();
        });

        Schema::table('facebook_ads', function (Blueprint $table) {
            // Thêm foreign key cho account_id -> facebook_ad_accounts.id
            $table->foreign('account_id')
                  ->references('id')
                  ->on('facebook_ad_accounts')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            // Thêm foreign key cho campaign_id -> facebook_campaigns.id
            $table->foreign('campaign_id')
                  ->references('id')
                  ->on('facebook_campaigns')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            // Thêm foreign key cho adset_id -> facebook_ad_sets.id
            $table->foreign('adset_id')
                  ->references('id')
                  ->on('facebook_ad_sets')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            // Thêm foreign key cho post_id -> facebook_posts.id
            $table->foreign('post_id')
                  ->references('id')
                  ->on('facebook_posts')
                  ->onDelete('set null')
                  ->onUpdate('cascade');

            // Thêm foreign key cho page_id -> facebook_pages.id
            $table->foreign('page_id')
                  ->references('id')
                  ->on('facebook_pages')
                  ->onDelete('set null')
                  ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facebook_ads', function (Blueprint $table) {
            // Xóa foreign key constraints
            $table->dropForeign(['account_id']);
            $table->dropForeign(['campaign_id']);
            $table->dropForeign(['adset_id']);
            $table->dropForeign(['post_id']);
            $table->dropForeign(['page_id']);
        });
    }
};
