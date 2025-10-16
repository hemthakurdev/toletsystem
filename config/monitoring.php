<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Monitoring Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for application monitoring, logging, and alerting.
    |
    */

    'enabled' => env('MONITORING_ENABLED', true),

    'channels' => [
        'performance' => [
            'driver' => 'daily',
            'path' => storage_path('logs/performance.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
        ],
        'database' => [
            'driver' => 'daily',
            'path' => storage_path('logs/database.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
        ],
        'api' => [
            'driver' => 'daily',
            'path' => storage_path('logs/api.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
        ],
        'errors' => [
            'driver' => 'daily',
            'path' => storage_path('logs/errors.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 30,
        ],
        'security' => [
            'driver' => 'daily',
            'path' => storage_path('logs/security.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 90,
        ],
    ],

    'alerts' => [
        'email' => [
            'enabled' => env('MONITORING_EMAIL_ALERTS', true),
            'recipients' => [
                env('MONITORING_EMAIL_RECIPIENT', 'admin@salemitra.com'),
            ],
        ],
        'slack' => [
            'enabled' => env('MONITORING_SLACK_ALERTS', false),
            'webhook' => env('MONITORING_SLACK_WEBHOOK'),
        ],
        'sms' => [
            'enabled' => env('MONITORING_SMS_ALERTS', false),
            'recipients' => [
                env('MONITORING_SMS_RECIPIENT', '+1234567890'),
            ],
        ],
    ],

    'thresholds' => [
        'error_rate' => env('MONITORING_ERROR_RATE_THRESHOLD', 5), // percentage
        'response_time' => env('MONITORING_RESPONSE_TIME_THRESHOLD', 2000), // milliseconds
        'memory_usage' => env('MONITORING_MEMORY_USAGE_THRESHOLD', 80), // percentage
        'disk_usage' => env('MONITORING_DISK_USAGE_THRESHOLD', 90), // percentage
    ],

    'retention' => [
        'performance_logs' => env('MONITORING_PERFORMANCE_RETENTION', 14), // days
        'error_logs' => env('MONITORING_ERROR_RETENTION', 30), // days
        'security_logs' => env('MONITORING_SECURITY_RETENTION', 90), // days
        'api_logs' => env('MONITORING_API_RETENTION', 14), // days
    ],
];
