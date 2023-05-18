<?php

return [
    /**
     * The URL that the app is accessible from when running locally.
     */
    'webhook_url' => env('WEBHOOK_URL'),

    /**
     * An array of IP addresses that can be used as a SSH proxy.
     */
    'ssh_proxies' => array_filter(array_map('trim', explode(',', env('SSH_PROXIES', '')))),

    /**
     * Use 'set -x' in the shell scripts to easily debug them.
     */
    'print_shell_commands' => false,

    /**
     * Format the shell commands and Caddyfiles to make snapshot testing easier.
     */
    'format_server_content' => env('FORMAT_SERVER_CONTENT', false),

    /**
     * Defaults that are used to provision a new server.
     */
    'server_defaults' => [
        /**
         * The directory that Eddy will use to store its files on the server.
         */
        'working_directory' => '.eddy',

        /**
         * The username that Eddy will use to connect to the server.
         */
        'username' => 'eddy',

        /**
         * The port that Eddy will use to connect to the server.
         */
        'ssh_port' => 22,

        /**
         * The comment that Eddy will use when creating a new key pair.
         */
        'ssh_comment' => 'eddy@protone.media',

        /**
         * The database name that Eddy will use when provisioning a new server.
         */
        'database_name' => 'eddy',
    ],

    /**
     * Allow the Horizon dashboard to be accessed with these email addresses.
     */
    'view_horizon_with_emails' => array_filter(array_map('trim', explode(',', env('HORIZON_EMAILS', '')))),

    /**
     * Enable subscriptions.
     */
    'subscriptions_enabled' => env('SUBSCRIPTIONS_ENABLED', false),

    /**
     * A little helper to fake the validation of provider tokens (used in the Dusk tests).
     */
    'fake_credentials_validation' => env('FAKE_CREDENTIALS_VALIDATION', false),
];
