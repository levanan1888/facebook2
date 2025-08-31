<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facebook_ads', function (Blueprint $table) {
            if (!Schema::hasColumn('facebook_ads', 'page_meta')) {
                $table->json('page_meta')->nullable()->after('page_id');
            }
            if (!Schema::hasColumn('facebook_ads', 'post_meta')) {
                $table->json('post_meta')->nullable()->after('post_id');
            }
            if (!Schema::hasColumn('facebook_ads', 'creative_json')) {
                $table->json('creative_json')->nullable()->after('last_insights_sync');
            }
        });
    }

    public function down(): void
    {
        Schema::table('facebook_ads', function (Blueprint $table) {
            if (Schema::hasColumn('facebook_ads', 'page_meta')) {
                $table->dropColumn('page_meta');
            }
            if (Schema::hasColumn('facebook_ads', 'post_meta')) {
                $table->dropColumn('post_meta');
            }
            if (Schema::hasColumn('facebook_ads', 'creative_json')) {
                $table->dropColumn('creative_json');
            }
        });
    }
};




