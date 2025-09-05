<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Sửa lỗi JSON column page_id không thể tạo index
     * Chuyển page_id về VARCHAR(50) để có thể thêm foreign key
     */
    public function up(): void
    {
        // Kiểm tra xem cột page_id có phải là JSON không
        $columns = DB::select("SHOW COLUMNS FROM facebook_ads LIKE 'page_id'");
        if (empty($columns)) {
            return; // Cột không tồn tại
        }

        $column = $columns[0];
        
        // Nếu page_id là JSON, chuyển về VARCHAR(50)
        if (str_contains(strtolower($column->Type), 'json')) {
            // Backup dữ liệu JSON trước khi chuyển đổi
            $ads = DB::table('facebook_ads')
                ->whereNotNull('page_id')
                ->where('page_id', '!=', '')
                ->get(['id', 'page_id']);

            // Chuyển cột về VARCHAR(50)
            DB::statement('ALTER TABLE facebook_ads MODIFY COLUMN page_id VARCHAR(50) NULL');
            
            // Khôi phục dữ liệu từ JSON về string
            foreach ($ads as $ad) {
                if (is_string($ad->page_id)) {
                    continue; // Đã là string
                }
                
                $pageId = null;
                if (is_array($ad->page_id)) {
                    // Lấy page_id từ array JSON
                    $pageId = $ad->page_id['page_id'] ?? $ad->page_id['id'] ?? null;
                } elseif (is_object($ad->page_id)) {
                    // Lấy page_id từ object JSON
                    $pageId = $ad->page_id->page_id ?? $ad->page_id->id ?? null;
                }
                
                if ($pageId) {
                    DB::table('facebook_ads')
                        ->where('id', $ad->id)
                        ->update(['page_id' => (string) $pageId]);
                }
            }
        }

        // Đảm bảo cột page_id có kiểu dữ liệu đúng
        if (!Schema::hasColumn('facebook_ads', 'page_id')) {
            Schema::table('facebook_ads', function (Blueprint $table) {
                $table->string('page_id', 50)->nullable();
            });
        }

        // Thêm index cho page_id
        if (!DB::select("SHOW INDEX FROM facebook_ads WHERE Key_name = 'facebook_ads_page_id_index'")) {
            DB::statement('ALTER TABLE facebook_ads ADD INDEX facebook_ads_page_id_index (page_id)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Không cần rollback vì đây là migration sửa lỗi
    }
};

