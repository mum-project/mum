<?php

namespace App\Rules;

use App\Alias;
use Illuminate\Contracts\Validation\Rule;
use \App\Domain;

class AliasesAvailable implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $domainMaxAliases = Domain::query()->find($value)->max_aliases;
        $aliases = Alias::query()->where('domain_id', '=', $value)->count();

        if ($domainMaxAliases === null || $aliases + 1 <= $domainMaxAliases) {
            return true;
        }
        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.max_aliases');
    }
}
