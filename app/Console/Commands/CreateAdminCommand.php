<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create 
                            {--name= : Admin name}
                            {--email= : Admin email}
                            {--password= : Admin password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new admin user';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = $this->option('name') ?? $this->ask('Enter admin name', 'Admin User');
        $email = $this->option('email') ?? $this->ask('Enter admin email');
        $password = $this->option('password') ?? $this->secret('Enter admin password (min 8 characters)');

        // Validate input
        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        if ($validator->fails()) {
            $this->error('Validation failed:');
            foreach ($validator->errors()->all() as $error) {
                $this->error('  - ' . $error);
            }
            return Command::FAILURE;
        }

        // Check if user already exists
        if (User::where('email', $email)->exists()) {
            $this->error("User with email '{$email}' already exists.");
            
            if (!$this->confirm('Do you want to update this user to admin?', false)) {
                return Command::FAILURE;
            }

            $user = User::where('email', $email)->first();
            $user->update([
                'role' => 'admin',
                'password' => Hash::make($password),
                'is_banned' => false,
                'email_verified_at' => now(),
            ]);

            $this->info("User '{$email}' has been updated to admin successfully!");
            return Command::SUCCESS;
        }

        // Create admin user
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'admin',
            'email_verified_at' => now(),
            'is_banned' => false,
        ]);

        $this->info("Admin user created successfully!");
        $this->table(
            ['Field', 'Value'],
            [
                ['ID', $user->id],
                ['Name', $user->name],
                ['Email', $user->email],
                ['Role', $user->role],
                ['Email Verified', $user->email_verified_at ? 'Yes' : 'No'],
            ]
        );

        return Command::SUCCESS;
    }
}

