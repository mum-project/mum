<?php

namespace App;

use App\Interfaces\Integratable;
use App\Scopes\ExcludeAutoDeactivatedAliasesScope;
use App\Traits\QueryFilterTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Alias extends Model implements Integratable
{
    use QueryFilterTrait;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $dates = ['deactivate_at'];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new ExcludeAutoDeactivatedAliasesScope);
    }

    /**
     * Gets the domain that this alias belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function domain()
    {
        return $this->belongsTo(Domain::class);
    }

    /**
     * Gets all mailboxes that have permission to send emails with this alias as the sender address (MAIL FROM).
     * This collection does NOT include mailboxes that match one of the destination addresses of the alias.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function senderMailboxes()
    {
        return $this->belongsToMany(Mailbox::class, 'alias_senders');
    }


    /**
     * Gets all mailboxes that receive emails sent to this alias.
     * This collection does NOT include external addresses.
     *
     * ATTENTION:   Since the pivot table alias_recipients also holds external recipient addresses,
     *              you have to insert values into the table manually.
     *              It is NOT possible to use the Eloquent relationship save() method.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function recipientMailboxes()
    {
        return $this->belongsToMany(Mailbox::class, 'alias_recipients');
    }

    /**
     * Gets all entries of alias recipients, whether they are local mailboxes or external addresses.
     *
     * @return \Illuminate\Support\Collection
     */
    public function recipients()
    {
        return DB::table('alias_recipients')
            ->where('alias_id', '=', $this->id)
            ->get();
    }

    /**
     * Gets all external alias recipient addresses.
     *
     * @return \Illuminate\Support\Collection
     */
    public function externalRecipients()
    {
        return DB::table('alias_recipients')
            ->where('alias_id', '=', $this->id)
            ->whereNull('mailbox_id')
            ->get();
    }

    /**
     * Gets a collection of external recipients that is
     * compatible with the Javascript frontend code.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getExternalRecipientResource()
    {
        return $this->externalRecipients()
            ->map(function ($externalRecipient) {
                return [
                    'id'      => $externalRecipient->id,
                    'address' => $externalRecipient->recipient_address
                ];
            });
    }

    /**
     * Gets all recipient addresses (as a string) INCLUDING external ones.
     *
     * @return mixed
     */
    public function recipientAddresses()
    {
        return DB::table('alias_recipients')
            ->where('alias_id', $this->id)
            ->pluck('recipient_address');
    }

    /**
     * Gets the complete email address built from the local_part and the domain of this alias.
     *
     * @return string
     */
    public function address()
    {
        return $this->local_part . '@' . $this->domain->domain;
    }

    /**
     * Add a mailbox to the recipients of this alias.
     *
     * @param Mailbox $mailbox
     * @return bool
     */
    public function addRecipientMailbox(Mailbox $mailbox)
    {
        return DB::table('alias_recipients')
            ->insert([
                'alias_id'          => $this->id,
                'recipient_address' => $mailbox->address(),
                'mailbox_id'        => $mailbox->id
            ]);
    }

    /**
     * Remove a mailbox from the recipients of this alias.
     *
     * @param Mailbox $mailbox
     * @return bool
     */
    public function removeRecipientMailbox(Mailbox $mailbox)
    {
        return DB::table('alias_recipients')
            ->where([
                [
                    'alias_id',
                    '=',
                    $this->id
                ],
                [
                    'mailbox_id',
                    '=',
                    $mailbox->id
                ]
            ])
            ->delete();
    }

    /**
     * Add an external email address to the recipients of this alias.
     *
     * @param string $address
     * @return bool
     */
    public function addExternalRecipient(string $address)
    {
        return DB::table('alias_recipients')
            ->insert([
                'alias_id'          => $this->id,
                'recipient_address' => $address
            ]);
    }

    /**
     * Remove an external email address from the recipients of this alias.
     *
     * @param string $address
     * @return bool
     */
    public function removeExternalRecipient(string $address)
    {
        return DB::table('alias_recipients')
            ->whereNull('mailbox_id')
            ->where([
                [
                    'alias_id',
                    '=',
                    $this->id
                ],
                [
                    'recipient_address',
                    '=',
                    $address
                ]
            ])
            ->delete();
    }

    /**
     * Remove all external email addresses from the recipients of this alias.
     *
     * @return bool
     */
    public function removeAllExternalRecipients()
    {
        return DB::table('alias_recipients')
            ->whereNull('mailbox_id')
            ->where([
                [
                    'alias_id',
                    '=',
                    $this->id
                ]
            ])
            ->delete();
    }

    /**
     * Remove all recipient mailboxes from the recipients of this alias.
     *
     * @return int
     */
    public function removeAllRecipientMailboxes()
    {
        return DB::table('alias_recipients')
            ->whereNotNull('mailbox_id')
            ->where([
                [
                    'alias_id',
                    '=',
                    $this->id
                ]
            ])
            ->delete();
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
            'id'          => $this->id,
            'local_part'  => $this->local_part,
            'address'     => $this->address(),
            'description' => $this->description,
            'domain'      => $this->domain->domain,
            'active'      => $this->active
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
     * Scope a query to only include aliases that the authenticated
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
        return $query->whereHas('senderMailboxes', function (Builder $query) use ($mailbox) {
            $query->where('mailbox_id', $mailbox ? $mailbox->id : Auth::id());
        })
            ->orWhereHas('recipientMailboxes', function (Builder $query) use ($mailbox) {
                $query->where('mailbox_id', $mailbox ? $mailbox->id : Auth::id());
            })
            ->orWhereHas('domain.admins', function (Builder $query) use ($mailbox) {
                $query->where('mailbox_id', $mailbox ? $mailbox->id : Auth::id());
            });
    }
}
