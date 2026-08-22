<?php

declare(strict_types=1);

return [
    'api_url' => env('SGMEF_API_URL', env('APP_ENV') === 'production' 
        ? 'https://api.impots.bj/sygmef-emcf/api' 
        : 'https://developper.impots.bj/sygmef-emcf/api'
    ),
    'token' => env('SGMEF_TOKEN'),
    'default_ifu' => env('SGMEF_DEFAULT_IFU'),
    'ifus' => array_filter(array_map('trim', array_filter(explode(',', (string) env('SGMEF_IFUS', ''))))),
    'ifu_map' => [],
    'verification_url' => env('SGMEF_VERIFICATION_URL', 'https://developper.impots.bj/sygmef-test/verification'),
    'pending_invoice_ttl_seconds' => (int) env('SGMEF_PENDING_INVOICE_TTL', 120),
    'default_operator' => [
        'name' => env('SGMEF_DEFAULT_OPERATOR_NAME', 'Opérateur Principal'),
        'id' => env('SGMEF_DEFAULT_OPERATOR_ID', '1'),
    ],
    'default_operator_name' => env('SGMEF_DEFAULT_OPERATOR_NAME', 'Opérateur Principal'),
    'http_options' => [
        'timeout' => (int) env('SGMEF_HTTP_TIMEOUT', 30),
        'verify' => env('SGMEF_VERIFY_SSL', true),
        'connect_timeout' => (int) env('SGMEF_CONNECT_TIMEOUT', 10),
    ],
    'rate_limit_per_minute' => (int) env('SGMEF_RATE_LIMIT_PER_MINUTE', 60),
    'logging' => [
        'enabled' => env('SGMEF_LOGGING_ENABLED', true),
        'level' => env('SGMEF_LOG_LEVEL', 'info'),
        'channel' => env('SGMEF_LOG_CHANNEL', 'daily'),
    ],
    'web_interface' => [
        'enabled' => env('SGMEF_WEB_INTERFACE_ENABLED', true),
        'middleware' => ['web'],
        'route_prefix' => env('SGMEF_ROUTE_PREFIX', 'sgmef'),
        'polling_seconds' => (int) env('SGMEF_POLLING_SECONDS', 30),
    ],
    'queue' => [
        'enabled' => env('SGMEF_QUEUE_ENABLED', false),
        'connection' => env('SGMEF_QUEUE_CONNECTION', env('QUEUE_CONNECTION', 'sync')),
        'queue' => env('SGMEF_QUEUE', 'sgmef'),
        'tries' => [
            'create' => 1,
            'confirm' => 1,
            'cancel' => 1,
            'sync' => (int) env('SGMEF_SYNC_TRIES', 3),
        ],
        'timeout' => (int) env('SGMEF_JOB_TIMEOUT', 60),
        'backoff' => [
            'sync' => [5, 15, 60],
        ],
        'cleanup' => [
            'enabled' => env('SGMEF_CLEANUP_ENABLED', true),
            'frequency' => env('SGMEF_CLEANUP_FREQUENCY', 'everyMinute'),
            'limit' => (int) env('SGMEF_CLEANUP_LIMIT', 500),
        ],
    ],
    'templates' => [
        'default' => 'default_a4',
        'available' => [
            'default_a4' => 'Standard A4',
            'compact_a5' => 'Compact A5',
            'detailed_a4' => 'Détaillé A4',
        ],
    ],
    'print_formats' => [
        'a4' => ['width' => 210, 'height' => 297, 'unit' => 'mm'],
        'a5' => ['width' => 148, 'height' => 210, 'unit' => 'mm'],
        'letter' => ['width' => 216, 'height' => 279, 'unit' => 'mm'],
    ],
    'validation' => [
        'strict_ifu_validation' => env('SGMEF_STRICT_IFU_VALIDATION', true),
        'required_client_fields' => ['name'],
    ],
    'cache' => [
        'enabled' => env('SGMEF_CACHE_ENABLED', true),
        'ttl' => (int) env('SGMEF_CACHE_TTL', 3600),
        'prefix' => 'sgmef_',
    ],
    'company_info' => [
        'name'           => env('SGMEF_COMPANY_NAME', 'NOM DE VOTRE ENTREPRISE'),
        'tax_regime'     => env('SGMEF_TAX_REGIME', 'RÉGIME FISCAL'),
        'ifu'            => env('SGMEF_DEFAULT_IFU'),
        'rccm'           => env('SGMEF_RCCM', 'RCCM/XXXX/XX/XXXX'),
        'address'        => [
            'department' => env('SGMEF_ADDRESS_DEPT', 'DEPARTEMENT'),
            'city'       => env('SGMEF_ADDRESS_CITY', 'VILLE'),
            'district'   => env('SGMEF_ADDRESS_DISTRICT', 'ARRONDISSEMENT'),
            'qip'        => env('SGMEF_ADDRESS_QIP', 'QIP'),
        ],
        'phone'          => env('SGMEF_PHONE', 'XX XX XX XX'),
        'email'          => env('SGMEF_EMAIL', 'contact@entreprise.com'),
    ],
    'sfe_info' => [
        'origin_country'   => env('SGMEF_SFE_COUNTRY', 'Bénin'),
        'manufacturer'     => env('SGMEF_SFE_MANUFACTURER', 'NOM DU FABRICANT'),
        'software_name'    => env('SGMEF_SFE_NAME', 'LaraSgmefQR'),
        'software_version' => env('SGMEF_SFE_VERSION', '2.3.0'),
        'supported_features' => [
            'invoice_types' => [
                'FV' => true,
                'FA' => true,
                'EV' => true,
                'EA' => true,
            ],
            'tax_groups' => [
                'A' => true, 'B' => true, 'C' => true, 'D' => true, 'E' => true, 'F' => false,
            ],
            'other_taxes' => [
                'AIB'          => true,
                'specific_tax' => true,
                'tourist_tax'  => false,
            ],
        ],
    ],
    'emecef_test_cases' => require __DIR__ . '/emecef_test_cases.php',
];
