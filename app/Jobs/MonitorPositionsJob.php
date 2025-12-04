<?php

namespace App\Jobs;

use App\Services\PositionMonitorService;
use Illuminate\Bus\Queueable;
use App\Models\Setting;
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
        Log::info('[MonitorPositionsJob] Job instance created');
    }

    public function handle(PositionMonitorService $monitorService)
    {
        Log::info('[MonitorPositionsJob] Starting execution');
        
        try {
            Log::info('[MonitorPositionsJob] Calling PositionMonitorService->monitorAllPositions()');

            // Check if feature is enabled
            if (!Setting::get('enable_profit_milestones', true)) {
                Log::info('[MonitorPositionsJob] Profit milestones disabled, skipping');
                return;
            }
            
            $results = $monitorService->monitorAllPositions();
            
            Log::info('[MonitorPositionsJob] Position monitoring completed', $results);
            
        } catch (\Exception $e) {
            Log::error('[MonitorPositionsJob] Critical exception occurred', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}