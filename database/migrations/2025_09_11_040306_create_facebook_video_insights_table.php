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
        Schema::create('facebook_video_insights', function (Blueprint $table) {
            $table->id();
            $table->string('post_id')->index()->comment('Reference to post_facebook_fanpage_not_ads');
            $table->string('video_id')->nullable()->comment('Facebook video ID');
            $table->integer('video_views_autoplayed')->default(0)->comment('Auto-played video views');
            $table->integer('video_views_clicked_to_play')->default(0)->comment('Click-to-play video views');
            $table->integer('video_views_unique')->default(0)->comment('Unique video views');
            $table->decimal('video_avg_time_watched', 10, 2)->default(0)->comment('Average time watched in seconds');
            $table->integer('video_complete_views')->default(0)->comment('Complete video views');
            $table->json('video_retention_graph')->nullable()->comment('Video retention graph data');
            $table->json('video_play_actions')->nullable()->comment('Video play actions data');
            $table->timestamps();
            
            $table->foreign('post_id')->references('post_id')->on('post_facebook_fanpage_not_ads')->onDelete('cascade');
            $table->index(['post_id', 'video_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facebook_video_insights');
    }
};
