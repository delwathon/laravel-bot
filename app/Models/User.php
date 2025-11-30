<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'first_name',
        'last_name',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    /**
     * Check if user is an admin
     */
    public function isAdmin(): bool
    {
        return $this->is_admin === true;
    }

    /**
     * Check if user is a regular user
     */
    public function isUser(): bool
    {
        return $this->is_admin === false;
    }

    /**
     * Get full name attribute
     */
    public function getFullNameAttribute(): string
    {
        if ($this->first_name && $this->last_name) {
            return $this->first_name . ' ' . $this->last_name;
        }
        
        return $this->name;
    }

    /**
     * Get the user's exchange account (Bybit only - one per user)
     */
    public function exchangeAccount()
    {
        return $this->hasOne(ExchangeAccount::class);
    }

    /**
     * Get user's trades
     */
    public function trades()
    {
        return $this->hasMany(Trade::class);
    }

    /**
     * Get user's positions
     */
    public function positions()
    {
        return $this->hasMany(Position::class);
    }

    /**
     * Check if user has connected their Bybit account
     */
    public function hasConnectedExchange(): bool
    {
        return $this->exchangeAccount()->exists() && $this->exchangeAccount->isConnected();
    }

    /**
     * Get the Bybit account
     */
    public function getBybitAccountAttribute()
    {
        return $this->exchangeAccount;
    }

    /**
     * Get active positions
     */
    public function activePositions()
    {
        return $this->positions()->where('is_active', true);
    }

    /**
     * Get open trades
     */
    public function openTrades()
    {
        return $this->trades()->where('status', 'open');
    }

    /**
     * Get closed trades
     */
    public function closedTrades()
    {
        return $this->trades()->where('status', 'closed');
    }

    /**
     * Calculate total P&L
     */
    public function getTotalPnlAttribute()
    {
        return $this->trades()
            ->where('status', 'closed')
            ->sum('realized_pnl');
    }

    /**
     * Calculate win rate
     */
    public function getWinRateAttribute()
    {
        $closedTrades = $this->closedTrades()->count();
        
        if ($closedTrades === 0) {
            return 0;
        }

        $winningTrades = $this->closedTrades()
            ->where('realized_pnl', '>', 0)
            ->count();

        return round(($winningTrades / $closedTrades) * 100, 2);
    }

    /**
     * Get trades count for today
     */
    public function getTodayTradesCountAttribute()
    {
        return $this->trades()
            ->whereDate('created_at', today())
            ->count();
    }

    /**
     * Get active positions count
     */
    public function getActivePositionsCountAttribute()
    {
        return $this->activePositions()->count();
    }

    /**
     * Get total trades count
     */
    public function getTotalTradesCountAttribute()
    {
        return $this->trades()->count();
    }
}