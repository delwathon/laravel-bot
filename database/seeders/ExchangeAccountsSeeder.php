<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ExchangeAccount;
use Illuminate\Support\Facades\Hash;

class ExchangeAccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        ExchangeAccount::create([
            'user_id' => 1,
            'is_admin' => true,
            'exchange' => 'bybit',
            'api_key' => 'V72ICNXjl3vnqeE3WZ',
            'api_secret' => 'eyJpdiI6IlF0WSsyL0ViWWxPS2VlWnFabzNZdEE9PSIsInZhbHVlIjoiMEM1NFZLSlE3TFBueXZjTzNhQVBFdlJ1QlN3WHZjTzd5WlBhY0VYSXhQRFhDamdYYXVmQXFJS0svakZla2I4cyIsIm1hYyI6IjJmYjRkNTA2NWRiYjNiYzkxYjZiNTYwZWVhOWY3ZGE0Yjg0NjI5ZThmMGQ2MTJhNGEzYzQ1YTg4NGEwZWUyNmEiLCJ0YWciOiIifQ==',
            'is_active' => true,
            'balance' => '0.00',
            'last_synced_at' => now(),
        ]);

        // Create test regular user
        ExchangeAccount::create([
            'user_id' => 2,
            'is_admin' => false,
            'exchange' => 'bybit',
            'api_key' => 'YamkB2StOXGw1wayVN',
            'api_secret' => 'eyJpdiI6ImFwQUtMYXZ4YWRoMHFXaGtiNGFTSFE9PSIsInZhbHVlIjoiREZobWxSQ3UrREkxT3h6Um9IZEVjb1dBRDVYcUlJTzMxLzdKbENtN1ZHdlFvc0FDNzRzbCtpZVhZQzhFb0IzNSIsIm1hYyI6IjdlZjk3MjY1ZWIyNzE3YjY5NWE5MTNmZDRmNTlkZjVlOTAyN2RlMjUzMzBlMmI1ZWRhNjliMDFhZjhjYzQxYTgiLCJ0YWciOiIifQ==',
            'is_active' => true,
            'balance' => '0.00',
            'last_synced_at' => now(),
        ]);

        $this->command->info('Admin and 1 user exchange account created successfully!');
    }
}