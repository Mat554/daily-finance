<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->unsignedTinyInteger('due_date')->nullable()->after('rollover');
            $table->boolean('is_credit')->default(false)->after('due_date');
            $table->decimal('credit_limit', 15, 2)->nullable()->after('is_credit');
            $table->decimal('current_balance', 15, 2)->nullable()->after('credit_limit');
            $table->decimal('minimum_payment', 15, 2)->nullable()->after('current_balance');
            $table->unsignedTinyInteger('reminder_days')->default(3)->after('minimum_payment');
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn([
                'due_date',
                'is_credit',
                'credit_limit',
                'current_balance',
                'minimum_payment',
                'reminder_days',
            ]);
        });
    }
};
