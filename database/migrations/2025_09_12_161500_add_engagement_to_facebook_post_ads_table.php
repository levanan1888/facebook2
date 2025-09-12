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
                if (!Schema::hasColumn('facebook_post_ads', 'likes_count')) {
                    $table->unsignedBigInteger('likes_count')->default(0)->after('updated_time');
                }
                if (!Schema::hasColumn('facebook_post_ads', 'comments_count')) {
                    $table->unsignedBigInteger('comments_count')->default(0)->after('likes_count');
                }
                if (!Schema::hasColumn('facebook_post_ads', 'shares_count')) {
                    $table->unsignedBigInteger('shares_count')->default(0)->after('comments_count');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('facebook_post_ads')) {
            Schema::table('facebook_post_ads', function (Blueprint $table) {
                if (Schema::hasColumn('facebook_post_ads', 'likes_count')) {
                    $table->dropColumn('likes_count');
                }
                if (Schema::hasColumn('facebook_post_ads', 'comments_count')) {
                    $table->dropColumn('comments_count');
                }
                if (Schema::hasColumn('facebook_post_ads', 'shares_count')) {
                    $table->dropColumn('shares_count');
                }
            });
        }
    }
};


