<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Expense extends Model
{
    use HasFactory;

    public function payments(): HasMany
    {
        return $this->hasMany(ExpensePayment::class);
    }

    public function paymentsForPeriod(\DateTimeInterface $start, \DateTimeInterface $end): HasMany
    {
        return $this->payments()
            ->whereBetween('payment_date', [$start->format('Y-m-d'), $end->format('Y-m-d')]);
    }

    protected $fillable = [
        'username',
        'name',
        'category',
        'allocated_amount',
        'keywords',
        'is_active',
        'rollover',
        'due_date',
        'is_credit',
        'credit_limit',
        'current_balance',
        'minimum_payment',
        'reminder_days',
        'payment_frequency',
    ];

    protected $casts = [
        'keywords' => 'array',
        'is_active' => 'boolean',
        'rollover' => 'boolean',
        'allocated_amount' => 'float',
        'is_credit' => 'boolean',
        'credit_limit' => 'float',
        'current_balance' => 'float',
        'minimum_payment' => 'float',
        'reminder_days' => 'integer',
    ];

    public function matchesTransaction(string $description): bool
    {
        $keywords = $this->keywords ?? [];
        $descLower = strtolower($description);

        foreach ($keywords as $keyword) {
            if (stripos($descLower, strtolower($keyword)) !== false) {
                return true;
            }
        }

        return false;
    }
}
