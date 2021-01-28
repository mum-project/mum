<?php

namespace App;

use App\Scopes\ExcludeAutoDeactivatedAliasesScope;
use App\Traits\QueryFilterTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Alias extends Model
{
    use HasFactory, QueryFilterTrait;

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
     * @return BelongsTo
     */
    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }

    /**
     * Gets all mailboxes that have permission to send emails with this alias as the sender address (MAIL FROM).
     * This collection does NOT include mailboxes that match one of the destination addresses of the alias.
     *
     * @return BelongsToMany
     */
    public function senderMailboxes(): BelongsToMany
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
     * @return BelongsToMany
     */
    public function recipientMailboxes(): BelongsToMany
    {
        return $this->belongsToMany(Mailbox::class, 'alias_recipients');
    }

    /**
     * Gets all entries of alias recipients, whether they are local mailboxes or external addresses.
     *
     * @return Collection
     */
    public function recipients(): Collection
    {
        return DB::table('alias_recipients')
            ->where('alias_id', '=', $this->id)
            ->get();
    }

    /**
     * Gets all external alias recipient addresses.
     *
     * @return Collection
     */
    public function externalRecipients(): Collection
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
     * @return Collection
     */
    public function getExternalRecipientResource(): Collection
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
     * @return Collection
     */
    public function recipientAddresses(): Collection
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
    public function address(): string
    {
        return $this->local_part . '@' . $this->domain->domain;
    }

    /**
     * Add a mailbox to the recipients of this alias.
     *
     * @param Mailbox $mailbox
     * @return bool
     */
    public function addRecipientMailbox(Mailbox $mailbox): bool
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
    public function removeRecipientMailbox(Mailbox $mailbox): bool
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
    public function addExternalRecipient(string $address): bool
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
    public function removeExternalRecipient(string $address): bool
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
    public function removeAllExternalRecipients(): bool
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
    public function removeAllRecipientMailboxes(): int
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
     * Scope a query to only include aliases that the authenticated
     * mailbox user is authorized to view.
     *
     * @param Builder $query
     * @param Mailbox|null $mailbox
     * @return Builder
     */
    public function scopeWhereAuthorized(Builder $query, Mailbox $mailbox = null): Builder
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
