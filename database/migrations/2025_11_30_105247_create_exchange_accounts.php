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
        Schema::create('exchange_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('exchange')->default('bybit'); // Only bybit for now
            $table->string('api_key');
            $table->text('api_secret'); // Will be encrypted
            $table->boolean('is_active')->default(true);
            $table->decimal('balance', 20, 8)->default(0);
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
            
            // Each user can only have one Bybit account
            $table->unique(['user_id', 'exchange']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exchange_accounts');
    }
};