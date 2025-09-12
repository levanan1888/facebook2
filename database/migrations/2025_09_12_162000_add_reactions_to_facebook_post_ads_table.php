<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('facebook_post_ads') && !Schema::hasColumn('facebook_post_ads', 'reactions_count')) {
            Schema::table('facebook_post_ads', function (Blueprint $table) {
                $table->unsignedBigInteger('reactions_count')->default(0)->after('shares_count');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('facebook_post_ads') && Schema::hasColumn('facebook_post_ads', 'reactions_count')) {
            Schema::table('facebook_post_ads', function (Blueprint $table) {
                $table->dropColumn('reactions_count');
            });
        }
    }
};


