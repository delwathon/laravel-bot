<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_exchange_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('exchange')->default('bybit');
            $table->string('api_key');
            $table->text('api_secret');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
            
            $table->unique('exchange');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_exchange_accounts');
    }
};