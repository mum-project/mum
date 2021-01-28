<?php

namespace App\Http\Controllers;

use App\Http\Filters\QueryFilter;
use Illuminate\Http\Request;
use function array_push;

class ControllerHelper
{
    /** @var Request */
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @param QueryFilter $queryFilter
     * @return array
     */
    public function generateSearchHiddenInputValues(QueryFilter $queryFilter)
    {
        $hiddenInputValues = [];

        foreach ($queryFilter->appliedFilters() as $key => $value) {
            array_push($hiddenInputValues, [
                'name'  => $key,
                'value' => $value
            ]);
        }

        return $hiddenInputValues;
    }
}
