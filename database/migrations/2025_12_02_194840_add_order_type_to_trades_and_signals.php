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
        // Add order_type to trades table if not exists
        if (Schema::hasTable('trades') && !Schema::hasColumn('trades', 'order_type')) {
            Schema::table('trades', function (Blueprint $table) {
                $table->string('order_type')->default('Market')->after('type');
            });
        }

        // Add order_type to signals table if not exists  
        if (Schema::hasTable('signals') && !Schema::hasColumn('signals', 'order_type')) {
            Schema::table('signals', function (Blueprint $table) {
                $table->string('order_type')->default('Market')->after('type');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('trades') && Schema::hasColumn('trades', 'order_type')) {
            Schema::table('trades', function (Blueprint $table) {
                $table->dropColumn('order_type');
            });
        }

        if (Schema::hasTable('signals') && Schema::hasColumn('signals', 'order_type')) {
            Schema::table('signals', function (Blueprint $table) {
                $table->dropColumn('order_type');
            });
        }
    }
};