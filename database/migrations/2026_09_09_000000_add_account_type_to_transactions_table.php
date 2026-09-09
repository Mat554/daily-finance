<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('account_type')->nullable()->after('expense_category');
        });

        Schema::create('account_balances', function (Blueprint $table) {
            $table->id();
            $table->string('username');
            $table->string('account_type'); // Cash, Bank, E-Wallet, Savings
            $table->decimal('balance', 15, 2)->default(0);
            $table->timestamps();
            $table->unique(['username', 'account_type']);
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('account_type');
        });
        Schema::dropIfExists('account_balances');
    }
};
