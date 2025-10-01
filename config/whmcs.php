<?php

return [

    /*
    |--------------------------------------------------------------------------
    | WHMCS API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for WHMCS API integration.
    | All credentials should be stored in .env file for security.
    |
    */

    // WHMCS API URL (without trailing slash)
    'api_url' => env('WHMCS_API_URL', 'https://yourdomain.com/whmcs'),

    // WHMCS API Credentials
    'api_identifier' => env('WHMCS_API_IDENTIFIER', ''),
    'api_secret' => env('WHMCS_API_SECRET', ''),

    // API Response Format (json or xml)
    'response_type' => 'json',

    // Timeout for API requests (seconds)
    'timeout' => env('WHMCS_API_TIMEOUT', 30),

    // Enable/Disable API calls (useful for development)
    'enabled' => env('WHMCS_ENABLED', true),

    // Cache settings
    'cache' => [
        'enabled' => env('WHMCS_CACHE_ENABLED', true),
        'ttl' => env('WHMCS_CACHE_TTL', 300), // 5 minutes default
    ],

    // Logging settings
    'logging' => [
        'enabled' => env('WHMCS_LOG_ENABLED', true),
        'log_requests' => env('WHMCS_LOG_REQUESTS', true),
        'log_responses' => env('WHMCS_LOG_RESPONSES', true),
    ],

    // Default client settings when pushing to WHMCS
    'defaults' => [
        'client' => [
            'currency' => 1, // Default currency ID in WHMCS
            'language' => 'english',
            'client_group_id' => 0, // 0 = no group
            'status' => 'Active',
            'email_preferences' => [
                'general_emails' => true,
                'product_emails' => true,
                'domain_emails' => true,
                'invoice_emails' => true,
                'support_emails' => true,
            ],
        ],
        'product' => [
            'payment_type' => 'recurring',
            'module' => 'none',
        ],
    ],

    // Field mapping: Laravel -> WHMCS
    'field_mapping' => [
        'client' => [
            'firstname' => 'client_name', // WHMCS field => Laravel field
            'lastname' => 'client_lastname',
            'email' => 'email',
            'address1' => 'address',
            'city' => 'city',
            'state' => 'state',
            'postcode' => 'postal_code',
            'country' => 'country_code',
            'phonenumber' => 'phone',
            'companyname' => 'company_name',
            'notes' => 'notes',
        ],
    ],

    // Webhook settings (for future bidirectional sync)
    'webhooks' => [
        'enabled' => env('WHMCS_WEBHOOKS_ENABLED', false),
        'secret' => env('WHMCS_WEBHOOK_SECRET', ''),
        'events' => [
            'ClientAdd',
            'ClientEdit',
            'ClientDelete',
            'InvoiceCreated',
            'InvoicePaid',
            'TicketOpen',
            'TicketClose',
        ],
    ],

    // Sync settings
    'sync' => [
        // Auto-retry failed syncs
        'auto_retry' => env('WHMCS_AUTO_RETRY', false),
        'retry_attempts' => 3,
        'retry_delay' => 5, // seconds
        
        // Conflict resolution (laravel_wins, whmcs_wins, manual)
        'conflict_resolution' => 'manual',
    ],

    // Testing mode
    'test_mode' => env('WHMCS_TEST_MODE', false),

];

