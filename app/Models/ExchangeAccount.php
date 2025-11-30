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
        'balance',
        'last_synced_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
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
}