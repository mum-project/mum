<?php

namespace App;

use App\Traits\QueryFilterTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AliasRequest extends Model
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

    /**
     * Gets the domain this alias request belongs to
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function domain()
    {
        return $this->belongsTo(Domain::class);
    }

    /**
     * Gets the mailbox this alias request belongs to
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function mailbox()
    {
        return $this->belongsTo(Mailbox::class);
    }

    /**
     * Gets the alias that was created through this alias request.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function alias()
    {
        return $this->belongsTo(Alias::class);
    }

    /**
     * Scope a query to only include open alias requests.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeOpen(Builder $query)
    {
        return $query->where('status', 'open');
    }

    /**
     * Gets all mailboxes that have permission to send emails with this alias request as the sender address (MAIL FROM).
     * This collection does NOT include mailboxes that match one of the destination addresses of the alias request.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function senderMailboxes()
    {
        return $this->belongsToMany(Mailbox::class, 'alias_request_senders', 'request_id', 'mailbox_id')
            ->orderBy('local_part');
    }

    /**
     * Gets all mailboxes that receive emails sent to this alias request.
     * This collection does NOT include external addresses.
     *
     * ATTENTION:   Since the pivot table alias_recipients also holds external recipient addresses,
     *              you have to insert values using the addRecipientMailbox() method.
     *              It is NOT possible to use the Eloquent relationship save() method.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function recipientMailboxes()
    {
        return $this->belongsToMany(Mailbox::class, 'alias_request_recipients', 'request_id', 'mailbox_id')
            ->orderBy('local_part');
    }

    /**
     * Gets all entries of alias request recipients, whether they are local mailboxes or external addresses.
     *
     * @return \Illuminate\Support\Collection
     */
    public function recipients()
    {
        return DB::table('alias_request_recipients')
            ->where('request_id', '=', $this->id)
            ->get();
    }

    /**
     * Gets all external alias recipient addresses.
     * ATTENTION:  This output is not compatible with the Javascript frontend code.
     *             See getExternalRecipientResource() for a compatible version.
     *
     * @return \Illuminate\Support\Collection
     */
    public function externalRecipients()
    {
        return DB::table('alias_request_recipients')
            ->where('request_id', '=', $this->id)
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
        return DB::table('alias_request_recipients')
            ->where('request_id', $this->id)
            ->pluck('recipient_address');
    }

    /**
     * Gets the complete email address built from the local_part and the domain of this alias request.
     *
     * @return string
     */
    public function address()
    {
        return $this->local_part . '@' . $this->domain->domain;
    }

    /**
     * Add a mailbox to the recipients of this alias request.
     *
     * @param Mailbox $mailbox
     * @return bool
     */
    public function addRecipientMailbox(Mailbox $mailbox)
    {
        return DB::table('alias_request_recipients')
            ->insert([
                'request_id'        => $this->id,
                'recipient_address' => $mailbox->address(),
                'mailbox_id'        => $mailbox->id
            ]);
    }

    /**
     * Remove a mailbox from the recipients of this alias request.
     *
     * @param Mailbox $mailbox
     * @return bool
     */
    public function removeRecipientMailbox(Mailbox $mailbox)
    {
        return DB::table('alias_request_recipients')
            ->where([
                [
                    'request_id',
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
     * Add an external email address to the recipients of this alias request.
     *
     * @param string $address
     * @return bool
     */
    public function addExternalRecipient(string $address)
    {
        return DB::table('alias_request_recipients')
            ->insert([
                'request_id'        => $this->id,
                'recipient_address' => $address
            ]);
    }

    /**
     * Remove an external email address from the recipients of this alias request.
     *
     * @param string $address
     * @return bool
     */
    public function removeExternalRecipient(string $address)
    {
        return DB::table('alias_request_recipients')
            ->whereNull('mailbox_id')
            ->where([
                [
                    'request_id',
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
     * Remove all external email addresses from the recipients of this alias request.
     *
     * @return bool
     */
    public function removeAllExternalRecipients()
    {
        return DB::table('alias_request_recipients')
            ->whereNull('mailbox_id')
            ->where([
                [
                    'request_id',
                    '=',
                    $this->id
                ]
            ])
            ->delete();
    }

    /**
     * Remove all recipient mailboxes from the recipients of this alias request.
     *
     * @return int
     */
    public function removeAllRecipientMailboxes()
    {
        return DB::table('alias_request_recipients')
            ->whereNotNull('mailbox_id')
            ->where([
                [
                    'request_id',
                    '=',
                    $this->id
                ]
            ])
            ->delete();
    }

    /**
     * Scope a query to only include alias requests that the user is authorized to view.
     *
     * @param Builder $query
     * @return $this|Builder
     */
    public function scopeWhereAuthorized(Builder $query)
    {
        if (isUserSuperAdmin()) {
            return $query;
        }
        return $query->where('mailbox_id', '=', Auth::id());
    }

    /**
     * Method writes Data from the alias requests tables into the alias tables.
     * After this, the requested alias is active.
     *
     * @return Alias
     */
    public function generateAlias()
    {
        /** @var Alias $alias */
        $alias = Alias::create([
            'local_part'  => $this->local_part,
            'description' => $this->description,
            'domain_id'   => $this->domain_id
        ]);

        $alias->senderMailboxes()
            ->attach($this->senderMailboxes);

        $aliasRequestRecipients = $this->recipients();

        foreach ($aliasRequestRecipients as $aliasRequestRecipient) {
            DB::table('alias_recipients')
                ->insert([
                    'alias_id'          => $alias->id,
                    'recipient_address' => $aliasRequestRecipient->recipient_address,
                    'mailbox_id'        => $aliasRequestRecipient->mailbox_id
                ]);
        }

        $this->alias()
            ->associate($alias)
            ->save();

        return $alias;
    }
}
