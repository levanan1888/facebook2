<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facebook_campaigns', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name')->nullable();
            $table->string('status')->nullable();
            $table->string('objective')->nullable();
            $table->timestamp('start_time')->nullable();
            $table->timestamp('stop_time')->nullable();
            $table->string('effective_status')->nullable();
            $table->string('configured_status')->nullable();
            $table->timestamp('updated_time')->nullable();
            $table->string('ad_account_id')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facebook_campaigns');
    }
};



