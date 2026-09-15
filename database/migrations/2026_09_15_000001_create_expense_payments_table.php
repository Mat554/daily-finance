<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_id')->constrained('expenses')->cascadeOnDelete();
            $table->string('username');
            $table->decimal('amount', 15, 2);
            $table->date('payment_date');
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->index(['expense_id', 'username']);
            $table->index(['username', 'payment_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_payments');
    }
};
