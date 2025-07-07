<?php

namespace App\Models;

use App\Filters\Player\ClubNameFilter;
use App\Filters\Player\EmailFilter;
use App\Filters\Player\FullNameFilter;
use App\Filters\Player\SalaryRangeFilter;
use App\Filters\Player\SortFilter;
use App\Models\Contracts\NotifiableEntityInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Pipeline\Pipeline;

class Player extends Model implements NotifiableEntityInterface
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

    public function scopeFilteredWithPipeline(Builder $builder, array $filtersToIgnore = []): Builder
    {
        // return app(Pipeline::class)
        //     ->send($builder)
        //     ->through([
        //         FullNameFilter::class,
        //         EmailFilter::class,
        //         ClubNameFilter::class,
        //         SalaryRangeFilter::class,
        //         SortFilter::class,
        //     ])
        //     ->thenReturn();

        // ahora, considerando los $filtersToIgnore ...

        $filterPipes = collect([
            FullNameFilter::class,
            EmailFilter::class,
            ClubNameFilter::class,
            SalaryRangeFilter::class,
            SortFilter::class,
        ])->reject(fn($filter) => in_array($filter, $filtersToIgnore))
            ->all();

        return app(Pipeline::class)
            ->send($builder)
            ->through($filterPipes)
            ->thenReturn();
    }
}
