<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facebook_page_daily_insights', function (Blueprint $table) {
            if (!Schema::hasColumn('facebook_page_daily_insights', 'ads_messaging_conversation_started')) {
                $table->unsignedBigInteger('ads_messaging_conversation_started')
                    ->default(0)
                    ->after('messages_active_threads')
                    ->comment('Raw daily value from Ads: messaging_conversation_started_7d (aligned to chosen attribution window)');
            }
        });
    }

    public function down(): void
    {
        Schema::table('facebook_page_daily_insights', function (Blueprint $table) {
            if (Schema::hasColumn('facebook_page_daily_insights', 'ads_messaging_conversation_started')) {
                $table->dropColumn('ads_messaging_conversation_started');
            }
        });
    }
};


