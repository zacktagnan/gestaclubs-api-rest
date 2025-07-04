<?php

namespace App\Filters\Player;

use App\Filters\QueryStringFilter;
use Illuminate\Database\Eloquent\Builder;

class SalaryRangeFilter extends QueryStringFilter
{
    protected ?float $min;
    protected ?float $max;

    public function __construct()
    {
        $this->min = request()->query('salary_min');
        $this->max = request()->query('salary_max');
    }

    protected function apply(Builder $builder): Builder
    {
        return $builder
            ->when($this->min !== null, fn($q) => $q->where('salary', '>=', $this->min))
            ->when($this->max !== null, fn($q) => $q->where('salary', '<=', $this->max));
    }

    protected function filterName(): string
    {
        // Este nombre de columna no se usa directamente porque se usan 2 parámetros.
        // Así que se podría devolver como algo genérico o una cadena vacía.
        // Todo para respetar lo que exige el QueryStringFilter
        return 'salary_range';
    }
}
