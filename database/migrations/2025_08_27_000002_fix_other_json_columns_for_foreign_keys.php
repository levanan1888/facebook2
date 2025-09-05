<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Sửa các cột JSON khác cần foreign key constraints
     * Chuyển về VARCHAR(50) để có thể thêm foreign key
     */
    public function up(): void
    {
        $columnsToFix = [
            'post_id' => 'facebook_posts',
            'adset_id' => 'facebook_ad_sets', 
            'campaign_id' => 'facebook_campaigns',
            'account_id' => 'facebook_ad_accounts'
        ];

        foreach ($columnsToFix as $columnName => $referencedTable) {
            // Kiểm tra xem cột có tồn tại không
            $columns = DB::select("SHOW COLUMNS FROM facebook_ads LIKE '{$columnName}'");
            if (empty($columns)) {
                continue;
            }

            $column = $columns[0];
            
            // Nếu cột là JSON, chuyển về VARCHAR(50)
            if (str_contains(strtolower($column->Type), 'json')) {
                // Backup dữ liệu JSON trước khi chuyển đổi
                $ads = DB::table('facebook_ads')
                    ->whereNotNull($columnName)
                    ->where($columnName, '!=', '')
                    ->get(['id', $columnName]);

                // Chuyển cột về VARCHAR(50)
                DB::statement("ALTER TABLE facebook_ads MODIFY COLUMN {$columnName} VARCHAR(50) NULL");
                
                // Khôi phục dữ liệu từ JSON về string
                foreach ($ads as $ad) {
                    $value = $ad->{$columnName};
                    if (is_string($value)) {
                        continue; // Đã là string
                    }
                    
                    $stringValue = null;
                    if (is_array($value)) {
                        // Lấy ID từ array JSON
                        $stringValue = $value['id'] ?? $value[$columnName] ?? null;
                    } elseif (is_object($value)) {
                        // Lấy ID từ object JSON
                        $stringValue = $value->id ?? $value->{$columnName} ?? null;
                    }
                    
                    if ($stringValue) {
                        DB::table('facebook_ads')
                            ->where('id', $ad->id)
                            ->update([$columnName => (string) $stringValue]);
                    }
                }
            }

            // Đảm bảo cột có kiểu dữ liệu đúng
            if (!Schema::hasColumn('facebook_ads', $columnName)) {
                Schema::table('facebook_ads', function (Blueprint $table) use ($columnName) {
                    $table->string($columnName, 50)->nullable();
                });
            }

            // Thêm index cho cột
            $indexName = "facebook_ads_{$columnName}_index";
            if (!DB::select("SHOW INDEX FROM facebook_ads WHERE Key_name = '{$indexName}'")) {
                DB::statement("ALTER TABLE facebook_ads ADD INDEX {$indexName} ({$columnName})");
            }
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



