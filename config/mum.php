<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Domains
    |--------------------------------------------------------------------------
    |
    | Here you can set the default quota value for new domains in GB.
    |
    */

    'domains' => [
        'quota'     => env('DOMAINS_QUOTA', 0),
        'max_quota' => env('DOMAINS_MAX_QUOTA', null)
    ],

    /*
    |--------------------------------------------------------------------------
    | Mailboxes
    |--------------------------------------------------------------------------
    |
    | Here you can set the default home and mail directory paths for new
    | mailboxes. You may use the following placeholders (similar to Dovecot):
    |   - %d  (Domain, e.g. "example.com")
    |   - %n  (Username / Local part, e.g. "jon.doe")
    |   - %u  (User, eg. "jon.doe@example.com")
    |
    */

    'mailboxes' => [
        'root_directory' => env('MAILBOXES_ROOT_DIRECTORY', '/srv/mail/mailboxes'),
        'homedir'        => env('MAILBOXES_HOMEDIR', '/srv/mail/mailboxes/%d/%n'),
        'maildir'        => env('MAILBOXES_MAILDIR', 'maildir:/srv/mail/mailboxes/%d/%n:LAYOUT=fs')
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Aliases
    |--------------------------------------------------------------------------
    |
    | Here you can set the default time that automatically deactivating alias
    | will live.
    |
    */

    'aliases' => [
        'deactivate_at' => [
            'default_days'    => env('ALIASES_DEACTIVATE_AT_DEFAULT_DAYS', 0),
            'default_hours'   => env('ALIASES_DEACTIVATE_AT_DEFAULT_HOURS', 0),
            'default_minutes' => env('ALIASES_DEACTIVATE_AT_DEFAULT_MINUTES', 10),
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Size Measurements
    |--------------------------------------------------------------------------
    |
    | Here you can set whether old size measurements should be deleted
    | automatically. If set to true, you may also specify after what time
    | a record should be deleted. The provided values will be subtracted from
    | the current timestamp. For example, you could automatically delete all
    | records older than 5 months, 2 weeks and 15 days.
    |
    */

    'size_measurements' => [
        'delete_old'   => env('SIZE_MEASUREMENTS_DELETE_OLD', true),
        'delete_after' => [
            'months' => env('SIZE_MEASUREMENTS_DELETE_AFTER_MONTHS', 12),
            'weeks'  => env('SIZE_MEASUREMENTS_DELETE_AFTER_WEEKS', 0),
            'days'   => env('SIZE_MEASUREMENTS_DELETE_AFTER_DAYS', 0),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Random Generator
    |--------------------------------------------------------------------------
    |
    | Here you can set the provider of random strings in the frontend.
    | Currently supported values for providers are:
    |   - 'diceware'
    |   - 'insecureRandom'
    |
    */

    'random_generator' => [
        'local_part' => [
            'provider' => env('RANDOM_GENERATOR_LOCAL_PART_PROVIDER', 'insecureRandom'),

            'diceware_word_count' => env('RANDOM_GENERATOR_LOCAL_PART_DICEWARE_WORD_COUNT', 4),
            'diceware_separator'  => env('RANDOM_GENERATOR_LOCAL_PART_DICEWARE_SEPARATOR', '-'),

            'insecure_random_char_count' => env('RANDOM_GENERATOR_LOCAL_PART_INSECURE_RANDOM_CHAR_COUNT', 20),
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Email Settings
    |--------------------------------------------------------------------------
    |
    | Here you can set the default settings for SMTP, IMAP and POP3 for clients
    | that will be displayed for users.
    |
     */

    'email_settings' => [
        'show' => env('SHOW_EMAIL_SETTINGS', true),
        'smtp' => [
            'hostname' => env('SMTP_HOSTNAME'),
            'port'     => env('SMTP_PORT'),
            'ssl'      => env('SMTP_SSL')
        ],
        'imap' => [
            'hostname' => env('IMAP_HOSTNAME'),
            'port'     => env('IMAP_PORT'),
            'ssl'      => env('IMAP_SSL')
        ],
        'pop3' => [
            'hostname' => env('POP3_HOSTNAME'),
            'port'     => env('POP3_PORT'),
            'ssl'      => env('POP3_SSL')
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | System Service Health
    |--------------------------------------------------------------------------
    |
    | Here you can specify whether system services should be monitored using
    | systemctl and how long to keep track of the status history.
    | Valid values for the check frequency are:
    | '1min', '5min', '10min', '15min', '30min'
    |
     */

    'system_health' => [
        'check_services'               => env('SYSTEM_HEALTH_CHECK_SERVICES', false),
        'check_frequency'              => env('SYSTEM_HEALTH_CHECK_FREQUENCY', '5min'),
        'max_entries_incident_history' => env('SYSTEM_HEALTH_MAX_ENTRIES_INCIDENT_HISTORY', 100),
    ],
];
