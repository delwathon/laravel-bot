<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'trade_id',
        // 'exchange_account_id',
        'symbol',
        'exchange',
        'side',
        'exchange_position_id',
        'entry_price',
        'current_price',
        'quantity',
        'leverage',
        'stop_loss',
        'take_profit',
        'unrealized_pnl',
        'unrealized_pnl_percent',
        'margin_used',
        'is_active',
        'last_updated_at',
    ];

    protected $casts = [
        'entry_price' => 'decimal:8',
        'current_price' => 'decimal:8',
        'quantity' => 'decimal:8',
        'leverage' => 'decimal:2',
        'stop_loss' => 'decimal:8',
        'take_profit' => 'decimal:8',
        'unrealized_pnl' => 'decimal:8',
        'unrealized_pnl_percent' => 'decimal:4',
        'margin_used' => 'decimal:8',
        'is_active' => 'boolean',
        'last_updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the position
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the trade associated with this position
     */
    public function trade()
    {
        return $this->belongsTo(Trade::class);
    }

    /**
     * Get the exchange account
     */
    public function exchangeAccount()
    {
        return $this->belongsTo(ExchangeAccount::class);
    }

    /**
     * Update position metrics
     */
    public function updateMetrics($currentPrice)
    {
        $this->current_price = $currentPrice;

        // Calculate unrealized P&L
        if ($this->side === 'long') {
            $pnl = ($currentPrice - $this->entry_price) * $this->quantity;
        } else {
            $pnl = ($this->entry_price - $currentPrice) * $this->quantity;
        }

        $pnlPercent = ($pnl / ($this->entry_price * $this->quantity)) * 100;

        $this->unrealized_pnl = $pnl;
        $this->unrealized_pnl_percent = $pnlPercent;
        $this->last_updated_at = now();

        $this->save();
    }

    /**
     * Check if position should be closed (SL/TP hit)
     */
    public function shouldClose(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        // Check stop loss
        if ($this->side === 'long' && $this->current_price <= $this->stop_loss) {
            return true;
        }
        if ($this->side === 'short' && $this->current_price >= $this->stop_loss) {
            return true;
        }

        // Check take profit
        if ($this->side === 'long' && $this->current_price >= $this->take_profit) {
            return true;
        }
        if ($this->side === 'short' && $this->current_price <= $this->take_profit) {
            return true;
        }

        return false;
    }

    /**
     * Close position
     */
    public function close($exitPrice)
    {
        $this->update([
            'is_active' => false,
            'current_price' => $exitPrice,
        ]);

        // Update associated trade
        if ($this->trade) {
            $this->trade->update([
                'exit_price' => $exitPrice,
                'status' => 'closed',
                'closed_at' => now(),
            ]);
            $this->trade->calculatePnl();
        }
    }

    /**
     * Scope: Active positions
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: By user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: By symbol
     */
    public function scopeBySymbol($query, $symbol)
    {
        return $query->where('symbol', $symbol);
    }
}