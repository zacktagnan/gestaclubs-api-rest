<?php

namespace App\Filters;

use Closure;
use Illuminate\Database\Eloquent\Builder;

abstract class QueryStringFilter
{
    public function handle(Builder $builder, Closure $next): Builder
    {
        if (!request()->query($this->filterName())) {
            return $next($builder);
        }

        return $next($this->apply($builder));
    }

    abstract protected function apply(Builder $builder): Builder;

    abstract protected function filterName(): string;
}
