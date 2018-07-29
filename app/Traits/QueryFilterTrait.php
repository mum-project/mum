<?php

namespace App\Traits;

use App\Http\Filters\QueryFilter;
use Illuminate\Database\Eloquent\Builder;

trait QueryFilterTrait
{
    /**
     * Scope a query to only include models that match
     * the query filters provided by the request.
     *
     * @param Builder     $builder
     * @param QueryFilter $filter
     * @return Builder
     */
    public function scopeFilter(Builder $builder, QueryFilter $filter)
    {
        return $filter->apply($builder);
    }
}
