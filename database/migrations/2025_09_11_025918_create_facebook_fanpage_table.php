<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facebook_fanpage', function (Blueprint $table) {
            $table->id();

            // Core identifiers
            $table->string('page_id')->unique();
            $table->string('name')->nullable();

            // Access token per page
            $table->text('access_token')->nullable();

            // Profile & metadata
            $table->string('category')->nullable();
            $table->json('category_list')->nullable();
            $table->text('about')->nullable();
            $table->text('website')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('location')->nullable();
            $table->text('cover_photo_url')->nullable();
            $table->text('profile_picture_url')->nullable();

            // Stats & flags
            $table->boolean('is_published')->default(true);
            $table->boolean('is_verified')->default(false);
            $table->unsignedBigInteger('fan_count')->default(0);
            $table->unsignedBigInteger('followers_count')->default(0);
            $table->unsignedBigInteger('likes_count')->default(0);
            $table->timestamp('last_synced_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facebook_fanpage');
    }
};
