<?php

namespace App\Filters\Player;

use Illuminate\Support\Str;
use App\Filters\QueryStringFilter;
use Illuminate\Database\Eloquent\Builder;

final class SortFilter extends QueryStringFilter
{
    protected array $allowedColumns = [
        'id',
        'full_name',
        'email',
        // otros ...
    ];

    protected ?string $column;
    protected ?string $direction;

    public function __construct()
    {
        $this->column = request()->query('sort_by', 'id');
        $this->direction = strtolower(request()->query($this->filterName(), 'asc'));
    }

    protected function apply(Builder $builder): Builder
    {
        if (!in_array($this->column, $this->allowedColumns)) {
            return $builder; // ignorar si no es una columna válida
        }

        return $builder->orderBy($this->column, Str::lower($this->direction) === 'desc' ? 'desc' : 'asc');
    }

    protected function filterName(): string
    {
        return 'sort';
    }
}
