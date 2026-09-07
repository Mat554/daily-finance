<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Income split fields (only populated when type='in' and user consciously splits)
            $table->decimal('spend_pct', 5, 2)->nullable()->after('type');
            $table->decimal('save_pct', 5, 2)->nullable()->after('spend_pct');
            $table->decimal('saved_amount', 12, 2)->nullable()->after('save_pct');
            $table->decimal('spent_amount', 12, 2)->nullable()->after('saved_amount');
            $table->boolean('is_split')->default(false)->after('spent_amount');
            $table->string('split_preset')->nullable()->after('is_split');

            // Expense category fields (only populated when type='out')
            $table->string('need_or_want')->nullable()->after('split_preset');
            $table->string('expense_category')->nullable()->after('need_or_want');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn([
                'spend_pct', 'save_pct', 'saved_amount', 'spent_amount',
                'is_split', 'split_preset', 'need_or_want', 'expense_category',
            ]);
        });
    }
};
