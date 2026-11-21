<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Product name
    |--------------------------------------------------------------------------
    | Shown only inside the admin panel. Never exposed to public visitors,
    | HTTP headers, emails, meta tags or asset paths.
    */
    'product_name' => env('CMS_PRODUCT_NAME', 'Party Sites CMS'),

    /*
    |--------------------------------------------------------------------------
    | Version
    |--------------------------------------------------------------------------
    | Read from the VERSION file at the project root.
    */
    'version' => trim((string) @file_get_contents(base_path('VERSION'))),

    /*
    |--------------------------------------------------------------------------
    | Instance type
    | production | demo | local
    |--------------------------------------------------------------------------
    | 'demo'  : enables noindex, panel banner, mail allow-list, nightly reset.
    | 'local' : development mode; never deploy to production with this value.
    */
    'instance' => env('CMS_INSTANCE', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Feature flags
    |--------------------------------------------------------------------------
    */
    'features' => [
        // Enable the AI translation button (requires the party's own Anthropic key).
        'ai_translation'   => (bool) env('CMS_FEATURE_AI_TRANSLATION', false),

        // Cookie-free server-side page counter (IP hashed, panel-only).
        'page_counter'     => (bool) env('CMS_FEATURE_PAGE_COUNTER', true),

        // Optional Redis cache (falls back to file cache automatically).
        'redis_cache'      => (bool) env('CMS_FEATURE_REDIS', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin panel path (default: /panel)
    | Configurable per installation in the settings table.
    |--------------------------------------------------------------------------
    */
    'panel_path' => env('CMS_PANEL_PATH', 'panel'),

    /*
    |--------------------------------------------------------------------------
    | Supported locales
    |--------------------------------------------------------------------------
    */
    'supported_locales' => ['en', 'fr', 'pt', 'ar'],

    /*
    |--------------------------------------------------------------------------
    | Demo mail allow-list
    | Only used when CMS_INSTANCE=demo. All other mail is written to the log.
    |--------------------------------------------------------------------------
    */
    'demo_mail_allowlist' => array_filter(
        explode(',', env('CMS_DEMO_MAIL_ALLOWLIST', ''))
    ),

];
