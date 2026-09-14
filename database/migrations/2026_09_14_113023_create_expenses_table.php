<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('username');
            $table->string('name');
            $table->enum('category', ['fixed', 'variable'])->default('variable');
            $table->decimal('allocated_amount', 15, 2)->default(0);
            $table->json('keywords')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('rollover')->default(false);
            $table->timestamps();

            $table->index(['username', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
