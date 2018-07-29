<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;

class AliasRequestFilter extends QueryFilter
{
    /**
     * Filter alias requests based on their domain.
     *
     * @param $domain
     * @return Builder
     */
    protected function domain($domain)
    {
        return $this->builder->whereHas('domain', function (Builder $query) use ($domain) {
            $query->where('id', $domain)
                ->orWhere('domain', $domain);
        });
    }

    /**
     * Orders alias requests based on their id.
     *
     * @param string $direction
     * @return Builder
     */
    protected function orderById($direction = 'asc')
    {
        return $this->builder->orderBy('id', $direction);
    }

    /**
     * Orders alias requests based on their local part.
     *
     * @param string $direction
     * @return Builder
     */
    protected function orderByLocalPart($direction = 'asc')
    {
        return $this->builder->orderBy('local_part', $direction);
    }

    /**
     * Finds alias requests that match the search query.
     *
     * @param $searchQuery
     * @return Builder
     */
    protected function search($searchQuery)
    {
        return $this->builder->where(function (Builder $query) use ($searchQuery) {
            $query->where('local_part', 'LIKE', $searchQuery . '%')
                ->orWhereHas('domain', function (Builder $query) use ($searchQuery) {
                    $query->where('domain', 'LIKE', $searchQuery . '%');
                });
        });
    }

    /**
     * Filters alias requests based on their status.
     *
     * @param $status
     * @return Builder
     */
    protected function status($status)
    {
        return $this->builder->where('status', $status);
    }
}
