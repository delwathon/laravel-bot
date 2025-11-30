<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class AdminExchangeAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'exchange',
        'api_key',
        'api_secret',
        'is_active',
        'last_synced_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    protected $hidden = [
        'api_secret',
    ];

    public function setApiSecretAttribute($value)
    {
        $this->attributes['api_secret'] = Crypt::encryptString($value);
    }

    public function getApiSecretAttribute($value)
    {
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return null;
        }
    }

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

    public static function getBybitAccount()
    {
        return self::where('exchange', 'bybit')->where('is_active', true)->first();
    }
}