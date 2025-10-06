<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facebook_page_daily_insights', function (Blueprint $table) {
            $table->id();

            // Reference to page in facebook_fanpage
            $table->string('page_id')->index()->comment('Facebook Page ID');
            $table->date('date')->index()->comment('UTC date for the metric (period=day end_time)');

            // Messaging metrics from Page Insights (v23, period=day)
            $table->unsignedBigInteger('messages_new_conversations')->default(0)->comment('page_messages_new_conversations_unique');
            $table->unsignedBigInteger('messages_total_connections')->default(0)->comment('page_messages_total_messaging_connections');
            $table->unsignedBigInteger('messages_active_threads')->default(0)->comment('page_messages_active_threads_unique');

            // Derived metrics by combining Ads paid with Page totals
            $table->unsignedBigInteger('messages_paid_conversations')->default(0)->comment('Derived from ads onsite_conversion.messaging_conversation_started_7d (by day)');
            $table->unsignedBigInteger('messages_organic_conversations')->default(0)->comment('messages_new_conversations - messages_paid_conversations (>=0)');

            $table->timestamps();

            $table->unique(['page_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facebook_page_daily_insights');
    }
};




