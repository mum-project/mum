<?php

namespace App\Http\Filters;

use App\Scopes\ExcludeAutoDeactivatedAliasesScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use function explode;
use function is_array;
use function is_string;

class AliasFilter extends QueryFilter
{

    /**
     * Filter aliases based on their domain.
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
     * Filters aliases based on their sender mailboxes.
     *
     * @param $mailboxes
     * @return Builder
     */
    protected function senderMailboxes($mailboxes)
    {
        if (is_array($mailboxes)) {
            return $this->builder->where(function (Builder $query) use ($mailboxes) {
                $firstElement = true;
                foreach ($mailboxes as $mailbox) {
                    if ($firstElement) {
                        $query->whereHas('senderMailboxes', function (Builder $query) use ($mailbox) {
                            $query->where('mailbox_id', $mailbox);
                        });
                        $firstElement = false;
                    }
                    $query->orWhereHas('senderMailboxes', function (Builder $query) use ($mailbox) {
                        $query->where('mailbox_id', $mailbox);
                    });
                }
            });
        }
        return $this->builder->whereHas('senderMailboxes', function (Builder $query) use ($mailboxes) {
            $query->where('mailbox_id', $mailboxes);
        });
    }

    /**
     * Filters aliases based on their recipient mailboxes.
     * ATTENTION: external recipient email addresses are NOT included in this filter.
     *
     * @param $mailboxes
     * @return Builder
     */
    protected function recipientMailboxes($mailboxes)
    {
        if (is_array($mailboxes)) {
            return $this->builder->where(function (Builder $query) use ($mailboxes) {
                $firstElement = true;
                foreach ($mailboxes as $mailbox) {
                    if ($firstElement) {
                        $query->whereHas('recipientMailboxes', function (Builder $query) use ($mailbox) {
                            $query->where('mailbox_id', $mailbox);
                        });
                        $firstElement = false;
                    }
                    $query->orWhereHas('recipientMailboxes', function (Builder $query) use ($mailbox) {
                        $query->where('mailbox_id', $mailbox);
                    });
                }
            });
        }
        return $this->builder->whereHas('recipientMailboxes', function (Builder $query) use ($mailboxes) {
            $query->where('mailbox_id', $mailboxes);
        });
    }

    /**
     * Filters aliases based on their sender or recipient mailboxes.
     * ATTENTION: external recipient email addresses are NOT included in this filter.
     *
     * @param $mailboxes
     * @return Builder
     */
    protected function senderOrRecipientMailboxes($mailboxes)
    {
        if (is_array($mailboxes)) {
            return $this->builder->where(function (Builder $query) use ($mailboxes) {
                $firstElement = true;
                foreach ($mailboxes as $mailbox) {
                    if ($firstElement) {
                        $query->whereHas('senderMailboxes', function (Builder $query) use ($mailbox) {
                            $query->where('mailbox_id', $mailbox);
                        })
                            ->orWhereHas('recipientMailboxes', function (Builder $query) use ($mailbox) {
                                $query->where('mailbox_id', $mailbox);
                            });
                        $firstElement = false;
                    }
                    $query->orWhereHas('senderMailboxes', function (Builder $query) use ($mailbox) {
                        $query->where('mailbox_id', $mailbox);
                    })
                        ->orWhereHas('recipientMailboxes', function (Builder $query) use ($mailbox) {
                            $query->where('mailbox_id', $mailbox);
                        });
                }
            });
        }
        return $this->builder->whereHas('senderMailboxes', function (Builder $query) use ($mailboxes) {
            $query->where('mailbox_id', $mailboxes);
        })
            ->orWhereHas('recipientMailboxes', function (Builder $query) use ($mailboxes) {
                $query->where('mailbox_id', $mailboxes);
            });
    }

    /**
     * Filters aliases based on their sender and recipient mailboxes.
     * ATTENTION: external recipient email addresses are NOT included in this filter.
     *
     * @param $mailboxes
     * @return Builder
     */
    protected function senderAndRecipientMailboxes($mailboxes)
    {
        if (is_array($mailboxes)) {
            return $this->builder->where(function (Builder $query) use ($mailboxes) {
                $firstElement = true;
                foreach ($mailboxes as $mailbox) {
                    if ($firstElement) {
                        $query->whereHas('senderMailboxes', function (Builder $query) use ($mailbox) {
                            $query->where('mailbox_id', $mailbox);
                        })
                            ->whereHas('recipientMailboxes', function (Builder $query) use ($mailbox) {
                                $query->where('mailbox_id', $mailbox);
                            });
                        $firstElement = false;
                    }
                    $query->orWhereHas('senderMailboxes', function (Builder $query) use ($mailbox) {
                        $query->where('mailbox_id', $mailbox);
                    })
                        ->whereHas('recipientMailboxes', function (Builder $query) use ($mailbox) {
                            $query->where('mailbox_id', $mailbox);
                        });
                }
            });
        }
        return $this->builder->whereHas('senderMailboxes', function (Builder $query) use ($mailboxes) {
            $query->where('mailbox_id', $mailboxes);
        })
            ->whereHas('recipientMailboxes', function (Builder $query) use ($mailboxes) {
                $query->where('mailbox_id', $mailboxes);
            });
    }

