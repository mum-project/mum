<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;

class SystemServiceFilter extends QueryFilter
{
    /**
     * Finds system services that match the search query.
     *
     * @param $searchQuery
     * @return Builder
     */
    protected function search($searchQuery)
    {
        return $this->builder->where(function (Builder $query) use ($searchQuery) {
            $query->where('service', 'LIKE', $searchQuery . '%')
                ->orWhere('name', 'LIKE', $searchQuery . '%');
        });
    }
}
