<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Signal extends Model
{
    use HasFactory;

    protected $fillable = [
        'symbol',
        'exchange',
        'type',
        'timeframe',
        'pattern',
        'confidence',
        'entry_price',
        'stop_loss',
        'take_profit',
        'risk_reward_ratio',
        'position_size_percent',
        'status',
        'expires_at',
        'executed_at',
        'analysis_data',
        'notes',
    ];

    protected $casts = [
        'confidence' => 'decimal:2',
        'entry_price' => 'decimal:8',
        'stop_loss' => 'decimal:8',
        'take_profit' => 'decimal:8',
        'risk_reward_ratio' => 'decimal:2',
        'position_size_percent' => 'decimal:2',
        'expires_at' => 'datetime',
        'executed_at' => 'datetime',
        'analysis_data' => 'array',
    ];

    /**
     * Get trades created from this signal
     */
    public function trades()
    {
        return $this->hasMany(Trade::class);
    }

    /**
     * Check if signal is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if signal is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && !$this->isExpired();
    }

    /**
     * Mark signal as executed
     */
    public function markExecuted()
    {
        $this->update([
            'status' => 'executed',
            'executed_at' => now(),
        ]);
    }

    /**
     * Scope: Active signals
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                    ->where(function($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
    }

    /**
     * Scope: Pending signals
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: By symbol
     */
    public function scopeBySymbol($query, $symbol)
    {
        return $query->where('symbol', $symbol);
    }

    /**
     * Get formatted risk-reward ratio
     */
    public function getRiskRewardAttribute(): string
    {
        return '1:' . $this->risk_reward_ratio;
    }
}