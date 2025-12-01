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
        // Drop the admin_exchange_accounts table as we've migrated to exchange_accounts
        Schema::dropIfExists('admin_exchange_accounts');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate admin_exchange_accounts table
        Schema::create('admin_exchange_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('exchange')->unique();
            $table->string('api_key');
            $table->text('api_secret');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });
    }
};