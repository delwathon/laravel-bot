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
        Schema::table('positions', function (Blueprint $table) {
            // Add metadata column to store milestone tracking data
            if (!Schema::hasColumn('positions', 'metadata')) {
                $table->json('metadata')->nullable()->after('last_updated_at');
            }
        });
        
        Schema::table('trades', function (Blueprint $table) {
            // Add failure_reason column if not exists (for better error tracking)
            if (!Schema::hasColumn('trades', 'failure_reason')) {
                $table->text('failure_reason')->nullable()->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            if (Schema::hasColumn('positions', 'metadata')) {
                $table->dropColumn('metadata');
            }
        });
        
        Schema::table('trades', function (Blueprint $table) {
            if (Schema::hasColumn('trades', 'failure_reason')) {
                $table->dropColumn('failure_reason');
            }
        });
    }
};