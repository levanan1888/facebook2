<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fb_posts', function (Blueprint $table) {
            $table->id();
            $table->string('post_id')->unique()->index(); // ID bài viết từ Facebook
            $table->string('page_id')->index(); // ID trang Facebook
            $table->string('ad_id')->nullable()->index(); // Liên kết với ad nếu có
            $table->string('campaign_id')->nullable()->index(); // Liên kết với campaign
            $table->string('adset_id')->nullable()->index(); // Liên kết với adset
            $table->string('account_id')->nullable()->index(); // Liên kết với ad account
            
            // Thông tin cơ bản của bài viết
            $table->text('message')->nullable(); // Nội dung bài viết
            $table->string('type')->nullable(); // Loại bài viết (photo, video, link, etc.)
            $table->string('status_type')->nullable(); // Trạng thái bài viết
            $table->json('attachments')->nullable(); // Thông tin đính kèm (hình ảnh, video)
            $table->json('permalink_url')->nullable(); // URL bài viết
            $table->timestamp('created_time')->nullable(); // Thời gian tạo
            $table->timestamp('updated_time')->nullable(); // Thời gian cập nhật
            
            // Các chỉ số tương tác
            $table->unsignedBigInteger('likes_count')->default(0);
            $table->unsignedBigInteger('shares_count')->default(0);
            $table->unsignedBigInteger('comments_count')->default(0);
            $table->unsignedBigInteger('reactions_count')->default(0);
            $table->unsignedBigInteger('impressions')->default(0);
            $table->unsignedBigInteger('reach')->default(0);
            $table->unsignedBigInteger('clicks')->default(0);
            $table->decimal('engagement_rate', 8, 4)->nullable();
            
            // Thông tin bổ sung
            $table->json('insights_data')->nullable(); // Dữ liệu insights chi tiết
            $table->boolean('is_promoted')->default(false); // Có phải bài quảng cáo không
            $table->string('sync_status')->default('pending'); // Trạng thái đồng bộ
            $table->timestamp('last_synced_at')->nullable(); // Lần đồng bộ cuối
            
            $table->timestamps();
            
            // Indexes để tối ưu truy vấn
            $table->index(['page_id', 'created_time']);
            $table->index(['ad_id', 'created_time']);
            $table->index(['sync_status', 'last_synced_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fb_posts');
    }
};
