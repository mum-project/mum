<?php

namespace App;

use App\Traits\QueryFilterTrait;
use App\Traits\SizeMeasurable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use function isUserSuperAdmin;

class Mailbox extends Authenticatable
{
    use HasFactory, Notifiable, QueryFilterTrait, SizeMeasurable;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id',
        'password',
        'homedir',
        'maildir',
        'created_at',
        'updated_at'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Scope a query to only include super admin mailboxes.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeIsSuperAdmin(Builder $query)
    {
        return $query->where('is_super_admin', '=', true);
    }

    /**
     * Gets the domain that this mailbox belongs to.
     *
     * @return BelongsTo
     */
    public function domain()
    {
        return $this->belongsTo(Domain::class);
    }

    /**
     * Gets all mailboxes that have the right to administrate this mailbox.
     * This collection does NOT include any domain admins that inherit this right automatically.
     *
     * @return BelongsToMany
     */
    public function admins()
    {
        return $this->belongsToMany(Mailbox::class, 'mailbox_admins', 'admin_mailbox_id');
    }

    /**
     * Gets all aliases that this mailbox is allowed to use as a sender address (MAIL FROM).
     * This collection does NOT include any aliases where this mailbox is a destination address.
     *
     * @return BelongsToMany
     */
    public function sendingAliases()
    {
        return $this->belongsToMany(Alias::class, 'alias_senders');
    }

    /**
     * Gets all aliases that have listed this mailbox as one of their recipient addresses.
     *
     * ATTENTION:   Since the pivot table alias_recipients also holds external recipient addresses,
     *              you have to insert values into the table manually.
     *              It is NOT possible to use the Eloquent relationship save() method.
     *
     * @return BelongsToMany
     */
    public function receivingAliases(): BelongsToMany
    {
        return $this->belongsToMany(Alias::class, 'alias_recipients');
    }

    /**
     * Gets all domains that are administrated by this mailbox user.
     *
     * @return BelongsToMany
     */
    public function administratedDomains(): BelongsToMany
    {
        return $this->belongsToMany(Domain::class, 'domain_admins');
    }

    /**
     * Gets all mailboxes that are administrated by this mailbox user.
     *
     * @return BelongsToMany
     */
    public function administratedMailboxes(): BelongsToMany
    {
        return $this->belongsToMany(Mailbox::class, 'mailbox_admins', 'mailbox_id', 'admin_mailbox_id');
    }

    /**
     * Gets the complete email address built from the local_part and the domain of this mailbox.
     *
     * @return string
     */
    public function address(): string
    {
        return $this->local_part . '@' . $this->domain->domain;
    }

    /**
     * Get the notification routing information for the custom alternative_mail driver.
     *
     * @param null $notification
     * @return mixed
     */
    public function routeNotificationForAlternativeMail($notification = null)
    {
        return $this->alternative_email;
    }

    /**
     * Get the notification routing information for the mail driver.
     *
     * @param null $notification
     * @return string
     */
    public function routeNotificationForMail($notification = null): string
    {
        return $this->address();
    }

    /**
     * This email address gets saved into the password_resets table
     * and is used on the password reset page that is linked in a password reset email.
     *
     * @return string
     */
    public function getEmailForPasswordReset(): string
    {
        return $this->address();
    }

    /**
     * Send the password reset notification.
     *
     * @param  string $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * Determines whether the mailbox is a super admin.
     *
     * @return bool
     */
    public function isSuperAdmin(): bool
    {
        return $this->is_super_admin;
    }

    /**
     * Determines whether the mailbox has any administrative roles.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->is_super_admin || $this->administratesDomains() || $this->administratesMailboxes();
    }

    /**
     * Determines whether the mailbox administrates any domains.
     *
     * @return bool
     */
    public function administratesDomains(): bool
    {
        return $this->administratedDomains()
            ->exists();
    }

    /**
     * Determines whether the mailbox administrates any mailboxes.
     *
     * @return bool
     */
    public function administratesMailboxes(): bool
    {
        return $this->administratedMailboxes()
            ->exists();
    }

    /**
     * Scope a query to only include mailboxes that the authenticated
     * mailbox user is authorized to view.
     *
     * @param Builder      $query
     * @param Mailbox|null $mailbox
     * @return Builder
     */
    public function scopeWhereAuthorized(Builder $query, Mailbox $mailbox = null): Builder
    {
        if (isUserSuperAdmin()) {
            return $query;
        }
        return $query->whereHas('admins', function (Builder $query) use ($mailbox) {
            $query->where('mailbox_id', $mailbox ? $mailbox->id : Auth::id());
        })
            ->orWhereHas('domain.admins', function (Builder $query) use ($mailbox) {
                $query->where('mailbox_id', $mailbox ? $mailbox->id : Auth::id());
            })
            ->orWhere('id', $mailbox ? $mailbox->id : Auth::id());
    }
}
