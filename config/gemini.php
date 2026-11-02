<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Gemini API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the Google Gemini AI service integration.
    | The API key should be set in your .env file for security.
    |
    */

    'api_key' => env('GEMINI_API_KEY'),

    'model' => env('GEMINI_MODEL', 'gemini-2.0-flash'),

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Configure rate limiting for AI requests to prevent abuse.
    |
    */

    'rate_limit' => [
        'max_requests' => env('GEMINI_RATE_LIMIT', 30),
        'per_minutes' => 1,
    ],

    /*
    |--------------------------------------------------------------------------
    | Request Options
    |--------------------------------------------------------------------------
    |
    | Default options for AI chat requests.
    |
    */

    'defaults' => [
        'max_tokens' => env('GEMINI_MAX_TOKENS', 1000),
        'temperature' => env('GEMINI_TEMPERATURE', 0.7),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security
    |--------------------------------------------------------------------------
    |
    | Security-related configurations.
    |
    */

    'security' => [
        // Maximum message length allowed
        'max_message_length' => 2000,

        // Allowed roles in messages
        'allowed_roles' => ['user', 'model'],
    ],

];
