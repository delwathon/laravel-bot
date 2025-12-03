<?php

namespace App\Jobs;

use App\Models\Signal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ExpireSignalsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 60;

    public function __construct()
    {
        Log::info('[ExpireSignalsJob] Job instance created');
    }

    public function handle(): void
    {
        Log::info('[ExpireSignalsJob] Starting execution');
        
        try {
            Log::info('[ExpireSignalsJob] Querying for active signals past expiry time');
            
            $expiredCount = Signal::where('status', 'active')
                ->where('expires_at', '<=', now())
                ->update([
                    'status' => 'expired',
                ]);

            Log::info("[ExpireSignalsJob] Query executed, found {$expiredCount} signal(s) to expire");

            if ($expiredCount > 0) {
                Log::info("[ExpireSignalsJob] Successfully expired {$expiredCount} signal(s)");
            } else {
                Log::info('[ExpireSignalsJob] No signals needed expiration');
            }

            Log::info('[ExpireSignalsJob] Execution completed successfully');

        } catch (\Exception $e) {
            Log::error('[ExpireSignalsJob] Exception occurred', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}