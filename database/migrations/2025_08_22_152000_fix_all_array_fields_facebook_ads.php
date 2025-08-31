<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Sửa tất cả các trường có thể chứa array thành JSON cho MySQL
        DB::statement('ALTER TABLE facebook_ads MODIFY COLUMN page_id JSON NULL');
        DB::statement('ALTER TABLE facebook_ads MODIFY COLUMN post_id JSON NULL');
        DB::statement('ALTER TABLE facebook_ads MODIFY COLUMN adset_id JSON NULL');
        DB::statement('ALTER TABLE facebook_ads MODIFY COLUMN campaign_id JSON NULL');
        DB::statement('ALTER TABLE facebook_ads MODIFY COLUMN account_id JSON NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Khôi phục về kiểu VARCHAR
        DB::statement('ALTER TABLE facebook_ads MODIFY COLUMN page_id VARCHAR(50) NULL');
        DB::statement('ALTER TABLE facebook_ads MODIFY COLUMN post_id VARCHAR(50) NULL');
        DB::statement('ALTER TABLE facebook_ads MODIFY COLUMN adset_id VARCHAR(50) NULL');
        DB::statement('ALTER TABLE facebook_ads MODIFY COLUMN campaign_id VARCHAR(50) NULL');
        DB::statement('ALTER TABLE facebook_ads MODIFY COLUMN account_id VARCHAR(50) NULL');
    }
};

