<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add new settings for dynamic pair selection
        $settings = [
            [
                'key' => 'signal_use_dynamic_pairs',
                'value' => '1',
                'group' => 'signal_generator',
                'type' => 'boolean',
                'description' => 'Use dynamic pair selection based on volume instead of fixed pairs'
            ],
            [
                'key' => 'signal_min_volume',
                'value' => '5000000',
                'group' => 'signal_generator',
                'type' => 'integer',
                'description' => 'Minimum 24h trading volume in USDT for pair selection'
            ],
            [
                'key' => 'signal_auto_execute_count',
                'value' => '3',
                'group' => 'signal_generator',
                'type' => 'integer',
                'description' => 'Number of signals to auto-execute'
            ],
            [
                'key' => 'signal_max_pairs',
                'value' => '50',
                'group' => 'signal_generator',
                'type' => 'integer',
                'description' => 'Number of signals to analyze for dynamic pair selection'
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['key' => $setting['key']],
                array_merge($setting, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('settings')->whereIn('key', [
            'signal_use_dynamic_pairs',
            'signal_min_volume',
        ])->delete();
    }
};