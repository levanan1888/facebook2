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
        Schema::create('facebook_daily_insights', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('post_id')->comment('Post ID from post_facebook_fanpage_not_ads');
            $table->string('metric_name')->comment('Metric name (e.g., post_impressions, post_clicks)');
            $table->date('date')->comment('Date of the insight');
            $table->bigInteger('metric_value')->default(0)->comment('Metric value for the day');
            $table->timestamps();
            
            // Indexes
            $table->index(['post_id', 'date']);
            $table->index(['metric_name', 'date']);
            $table->unique(['post_id', 'metric_name', 'date'], 'unique_post_metric_date');
            
            // Foreign key
            $table->foreign('post_id')->references('id')->on('post_facebook_fanpage_not_ads')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facebook_daily_insights');
    }
};