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
        //
    }

    public function handle(): void
    {
        try {
            $expiredCount = Signal::where('status', 'active')
                ->where('expires_at', '<=', now())
                ->update([
                    'status' => 'expired',
                ]);

            if ($expiredCount > 0) {
                Log::info("Expired {$expiredCount} signal(s)");
            }

        } catch (\Exception $e) {
            Log::error('Signal expiration job failed: ' . $e->getMessage());
            throw $e;
        }
    }
}