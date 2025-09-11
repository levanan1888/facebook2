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
        Schema::create('post_facebook_fanpage_not_ads', function (Blueprint $table) {
            $table->id();
            $table->string('post_id')->unique()->comment('Facebook Post ID');
            $table->string('page_id')->comment('Facebook Page ID');
            $table->text('message')->nullable()->comment('Post message/content');
            $table->text('story')->nullable()->comment('Post story');
            $table->string('type')->nullable()->comment('Post type (photo, video, link, etc.)');
            $table->string('status_type')->nullable()->comment('Status type');
            $table->string('link')->nullable()->comment('Post link');
            $table->string('picture')->nullable()->comment('Post picture URL');
            $table->string('full_picture')->nullable()->comment('Full picture URL');
            $table->string('source')->nullable()->comment('Video source URL');
            $table->text('description')->nullable()->comment('Post description');
            $table->string('caption')->nullable()->comment('Post caption');
            $table->string('name')->nullable()->comment('Post name');
            $table->json('attachments')->nullable()->comment('Post attachments');
            $table->json('properties')->nullable()->comment('Post properties');
            $table->boolean('is_published')->default(true)->comment('Is post published');
            $table->boolean('is_hidden')->default(false)->comment('Is post hidden');
            $table->boolean('is_expired')->default(false)->comment('Is post expired');
            $table->timestamp('created_time')->comment('Post creation time');
            $table->timestamp('updated_time')->nullable()->comment('Post update time');
            
            // Insights/Metrics
            $table->integer('post_impressions')->default(0)->comment('Post impressions');
            $table->integer('post_engaged_users')->default(0)->comment('Post engaged users');
            $table->integer('post_clicks')->default(0)->comment('Post clicks');
            $table->integer('post_reactions')->default(0)->comment('Post reactions');
            $table->integer('post_comments')->default(0)->comment('Post comments');
            $table->integer('post_shares')->default(0)->comment('Post shares');
            $table->integer('post_video_views')->default(0)->comment('Post video views');
            $table->integer('post_video_complete_views')->default(0)->comment('Post video complete views');
            $table->json('insights_data')->nullable()->comment('Full insights data');
            $table->timestamp('insights_synced_at')->nullable()->comment('Insights sync time');
            $table->timestamp('last_synced_at')->nullable()->comment('Last sync time');
            $table->timestamps();
            
            $table->index(['post_id']);
            $table->index(['page_id']);
            $table->index(['created_time']);
            $table->index(['type']);
            $table->index(['last_synced_at']);
            
            $table->foreign('page_id')->references('page_id')->on('facebook_fanpage')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_facebook_fanpage_not_ads');
    }
};
