<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
  public function up()
{
    Schema::create('transactions', function (Blueprint $table) {
        $table->id();
        $table->string('description'); // e.g., "Coffee"
        $table->decimal('amount', 10, 2); // e.g., 50000.00
        $table->enum('type', ['in', 'out']);
        $table->date('transaction_date'); // 'in' for money put, 'out' for thrown away
        $table->timestamps(); // Tracks created_at automatically
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
