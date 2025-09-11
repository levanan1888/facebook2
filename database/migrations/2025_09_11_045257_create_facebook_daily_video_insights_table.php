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
        Schema::create('facebook_daily_video_insights', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('post_id')->comment('Post ID from post_facebook_fanpage_not_ads');
            $table->string('video_id')->comment('Facebook Video ID');
            $table->string('metric_name')->comment('Video metric name (e.g., video_views, video_complete_views)');
            $table->date('date')->comment('Date of the insight');
            $table->bigInteger('metric_value')->default(0)->comment('Metric value for the day');
            $table->timestamps();
            
            // Indexes
            $table->index(['post_id', 'date']);
            $table->index(['video_id', 'date']);
            $table->index(['metric_name', 'date']);
            $table->unique(['post_id', 'video_id', 'metric_name', 'date'], 'unique_post_video_metric_date');
            
            // Foreign keys
            $table->foreign('post_id')->references('id')->on('post_facebook_fanpage_not_ads')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facebook_daily_video_insights');
    }
};