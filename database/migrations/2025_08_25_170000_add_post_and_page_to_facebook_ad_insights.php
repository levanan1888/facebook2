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
        Schema::table('facebook_ad_insights', function (Blueprint $table) {
            if (!Schema::hasColumn('facebook_ad_insights', 'post_id')) {
                $table->string('post_id')->nullable()->after('ad_id')->index();
            }
            if (!Schema::hasColumn('facebook_ad_insights', 'page_id')) {
                $table->string('page_id')->nullable()->after('post_id')->index();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facebook_ad_insights', function (Blueprint $table) {
            if (Schema::hasColumn('facebook_ad_insights', 'page_id')) {
                $table->dropIndex(['page_id']);
                $table->dropColumn('page_id');
            }
            if (Schema::hasColumn('facebook_ad_insights', 'post_id')) {
                $table->dropIndex(['post_id']);
                $table->dropColumn('post_id');
            }
        });
    }
};
