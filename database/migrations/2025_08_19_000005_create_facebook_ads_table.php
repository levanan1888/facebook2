<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facebook_ads', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name')->nullable();
            $table->string('status')->nullable();
            $table->string('effective_status')->nullable();
            $table->string('adset_id')->nullable()->index();
            $table->string('campaign_id')->nullable()->index();
            $table->string('account_id')->nullable()->index();
            $table->json('creative')->nullable();
            $table->timestamp('created_time')->nullable();
            $table->timestamp('updated_time')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facebook_ads');
    }
};



