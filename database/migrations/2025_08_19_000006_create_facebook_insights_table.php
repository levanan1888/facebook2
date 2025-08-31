<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facebook_insights', function (Blueprint $table) {
            $table->id();
            $table->string('level'); // account|adset|ad
            $table->string('ref_id')->index(); // id of account/adset/ad
            $table->date('date')->nullable();
            $table->decimal('spend', 14, 2)->nullable();
            $table->unsignedBigInteger('reach')->nullable();
            $table->unsignedBigInteger('impressions')->nullable();
            $table->unsignedBigInteger('clicks')->nullable();
            $table->decimal('ctr', 8, 4)->nullable();
            $table->decimal('cpc', 14, 4)->nullable();
            $table->decimal('cpm', 14, 4)->nullable();
            $table->decimal('frequency', 8, 4)->nullable();
            $table->unsignedBigInteger('unique_clicks')->nullable();
            $table->json('actions')->nullable(); // hành động (mua hàng, add_to_cart...)
            $table->json('action_values')->nullable(); // giá trị hành động (ví dụ purchase)
            $table->json('purchase_roas')->nullable(); // ROAS từ API
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facebook_insights');
    }
};



