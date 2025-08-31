<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facebook_insights', function (Blueprint $table) {
            if (!Schema::hasColumn('facebook_insights', 'action_values')) {
                $table->json('action_values')->nullable()->after('actions');
            }
            if (!Schema::hasColumn('facebook_insights', 'purchase_roas')) {
                $table->json('purchase_roas')->nullable()->after('action_values');
            }
        });
    }

    public function down(): void
    {
        Schema::table('facebook_insights', function (Blueprint $table) {
            if (Schema::hasColumn('facebook_insights', 'purchase_roas')) {
                $table->dropColumn('purchase_roas');
            }
            if (Schema::hasColumn('facebook_insights', 'action_values')) {
                $table->dropColumn('action_values');
            }
        });
    }
};


