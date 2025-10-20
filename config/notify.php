<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Notification Provider
    |--------------------------------------------------------------------------
    |
    | This option controls the default notification provider that will be used
    | when no specific provider is specified. You may change this default
    | as needed, but it must be one of the providers listed below.
    |
    */

    'default' => env('NOTIFY_DEFAULT_PROVIDER', 'slack'),

    /*
    |--------------------------------------------------------------------------
    | Notification Providers
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many notification providers as you wish.
    | Each provider can be enabled or disabled independently.
    |
    */

    'providers' => [

        'slack' => [
            'enabled' => env('NOTIFY_SLACK_ENABLED', true),
            'webhook_url' => env('NOTIFY_SLACK_WEBHOOK_URL'),
            'username' => env('NOTIFY_SLACK_USERNAME', 'Laravel Notify'),
            'icon_emoji' => env('NOTIFY_SLACK_ICON', ':bell:'),
            'channel' => env('NOTIFY_SLACK_CHANNEL'),
            'timeout' => 30,
            'verify_ssl' => true,
        ],

        'discord' => [
            'enabled' => env('NOTIFY_DISCORD_ENABLED', false),
            'webhook_url' => env('NOTIFY_DISCORD_WEBHOOK_URL'),
            'username' => env('NOTIFY_DISCORD_USERNAME', 'Laravel Notify'),
            'avatar_url' => env('NOTIFY_DISCORD_AVATAR_URL'),
            'timeout' => 30,
            'verify_ssl' => true,
        ],

        'telegram' => [
            'enabled' => env('NOTIFY_TELEGRAM_ENABLED', false),
            'bot_token' => env('NOTIFY_TELEGRAM_BOT_TOKEN'),
            'chat_id' => env('NOTIFY_TELEGRAM_CHAT_ID'),
            'parse_mode' => env('NOTIFY_TELEGRAM_PARSE_MODE', 'HTML'),
            'disable_preview' => env('NOTIFY_TELEGRAM_DISABLE_PREVIEW', false),
            'disable_notification' => env('NOTIFY_TELEGRAM_DISABLE_NOTIFICATION', false),
            'timeout' => 30,
            'verify_ssl' => true,
        ],

        'email' => [
            'enabled' => env('NOTIFY_EMAIL_ENABLED', false),
            'to' => env('NOTIFY_EMAIL_TO'),
            'from' => env('NOTIFY_EMAIL_FROM'),
            'from_name' => env('NOTIFY_EMAIL_FROM_NAME', 'Laravel Notify'),
            'subject' => env('NOTIFY_EMAIL_SUBJECT', 'Laravel Notification'),
        ],

    ],

];
