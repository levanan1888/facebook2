<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fb_posts', function (Blueprint $table) {
            // Foreign key constraints
            $table->foreign('page_id')->references('page_id')->on('fb_pages')->onDelete('cascade');
            $table->foreign('ad_id')->references('id')->on('facebook_ads')->onDelete('set null');
            $table->foreign('campaign_id')->references('id')->on('facebook_campaigns')->onDelete('set null');
            $table->foreign('adset_id')->references('id')->on('facebook_ad_sets')->onDelete('set null');
            $table->foreign('account_id')->references('id')->on('facebook_ad_accounts')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('fb_posts', function (Blueprint $table) {
            $table->dropForeign(['page_id']);
            $table->dropForeign(['ad_id']);
            $table->dropForeign(['campaign_id']);
            $table->dropForeign(['adset_id']);
            $table->dropForeign(['account_id']);
        });
    }
};
