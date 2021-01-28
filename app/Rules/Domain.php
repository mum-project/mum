<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use function config;
use function filter_var;
use function trans;
use const FILTER_FLAG_HOSTNAME;
use const FILTER_VALIDATE_DOMAIN;

class Domain implements Rule
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
     * @param  string $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (!config('validation.domain.enabled')) {
            return true;
        }

        if (!config('validation.domain.hostname')) {
            return !! filter_var($value, FILTER_VALIDATE_DOMAIN);
        }

        return !! filter_var($value, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return config('validation.domain.hostname') ? trans('validation.domain_hostname') : trans('validation.domain');
    }
}
