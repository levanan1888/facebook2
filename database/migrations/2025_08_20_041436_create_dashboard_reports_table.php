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
        Schema::create('dashboard_reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_type'); // overview, analytics, hierarchy
            $table->json('data'); // Dữ liệu báo cáo dạng JSON
            $table->timestamp('last_updated');
            $table->timestamps();
            
            $table->index(['report_type', 'last_updated']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dashboard_reports');
    }
};
