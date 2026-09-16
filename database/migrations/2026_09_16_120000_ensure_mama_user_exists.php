<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Ensure the Mama user exists in the users table
        // This is for existing installations where 'Mama' transactions already exist
        DB::table('users')->insertOrIgnore([
            'username' => 'Mama',
            'name' => 'Mama',
            'password' => null,
            'password_change_required' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
    }
};
