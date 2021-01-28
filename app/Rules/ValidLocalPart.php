<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use function config;
use function filter_var;
use function trans;
use const FILTER_FLAG_EMAIL_UNICODE;
use const FILTER_VALIDATE_EMAIL;

class ValidLocalPart implements Rule
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
     * If the local part validation is not enabled, true is always returned.
     * If unicode characters are not allowed in the local part,
     * the corresponding flag is omitted.
     *
     * @param  string $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (!config('validation.local_part.enabled')) {
            return true;
        }
        if (!config('validation.local_part.unicode')) {
            return !! filter_var($value . '@example.com', FILTER_VALIDATE_EMAIL);
        }
        return !! filter_var($value . '@example.com', FILTER_VALIDATE_EMAIL, FILTER_FLAG_EMAIL_UNICODE);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.local_part');
    }
}
