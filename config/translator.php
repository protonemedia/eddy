<?php

declare(strict_types=1);

use Translator\Framework\LaravelConfigLoader;
use Translator\Infra\LaravelJsonTranslationRepository;

return [
    'languages' => ['en'],
    'directories' => [
        app_path(),
        resource_path('views'),
    ],
    'output' => lang_path(),
    'extensions' => ['php'],
    'functions' => ['lang', '__'],
    'container' => [
        'config_loader' => LaravelConfigLoader::class,
        'translation_repository' => LaravelJsonTranslationRepository::class,
    ],

    'use_keys_as_default_value' => true,
    'default_language' => 'en',
];
