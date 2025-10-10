<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Configuration API SyGM-eMCF
    |--------------------------------------------------------------------------
    |
    | Configuration pour l'intégration avec l'API de facturation électronique
    | du Bénin (SyGM-eMCF). Configurez les URLs et tokens selon l'environnement.
    |
    */

    // URL de base de l'API selon l'environnement
    'api_url' => env('SGMEF_API_URL', env('APP_ENV') === 'production' 
        ? 'https://api.impots.bj/sygmef-emcf' 
        : 'https://developper.impots.bj/sygmef-emcf'
    ),

    // Token JWT pour l'authentification
    'token' => env('SGMEF_TOKEN'),

    // IFU par défaut de l'entreprise
    'default_ifu' => env('SGMEF_DEFAULT_IFU'),

    /*
    |--------------------------------------------------------------------------
    | Default Operator
    |--------------------------------------------------------------------------
    |
    | Configuration de l'opérateur par défaut utilisé lorsqu'aucun opérateur
    | spécifique n'est fourni. Ceci garantit que le package fonctionne
    | immédiatement sans système d'authentification.
    |
    */
    'default_operator' => [
        'name' => env('SGMEF_DEFAULT_OPERATOR_NAME', 'Opérateur Principal'),
        'id' => env('SGMEF_DEFAULT_OPERATOR_ID', '1'),
    ],

    // Nom de l'opérateur par défaut (legacy - utilisez default_operator.name)
    'default_operator_name' => env('SGMEF_DEFAULT_OPERATOR_NAME', 'Opérateur Principal'),

    // Options HTTP pour les requêtes
    'http_options' => [
        'timeout' => (int) env('SGMEF_HTTP_TIMEOUT', 30),
        'verify' => env('SGMEF_VERIFY_SSL', true),
        'connect_timeout' => (int) env('SGMEF_CONNECT_TIMEOUT', 10),
    ],

    // Configuration des logs
    'logging' => [
        'enabled' => env('SGMEF_LOGGING_ENABLED', true),
        'level' => env('SGMEF_LOG_LEVEL', 'info'),
        'channel' => env('SGMEF_LOG_CHANNEL', 'daily'),
    ],

    // Configuration de l'interface web
    'web_interface' => [
        'enabled' => env('SGMEF_WEB_INTERFACE_ENABLED', true),
        'middleware' => ['web'], // Middleware de base (pas d'authentification requise)
        'route_prefix' => env('SGMEF_ROUTE_PREFIX', 'sgmef'),
    ],

    // Configuration des templates de facture
    'templates' => [
        'default' => 'default_a4',
        'available' => [
            'default_a4' => 'Standard A4',
            'compact_a5' => 'Compact A5',
            'detailed_a4' => 'Détaillé A4',
        ],
    ],

    // Configuration des formats d'impression
    'print_formats' => [
        'a4' => ['width' => 210, 'height' => 297, 'unit' => 'mm'],
        'a5' => ['width' => 148, 'height' => 210, 'unit' => 'mm'],
        'letter' => ['width' => 216, 'height' => 279, 'unit' => 'mm'],
    ],

    // Validation des données
    'validation' => [
        'strict_ifu_validation' => env('SGMEF_STRICT_IFU_VALIDATION', true),
        'required_client_fields' => ['name'], // Champs client obligatoires
    ],

    // Cache des données de l'API
    'cache' => [
        'enabled' => env('SGMEF_CACHE_ENABLED', true),
        'ttl' => (int) env('SGMEF_CACHE_TTL', 3600), // 1 heure
        'prefix' => 'sgmef_',
    ],
];
