<?php

namespace App\Jobs;

use App\Models\ExchangeAccount;
use App\Services\BybitService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncUserBalancesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;

    public function __construct()
    {
        Log::info('[SyncUserBalancesJob] Job instance created');
    }

    public function handle(): void
    {
        Log::info('[SyncUserBalancesJob] Starting execution');
        
        try {
            Log::info('[SyncUserBalancesJob] Querying for active exchange accounts');
            
            $accounts = ExchangeAccount::where('is_active', true)->get();

            Log::info('[SyncUserBalancesJob] Query completed', [
                'accounts_count' => $accounts->count()
            ]);

            $results = [
                'total' => $accounts->count(),
                'successful' => 0,
                'failed' => 0,
            ];

            foreach ($accounts as $account) {
                Log::info("[SyncUserBalancesJob] Processing account {$account->id}", [
                    'user_id' => $account->user_id,
                    'exchange' => $account->exchange
                ]);
                
                try {
                    Log::info("[SyncUserBalancesJob] Initializing BybitService for account {$account->id}");
                    $bybit = new BybitService($account->api_key, $account->api_secret);
                    
                    Log::info("[SyncUserBalancesJob] Fetching balance from Bybit for account {$account->id}");
                    $balance = $bybit->getBalance();

                    Log::info("[SyncUserBalancesJob] Balance fetched successfully", [
                        'account_id' => $account->id,
                        'balance' => $balance
                    ]);

                    $account->update([
                        'balance' => $balance,
                        'last_synced_at' => now(),
                    ]);

                    Log::info("[SyncUserBalancesJob] Account {$account->id} balance updated in database");

                    $results['successful']++;

                } catch (\Exception $e) {
                    $results['failed']++;
                    Log::warning("[SyncUserBalancesJob] Failed to sync balance for account {$account->id}", [
                        'error' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ]);
                }
            }

            Log::info('[SyncUserBalancesJob] Balance sync completed', $results);

        } catch (\Exception $e) {
            Log::error('[SyncUserBalancesJob] Critical exception occurred', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}