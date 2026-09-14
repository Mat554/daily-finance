<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'username',
        'name',
        'category',
        'allocated_amount',
        'keywords',
        'is_active',
        'rollover',
    ];

    protected $casts = [
        'keywords' => 'array',
        'is_active' => 'boolean',
        'rollover' => 'boolean',
        'allocated_amount' => 'float',
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
