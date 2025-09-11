<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('post_facebook_fanpage_not_ads', function (Blueprint $table) {
            $table->timestamp('created_time_video')->nullable()->after('created_time')->comment('Original video created_time if post is a video');
        });
    }

    public function down(): void
    {
        Schema::table('post_facebook_fanpage_not_ads', function (Blueprint $table) {
            $table->dropColumn('created_time_video');
        });
    }
};


