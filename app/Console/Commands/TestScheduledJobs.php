<?php

namespace App\Console\Commands;

use App\Jobs\GenerateSignalsJob;
use App\Jobs\MonitorPositionsJob;
use App\Jobs\MonitorLimitOrdersJob;
use App\Jobs\ExpireSignalsJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestScheduledJobs extends Command
{
    protected $signature = 'scheduler:test';
    protected $description = 'Test all scheduled jobs manually';

    public function handle()
    {
        Log::info('[TestScheduledJobs] Command started');
        
        $this->info('=== Testing Scheduled Jobs ===');
        $this->newLine();

        // Test 1: Expire Signals Job
        $this->info('1. Testing ExpireSignalsJob...');
        Log::info('[TestScheduledJobs] Testing ExpireSignalsJob');
        
        try {
            ExpireSignalsJob::dispatchSync();
            $this->info('   ✓ ExpireSignalsJob completed');
            Log::info('[TestScheduledJobs] ExpireSignalsJob completed successfully');
        } catch (\Exception $e) {
            $this->error('   ✗ Failed: ' . $e->getMessage());
            Log::error('[TestScheduledJobs] ExpireSignalsJob failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
        $this->newLine();

        // Test 2: Monitor Positions Job
        $this->info('2. Testing MonitorPositionsJob...');
        Log::info('[TestScheduledJobs] Testing MonitorPositionsJob');
        
        try {
            MonitorPositionsJob::dispatchSync();
            $this->info('   ✓ MonitorPositionsJob completed');
            Log::info('[TestScheduledJobs] MonitorPositionsJob completed successfully');
        } catch (\Exception $e) {
            $this->error('   ✗ Failed: ' . $e->getMessage());
            Log::error('[TestScheduledJobs] MonitorPositionsJob failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
        $this->newLine();

        // Test 3: Monitor Limit Orders Job
        $this->info('3. Testing MonitorLimitOrdersJob...');
        Log::info('[TestScheduledJobs] Testing MonitorLimitOrdersJob');
        
        try {
            MonitorLimitOrdersJob::dispatchSync();
            $this->info('   ✓ MonitorLimitOrdersJob completed');
            Log::info('[TestScheduledJobs] MonitorLimitOrdersJob completed successfully');
        } catch (\Exception $e) {
            $this->error('   ✗ Failed: ' . $e->getMessage());
            Log::error('[TestScheduledJobs] MonitorLimitOrdersJob failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
        $this->newLine();

        // Test 4: Generate Signals Job
        $this->info('4. Testing GenerateSignalsJob...');
        $this->warn('   (This may take a few seconds...)');
        Log::info('[TestScheduledJobs] Testing GenerateSignalsJob');
        
        try {
            GenerateSignalsJob::dispatchSync();
            $this->info('   ✓ GenerateSignalsJob completed');
            Log::info('[TestScheduledJobs] GenerateSignalsJob completed successfully');
        } catch (\Exception $e) {
            $this->error('   ✗ Failed: ' . $e->getMessage());
            Log::error('[TestScheduledJobs] GenerateSignalsJob failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
        $this->newLine();

        $this->info('=== All Tests Complete ===');
        $this->newLine();
        
        Log::info('[TestScheduledJobs] Command completed');

        return Command::SUCCESS;
    }
}