    /**
     * Filters aliases based on their sender mailbox.
     * ATTENTION: external recipient email addresses are NOT included in this filter.
     *
     * @param $mailbox
     * @return Builder
     */
    protected function senderMailbox($mailbox)
    {
        return $this->senderMailboxes($mailbox);
    }

    /**
     * Filters aliases based on their recipient mailbox.
     * ATTENTION: external recipient email addresses are NOT included in this filter.
     *
     * @param $mailbox
     * @return Builder
     */
    protected function recipientMailbox($mailbox)
    {
        return $this->recipientMailboxes($mailbox);
    }

    /**
     * Filters aliases based on their sender or recipient mailbox.
     * ATTENTION: external recipient email addresses are NOT included in this filter.
     *
     * @param $mailbox
     * @return Builder
     */
    protected function senderOrRecipientMailbox($mailbox)
    {
        return $this->senderOrRecipientMailboxes($mailbox);
    }

    /**
     * Filters aliases based on their sender and recipient mailbox.
     * ATTENTION: external recipient email addresses are NOT included in this filter.
     *
     * @param $mailbox
     * @return Builder
     */
    protected function senderAndRecipientMailbox($mailbox)
    {
        return $this->senderAndRecipientMailboxes($mailbox);
    }

    /**
     * Filters aliases based on their recipient email addresses.
     *
     * @param $addresses
     * @return Builder
     */
    protected function recipientAddresses($addresses)
    {
        if (is_array($addresses)) {
            return $this->builder->join('alias_recipients', 'aliases.id', '=', 'alias_recipients.alias_id')
                ->where(function (Builder $query) use ($addresses) {
                    $firstElement = true;
                    foreach ($addresses as $address) {
                        if ($firstElement) {
                            $query->where('alias_recipients.recipient_address', $address);
                            $firstElement = false;
                        }
                        $query->orWhere('alias_recipients.recipient_address', $address);
                    }
                })->select('aliases.*');
        }
        return $this->builder->join('alias_recipients', 'aliases.id', '=', 'alias_recipients.alias_id')
            ->where('alias_recipients.recipient_address', $addresses)
            ->select('aliases.*');
    }

    /**
     * Filters aliases based on their recipient email address.
     *
     * @param $address
     * @return Builder
     */
    protected function recipientAddress($address)
    {
        return $this->recipientAddresses($address);
    }

    /**
     * Filters aliases based on their active state.
     *
     * @param bool $bool
     * @return Builder
     */
    protected function active($bool = true)
    {
        return $this->builder->where('active', filter_var($bool, FILTER_VALIDATE_BOOLEAN));
    }

    /**
     * Filters aliases based on their nullable description.
     *
     * @param bool $bool
     * @return Builder
     */
    protected function hasDescription($bool = true)
    {
        return filter_var($bool, FILTER_VALIDATE_BOOLEAN) ? $this->builder->whereNotNull('description') :
            $this->builder->whereNull('description');
    }

    /**
     * Orders aliases based on their id.
     *
     * @param string $direction
     * @return Builder
     */
    protected function orderById($direction = 'asc')
    {
        return $this->builder->orderBy('id', $direction);
    }

    /**
     * Orders aliases based on their local part.
     *
     * @param string $direction
     * @return Builder
     */
    protected function orderByLocalPart($direction = 'asc')
    {
        return $this->builder->orderBy('local_part', $direction);
    }

    /**
     * Finds aliases that match the search query.
     *
     * @param $searchQuery
     * @return Builder
     */
    protected function search($searchQuery)
    {
        $exploded = is_string($searchQuery) ? explode('@', $searchQuery) : [];

        return $this->builder->where(function (Builder $query) use ($searchQuery, $exploded) {
            $query->where('local_part', 'LIKE', $searchQuery . '%')
                ->orWhereHas('domain', function (Builder $query) use ($searchQuery) {
                    $query->where('domain', 'LIKE', $searchQuery . '%');
                });

            if (sizeof($exploded) >= 2) {
                $query->orWhere(function (Builder $query) use ($exploded) {
                    $query->where('local_part', '=', $exploded[0])
                        ->whereHas('domain', function (Builder $query) use ($exploded) {
                            $query->where('domain', 'LIKE', $exploded[1] . '%');
                        });
                });
            }
        });
    }

    /**
     * Filters aliases on whether they were automatically deactivated.
     *
     * @return Builder
     */
    protected function automaticallyDeactivated()
    {
        return $this->builder->withoutGlobalScope(ExcludeAutoDeactivatedAliasesScope::class)
            ->where('active', false)
            ->where('deactivate_at', '<', Carbon::now());
    }
}
