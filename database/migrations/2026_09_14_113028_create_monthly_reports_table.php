<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monthly_reports', function (Blueprint $table) {
            $table->id();
            $table->string('username');
            $table->year('year');
            $table->tinyInteger('month');
            $table->decimal('total_income', 15, 2)->default(0);
            $table->decimal('total_expenses', 15, 2)->default(0);
            $table->decimal('total_money_out', 15, 2)->default(0);
            $table->decimal('total_saved', 15, 2)->default(0);
            $table->decimal('net_balance', 15, 2)->default(0);
            $table->string('condition');
            $table->tinyInteger('score')->default(0);
            $table->json('summary_json')->nullable();
            $table->timestamps();

            $table->unique(['username', 'year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_reports');
    }
};
