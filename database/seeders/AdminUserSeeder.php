<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Rilwan Adelaja',
            'first_name' => 'Rilwan',
            'last_name' => 'Adelaja',
            'email' => 'younghardehlaja@gmail.com',
            'password' => Hash::make('password'),
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);

        // Create test regular user
        User::create([
            'name' => 'Abiodun Afuwape',
            'first_name' => 'Abiodun',
            'last_name' => 'Afuwape',
            'email' => 'abiodunafuwape@gmail.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Adeshina Ajobo',
            'first_name' => 'Adeshina',
            'last_name' => 'Ajobo',
            'email' => 'adeshinaajobo@gmail.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Saheed Adewale',
            'first_name' => 'Saheed',
            'last_name' => 'Adewale',
            'email' => 'celebritytreatz@gmail.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
            'email_verified_at' => now(),
        ]);

        $this->command->info('Admin and test users created successfully!');
        $this->command->info('Admin: admin@cryptobot.com / password');
        $this->command->info('User: younghardehlaja@gmail.com / password');
        $this->command->info('User: adeshinaajobo@gmail.com / password');
        $this->command->info('User: celebritytreatz@gmail.com / password');
    }
}