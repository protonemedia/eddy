<?php

return [
    'blade_echo_tags' => [
        ['{!!', '!!}'],
        ['@{{', '}}'],
        ['{{', '}}'],
        ['{{{', '}}}'],
    ],

    'middleware' => [
        'allow_file_uploads' => false,

        'allow_blade_echoes' => true,

        'completely_replace_malicious_input' => true,

        'terminate_request_on_malicious_input' => true,

        'dispatch_event_on_malicious_input' => false,
    ],
];
