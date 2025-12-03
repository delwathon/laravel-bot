<?php

namespace App\Console\Commands;

use App\Jobs\GenerateSignalsJob;
use App\Jobs\MonitorPositionsJob;
use App\Jobs\MonitorLimitOrdersJob;
use App\Jobs\ExpireSignalsJob;
use Illuminate\Console\Command;

class TestScheduledJobs extends Command
{
    protected $signature = 'scheduler:test';
    protected $description = 'Test all scheduled jobs manually';

    public function handle()
    {
        $this->info('=== Testing Scheduled Jobs ===');
        $this->newLine();

        // Test 1: Expire Signals Job
        $this->info('1. Testing ExpireSignalsJob...');
        try {
            ExpireSignalsJob::dispatchSync();
            $this->info('   ✓ ExpireSignalsJob completed');
        } catch (\Exception $e) {
            $this->error('   ✗ Failed: ' . $e->getMessage());
        }
        $this->newLine();

        // Test 2: Monitor Positions Job
        $this->info('2. Testing MonitorPositionsJob...');
        try {
            MonitorPositionsJob::dispatchSync();
            $this->info('   ✓ MonitorPositionsJob completed');
        } catch (\Exception $e) {
            $this->error('   ✗ Failed: ' . $e->getMessage());
        }
        $this->newLine();

        // Test 3: Monitor Limit Orders Job
        $this->info('3. Testing MonitorLimitOrdersJob...');
        try {
            MonitorLimitOrdersJob::dispatchSync();
            $this->info('   ✓ MonitorLimitOrdersJob completed');
        } catch (\Exception $e) {
            $this->error('   ✗ Failed: ' . $e->getMessage());
        }
        $this->newLine();

        // Test 4: Generate Signals Job
        $this->info('4. Testing GenerateSignalsJob...');
        $this->warn('   (This may take a few seconds...)');
        try {
            GenerateSignalsJob::dispatchSync();
            $this->info('   ✓ GenerateSignalsJob completed');
        } catch (\Exception $e) {
            $this->error('   ✗ Failed: ' . $e->getMessage());
        }
        $this->newLine();

        $this->info('=== All Tests Complete ===');
        $this->newLine();

        return Command::SUCCESS;
    }
}