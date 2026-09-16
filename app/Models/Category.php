<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = ['username', 'name', 'icon', 'color', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'username', 'username');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function scopeForUser($query, string $username)
    {
        return $query->where('username', $username);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
