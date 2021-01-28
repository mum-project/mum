<?php

namespace App\Rules;

use App\Domain;
use App\Mailbox;
use Illuminate\Contracts\Validation\Rule;

class MailboxesAvailable implements Rule
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
        $domainMaxMailboxes = Domain::query()->find($value)->max_mailboxes;
        $mailboxes = Mailbox::query()->where('domain_id', '=', $value)->count();

        if ($domainMaxMailboxes === null || $mailboxes + 1 <= $domainMaxMailboxes) {
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
        return trans('validation.max_mailboxes');
    }
}
