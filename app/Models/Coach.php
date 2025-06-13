<?php

namespace App\Models;

use App\Models\Contracts\NotifiableEntityInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class Coach extends Model implements NotifiableEntityInterface
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'full_name',
        'email',
        'salary',
        'club_id',
    ];

    public function preferredNotificationChannels(): array
    {
        return ['mail'];
    }

    protected function casts(): array
    {
        return [
            'salary' => 'integer',
        ];
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }
}
