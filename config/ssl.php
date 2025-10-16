<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SSL Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for SSL/TLS certificates and HTTPS enforcement.
    |
    */

    'enabled' => env('SSL_ENABLED', true),

    'force_https' => env('FORCE_HTTPS', true),

    'hsts' => [
        'enabled' => env('HSTS_ENABLED', true),
        'max_age' => env('HSTS_MAX_AGE', 31536000), // 1 year
        'include_subdomains' => env('HSTS_INCLUDE_SUBDOMAINS', true),
        'preload' => env('HSTS_PRELOAD', true),
    ],

    'certificate' => [
        'provider' => env('SSL_CERTIFICATE_PROVIDER', 'letsencrypt'), // letsencrypt, custom
        'path' => env('SSL_CERTIFICATE_PATH', '/etc/letsencrypt/live/'),
        'auto_renewal' => env('SSL_AUTO_RENEWAL', true),
        'renewal_threshold' => env('SSL_RENEWAL_THRESHOLD', 30), // days
    ],

    'security' => [
        'protocols' => env('SSL_PROTOCOLS', 'TLSv1.2,TLSv1.3'),
        'ciphers' => env('SSL_CIPHERS', 'ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512:ECDHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES256-GCM-SHA384'),
        'prefer_server_ciphers' => env('SSL_PREFER_SERVER_CIPHERS', false),
        'session_cache' => env('SSL_SESSION_CACHE', 'shared:SSL:10m'),
        'session_timeout' => env('SSL_SESSION_TIMEOUT', '10m'),
    ],

    'monitoring' => [
        'enabled' => env('SSL_MONITORING_ENABLED', true),
        'check_interval' => env('SSL_CHECK_INTERVAL', 24), // hours
        'alert_threshold' => env('SSL_ALERT_THRESHOLD', 30), // days
        'monitored_domains' => [
            env('APP_DOMAIN', 'salemitra.com'),
        ],
    ],

    'letsencrypt' => [
        'email' => env('LETSENCRYPT_EMAIL', 'admin@salemitra.com'),
        'staging' => env('LETSENCRYPT_STAGING', false),
        'webroot' => env('LETSENCRYPT_WEBROOT', public_path()),
    ],
];
