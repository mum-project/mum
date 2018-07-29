<?php

namespace App\Rules;

use App\Alias;
use App\AliasRequest;
use App\Mailbox;
use Illuminate\Contracts\Validation\Rule;

class UniqueEmailAddress implements Rule
{
    protected $domainId;

    /**
     * Create a new rule instance.
     *
     * @param $domainId
     */
    public function __construct($domainId)
    {
        $this->domainId = $domainId;
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
        $existsInAliases = $this->performExistsQuery(Alias::class, $value);
        $existsInMailboxes = $this->performExistsQuery(Mailbox::class, $value);
        $existsInAliasRequests = $this->performExistsQuery(AliasRequest::class, $value);

        return !$existsInMailboxes && !$existsInAliases && !$existsInAliasRequests;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.unique_email_address');
    }

    /**
     * Check if the combination of local_part and domain_id exists
     * on the provided model class.
     *
     * @param $modelClass
     * @param $localPart
     * @return mixed
     */
    protected function performExistsQuery($modelClass, $localPart)
    {
        return $modelClass::query()
            ->where('local_part', '=', $localPart)
            ->where('domain_id', '=', $this->domainId)
            ->exists();
    }
}
