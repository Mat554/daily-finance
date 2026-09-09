<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountBalance extends Model
{
    use HasFactory;

    protected $fillable = ['username', 'account_type', 'balance'];

    public const TYPES = ['Cash', 'Bank', 'E-Wallet', 'Savings'];
}
