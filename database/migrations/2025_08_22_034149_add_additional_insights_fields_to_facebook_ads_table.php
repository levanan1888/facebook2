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
        Schema::table('facebook_ads', function (Blueprint $table) {
            // Thêm các trường insights bổ sung
            $table->integer('ad_conversions')->default(0)->after('ad_purchase_roas');
            $table->decimal('ad_conversion_values', 12, 2)->default(0)->after('ad_conversions');
            $table->decimal('ad_cost_per_conversion', 10, 2)->default(0)->after('ad_conversion_values');
            $table->integer('ad_outbound_clicks')->default(0)->after('ad_cost_per_conversion');
            $table->integer('ad_unique_outbound_clicks')->default(0)->after('ad_outbound_clicks');
            $table->integer('ad_inline_link_clicks')->default(0)->after('ad_unique_outbound_clicks');
            $table->integer('ad_unique_inline_link_clicks')->default(0)->after('ad_inline_link_clicks');
            $table->integer('ad_website_clicks')->default(0)->after('ad_unique_inline_link_clicks');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facebook_ads', function (Blueprint $table) {
            $table->dropColumn([
                'ad_conversions',
                'ad_conversion_values',
                'ad_cost_per_conversion',
                'ad_outbound_clicks',
                'ad_unique_outbound_clicks',
                'ad_inline_link_clicks',
                'ad_unique_inline_link_clicks',
                'ad_website_clicks'
            ]);
        });
    }
};
