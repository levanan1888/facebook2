<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'facebook' => [
        'ads_token' => env('FACEBOOK_ADS_TOKEN'),
        'app_id' => env('FACEBOOK_APP_ID'),
        'app_secret' => env('FACEBOOK_APP_SECRET'),
        'api_version' => env('FACEBOOK_API_VERSION', 'v19.0'),
        'graph_url' => env('FACEBOOK_GRAPH_URL', 'https://graph.facebook.com'),
        'batch_size' => env('FACEBOOK_BATCH_SIZE', 100),
        'timeout' => env('FACEBOOK_TIMEOUT', 30),
        'retry_attempts' => env('FACEBOOK_RETRY_ATTEMPTS', 3),
    ],

    'google' => [
        'ads_client_id' => env('GOOGLE_ADS_CLIENT_ID'),
        'ads_client_secret' => env('GOOGLE_ADS_CLIENT_SECRET'),
        'ads_refresh_token' => env('GOOGLE_ADS_REFRESH_TOKEN'),
        'ads_developer_token' => env('GOOGLE_ADS_DEVELOPER_TOKEN'),
        'ads_login_customer_id' => env('GOOGLE_ADS_LOGIN_CUSTOMER_ID'),
    ],

    'tiktok' => [
        'ads_app_id' => env('TIKTOK_ADS_APP_ID'),
        'ads_secret' => env('TIKTOK_ADS_SECRET'),
        'ads_access_token' => env('TIKTOK_ADS_ACCESS_TOKEN'),
    ],

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
    ],

];
