<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facebook_fanpage', function (Blueprint $table) {
            // Snapshot of the latest day (for quick overview)
            $table->unsignedBigInteger('msg_new_conversations_day')->default(0)->after('likes_count');
            $table->unsignedBigInteger('msg_paid_conversations_day')->default(0)->after('msg_new_conversations_day');
            $table->unsignedBigInteger('msg_organic_conversations_day')->default(0)->after('msg_paid_conversations_day');

            // Rolling 28d aggregates (computed during sync for fast read)
            $table->unsignedBigInteger('msg_new_conversations_28d')->default(0)->after('msg_organic_conversations_day');
            $table->unsignedBigInteger('msg_paid_conversations_28d')->default(0)->after('msg_new_conversations_28d');
            $table->unsignedBigInteger('msg_organic_conversations_28d')->default(0)->after('msg_paid_conversations_28d');

            $table->timestamp('messages_last_synced_at')->nullable()->after('last_synced_at');
        });
    }

    public function down(): void
    {
        Schema::table('facebook_fanpage', function (Blueprint $table) {
            $table->dropColumn([
                'msg_new_conversations_day',
                'msg_paid_conversations_day',
                'msg_organic_conversations_day',
                'msg_new_conversations_28d',
                'msg_paid_conversations_28d',
                'msg_organic_conversations_28d',
                'messages_last_synced_at',
            ]);
        });
    }
};




