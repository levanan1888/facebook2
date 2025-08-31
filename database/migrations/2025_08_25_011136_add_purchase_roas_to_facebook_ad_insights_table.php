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
            if (!Schema::hasColumn('facebook_ad_insights', 'purchase_roas')) {
                $table->decimal('purchase_roas', 10, 4)->nullable()->after('cost_per_conversion');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facebook_ad_insights', function (Blueprint $table) {
            if (Schema::hasColumn('facebook_ad_insights', 'purchase_roas')) {
                $table->dropColumn('purchase_roas');
            }
        });
    }
};
