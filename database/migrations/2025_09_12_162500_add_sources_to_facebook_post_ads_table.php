<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('facebook_post_ads')) {
            Schema::table('facebook_post_ads', function (Blueprint $table) {
                if (!Schema::hasColumn('facebook_post_ads', 'attachment_type')) {
                    $table->string('attachment_type')->nullable()->after('type');
                }
                if (!Schema::hasColumn('facebook_post_ads', 'attachment_image')) {
                    $table->text('attachment_image')->nullable()->after('attachment_type');
                }
                if (!Schema::hasColumn('facebook_post_ads', 'attachment_source')) {
                    $table->text('attachment_source')->nullable()->after('attachment_image');
                }
                if (!Schema::hasColumn('facebook_post_ads', 'from_id')) {
                    $table->string('from_id')->nullable()->after('post_id');
                }
                if (!Schema::hasColumn('facebook_post_ads', 'from_name')) {
                    $table->string('from_name')->nullable()->after('from_id');
                }
                if (!Schema::hasColumn('facebook_post_ads', 'from_picture')) {
                    $table->text('from_picture')->nullable()->after('from_name');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('facebook_post_ads')) {
            Schema::table('facebook_post_ads', function (Blueprint $table) {
                foreach (['attachment_type','attachment_image','attachment_source','from_id','from_name','from_picture'] as $col) {
                    if (Schema::hasColumn('facebook_post_ads', $col)) { $table->dropColumn($col); }
                }
            });
        }
    }
};


