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
        'salary',
        // 'club_name', // pasado a la allowedRelations
        // otros ...
    ];

    protected array $allowedRelations = [
        'club_name' => [
            'relation' => 'club',
            'table' => 'clubs',
            'column' => 'name',
            'column_relation' => 'club_id',
        ],
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
        // dump('Desde el APPLY del SortFilter:', request()->query('sort'));

        if (
            !in_array($this->column, $this->allowedColumns)
            && !array_key_exists($this->column, $this->allowedRelations)
        ) {
            return $builder; // ignorar si no es una columna válida
        }

        if (array_key_exists($this->column, $this->allowedRelations)) {
            return $this->applyRelationSort($builder);
        }

        return $builder->orderBy($this->column, Str::lower($this->direction) === 'desc' ? 'desc' : 'asc');
    }

    protected function applyRelationSort(Builder $builder): Builder
    {
        $relation = $this->allowedRelations[$this->column];
        $foreignKey = $relation['column_relation'] ?? "{$relation['relation']}_id";

        return $builder
            ->leftJoin("{$relation['table']}", "players.{$foreignKey}", '=', "{$relation['table']}.id")
            ->orderBy("{$relation['table']}.{$relation['column']}", $this->direction === 'desc' ? 'desc' : 'asc')
            ->select('players.*');
    }

    protected function filterName(): string
    {
        return 'sort';
    }
}
