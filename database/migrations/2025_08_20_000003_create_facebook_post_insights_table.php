<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fb_post_insights', function (Blueprint $table) {
            $table->id();
            $table->string('post_id')->index(); // ID bài viết
            $table->string('page_id')->index(); // ID trang
            $table->date('date')->index(); // Ngày thống kê
            
            // Các chỉ số cơ bản
            $table->unsignedBigInteger('impressions')->default(0);
            $table->unsignedBigInteger('reach')->default(0);
            $table->unsignedBigInteger('clicks')->default(0);
            $table->unsignedBigInteger('unique_clicks')->default(0);
            
            // Các chỉ số tương tác
            $table->unsignedBigInteger('likes')->default(0);
            $table->unsignedBigInteger('shares')->default(0);
            $table->unsignedBigInteger('comments')->default(0);
            $table->unsignedBigInteger('reactions')->default(0);
            $table->unsignedBigInteger('saves')->default(0);
            $table->unsignedBigInteger('hides')->default(0);
            $table->unsignedBigInteger('hide_all_clicks')->default(0);
            $table->unsignedBigInteger('unlikes')->default(0);
            $table->unsignedBigInteger('negative_feedback')->default(0);
            
            // Các chỉ số video (nếu là video)
            $table->unsignedBigInteger('video_views')->default(0);
            $table->unsignedBigInteger('video_view_time')->default(0); // Thời gian xem (giây)
            $table->unsignedBigInteger('video_avg_time_watched')->default(0); // Thời gian xem trung bình
            $table->unsignedBigInteger('video_p25_watched_actions')->default(0); // Xem 25% video
            $table->unsignedBigInteger('video_p50_watched_actions')->default(0); // Xem 50% video
            $table->unsignedBigInteger('video_p75_watched_actions')->default(0); // Xem 75% video
            $table->unsignedBigInteger('video_p95_watched_actions')->default(0); // Xem 95% video
            $table->unsignedBigInteger('video_p100_watched_actions')->default(0); // Xem 100% video
            
            // Các chỉ số engagement
            $table->decimal('engagement_rate', 8, 4)->nullable();
            $table->decimal('ctr', 8, 4)->nullable();
            $table->decimal('cpm', 14, 4)->nullable();
            $table->decimal('cpc', 14, 4)->nullable();
            
            // Các chỉ số khác
            $table->json('actions')->nullable(); // Các hành động khác
            $table->json('action_values')->nullable(); // Giá trị hành động
            $table->json('cost_per_action_type')->nullable(); // Chi phí theo loại hành động
            $table->json('cost_per_unique_action_type')->nullable(); // Chi phí theo hành động duy nhất
            
            // Thông tin bổ sung
            $table->json('breakdowns')->nullable(); // Dữ liệu breakdown (theo age, gender, etc.)
            $table->timestamp('last_synced_at')->nullable(); // Lần đồng bộ cuối
            
            $table->timestamps();
            
            // Unique constraint để tránh duplicate
            $table->unique(['post_id', 'date']);
            
            // Indexes để tối ưu truy vấn
            $table->index(['page_id', 'date']);
            $table->index(['date', 'impressions']);
            $table->index(['date', 'reach']);
            $table->index(['date', 'engagement_rate']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fb_post_insights');
    }
};
