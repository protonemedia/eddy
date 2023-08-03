<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Title and meta tags (SEO)
    |--------------------------------------------------------------------------
    |
    | You may use the SEO facade to set your page's title, description, and keywords.
    | @see https://splade.dev/docs/title-meta
    |
    */

    'defaults' => [
        'title' => 'Eddy Server Management',
        'description' => 'Effortlessly Provision Servers and Deploy PHP Applications with Zero Downtime using Eddy - The Open-Source Solution for a Smooth Deployment Process!',
        'keywords' => ['open-source server provisioning', 'PHP application deployment', 'zero downtime deployment', 'Eddy deployment tool', 'provisioning and deployment automation', 'Eddy server management', 'web server deployment', 'continuous deployment', 'DevOps automation', 'automated application deployment'],
    ],

    'title_prefix' => '',
    'title_separator' => ' | ',
    'title_suffix' => 'Eddy Server Management',

    'auto_canonical_link' => true,

    'open_graph' => [
        'auto_fill' => true,
        'image' => 'https://eddy.management/card.jpg',
        'site_name' => null,
        'title' => null,
        'type' => 'WebPage',
        'url' => null,
    ],

    'twitter' => [
        'auto_fill' => true,
        'card' => 'summary_large_image',
        'description' => null,
        'image' => 'https://eddy.management/card.jpg',
        'site' => '@pascalbaljet',
        'title' => null,
    ],

];
