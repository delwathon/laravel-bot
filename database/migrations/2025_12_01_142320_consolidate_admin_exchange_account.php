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
        // Add is_admin column to exchange_accounts
        Schema::table('exchange_accounts', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false)->after('user_id');
        });

        // Migrate data from admin_exchange_accounts to exchange_accounts
        if (Schema::hasTable('admin_exchange_accounts')) {
            $adminAccounts = DB::table('admin_exchange_accounts')->get();
            
            foreach ($adminAccounts as $adminAccount) {
                // Get the first admin user
                $adminUser = DB::table('users')->where('is_admin', true)->first();
                
                if ($adminUser) {
                    // Check if this admin already has an exchange account for this exchange
                    $existing = DB::table('exchange_accounts')
                        ->where('user_id', $adminUser->id)
                        ->where('exchange', $adminAccount->exchange)
                        ->first();
                    
                    if (!$existing) {
                        DB::table('exchange_accounts')->insert([
                            'user_id' => $adminUser->id,
                            'exchange' => $adminAccount->exchange,
                            'api_key' => $adminAccount->api_key,
                            'api_secret' => $adminAccount->api_secret,
                            'is_active' => $adminAccount->is_active,
                            'is_admin' => true,
                            'balance' => 0,
                            'last_synced_at' => $adminAccount->last_synced_at,
                            'created_at' => $adminAccount->created_at,
                            'updated_at' => $adminAccount->updated_at,
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove admin exchange accounts from exchange_accounts
        DB::table('exchange_accounts')->where('is_admin', true)->delete();
        
        // Remove is_admin column
        Schema::table('exchange_accounts', function (Blueprint $table) {
            $table->dropColumn('is_admin');
        });
    }
};