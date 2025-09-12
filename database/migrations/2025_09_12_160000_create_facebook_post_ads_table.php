<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('facebook_post_ads')) {
            Schema::create('facebook_post_ads', function (Blueprint $table) {
                $table->id();
                $table->string('page_id')->index();
                $table->string('post_id')->index();
                $table->string('time_range')->default('lifetime');
                $table->text('message')->nullable();
                $table->string('type')->nullable();
                $table->text('permalink_url')->nullable();
                $table->timestamp('created_time')->nullable();
                $table->timestamp('updated_time')->nullable();
                $table->json('raw')->nullable();
                $table->timestamps();
                $table->unique(['page_id', 'post_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('facebook_post_ads');
    }
};


