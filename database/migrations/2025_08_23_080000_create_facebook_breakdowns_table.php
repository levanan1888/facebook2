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
        Schema::create('facebook_breakdowns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ad_insight_id');
            $table->string('breakdown_type'); // age, gender, region, platform_position, action_type, etc.
            $table->string('breakdown_value'); // 18-24, male, US, facebook_feed, etc.
            $table->json('metrics'); // impressions, reach, clicks, spend, etc.
            $table->timestamps();

            $table->foreign('ad_insight_id')->references('id')->on('facebook_ad_insights')->onDelete('cascade');
            $table->index(['ad_insight_id', 'breakdown_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facebook_breakdowns');
    }
};





