<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Ensure post_id column exists
        Schema::table('facebook_ad_insights', function (Blueprint $table) {
            if (!Schema::hasColumn('facebook_ad_insights', 'post_id')) {
                $table->string('post_id')->nullable()->after('ad_id')->index();
            }
        });

        // 2) Deduplicate by (ad_id, post_id, date) keeping the newest by updated_at
        DB::statement(<<<SQL
DELETE t1 FROM facebook_ad_insights t1
JOIN facebook_ad_insights t2
  ON t1.ad_id = t2.ad_id
 AND COALESCE(t1.post_id,'') = COALESCE(t2.post_id,'')
 AND t1.date = t2.date
 AND (t1.updated_at < t2.updated_at OR (t1.updated_at = t2.updated_at AND t1.id < t2.id));
SQL);

        // 3) Drop old unique(ad_id, date) if exists, then add unique(ad_id, post_id, date)
        Schema::table('facebook_ad_insights', function (Blueprint $table) {
            // Drop potential old unique
            try {
                $table->dropUnique('facebook_ad_insights_ad_id_date_unique');
            } catch (\Throwable $e) {
                // ignore if not exists
            }

            // Add new unique with explicit name
            $table->unique(['ad_id', 'post_id', 'date'], 'uniq_ad_post_date');
        });
    }

    public function down(): void
    {
        Schema::table('facebook_ad_insights', function (Blueprint $table) {
            try {
                $table->dropUnique('uniq_ad_post_date');
            } catch (\Throwable $e) {
                // ignore
            }
        });
    }
};


