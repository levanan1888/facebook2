<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facebook_breakdowns', function (Blueprint $table) {
            $table->index('ad_insight_id', 'fb_bd_ad_insight_id_idx');
            $table->index(['breakdown_type', 'breakdown_value'], 'fb_bd_type_value_idx');
            $table->index(['ad_insight_id', 'breakdown_type'], 'fb_bd_adinsight_type_idx');
        });
    }

    public function down(): void
    {
        Schema::table('facebook_breakdowns', function (Blueprint $table) {
            $table->dropIndex('fb_bd_ad_insight_id_idx');
            $table->dropIndex('fb_bd_type_value_idx');
            $table->dropIndex('fb_bd_adinsight_type_idx');
        });
    }
};


