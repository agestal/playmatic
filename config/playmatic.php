<?php

return [
    'allow_unknown_domains_in_local' => env('PLAYMATIC_ALLOW_UNKNOWN_DOMAINS_LOCAL', false),
    'seed_demo_data' => filter_var(
        env('PLAYMATIC_SEED_DEMO_DATA', env('APP_ENV', 'production') === 'local'),
        FILTER_VALIDATE_BOOL
    ),
];
