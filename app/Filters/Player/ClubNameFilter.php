<?php

namespace App\Filters\Player;

use App\Filters\QueryStringFilter;
use Illuminate\Database\Eloquent\Builder;

class ClubNameFilter extends QueryStringFilter
{
    protected function apply(Builder $builder): Builder
    {
        return $builder->whereHas('club', function (Builder $query) {
            $clubName = request()->query($this->filterName());

            $query->where('name', 'like', '%' . $clubName . '%');
        });
    }

    protected function filterName(): string
    {
        return 'club_name';
    }
}
