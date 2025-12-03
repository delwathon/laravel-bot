<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\MonitorLimitOrdersJob;
use App\Models\Trade;

class CheckPendingOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:check-pending {--force : Force check even if disabled}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manually check and fill pending limit orders';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('╔════════════════════════════════════════════════╗');
        $this->info('║   CHECKING PENDING LIMIT ORDERS                ║');
        $this->info('╚════════════════════════════════════════════════╝');
        $this->newLine();

        // Check pending orders count
        $pendingCount = Trade::where('status', 'pending')->count();
        
        if ($pendingCount === 0) {
            $this->warn('No pending orders found.');
            return Command::SUCCESS;
        }

        $this->info("Found {$pendingCount} pending order(s)");
        $this->newLine();

        // Show details
        $this->line('Pending Orders:');
        $this->line('─────────────────────────────────────────────────');
        
        $pendingOrders = Trade::where('status', 'pending')
            ->with('user')
            ->get()
            ->groupBy('signal_id');

        foreach ($pendingOrders as $signalId => $trades) {
            $firstTrade = $trades->first();
            $this->line(sprintf(
                "Signal #%d: %s %s @ $%s (%d users)",
                $signalId,
                $firstTrade->symbol,
                strtoupper($firstTrade->type),
                number_format($firstTrade->entry_price, 2),
                $trades->count()
            ));
        }

        $this->newLine();
        
        // Run the job
        $this->info('Running MonitorLimitOrdersJob...');
        
        try {
            $job = new MonitorLimitOrdersJob();
            $job->handle();
            
            $this->newLine();
            $this->info('✓ Job completed successfully!');
            
            // Check results
            $this->newLine();
            $remainingPending = Trade::where('status', 'pending')->count();
            $filled = $pendingCount - $remainingPending;
            
            if ($filled > 0) {
                $this->info("✓ {$filled} order(s) were filled");
            }
            
            if ($remainingPending > 0) {
                $this->warn("⚠ {$remainingPending} order(s) still pending");
            } else {
                $this->info('✓ All orders processed!');
            }
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('✗ Error: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return Command::FAILURE;
        }
    }
}