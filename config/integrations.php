<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Enabled
    |--------------------------------------------------------------------------
    |
    | Here you can set whether integrations should be enabled generally and
    | whether an integration type should be enabled.
    | ATTENTION:    This configuration can have severe implications regarding
    |               your system's security. Use this feature only if you know
    |               what you're doing.
    |
    */

    'enabled' => [
        'generally'      => env('ENABLE_INTEGRATIONS', false),
        'shell_commands' => env('INTEGRATIONS_ENABLE_SHELL_COMMANDS', false),
        'web_hooks'      => env('INTEGRATIONS_ENABLE_WEB_HOOKS', false)
    ],

    /*
    |--------------------------------------------------------------------------
    | Options
    |--------------------------------------------------------------------------
    |
    | Here you can specify the retry delay after a failed integration in seconds.
    | For shell commands you may also configure if shell parameters
    | that are configured in MUM's web interface are allowed.
    | ATTENTION:    This configuration can have severe implications regarding
    |               your system's security. Use this feature only if you know
    |               what you're doing.
    |
    */

    'options' => [
        'shell_commands' => [
            'failed_retry_delay' => env('INTEGRATIONS_SHELL_COMMANDS_FAILED_RETRY_DELAY', 10),
            'allow_parameters'   => env('INTEGRATIONS_SHELL_COMMANDS_ALLOW_PARAMETERS', false),
        ],
        'web_hooks'      => [
            'failed_retry_delay' => env('INTEGRATIONS_WEB_HOOKS_FAILED_RETRY_DELAY', 60)
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Shell Commands
    |--------------------------------------------------------------------------
    |
    | Here you can specify up to 10 shell commands that you want to allow
    | to be executed with an integration configurable in MUM's web interface.
    | ATTENTION:    This configuration can have severe implications regarding
    |               your system's security. Use this feature only if you know
    |               what you're doing.
    |
    */

    'shell_commands' => [
        '01' => env('INTEGRATIONS_SHELL_COMMAND_01', null),
        '02' => env('INTEGRATIONS_SHELL_COMMAND_02', null),
        '03' => env('INTEGRATIONS_SHELL_COMMAND_03', null),
        '04' => env('INTEGRATIONS_SHELL_COMMAND_04', null),
        '05' => env('INTEGRATIONS_SHELL_COMMAND_05', null),
        '06' => env('INTEGRATIONS_SHELL_COMMAND_06', null),
        '07' => env('INTEGRATIONS_SHELL_COMMAND_07', null),
        '08' => env('INTEGRATIONS_SHELL_COMMAND_08', null),
        '09' => env('INTEGRATIONS_SHELL_COMMAND_09', null),
        '10' => env('INTEGRATIONS_SHELL_COMMAND_10', null),
    ],
];
