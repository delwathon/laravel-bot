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
        //
    }

    public function handle(): void
    {
        try {
            $accounts = ExchangeAccount::where('is_active', true)->get();

            $results = [
                'total' => $accounts->count(),
                'successful' => 0,
                'failed' => 0,
            ];

            foreach ($accounts as $account) {
                try {
                    $bybit = new BybitService($account->api_key, $account->api_secret);
                    $balance = $bybit->getBalance();

                    $account->update([
                        'balance' => $balance,
                        'last_synced_at' => now(),
                    ]);

                    $results['successful']++;

                } catch (\Exception $e) {
                    $results['failed']++;
                    Log::warning("Failed to sync balance for account {$account->id}: " . $e->getMessage());
                }
            }

            Log::info('Balance sync completed', $results);

        } catch (\Exception $e) {
            Log::error('Balance sync job failed: ' . $e->getMessage());
            throw $e;
        }
    }
}