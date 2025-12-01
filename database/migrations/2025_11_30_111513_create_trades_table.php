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
        Schema::create('trades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('signal_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('exchange_account_id')->nullable();
            
            // Trade Details
            $table->string('symbol'); // e.g., BTCUSDT
            $table->string('exchange')->default('bybit');
            $table->enum('type', ['long', 'short']);
            $table->string('exchange_order_id')->nullable(); // Bybit order ID
            
            // Prices
            $table->decimal('entry_price', 20, 8);
            $table->decimal('stop_loss', 20, 8);
            $table->decimal('take_profit', 20, 8);
            $table->decimal('exit_price', 20, 8)->nullable();
            
            // Position Details
            $table->decimal('quantity', 20, 8);
            $table->decimal('leverage', 10, 2)->default(1.00);
            
            // P&L Tracking
            $table->decimal('realized_pnl', 20, 8)->default(0);
            $table->decimal('realized_pnl_percent', 10, 4)->default(0);
            $table->decimal('fees', 20, 8)->default(0);
            
            // Status
            $table->enum('status', ['pending', 'open', 'closed', 'cancelled', 'failed'])->default('pending');
            
            // Timestamps
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'status']);
            $table->index(['symbol', 'status']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trades');
    }
};