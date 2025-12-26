<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::firstOrCreate(
            ['email' => 'admin@ebrook.com.tw'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        // Create regular test user
        User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
                'role' => 'user',
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Admin and test users created successfully.');
        $this->command->info('Admin: admin@ebrook.com.tw / password');
        $this->command->info('User: user@example.com / password');
    }
}

