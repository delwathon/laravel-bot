<?php

namespace App\Jobs;

use App\Services\PositionMonitorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MonitorPositionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        //
    }

    public function handle(PositionMonitorService $monitorService)
    {
        try {
            $results = $monitorService->monitorAllPositions();
            
            Log::info('Position monitoring completed', $results);
        } catch (\Exception $e) {
            Log::error('Position monitoring job failed: ' . $e->getMessage());
        }
    }
}