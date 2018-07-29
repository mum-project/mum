<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;

class DomainFilter extends QueryFilter
{
    /**
     * Filters domains based on their active state.
     *
     * @param bool $bool
     * @return Builder
     */
    protected function active($bool = true)
    {
        return $this->builder->where('active', filter_var($bool, FILTER_VALIDATE_BOOLEAN));
    }

    /**
     * Filters domains based on their relationship to a mailbox
     * that administrates the domain.
     *
     * @param $mailbox
     * @return Builder
     */
    protected function admin($mailbox)
    {
        return $this->builder->whereHas('admins', function (Builder $query) use ($mailbox) {
            $query->where('mailbox_id', $mailbox);
        });
    }

    /**
     * Orders domains based on their id.
     *
     * @param string $direction
     * @return Builder
     */
    protected function orderById($direction = 'asc')
    {
        return $this->builder->orderBy('id', $direction);
    }

    /**
     * Orders domains based on their domain value.
     *
     * @param string $direction
     * @return Builder
     */
    protected function orderByDomain($direction = 'asc')
    {
        return $this->builder->orderBy('domain', $direction);
    }

    /**
     * Orders domains based on their quota.
     *
     * @param string $direction
     * @return Builder
     */
    protected function orderByQuota($direction = 'asc')
    {
        return $this->builder->orderBy('quota', $direction);
    }

    /**
     * Orders domains based on their max_quota.
     *
     * @param string $direction
     * @return Builder
     */
    protected function orderByMaxQuota($direction = 'asc')
    {
        return $this->builder->orderBy('max_quota', $direction);
    }

    /**
     * Orders domains based on their max_aliases.
     *
     * @param string $direction
     * @return Builder
     */
    protected function orderByMaxAliases($direction = 'asc')
    {
        return $this->builder->orderBy('max_aliases', $direction);
    }

    /**
     * Orders domains based on their max_mailboxes.
     *
     * @param string $direction
     * @return Builder
     */
    protected function orderByMaxMailboxes($direction = 'asc')
    {
        return $this->builder->orderBy('max_mailboxes', $direction);
    }

    /**
     * Finds domains that match the search query.
     *
     * @param $searchQuery
     * @return Builder
     */
    protected function search($searchQuery)
    {
        return $this->builder->where('domain', 'LIKE', $searchQuery . '%');
    }
}
