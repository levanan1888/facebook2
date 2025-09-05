<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Thêm foreign key constraints sau khi đã sửa các cột JSON
     */
    public function up(): void
    {
        Schema::table('facebook_ads', function (Blueprint $table) {
            // Thêm foreign key cho account_id -> facebook_ad_accounts.id
            if (!Schema::hasColumn('facebook_ads', 'account_id')) {
                $table->string('account_id', 50)->nullable();
            }
            if (!$this->foreignKeyExists('facebook_ads', 'account_id')) {
                $table->foreign('account_id')
                      ->references('id')
                      ->on('facebook_ad_accounts')
                      ->onDelete('cascade')
                      ->onUpdate('cascade');
            }

            // Thêm foreign key cho campaign_id -> facebook_campaigns.id
            if (!$this->foreignKeyExists('facebook_ads', 'campaign_id')) {
                $table->foreign('campaign_id')
                      ->references('id')
                      ->on('facebook_campaigns')
                      ->onDelete('cascade')
                      ->onUpdate('cascade');
            }

            // Thêm foreign key cho adset_id -> facebook_ad_sets.id
            if (!$this->foreignKeyExists('facebook_ads', 'adset_id')) {
                $table->foreign('adset_id')
                      ->references('id')
                      ->on('facebook_ad_sets')
                      ->onDelete('cascade')
                      ->onUpdate('cascade');
            }

            // Thêm foreign key cho post_id -> facebook_posts.id
            if (!$this->foreignKeyExists('facebook_ads', 'post_id')) {
                $table->foreign('post_id')
                      ->references('id')
                      ->on('facebook_posts')
                      ->onDelete('set null')
                      ->onUpdate('cascade');
            }

            // Thêm foreign key cho page_id -> facebook_pages.id
            if (!$this->foreignKeyExists('facebook_ads', 'page_id')) {
                $table->foreign('page_id')
                      ->references('id')
                      ->on('facebook_pages')
                      ->onDelete('set null')
                      ->onUpdate('cascade');
            }
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

    /**
     * Kiểm tra xem foreign key đã tồn tại chưa
     */
    private function foreignKeyExists(string $table, string $column): bool
    {
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = '{$table}' 
            AND COLUMN_NAME = '{$column}' 
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        
        return !empty($foreignKeys);
    }
};
