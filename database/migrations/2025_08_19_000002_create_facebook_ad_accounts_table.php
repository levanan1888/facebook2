<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facebook_ad_accounts', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('account_id')->nullable();
            $table->string('name')->nullable();
            $table->integer('account_status')->nullable();
            $table->string('business_id')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facebook_ad_accounts');
    }
};



