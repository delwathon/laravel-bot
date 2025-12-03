<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trade extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'signal_id',
        'exchange_account_id',
        'symbol',
        'exchange',
        'type',
        'exchange_order_id',
        'entry_price',
        'stop_loss',
        'take_profit',
        'exit_price',
        'quantity',
        'leverage',
        'realized_pnl',
        'realized_pnl_percent',
        'fees',
        'status',
        'opened_at',
        'closed_at',
    ];

    protected $casts = [
        'entry_price' => 'decimal:8',
        'stop_loss' => 'decimal:8',
        'take_profit' => 'decimal:8',
        'exit_price' => 'decimal:8',
        'quantity' => 'decimal:8',
        'leverage' => 'decimal:2',
        'realized_pnl' => 'decimal:8',
        'realized_pnl_percent' => 'decimal:4',
        'fees' => 'decimal:8',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    /**
     * Get the user that owns the trade
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the signal that generated this trade
     */
    public function signal()
    {
        return $this->belongsTo(Signal::class);
    }

    /**
     * Get the exchange account used for this trade
     */
    public function exchangeAccount()
    {
        return $this->belongsTo(ExchangeAccount::class);
    }

    /**
     * Get the position for this trade
     */
    public function position()
    {
        return $this->hasOne(Position::class);
    }

    /**
     * Check if trade is open
     */
    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    /**
     * Check if trade is closed
     */
    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    /**
     * Check if trade is profitable
     */
    public function isProfitable(): bool
    {
        return $this->realized_pnl > 0;
    }

    /**
     * Calculate P&L
     */
    public function calculatePnl()
    {
        if (!$this->exit_price) {
            return;
        }

        if ($this->type === 'long') {
            $pnl = ($this->exit_price - $this->entry_price) * $this->quantity;
        } else {
            $pnl = ($this->entry_price - $this->exit_price) * $this->quantity;
        }

        $pnl -= $this->fees;

        $pnlPercent = ($pnl / ($this->entry_price * $this->quantity)) * 100;

        $this->update([
            'realized_pnl' => $pnl,
            'realized_pnl_percent' => $pnlPercent,
        ]);
    }

    /**
     * Scope: Open trades
     */
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    /**
     * Scope: Closed trades
     */
    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    /**
     * Scope: Profitable trades
     */
    public function scopeProfitable($query)
    {
        return $query->where('realized_pnl', '>', 0);
    }

    /**
     * Scope: By user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}