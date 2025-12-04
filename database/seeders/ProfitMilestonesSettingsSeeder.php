<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class ProfitMilestonesSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Default profit milestones configuration
        $defaultMilestones = [
            '100' => ['0', '50'],      // At 100% profit: SL to breakeven, close 50%
            '110' => ['10', '0'],      // At 110% profit: SL to 10%, close 0%
            '120' => ['20', '0'],      // At 120% profit: SL to 20%, close 0%
            '130' => ['30', '0'],      // At 130% profit: SL to 30%, close 0%
            '140' => ['40', '0'],      // At 140% profit: SL to 40%, close 0%
            '150' => ['50', '0'],      // At 150% profit: SL to 50%, close 0%
            '160' => ['60', '0'],      // At 160% profit: SL to 60%, close 0%
            '170' => ['70', '0'],      // At 170% profit: SL to 70%, close 0%
            '180' => ['80', '0'],      // At 180% profit: SL to 80%, close 0%
            '190' => ['90', '0'],      // At 190% profit: SL to 90%, close 0%
            '200' => ['100', '20'],    // At 200% profit: SL to 100%, close 20%
            '210' => ['110', '0'],     // At 210% profit: SL to 110%, close 0%
            '220' => ['120', '0'],     // At 220% profit: SL to 120%, close 0%
            '230' => ['130', '0'],     // At 230% profit: SL to 130%, close 0%
            '240' => ['140', '0'],     // At 240% profit: SL to 140%, close 0%
            '250' => ['150', '0'],     // At 250% profit: SL to 150%, close 0%
            '260' => ['160', '0'],     // At 260% profit: SL to 160%, close 0%
            '270' => ['170', '0'],     // At 270% profit: SL to 170%, close 0%
            '280' => ['180', '0'],     // At 280% profit: SL to 180%, close 0%
            '290' => ['190', '0'],     // At 290% profit: SL to 190%, close 0%
            '300' => ['200', '50'],    // At 300% profit: SL to 200%, close 50%
            '310' => ['210', '0'],     // At 310% profit: SL to 210%, close 0%
            '320' => ['220', '0'],     // At 320% profit: SL to 220%, close 0%
            '330' => ['230', '0'],     // At 330% profit: SL to 230%, close 0%
            '340' => ['240', '0'],     // At 340% profit: SL to 240%, close 0%
            '350' => ['250', '0'],     // At 350% profit: SL to 250%, close 0%
            '360' => ['260', '0'],     // At 360% profit: SL to 260%, close 0%
            '370' => ['270', '0'],     // At 370% profit: SL to 270%, close 0%
            '380' => ['280', '0'],     // At 380% profit: SL to 280%, close 0%
            '390' => ['290', '0'],     // At 390% profit: SL to 290%, close 0%
            '400' => ['300', '20'],    // At 400% profit: SL to 300%, close 20%
            '410' => ['310', '0'],     // At 410% profit: SL to 310%, close 0%
            '420' => ['320', '0'],     // At 420% profit: SL to 320%, close 0%
            '430' => ['330', '0'],     // At 430% profit: SL to 330%, close 0%
            '440' => ['340', '0'],     // At 440% profit: SL to 340%, close 0%
            '450' => ['350', '0'],     // At 450% profit: SL to 350%, close 0%
            '460' => ['360', '0'],     // At 460% profit: SL to 360%, close 0%
            '470' => ['370', '0'],     // At 470% profit: SL to 370%, close 0%
            '480' => ['380', '0'],     // At 480% profit: SL to 380%, close 0%
            '490' => ['390', '0'],     // At 490% profit: SL to 390%, close 0%
            '500' => ['400', '50'],    // At 500% profit: SL to 400%, close 50%
            '510' => ['410', '0'],     // At 510% profit: SL to 410%, close 0%
            '520' => ['420', '0'],     // At 520% profit: SL to 420%, close 0%
            '530' => ['430', '0'],     // At 530% profit: SL to 430%, close 0%
            '540' => ['440', '0'],     // At 540% profit: SL to 440%, close 0%
            '550' => ['450', '0'],     // At 550% profit: SL to 450%, close 0%
            '560' => ['460', '0'],     // At 560% profit: SL to 460%, close 0%
            '570' => ['470', '0'],     // At 570% profit: SL to 470%, close 0%
            '580' => ['480', '0'],     // At 580% profit: SL to 480%, close 0%
            '590' => ['490', '0'],     // At 590% profit: SL to 490%, close 0%
            '600' => ['500', '20'],    // At 600% profit: SL to 500%, close 20%
        ];

        Setting::updateOrCreate(
            ['key' => 'profit_milestones'],
            [
                'value' => json_encode($defaultMilestones),
                'type' => 'json',
                'description' => 'Profit milestone configuration for automated trailing stops and partial profit taking. Format: {"profit_percent": ["new_sl_percent", "close_percent"]}'
            ]
        );

        // Enable/disable profit milestones feature
        Setting::updateOrCreate(
            ['key' => 'enable_profit_milestones'],
            [
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Enable automatic profit milestone management (trailing stops and partial profits)'
            ]
        );

        $this->command->info('✓ Profit milestones settings seeded successfully');
    }
}