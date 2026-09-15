<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE expenses DROP CONSTRAINT IF EXISTS expenses_payment_frequency_check');
        DB::statement("ALTER TABLE expenses ADD CONSTRAINT expenses_payment_frequency_check CHECK (payment_frequency IN ('monthly', 'biweekly', 'weekly', 'onetime'))");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE expenses DROP CONSTRAINT IF EXISTS expenses_payment_frequency_check');
        DB::statement("ALTER TABLE expenses ADD CONSTRAINT expenses_payment_frequency_check CHECK (payment_frequency IN ('monthly', 'biweekly', 'weekly'))");
    }
};
