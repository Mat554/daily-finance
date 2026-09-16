<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'username',
        'email',
        'name',
        'password',
        'password_change_required',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'password_change_required' => 'boolean',
        ];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'username', 'username');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'username', 'username');
    }

    public function expensePayments(): HasMany
    {
        return $this->hasMany(ExpensePayment::class, 'username', 'username');
    }

    public function monthlyReports(): HasMany
    {
        return $this->hasMany(MonthlyReport::class, 'username', 'username');
    }

    public function accountBalances(): HasMany
    {
        return $this->hasMany(AccountBalance::class, 'username', 'username');
    }

    public function isMama(): bool
    {
        return strtolower($this->username) === 'mama';
    }

}
