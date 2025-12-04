<?php

namespace App\Services;

use App\Models\Position;
use App\Models\ExchangeAccount;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PositionMonitorService
{
    /**
     * Profit milestones configuration
     * Format: 'profit_percent' => ['new_sl_percent', 'close_percent']
     * All percentages are leveraged percentages
     */
    protected $profitMilestones = [
        '100' => ['0', '50'],      // At 100% profit: SL to breakeven, close 50%
        '110' => ['10', '0'],      // At 110% profit: SL to 10%, close 0%
        '120' => ['20', '0'],      // At 120% profit: SL to 20%, close 0%
        '130' => ['30', '0'],      // At 130% profit: SL to 30%, close 0%
        '140' => ['40', '0'],      // At 140% profit: SL to 40%, close 0%
        '150' => ['50', '0'],      // At 150% profit: SL to 50%, close 0%
        '160' => ['60', '0'],      // At 160% profit: SL to 60%, close 0%
        '170' => ['70', '0'],      // At 170% profit: SL to 70%, close 0%
        '180' => ['80', '0'],      // At 180% profit: SL to 80%, close 0%
        '190' => ['90', '0'],      // At 190% profit: SL to 90%, close 0%
        '200' => ['100', '20'],    // At 200% profit: SL to 100%, close 20%
        '210' => ['110', '0'],     // At 210% profit: SL to 110%, close 0%
        '220' => ['120', '0'],     // At 220% profit: SL to 120%, close 0%
        '230' => ['130', '0'],     // At 230% profit: SL to 130%, close 0%
        '240' => ['140', '0'],     // At 240% profit: SL to 140%, close 0%
        '250' => ['150', '0'],     // At 250% profit: SL to 150%, close 0%
        '260' => ['160', '0'],     // At 260% profit: SL to 160%, close 0%
        '270' => ['170', '0'],     // At 270% profit: SL to 170%, close 0%
        '280' => ['180', '0'],     // At 280% profit: SL to 180%, close 0%
        '290' => ['190', '0'],     // At 290% profit: SL to 190%, close 0%
        '300' => ['200', '50'],    // At 300% profit: SL to 200%, close 50%
        '310' => ['210', '0'],     // At 310% profit: SL to 210%, close 0%
        '320' => ['220', '0'],     // At 320% profit: SL to 220%, close 0%
        '330' => ['230', '0'],     // At 330% profit: SL to 230%, close 0%
        '340' => ['240', '0'],     // At 340% profit: SL to 240%, close 0%
        '350' => ['250', '0'],     // At 350% profit: SL to 250%, close 0%
        '360' => ['260', '0'],     // At 360% profit: SL to 260%, close 0%
        '370' => ['270', '0'],     // At 370% profit: SL to 270%, close 0%
        '380' => ['280', '0'],     // At 380% profit: SL to 280%, close 0%
        '390' => ['290', '0'],     // At 390% profit: SL to 290%, close 0%
        '400' => ['300', '20'],    // At 400% profit: SL to 300%, close 20%
        '410' => ['310', '0'],     // At 410% profit: SL to 310%, close 0%
        '420' => ['320', '0'],     // At 420% profit: SL to 320%, close 0%
        '430' => ['330', '0'],     // At 430% profit: SL to 330%, close 0%
        '440' => ['340', '0'],     // At 440% profit: SL to 340%, close 0%
        '450' => ['350', '0'],     // At 450% profit: SL to 350%, close 0%
        '460' => ['360', '0'],     // At 460% profit: SL to 360%, close 0%
        '470' => ['370', '0'],     // At 470% profit: SL to 370%, close 0%
        '480' => ['380', '0'],     // At 480% profit: SL to 380%, close 0%
        '490' => ['390', '0'],     // At 490% profit: SL to 390%, close 0%
        '500' => ['400', '50'],    // At 500% profit: SL to 400%, close 50%
        '510' => ['410', '0'],     // At 510% profit: SL to 410%, close 0%
        '520' => ['420', '0'],     // At 520% profit: SL to 420%, close 0%
        '530' => ['430', '0'],     // At 530% profit: SL to 430%, close 0%
        '540' => ['440', '0'],     // At 540% profit: SL to 440%, close 0%
        '550' => ['450', '0'],     // At 550% profit: SL to 450%, close 0%
        '560' => ['460', '0'],     // At 560% profit: SL to 460%, close 0%
        '570' => ['470', '0'],     // At 570% profit: SL to 470%, close 0%
        '580' => ['480', '0'],     // At 580% profit: SL to 480%, close 0%
        '590' => ['490', '0'],     // At 590% profit: SL to 490%, close 0%
        '600' => ['500', '20'],    // At 600% profit: SL to 500%, close 20%
    ];

    public function __construct()
    {
        // Load profit milestones from settings if configured
        $configuredMilestones = Setting::get('profit_milestones', null);
        
        if ($configuredMilestones) {
            $decoded = is_array($configuredMilestones) ? $configuredMilestones : json_decode($configuredMilestones, true);
            if (is_array($decoded) && !empty($decoded)) {
                $this->profitMilestones = $decoded;
                Log::info('[PositionMonitor] Loaded custom profit milestones from settings');
            }
        }
    }

    public function monitorAllPositions()
    {
        $positions = Position::where('is_active', true)
            ->with(['user', 'exchangeAccount', 'trade'])
            ->get();

        $results = [
            'total' => $positions->count(),
            'updated' => 0,
            'closed' => 0,
            'errors' => 0,
            'milestones_triggered' => 0,
        ];

        foreach ($positions as $position) {
            try {
                $milestoneTriggered = $this->monitorPosition($position);
                $results['updated']++;
                
                if ($milestoneTriggered) {
                    $results['milestones_triggered']++;
                }
                
                if (!$position->is_active) {
                    $results['closed']++;
                }
            } catch (\Exception $e) {
                $results['errors']++;
                Log::error("Failed to monitor position {$position->id}: " . $e->getMessage());
            }
        }

        return $results;
    }

    public function monitorPosition(Position $position)
    {
        if (!$position->is_active) {
            return false;
        }

        $exchangeAccount = $position->exchangeAccount;
        
        if (!$exchangeAccount || !$exchangeAccount->is_active) {
            return false;
        }

        $bybit = new BybitService($exchangeAccount->api_key, $exchangeAccount->api_secret);
        
        $currentPrice = $bybit->getCurrentPrice($position->symbol);
        
        if (!$currentPrice) {
            Log::warning("Could not get current price for {$position->symbol}");
            return false;
        }

        // Update position metrics
        $position->updateMetrics($currentPrice);

        // Check for profit milestone triggers
        $milestoneTriggered = $this->checkAndApplyProfitMilestones($position, $currentPrice, $bybit);

        // Check if position should close based on stop loss or take profit
        if ($position->shouldClose()) {
            $this->closePosition($position, $currentPrice);
            return $milestoneTriggered;
        }

        return $milestoneTriggered;
    }

    /**
     * Check if any profit milestones have been reached and apply them
     * 
     * @param Position $position
     * @param float $currentPrice
     * @param BybitService $bybit
     * @return bool Whether a milestone was triggered
     */
    protected function checkAndApplyProfitMilestones(Position $position, $currentPrice, BybitService $bybit)
    {
        // Calculate current profit percentage (leveraged)
        $profitPercent = $this->calculateLeveragedProfitPercent($position, $currentPrice);
        
        // Get the highest milestone that has been reached
        $applicableMilestone = $this->getApplicableMilestone($profitPercent, $position);
        
        if (!$applicableMilestone) {
            return false;
        }
        
        $milestonePercent = $applicableMilestone['milestone'];
        $newSlPercent = (float) $applicableMilestone['new_sl_percent'];
        $closePercent = (float) $applicableMilestone['close_percent'];
        
        Log::info("[PositionMonitor] Milestone {$milestonePercent}% reached for position {$position->id}", [
            'symbol' => $position->symbol,
            'current_profit' => round($profitPercent, 2) . '%',
            'new_sl_percent' => $newSlPercent . '%',
            'close_percent' => $closePercent . '%',
        ]);

        DB::beginTransaction();

        try {
            $actions = [];
            
            // Apply trailing stop loss
            if ($newSlPercent > 0 || $newSlPercent === 0.0) {
                $newStopLoss = $this->calculateNewStopLoss($position, $newSlPercent);
                
                if ($this->shouldUpdateStopLoss($position, $newStopLoss)) {
                    $this->updateStopLoss($position, $newStopLoss, $bybit);
                    $actions[] = "SL adjusted to {$newSlPercent}% profit";
                }
            }
            
            // Take partial profits
            if ($closePercent > 0) {
                $this->takePartialProfits($position, $closePercent, $currentPrice, $bybit);
                $actions[] = "Closed {$closePercent}% of position";
            }
            
            // Record milestone in position metadata
            $this->recordMilestone($position, $milestonePercent, $profitPercent, $actions);
            
            DB::commit();
            
            Log::info("[PositionMonitor] Milestone actions completed for position {$position->id}", [
                'actions' => implode(', ', $actions),
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("[PositionMonitor] Failed to apply milestone for position {$position->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Calculate leveraged profit percentage
     * 
     * @param Position $position
     * @param float $currentPrice
     * @return float
     */
    protected function calculateLeveragedProfitPercent(Position $position, $currentPrice)
    {
        $entryPrice = $position->entry_price;
        $leverage = $position->leverage;
        
        if ($position->side === 'long') {
            $priceChangePercent = (($currentPrice - $entryPrice) / $entryPrice) * 100;
        } else {
            $priceChangePercent = (($entryPrice - $currentPrice) / $entryPrice) * 100;
        }
        
        // Apply leverage to get leveraged profit
        $leveragedProfitPercent = $priceChangePercent * $leverage;
        
        return $leveragedProfitPercent;
    }

    /**
     * Get the applicable milestone based on current profit and position history
     * Returns the highest milestone reached that hasn't been applied yet
     * 
     * @param float $profitPercent
     * @param Position $position
     * @return array|null
     */
    protected function getApplicableMilestone($profitPercent, Position $position)
    {
        // Get list of milestones already applied (stored in position metadata)
        $appliedMilestones = $this->getAppliedMilestones($position);
        
        // Sort milestones by threshold descending to get highest first
        $sortedMilestones = $this->profitMilestones;
        krsort($sortedMilestones, SORT_NUMERIC);
        
        // Find the highest milestone that:
        // 1. Current profit has reached
        // 2. Hasn't been applied yet
        foreach ($sortedMilestones as $milestoneThreshold => $actions) {
            $threshold = (float) $milestoneThreshold;
            
            if ($profitPercent >= $threshold && !in_array($milestoneThreshold, $appliedMilestones)) {
                return [
                    'milestone' => $milestoneThreshold,
                    'new_sl_percent' => $actions[0],
                    'close_percent' => $actions[1],
                ];
            }
        }
        
        return null;
    }

    /**
     * Get list of milestones already applied to this position
     * 
     * @param Position $position
     * @return array
     */
    protected function getAppliedMilestones(Position $position)
    {
        $metadata = $position->metadata ?? [];
        
        if (is_string($metadata)) {
            $metadata = json_decode($metadata, true) ?? [];
        }
        
        return $metadata['applied_milestones'] ?? [];
    }

    /**
     * Calculate new stop loss price based on profit percentage
     * 
     * @param Position $position
     * @param float $profitPercent - The profit percentage to set SL at (leveraged)
     * @return float
     */
    protected function calculateNewStopLoss(Position $position, $profitPercent)
    {
        $entryPrice = $position->entry_price;
        $leverage = $position->leverage;
        
        // Convert leveraged profit percent back to unleveraged price change percent
        $priceChangePercent = $profitPercent / $leverage;
        
        if ($position->side === 'long') {
            // For long: new SL = entry + (entry * price_change_percent / 100)
            $newStopLoss = $entryPrice + ($entryPrice * $priceChangePercent / 100);
        } else {
            // For short: new SL = entry - (entry * price_change_percent / 100)
            $newStopLoss = $entryPrice - ($entryPrice * $priceChangePercent / 100);
        }
        
        return $newStopLoss;
    }

    /**
     * Determine if stop loss should be updated
     * Only update if new SL is better than current SL
     * 
     * @param Position $position
     * @param float $newStopLoss
     * @return bool
     */
    protected function shouldUpdateStopLoss(Position $position, $newStopLoss)
    {
        $currentStopLoss = $position->stop_loss;
        
        if ($position->side === 'long') {
            // For long positions, new SL should be higher than current
            return $newStopLoss > $currentStopLoss;
        } else {
            // For short positions, new SL should be lower than current
            return $newStopLoss < $currentStopLoss;
        }
    }

    /**
     * Update stop loss on exchange and in database
     * 
     * @param Position $position
     * @param float $newStopLoss
     * @param BybitService $bybit
     * @return void
     */
    protected function updateStopLoss(Position $position, $newStopLoss, BybitService $bybit)
    {
        try {
            // Update stop loss on Bybit
            $bybit->setTradingStop(
                $position->symbol,
                $newStopLoss,
                $position->take_profit
            );
            
            // Update in database
            $position->update([
                'stop_loss' => $newStopLoss,
            ]);
            
            // Also update the trade record
            if ($position->trade) {
                $position->trade->update([
                    'stop_loss' => $newStopLoss,
                ]);
            }
            
            Log::info("[PositionMonitor] Stop loss updated for position {$position->id}", [
                'symbol' => $position->symbol,
                'old_sl' => $position->stop_loss,
                'new_sl' => $newStopLoss,
            ]);
            
        } catch (\Exception $e) {
            Log::error("[PositionMonitor] Failed to update stop loss for position {$position->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Take partial profits by closing a percentage of the position
     * 
     * @param Position $position
     * @param float $closePercent - Percentage of position to close (0-100)
     * @param float $currentPrice
     * @param BybitService $bybit
     * @return void
     */
    protected function takePartialProfits(Position $position, $closePercent, $currentPrice, BybitService $bybit)
    {
        try {
            $originalQuantity = $position->quantity;
            $closeQuantity = ($originalQuantity * $closePercent) / 100;
            
            // Get instrument info to adjust quantity properly
            $instrumentInfo = $bybit->getInstrumentInfo($position->symbol);
            $closeQuantity = $this->adjustQuantity($closeQuantity, $instrumentInfo);
            
            if ($closeQuantity <= 0) {
                Log::warning("[PositionMonitor] Calculated close quantity is 0 for position {$position->id}, skipping partial close");
                return;
            }
            
            // Place market order to close partial position
            $side = $position->side === 'long' ? 'Sell' : 'Buy'; // Opposite side to close
            
            $orderResult = $bybit->placeOrder(
                $position->symbol,
                $side,
                $closeQuantity,
                'Market',
                null, // No limit price for market order
                null, // Don't set SL/TP on partial close order
                null,
                null  // Don't change leverage
            );
            
            if (!$orderResult || !isset($orderResult['orderId'])) {
                throw new \Exception('Failed to place partial close order on Bybit');
            }
            
            // Update position quantity
            $newQuantity = $originalQuantity - $closeQuantity;
            $position->update([
                'quantity' => $newQuantity,
            ]);
            
            // Update trade quantity
            if ($position->trade) {
                $position->trade->update([
                    'quantity' => $newQuantity,
                ]);
            }
            
            // Record partial close in metadata
            $this->recordPartialClose($position, $closeQuantity, $closePercent, $currentPrice);
            
            Log::info("[PositionMonitor] Partial profit taken for position {$position->id}", [
                'symbol' => $position->symbol,
                'original_qty' => $originalQuantity,
                'closed_qty' => $closeQuantity,
                'remaining_qty' => $newQuantity,
                'close_percent' => $closePercent,
                'close_price' => $currentPrice,
                'order_id' => $orderResult['orderId'],
            ]);
            
        } catch (\Exception $e) {
            Log::error("[PositionMonitor] Failed to take partial profits for position {$position->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Adjust quantity to meet instrument requirements
     * 
     * @param float $quantity
     * @param array $instrumentInfo
     * @return float
     */
    protected function adjustQuantity($quantity, $instrumentInfo)
    {
        $minQty = $instrumentInfo['minOrderQty'];
        $maxQty = $instrumentInfo['maxOrderQty'];
        $qtyStep = $instrumentInfo['qtyStep'];
        
        // Ensure quantity is within min/max bounds
        if ($quantity < $minQty) {
            return 0; // Can't close less than minimum
        }
        
        if ($quantity > $maxQty) {
            $quantity = $maxQty;
        }
        
        // Round to nearest valid step
        $adjusted = floor($quantity / $qtyStep) * $qtyStep;
        
        // Ensure we didn't round below minimum
        if ($adjusted < $minQty) {
            return 0;
        }
        
        // Get decimal places from qtyStep
        $decimals = strlen(substr(strrchr((string)$qtyStep, "."), 1));
        $adjusted = round($adjusted, $decimals);
        
        return $adjusted;
    }

    /**
     * Record milestone application in position metadata
     * 
     * @param Position $position
     * @param string $milestonePercent
     * @param float $actualProfitPercent
     * @param array $actions
     * @return void
     */
    protected function recordMilestone(Position $position, $milestonePercent, $actualProfitPercent, $actions)
    {
        $metadata = $position->metadata ?? [];
        
        if (is_string($metadata)) {
            $metadata = json_decode($metadata, true) ?? [];
        }
        
        // Initialize arrays if not present
        if (!isset($metadata['applied_milestones'])) {
            $metadata['applied_milestones'] = [];
        }
        
        if (!isset($metadata['milestone_history'])) {
            $metadata['milestone_history'] = [];
        }
        
        // Add to applied milestones
        $metadata['applied_milestones'][] = $milestonePercent;
        
        // Add to history
        $metadata['milestone_history'][] = [
            'milestone' => $milestonePercent,
            'actual_profit' => round($actualProfitPercent, 2),
            'actions' => $actions,
            'timestamp' => now()->toDateTimeString(),
        ];
        
        $position->update([
            'metadata' => json_encode($metadata),
        ]);
    }

    /**
     * Record partial close in position metadata
     * 
     * @param Position $position
     * @param float $closedQuantity
     * @param float $closePercent
     * @param float $closePrice
     * @return void
     */
    protected function recordPartialClose(Position $position, $closedQuantity, $closePercent, $closePrice)
    {
        $metadata = $position->metadata ?? [];
        
        if (is_string($metadata)) {
            $metadata = json_decode($metadata, true) ?? [];
        }
        
        if (!isset($metadata['partial_closes'])) {
            $metadata['partial_closes'] = [];
        }
        
        $metadata['partial_closes'][] = [
            'quantity' => $closedQuantity,
            'percent' => $closePercent,
            'price' => $closePrice,
            'timestamp' => now()->toDateTimeString(),
        ];
        
        $position->update([
            'metadata' => json_encode($metadata),
        ]);
    }

    protected function closePosition(Position $position, $exitPrice)
    {
        $trade = $position->trade;
        
        if (!$trade) {
            Log::error("Position {$position->id} has no associated trade");
            return;
        }

        try {
            $exchangeAccount = $position->exchangeAccount;
            $bybit = new BybitService($exchangeAccount->api_key, $exchangeAccount->api_secret);
            
            $side = $position->side === 'long' ? 'Buy' : 'Sell';
            $bybit->closePosition($position->symbol, $side);

            $position->close($exitPrice);

            Log::info("Position auto-closed", [
                'position_id' => $position->id,
                'symbol' => $position->symbol,
                'exit_price' => $exitPrice,
                'pnl' => $trade->realized_pnl,
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to close position {$position->id}: " . $e->getMessage());
            throw $e;
        }
    }

    public function monitorUserPositions($userId)
    {
        $positions = Position::where('is_active', true)
            ->where('user_id', $userId)
            ->with(['exchangeAccount', 'trade'])
            ->get();

        foreach ($positions as $position) {
            try {
                $this->monitorPosition($position);
            } catch (\Exception $e) {
                Log::error("Failed to monitor position {$position->id} for user {$userId}: " . $e->getMessage());
            }
        }
    }
}