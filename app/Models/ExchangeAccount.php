<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class ExchangeAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'exchange',
        'api_key',
        'api_secret',
        'is_active',
        'is_admin',
        'balance',
        'last_synced_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_admin' => 'boolean',
        'balance' => 'decimal:8',
        'last_synced_at' => 'datetime',
    ];

    protected $hidden = [
        'api_secret',
    ];

    /**
     * Encrypt API secret before saving
     */
    public function setApiSecretAttribute($value)
    {
        $this->attributes['api_secret'] = Crypt::encryptString($value);
    }

    /**
     * Decrypt API secret when retrieving
     */
    public function getApiSecretAttribute($value)
    {
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get the user that owns the exchange account
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if account is connected and active
     */
    public function isConnected()
    {
        return $this->is_active && !empty($this->api_key) && !empty($this->api_secret);
    }

    /**
     * Mask API key for display
     */
    public function getMaskedApiKeyAttribute()
    {
        if (empty($this->api_key)) {
            return '';
        }
        
        $key = $this->api_key;
        $length = strlen($key);
        
        if ($length <= 8) {
            return str_repeat('*', $length);
        }
        
        return substr($key, 0, 4) . str_repeat('*', $length - 8) . substr($key, -4);
    }

    /**
     * Get admin's Bybit account
     * 
     * @return ExchangeAccount|null
     */
    public static function getBybitAccount()
    {
        return self::where('exchange', 'bybit')
            ->where('is_active', true)
            ->where('is_admin', true)
            ->first();
    }

    /**
     * Get admin's exchange account for specific exchange
     * 
     * @param string $exchange
     * @return ExchangeAccount|null
     */
    public static function getAdminAccount($exchange = 'bybit')
    {
        return self::where('exchange', $exchange)
            ->where('is_active', true)
            ->where('is_admin', true)
            ->first();
    }

    /**
     * Check if this is an admin account
     * 
     * @return bool
     */
    public function isAdminAccount()
    {
        return $this->is_admin === true;
    }

    /**
     * Scope: Admin accounts only
     */
    public function scopeAdminAccounts($query)
    {
        return $query->where('is_admin', true);
    }

    /**
     * Scope: User accounts only
     */
    public function scopeUserAccounts($query)
    {
        return $query->where('is_admin', false);
    }
}