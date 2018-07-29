<?php

namespace App\Http\Filters;

use function array_key_exists;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use TypeError;

abstract class QueryFilter
{
    /** @var Builder */
    protected $builder;

    /** @var array */
    protected $filters = [];

    /** @var array */
    protected $appliedFilters = [];

    /**
     * QueryFilter constructor.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        foreach ($request->all() as $name => $value) {
            if (method_exists($this, $name)) {
                $this->filters[$name] = $value;
            }
        }
    }

    /**
     * Apply the filters from the request to an Eloquent query.
     *
     * @param Builder $builder
     * @return Builder
     */
    public function apply(Builder $builder)
    {
        $this->builder = $builder;

        foreach ($this->filters as $name => $value) {
            if (method_exists($this, $name)) {
                try {
                    call_user_func_array([
                        $this,
                        $name
                    ], [$value]);
                    $this->appliedFilters[$name] = $value;
                } catch (TypeError $error) {
                    Log::debug('A query filter was used without required parameters', [$error->getMessage()]);
                }
            }
        }

        return $this->builder;
    }

    /**
     * Gets all filters that are specified by the request
     * and that actually exist.
     *
     * @return array
     */
    public function filters()
    {
        return $this->filters;
    }

    /**
     * Gets all filters that are specified by the request,
     * that actually exist and that did not throw a type error.
     *
     * @return mixed
     */
    public function appliedFilters()
    {
        return $this->appliedFilters;
    }

    /**
     * Asserts whether a specified filter was present in the request.
     *
     * @param $name
     * @return bool
     */
    public function hasFilter($name)
    {
        return array_key_exists($name, $this->filters);
    }

    /**
     * Manually add a filter to the filters that were present in the request.
     *
     * @param      $name
     * @param null $value
     */
    public function addFilter($name, $value = null)
    {
        if (method_exists($this, $name)) {
            $this->filters[$name] = $value;
        }
    }
}
