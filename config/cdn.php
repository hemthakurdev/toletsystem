<?php

return [
    /*
    |--------------------------------------------------------------------------
    | CDN Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Content Delivery Network integration.
    |
    */

    'enabled' => env('CDN_ENABLED', false),

    'provider' => env('CDN_PROVIDER', 'cloudflare'), // cloudflare, aws, azure, custom

    'base_url' => env('CDN_BASE_URL'),

    'api_key' => env('CDN_API_KEY'),

    'api_secret' => env('CDN_API_SECRET'),

    'zone_id' => env('CDN_ZONE_ID'),

    'settings' => [
        'auto_upload' => env('CDN_AUTO_UPLOAD', true),
        'auto_purge' => env('CDN_AUTO_PURGE', true),
        'optimize_images' => env('CDN_OPTIMIZE_IMAGES', true),
        'compress_assets' => env('CDN_COMPRESS_ASSETS', true),
    ],

    'cache' => [
        'ttl' => env('CDN_CACHE_TTL', 31536000), // 1 year
        'browser_ttl' => env('CDN_BROWSER_TTL', 86400), // 1 day
        'edge_ttl' => env('CDN_EDGE_TTL', 3600), // 1 hour
    ],

    'optimization' => [
        'image_quality' => env('CDN_IMAGE_QUALITY', 85),
        'image_formats' => ['webp', 'avif', 'jpeg', 'png'],
        'css_minification' => env('CDN_CSS_MINIFICATION', true),
        'js_minification' => env('CDN_JS_MINIFICATION', true),
    ],

    'security' => [
        'hotlink_protection' => env('CDN_HOTLINK_PROTECTION', true),
        'referrer_policy' => env('CDN_REFERRER_POLICY', 'strict-origin-when-cross-origin'),
        'access_control' => env('CDN_ACCESS_CONTROL', true),
    ],

    'monitoring' => [
        'enabled' => env('CDN_MONITORING_ENABLED', true),
        'bandwidth_alerts' => env('CDN_BANDWIDTH_ALERTS', true),
        'bandwidth_threshold' => env('CDN_BANDWIDTH_THRESHOLD', 1000), // GB
        'cache_hit_rate_threshold' => env('CDN_CACHE_HIT_RATE_THRESHOLD', 90), // percentage
    ],
];
