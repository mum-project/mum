<?php

namespace App;

use App\Interfaces\Integratable;
use App\Traits\QueryFilterTrait;
use App\Traits\SizeMeasurable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Domain extends Model implements Integratable
{
    use HasFactory, QueryFilterTrait, SizeMeasurable;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id',
        'created_at',
        'updated_at'
    ];

    /**
     * Gets all mailboxes that belong to this domain.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function mailboxes()
    {
        return $this->hasMany(Mailbox::class);
    }

    /**
     * Gets all aliases that belong to this domain.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function aliases()
    {
        return $this->hasMany(Alias::class);
    }

    /**
     * Gets all mailboxes that have the right to administrate this domain.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function admins()
    {
        return $this->belongsToMany(Mailbox::class, 'domain_admins');
    }

    /**
     * Checks whether the domain has enough buffer of available mailboxes or if the maximum amount of mailboxes
     * will be reached shortly. The calculation uses the $percentage parameter to determine what a dangerous
     * level is. By default this parameter is 0.2 (20%).
     *
     * @param float $percentage
     * @return bool
     */
    public function isMailboxContingentShort(float $percentage = 0.2)
    {
        return $this->max_mailboxes &&
            $this->max_mailboxes - $this->mailboxes->count() < $this->max_mailboxes * $percentage;
    }

    /**
     * Checks whether the domain has enough buffer of available aliases or if the maximum amount of aliases
     * will be reached shortly. The calculation uses the $percentage parameter to determine what a dangerous
     * level is. By default this parameter is 0.2 (20%).
     *
     * @param float $percentage
     * @return bool
     */
    public function isAliasContingentShort(float $percentage = 0.2)
    {
        return $this->max_aliases && $this->max_aliases - $this->aliases->count() < $this->max_aliases * $percentage;
    }

    /**
     * Gets all available placeholders for integrations.
     * Example: ['placeholder' => $model->value]
     *
     * @return array
     */
    public function getIntegratablePlaceholders()
    {
        return [
            'id'            => $this->id,
            'domain'        => $this->domain,
            'description'   => $this->description,
            'quota'         => $this->quota,
            'max_quota'     => $this->max_quota,
            'max_aliases'   => $this->max_aliases,
            'max_mailboxes' => $this->max_mailboxes,
            'active'        => $this->active
        ];
    }

    /**
     * Gets the class name of the integratable.
     *
     * @return string
     */
    public function getIntegratableClassName()
    {
        return static::class;
    }

    /**
     * Gets all alias requests that belong to this domain.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function aliasRequests()
    {
        return $this->hasMany(AliasRequest::class);
    }

    /**
     * Scope a query to only include domains that the authenticated
     * mailbox user is authorized to view.
     *
     * @param Builder      $query
     * @param Mailbox|null $mailbox
     * @return Builder
     */
    public function scopeWhereAuthorized(Builder $query, Mailbox $mailbox = null)
    {
        if (isUserSuperAdmin()) {
            return $query;
        }
        return $query->whereHas('admins', function (Builder $query) use ($mailbox) {
            $query->where('mailbox_id', $mailbox ? $mailbox->id : Auth::id());
        });
    }
}
