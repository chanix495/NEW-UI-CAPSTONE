<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Owner account
        User::create([
            'name' => 'Owner Account',
            'email' => 'owner@FreshTrack.ph',
            'password' => bcrypt('password'),
            'role' => 'owner',
            'email_verified_at' => now(),
        ]);

        // Create Manager account
        User::create([
            'name' => 'Manager Account',
            'email' => 'manager@FreshTrack.ph',
            'password' => bcrypt('password'),
            'role' => 'manager',
            'email_verified_at' => now(),
        ]);

        // Create Cashier account
        User::create([
            'name' => 'Cashier Account',
            'email' => 'cashier@FreshTrack.ph',
            'password' => bcrypt('password'),
            'role' => 'cashier',
            'email_verified_at' => now(),
        ]);
    }
}
