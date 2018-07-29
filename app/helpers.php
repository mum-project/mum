<?php

use App\Mailbox;
use Illuminate\Support\Facades\Auth;

/**
 * Helper function for flashing a message to the session.
 * This function calls request()->session()->flash() and passes on any parameters.
 *
 * @param string $key
 * @param bool   $value
 */
function flash(string $key, $value = true)
{
    request()
        ->session()
        ->flash($key, $value);
}

/**
 * Gets the domain of an email address.
 * Since the local part may also contain "@" characters, a simple explode()
 * is not a good idea. Please use this function instead.
 *
 * @param string $address
 * @return string
 */
function getDomainOfEmailAddress(string $address)
{
    $explodedEmail = explode('@', $address);
    return end($explodedEmail);
}

/**
 * Gets the local part of an email address.
 * Since the local part may also contain "@" characters, a simple explode()
 * is not a good idea. Please use this function instead.
 *
 * @param string $address
 * @return string
 */
function getLocalPartOfEmailAddress(string $address)
{
    $explodedEmail = explode('@', $address);
    return implode('@', array_slice($explodedEmail, 0, -1));
}

/**
 * Returns true if a user is logged in and is a super admin.
 * Alternatively, you may provide a Mailbox user to check on.
 *
 * @param Mailbox|null $user
 * @return bool
 */
function isUserSuperAdmin(Mailbox $user = null)
{
    if ($user) {
        return $user->isSuperAdmin();
    }
    return Auth::check() && Auth::user()
            ->isSuperAdmin();
}

/**
 * Gets the homedir path from config and replaces all placeholders accordingly.
 *
 * @param string $localPart
 * @param string $domain
 * @return mixed
 */
function getHomedirForMailbox(string $localPart, string $domain = null)
{
    $s1 = str_replace('%d', $domain, config('mum.mailboxes.homedir'));
    $s2 = str_replace('%n', $localPart, $s1);
    return str_replace('%u', $localPart . '@' . $domain, $s2);
}

/**
 * Gets the maildir path from config and replaces all placeholders accordingly.
 *
 * @param string $localPart
 * @param string $domain
 * @return mixed
 */
function getMaildirForMailbox(string $localPart, string $domain = null)
{
    $s1 = str_replace('%d', $domain, config('mum.mailboxes.maildir'));
    $s2 = str_replace('%n', $localPart, $s1);
    return str_replace('%u', $localPart . '@' . $domain, $s2);
}

/**
 * Gets the home directory of a domain.
 * All emails for mailboxes should be saved in this directory.
 *
 * @param string $domain
 * @return mixed
 */
function getHomedirForDomain(string $domain)
{
    $config = config('mum.mailboxes.homedir');
    $s1 = substr($config, 0, strpos($config, '%d') + 2);
    return str_replace('%d', $domain, $s1);
}
