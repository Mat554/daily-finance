<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

	protected $fillable = [
        'description',
        'amount',
        'type',
        'transaction_date',
        'username',
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
}
