<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('facebook_ad_insights', function (Blueprint $table) {
            $table->integer('video_30_sec_watched')->default(0)->after('thruplays');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facebook_ad_insights', function (Blueprint $table) {
            $table->dropColumn('video_30_sec_watched');
        });
    }
};
