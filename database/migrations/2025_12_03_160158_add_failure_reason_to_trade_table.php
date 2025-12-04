<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('trades') && !Schema::hasColumn('trades', 'failure_reason')) {
            Schema::table('trades', function (Blueprint $table) {
                $table->text('failure_reason')->nullable()->after('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('trades') && Schema::hasColumn('trades', 'failure_reason')) {
            Schema::table('trades', function (Blueprint $table) {
                $table->dropColumn('failure_reason');
            });
        }
    }
};