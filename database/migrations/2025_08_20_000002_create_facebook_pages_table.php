<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fb_pages', function (Blueprint $table) {
            $table->id();
            $table->string('page_id')->unique()->index(); // ID trang từ Facebook
            $table->string('name'); // Tên trang
            $table->string('username')->nullable(); // Username của trang
            $table->text('about')->nullable(); // Thông tin về trang
            $table->string('category')->nullable(); // Danh mục trang
            $table->string('category_list')->nullable(); // Danh sách danh mục
            $table->string('fan_count')->nullable(); // Số lượng fan
            $table->string('followers_count')->nullable(); // Số lượng followers
            $table->string('rating_count')->nullable(); // Số lượng đánh giá
            $table->decimal('rating', 3, 2)->nullable(); // Điểm đánh giá trung bình
            $table->string('verification_status')->nullable(); // Trạng thái xác minh
            $table->string('website')->nullable(); // Website
            $table->string('phone')->nullable(); // Số điện thoại
            $table->string('emails')->nullable(); // Email
            $table->json('hours')->nullable(); // Giờ hoạt động
            $table->json('location')->nullable(); // Thông tin địa chỉ
            $table->string('cover_photo')->nullable(); // Ảnh bìa
            $table->string('profile_picture')->nullable(); // Ảnh đại diện
            $table->boolean('is_verified')->default(false); // Đã xác minh chưa
            $table->boolean('is_published')->default(true); // Trang có đang hoạt động không
            $table->timestamp('created_time')->nullable(); // Thời gian tạo trang
            $table->timestamp('last_synced_at')->nullable(); // Lần đồng bộ cuối
            
            $table->timestamps();
            
            // Indexes
            $table->index(['category', 'fan_count']);
            $table->index(['is_verified', 'is_published']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fb_pages');
    }
};
