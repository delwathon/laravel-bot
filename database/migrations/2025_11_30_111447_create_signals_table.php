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
        Schema::create('signals', function (Blueprint $table) {
            $table->id();
            $table->string('symbol'); // e.g., BTC/USDT
            $table->string('exchange')->default('bybit');
            $table->enum('type', ['long', 'short']);
            $table->enum('timeframe', ['1m', '5m', '15m', '30m', '1h', '4h', '1d'])->default('15m');
            
            // SMC Analysis Data
            $table->string('pattern'); // e.g., Order Block, FVG, Liquidity Sweep
            $table->decimal('confidence', 5, 2); // 0-100
            
            // Price Levels
            $table->decimal('entry_price', 20, 8);
            $table->decimal('stop_loss', 20, 8);
            $table->decimal('take_profit', 20, 8);
            
            // Risk Management
            $table->decimal('risk_reward_ratio', 10, 2);
            $table->decimal('position_size_percent', 5, 2)->default(5.00); // % of capital
            
            // Status
            $table->enum('status', ['pending', 'active', 'executed', 'expired', 'cancelled'])->default('pending');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('executed_at')->nullable();
            
            // Metadata
            $table->json('analysis_data')->nullable(); // Store detailed SMC analysis
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['symbol', 'status']);
            $table->index('created_at');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('signals');
    }
};