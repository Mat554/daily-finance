<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // No schema change needed — column already stores string values.
        // Just update the code to accept 'onetime' as a valid frequency.
    }

    public function down(): void
    {
        //
    }
};
