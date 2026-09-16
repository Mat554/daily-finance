<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['username' => 'Mama'],
            [
                'name' => 'Mama',
                'password' => null,
                'password_change_required' => false,
            ]
        );
    }
}
