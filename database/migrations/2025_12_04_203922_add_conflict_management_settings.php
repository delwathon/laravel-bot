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
        // Settings are stored in key-value format, so we just need to seed them
        // No schema changes needed
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Settings will be removed via seeder rollback if needed
    }
};