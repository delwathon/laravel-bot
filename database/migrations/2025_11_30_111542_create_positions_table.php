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
        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('trade_id')->constrained()->onDelete('cascade');
            $table->foreignId('exchange_account_id')->constrained()->onDelete('cascade');
            
            // Position Details
            $table->string('symbol'); // e.g., BTCUSDT
            $table->string('exchange')->default('bybit');
            $table->enum('side', ['long', 'short']);
            $table->string('exchange_position_id')->nullable(); // Bybit position ID
            
            // Position Metrics
            $table->decimal('entry_price', 20, 8);
            $table->decimal('current_price', 20, 8);
            $table->decimal('quantity', 20, 8);
            $table->decimal('leverage', 10, 2)->default(1.00);
            
            // Stop Loss & Take Profit
            $table->decimal('stop_loss', 20, 8);
            $table->decimal('take_profit', 20, 8);
            
            // P&L Tracking (Unrealized)
            $table->decimal('unrealized_pnl', 20, 8)->default(0);
            $table->decimal('unrealized_pnl_percent', 10, 4)->default(0);
            $table->decimal('margin_used', 20, 8)->default(0);
            
            // Status
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_updated_at')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'is_active']);
            $table->index(['symbol', 'is_active']);
            $table->index('exchange_position_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('positions');
    }
};