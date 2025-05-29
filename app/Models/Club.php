<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Club extends Model
{
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
}
