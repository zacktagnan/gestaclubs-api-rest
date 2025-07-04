<?php

namespace App\Filters\Player;

use App\Filters\QueryStringFilter;
use Illuminate\Database\Eloquent\Builder;

class EmailFilter extends QueryStringFilter
{
    protected ?string $value;

    public function __construct()
    {
        $this->value = request()->query($this->filterName());
    }

    protected function apply(Builder $builder): Builder
    {
        return $builder->where('email', 'like', '%' . $this->value . '%');
    }

    protected function filterName(): string
    {
        return 'email';
    }
}
