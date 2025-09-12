<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facebook_ad_insights', function (Blueprint $table) {
            // Truy vấn theo ad và ngày
            $table->index(['ad_id', 'date'], 'fai_ad_id_date_idx');
            // Truy vấn theo post/page
            $table->index('post_id', 'fai_post_id_idx');
            $table->index('page_id', 'fai_page_id_idx');
            $table->index(['page_id', 'post_id'], 'fai_page_post_idx');
            // Bộ lọc theo ngày
            $table->index('date', 'fai_date_idx');
        });
    }

    public function down(): void
    {
        Schema::table('facebook_ad_insights', function (Blueprint $table) {
            $table->dropIndex('fai_ad_id_date_idx');
            $table->dropIndex('fai_post_id_idx');
            $table->dropIndex('fai_page_id_idx');
            $table->dropIndex('fai_page_post_idx');
            $table->dropIndex('fai_date_idx');
        });
    }
};


