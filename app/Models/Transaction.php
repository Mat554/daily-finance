<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

	protected $fillable = [
        'description',
        'amount',
        'type',
        'transaction_date',
        'username',
        // Category
        'category_id',
        // Income split
        'spend_pct',
        'save_pct',
        'saved_amount',
        'spent_amount',
        'is_split',
        'split_preset',
        // Savings distribution
        'savings_distribution',
        'savings_allocated',
        // Expense category
        'need_or_want',
        'expense_category',
        // Account type
        'account_type',
    ];

	public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
