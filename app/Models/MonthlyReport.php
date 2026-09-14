<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonthlyReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'username',
        'year',
        'month',
        'total_income',
        'total_expenses',
        'total_money_out',
        'total_saved',
        'net_balance',
        'condition',
        'score',
        'summary_json',
    ];

    protected $casts = [
        'total_income' => 'float',
        'total_expenses' => 'float',
        'total_money_out' => 'float',
        'total_saved' => 'float',
        'net_balance' => 'float',
        'score' => 'integer',
        'summary_json' => 'array',
    ];

    public function getSummary(): array
    {
        return $this->summary_json ?? [
            'went_well' => [],
            'improvements' => [],
            'recommendations' => [],
        ];
    }
}
