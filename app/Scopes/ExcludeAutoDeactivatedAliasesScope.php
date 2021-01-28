<?php

namespace App\Scopes;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ExcludeAutoDeactivatedAliasesScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     * @param  \Illuminate\Database\Eloquent\Model   $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $builder->where('active', true)
            ->orWhere(function (Builder $query) {
                $query->where('active', false)
                    ->where('deactivate_at', '>=', Carbon::now())
                    ->orWhereNull('deactivate_at');
            });
    }
}
