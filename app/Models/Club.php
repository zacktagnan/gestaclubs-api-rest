<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Club extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'budget',
    ];

    protected function casts(): array
    {
        return [
            'budget' => 'integer',
        ];
    }

    public function coach(): HasOne
    {
        return $this->hasOne(Coach::class);
    }

    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }

    public function getInvestedBudget(): int
    {
        // return $this->players()->sum('salary') + ($this->coach?->salary ?? 0);
        return $this->players()->sum('salary') + ($this->coach()->first()?->salary ?? 0);
    }
}
