<?php

namespace App\Http\Filters;

use function filter_var;
use Illuminate\Database\Eloquent\Builder;

class MailboxFilter extends QueryFilter
{
    /**
     * Filters mailboxes based on their domain.
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
     * Filters mailboxes based on their active state.
     *
     * @param bool $bool
     * @return Builder
     */
    protected function active($bool = true)
    {
        return $this->builder->where('active', filter_var($bool, FILTER_VALIDATE_BOOLEAN));
    }

    /**
     * Filters mailboxes based on their value of is_super_admin.
     *
     * @param bool $bool
     * @return Builder
     */
    protected function isSuperAdmin($bool = true)
    {
        return $this->builder->where('is_super_admin', filter_var($bool, FILTER_VALIDATE_BOOLEAN));
    }

    /**
     * Filters mailboxes based on their value of send_only.
     *
     * @param bool $bool
     * @return Builder
     */
    protected function sendOnly($bool = true)
    {
        return $this->builder->where('send_only', filter_var($bool, FILTER_VALIDATE_BOOLEAN));
    }

    /**
     * Filters mailboxes based on their nullable name.
     *
     * @param bool $bool
     * @return Builder
     */
    protected function hasName($bool = true)
    {
        return filter_var($bool, FILTER_VALIDATE_BOOLEAN) ? $this->builder->whereNotNull('name') :
            $this->builder->whereNull('name');
    }

    /**
     * Filters mailboxes based on their nullable alternative_email.
     *
     * @param bool $bool
     * @return Builder
     */
    protected function hasAlternativeEmail($bool = true)
    {
        return filter_var($bool, FILTER_VALIDATE_BOOLEAN) ? $this->builder->whereNotNull('alternative_email') :
            $this->builder->whereNull('alternative_email');
    }

    /**
     * Filters mailboxes based on their nullable quota.
     *
     * @param bool $bool
     * @return Builder
     */
    protected function hasQuota($bool = true)
    {
        return filter_var($bool, FILTER_VALIDATE_BOOLEAN) ? $this->builder->whereNotNull('quota') :
            $this->builder->whereNull('quota');
    }

    /**
     * Filters mailboxes based on their relationship to a sending alias.
     *
     * @param $alias
     * @return Builder
     */
    protected function sendingAlias($alias)
    {
        return $this->builder->whereHas('sendingAliases', function (Builder $query) use ($alias) {
            $query->where('alias_id', $alias);
        });
    }

    /**
     * Filters mailboxes based on their relationship to a sending alias.
     *
     * @param $alias
     * @return Builder
     */
    protected function receivingAlias($alias)
    {
        return $this->builder->whereHas('receivingAliases', function (Builder $query) use ($alias) {
            $query->where('alias_id', $alias);
        });
    }

    /**
     * Filters mailboxes based on their relationship to a domain they administrate.
     *
     * @param $domain
     * @return Builder
     */
    protected function administratedDomain($domain)
    {
        return $this->builder->whereHas('administratedDomains', function (Builder $query) use ($domain) {
            $query->where('domain_id', $domain);
        });
    }

    /**
     * Orders mailboxes based on their value of local_part.
     *
     * @param string $direction
     * @return Builder
     */
    protected function orderByLocalPart($direction = 'asc')
    {
        return $this->builder->orderBy('local_part', $direction);
    }

    /**
     * Orders mailboxes based on their id.
     *
     * @param string $direction
     * @return Builder
     */
    protected function orderById($direction = 'asc')
    {
        return $this->builder->orderBy('id', $direction);
    }

    /**
     * Orders mailboxes based on their name.
     *
     * @param string $direction
     * @return Builder
     */
    protected function orderByName($direction = 'asc')
    {
        return $this->builder->orderBy('name', $direction);
    }

    /**
     * Finds mailboxes that match the search query.
     *
     * @param $searchQuery
     * @return Builder
     */
    protected function search($searchQuery)
    {
        return $this->builder->where(function (Builder $query) use ($searchQuery) {
            $query->where('local_part', 'LIKE', $searchQuery . '%')
                ->orWhere('name', 'LIKE', $searchQuery . '%')
                ->orWhereHas('domain', function (Builder $query) use ($searchQuery) {
                    $query->where('domain', 'LIKE', $searchQuery . '%');
                });
        });
    }
}
