<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class SetUserPassword extends Command
{
    protected $signature = 'user:password {username} {password}';
    protected $description = 'Set or update a user password (for seeded users who cannot change it themselves)';

    public function handle(): int
    {
        $username = $this->argument('username');
        $password = $this->argument('password');

        $user = User::where('username', $username)->first();

        if (!$user) {
            $this->error("User '{$username}' not found.");
            return Command::FAILURE;
        }

        if ($user->password === null) {
            $this->warn("User '{$username}' has no password set (Mama-style). Use this command to set a password for them.");
        }

        $user->update(['password' => Hash::make($password)]);
        $this->info("Password updated for '{$username}'.");
        return Command::SUCCESS;
    }
}
