<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // JSON field storing savings distribution: [{category: "Emergency Fund", pct: 40, amount: 400000}, ...]
            $table->json('savings_distribution')->nullable()->after('spent_amount');
            // Flag whether this income has had its savings distributed into categories
            $table->boolean('savings_allocated')->default(false)->after('savings_distribution');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['savings_distribution', 'savings_allocated']);
        });
    }
};
