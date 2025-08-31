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
        Schema::table('facebook_ad_accounts', function (Blueprint $table) {
            $table->timestamp('created_time')->nullable()->after('business_id');
            $table->timestamp('updated_time')->nullable()->after('created_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facebook_ad_accounts', function (Blueprint $table) {
            $table->dropColumn(['created_time', 'updated_time']);
        });
    }
};
