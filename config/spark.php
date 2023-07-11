<?php

use App\Models\Team;

return [

    /*
    |--------------------------------------------------------------------------
    | Spark Path
    |--------------------------------------------------------------------------
    |
    | This configuration option determines the URI at which the Spark billing
    | portal is available. You are free to change this URI to a value that
    | you prefer. You shall link to this location from your application.
    |
    */

    'path' => 'billing',

    /*
    |--------------------------------------------------------------------------
    | Spark Middleware
    |--------------------------------------------------------------------------
    |
    | These are the middleware that requests to the Spark billing portal must
    | pass through before being accepted. Typically, the default list that
    | is defined below should be suitable for most Laravel applications.
    |
    */

    'middleware' => ['web', 'auth'],

    /*
    |--------------------------------------------------------------------------
    | Branding
    |--------------------------------------------------------------------------
    |
    | These configuration values allow you to customize the branding of the
    | billing portal, including the primary color and the logo that will
    | be displayed within the billing portal. This logo value must be
    | the absolute path to an SVG logo within the local filesystem.
    |
    */

    'brand' => [
        'logo' => realpath(__DIR__.'/../public/svg/logo.svg'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Proration Behavior
    |--------------------------------------------------------------------------
    |
    | This value determines if charges are prorated when making adjustments
    | to a plan such as incrementing or decrementing the quantity of the
    | plan. This also determines proration behavior if changing plans.
    |
    */

    'prorates' => true,

    /*
    |--------------------------------------------------------------------------
    | Spark Date Format
    |--------------------------------------------------------------------------
    |
    | This date format will be utilized by Spark to format dates in various
    | locations within the billing portal, such as while showing invoice
    | dates. You should customize the format based on your own locale.
    |
    */

    'date_format' => 'F j, Y',

    /*
    |--------------------------------------------------------------------------
    | Spark Billables
    |--------------------------------------------------------------------------
    |
    | Below you may define billable entities supported by your Spark driven
    | application. You are free to have multiple billable entities which
    | can each define multiple subscription plans available for users.
    |
    | In addition to defining your billable entity, you may also define its
    | plans and the plan's features, including a short description of it
    | as well as a "bullet point" listing of its distinctive features.
    |
    */

    'billables' => [

        'team' => [
            'model' => Team::class,

            'trial_days' => 14,

            'default_interval' => 'monthly',

            'plans' => [
                [
                    'name' => 'Compact',
                    'short_description' => 'Ideal for individuals or small businesses managing a few servers and websites.',
                    'monthly_id' => env('COMPACT_MONTHLY_PLAN_ID', 49711),
                    'yearly_id' => env('COMPACT_YEARLY_PLAN_ID', 49715),
                    'features' => [
                        '3 Servers',
                        '10 Sites per Server',
                        'Deployment log limited to 5 releases per site',
                        'Unlimited cron, daemons, firewall rules, etc.',
                        'No team support',
                        'You support the development of Eddy and Splade!',
                    ],
                    'archived' => false,
                    'options' => [
                        'max_servers' => 3,
                        'max_sites_per_server' => 10,
                        'max_deployments_per_site' => 5,
                        'max_team_members' => 1,
                        'has_backups' => false,
                    ],
                ],

                [
                    'name' => 'Turbo',
                    'short_description' => 'Great for businesses that need to manage multiple servers and websites with team collaboration.',
                    'monthly_id' => env('TURBO_MONTHLY_PLAN_ID', 49714),
                    'yearly_id' => env('TURBO_YEARLY_PLAN_ID', 49716),
                    'features' => [
                        '10 Servers',
                        'Unlimited Sites per Server',
                        'Unlimited cron, daemons, firewall rules, etc.',
                        'Deployment log limited to 15 releases per site',
                        'Up to 5 team members',
                        'You support the development of Eddy and Splade!',
                    ],
                    'archived' => false,
                    'options' => [
                        'max_servers' => 10,
                        'max_sites_per_server' => false,
                        'max_deployments_per_site' => 15,
                        'max_team_members' => 5,
                        'has_backups' => true,
                    ],
                ],

                [
                    'name' => 'Platinum',
                    'short_description' => 'Same as Turbo, plus exclusive access to upcoming features for businesses that need to manage multiple servers and websites.',
                    'monthly_id' => env('PLATINUM_MONTHLY_PLAN_ID', 49717),
                    'yearly_id' => env('PLATINUM_YEARLY_PLAN_ID', 49718),
                    'features' => [
                        'Unlimited Servers',
                        'Unlimited Sites per Server',
                        'Unlimited Deployment logs',
                        'Unlimited cron, daemons, firewall rules, etc.',
                        'Unlimited team members',
                        'Access to upcoming features',
                        'You support the development of Eddy and Splade!',
                    ],
                    'archived' => false,
                    'options' => [
                        'max_servers' => false,
                        'max_sites_per_server' => false,
                        'max_deployments_per_site' => false,
                        'max_team_members' => false,
                        'has_backups' => true,
                    ],
                ],
            ],

        ],

    ],
];
