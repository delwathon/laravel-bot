<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class ConflictManagementSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Conflict Management Settings
            [
                'key' => 'signal_stale_order_hours',
                'value' => '24',
                'type' => 'integer',
                'group' => 'signal_generator',
                'description' => 'Hours before a pending order is considered stale and can be cancelled'
            ],
            [
                'key' => 'signal_skip_duplicate_positions',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'signal_generator',
                'description' => 'Skip signal execution if admin has an open position on the same symbol'
            ],
            [
                'key' => 'signal_cancel_opposite_pending',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'signal_generator',
                'description' => 'Cancel pending orders if new signal is opposite direction'
            ],
            [
                'key' => 'signal_cancel_stale_pending',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'signal_generator',
                'description' => 'Cancel pending orders that exceed the stale threshold'
            ],
            [
                'key' => 'signal_close_opposite_positions',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'signal_generator',
                'description' => 'Close open positions if new signal is opposite direction (risky!)'
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        $this->command->info('Conflict management settings seeded successfully!');
    }
}