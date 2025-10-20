# Laravel Notify

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mortogo321/laravel-notify.svg?style=flat-square)](https://packagist.org/packages/mortogo321/laravel-notify)
[![Total Downloads](https://img.shields.io/packagist/dt/mortogo321/laravel-notify.svg?style=flat-square)](https://packagist.org/packages/mortogo321/laravel-notify)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/mortogo321/laravel-notify/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/mortogo321/laravel-notify/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/mortogo321/laravel-notify/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/mortogo321/laravel-notify/actions?query=workflow%3A"fix-php-code-style-issues"+branch%3Amain)
[![License](https://img.shields.io/packagist/l/mortogo321/laravel-notify.svg?style=flat-square)](https://packagist.org/packages/mortogo321/laravel-notify)

A flexible Laravel package for sending notifications and alerts to multiple providers including Slack, Discord, Telegram, and Email.

## Features

- 🚀 Multiple notification providers (Slack, Discord, Telegram, Email)
- 🔌 Easy provider switching
- 📦 Simple and intuitive API
- ⚙️ Highly configurable
- 🎯 Send to multiple providers at once
- 🔧 Extensible - add custom providers
- 💪 Type-safe with modern PHP

## Requirements

- PHP 8.1 or higher
- Laravel 10.x or 11.x

## Installation

Install the package via Composer:

```bash
composer require mortogo321/laravel-notify
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag=notify-config
```

## Quick Start

### 1. Setup Slack Webhook

1. Go to https://api.slack.com/apps
2. Create a new app or select an existing one
3. Navigate to "Incoming Webhooks" and activate it
4. Click "Add New Webhook to Workspace"
5. Select a channel and authorize
6. Copy the webhook URL

### 2. Setup Discord Webhook

1. Open your Discord server
2. Go to Server Settings > Integrations
3. Click "Webhooks" > "New Webhook"
4. Choose a channel and customize the webhook name/avatar
5. Click "Copy Webhook URL"

### 3. Setup Telegram Bot

1. Open Telegram and search for `@BotFather`
2. Send `/newbot` and follow instructions to create your bot
3. Copy the bot token provided
4. Send a message to your bot
5. Visit `https://api.telegram.org/bot<YOUR_BOT_TOKEN>/getUpdates` in your browser
6. Find the `chat.id` value in the response

## Configuration

After publishing, configure your providers in `config/notify.php` or use environment variables in your `.env` file:

### Slack Configuration

```env
NOTIFY_DEFAULT_PROVIDER=slack
NOTIFY_SLACK_ENABLED=true
NOTIFY_SLACK_WEBHOOK_URL=https://hooks.slack.com/services/YOUR/WEBHOOK/URL
NOTIFY_SLACK_USERNAME="Laravel Notify"
NOTIFY_SLACK_ICON=:bell:
NOTIFY_SLACK_CHANNEL=#general
```

### Discord Configuration

```env
NOTIFY_DISCORD_ENABLED=true
NOTIFY_DISCORD_WEBHOOK_URL=https://discord.com/api/webhooks/YOUR/WEBHOOK/URL
NOTIFY_DISCORD_USERNAME="Laravel Notify"
```

### Telegram Configuration

```env
NOTIFY_TELEGRAM_ENABLED=true
NOTIFY_TELEGRAM_BOT_TOKEN=your-bot-token
NOTIFY_TELEGRAM_CHAT_ID=your-chat-id
NOTIFY_TELEGRAM_PARSE_MODE=HTML
```

### Email Configuration

```env
NOTIFY_EMAIL_ENABLED=true
NOTIFY_EMAIL_TO=admin@example.com
NOTIFY_EMAIL_FROM=noreply@example.com
NOTIFY_EMAIL_FROM_NAME="Laravel Notify"
NOTIFY_EMAIL_SUBJECT="Laravel Notification"
```

## Usage

### Basic Usage

Using the default provider:

```php
use Mortogo321\LaravelNotify\Facades\Notify;

// Send a simple notification
Notify::send('Hello, this is a test notification!');
```

### Using Specific Provider

```php
// Send to Slack
Notify::provider('slack')->send('Deployment completed successfully!');

// Send to Discord
Notify::provider('discord')->send('New user registered!');

// Send to Telegram
Notify::provider('telegram')->send('Server CPU usage is high!');

// Send to Email
Notify::provider('email')->send('Monthly report is ready.');
```

### Advanced Options

#### Slack with Attachments

```php
Notify::provider('slack')->send('Check out this info:', [
    'username' => 'Custom Bot Name',
    'icon_emoji' => ':rocket:',
    'channel' => '#deployments',
    'attachments' => [
        [
            'color' => 'good',
            'title' => 'Deployment Status',
            'text' => 'Successfully deployed to production',
            'fields' => [
                [
                    'title' => 'Environment',
                    'value' => 'Production',
                    'short' => true
                ],
                [
                    'title' => 'Version',
                    'value' => 'v2.1.0',
                    'short' => true
                ]
            ]
        ]
    ]
]);
```

#### Slack with Block Kit

```php
Notify::provider('slack')->send('', [
    'blocks' => [
        [
            'type' => 'section',
            'text' => [
                'type' => 'mrkdwn',
                'text' => '*Deployment Alert* :rocket:'
            ]
        ],
        [
            'type' => 'divider'
        ],
        [
            'type' => 'section',
            'fields' => [
                [
                    'type' => 'mrkdwn',
                    'text' => '*Status:*\nSuccess'
                ],
                [
                    'type' => 'mrkdwn',
                    'text' => '*Environment:*\nProduction'
                ]
            ]
        ]
    ]
]);
```

#### Discord with Embeds

```php
Notify::provider('discord')->send('New deployment!', [
    'username' => 'Deploy Bot',
    'embeds' => [
        [
            'title' => 'Deployment Status',
            'description' => 'Successfully deployed to production',
            'color' => 3066993, // Green color
            'fields' => [
                [
                    'name' => 'Environment',
                    'value' => 'Production',
                    'inline' => true
                ],
                [
                    'name' => 'Version',
                    'value' => 'v2.1.0',
                    'inline' => true
                ]
            ],
            'timestamp' => now()->toIso8601String()
        ]
    ]
]);
```

#### Telegram with Custom Options

```php
Notify::provider('telegram')->send('<b>Alert!</b> Server CPU usage is at 90%', [
    'parse_mode' => 'HTML',
    'disable_notification' => false,
    'reply_markup' => [
        'inline_keyboard' => [
            [
                ['text' => 'View Dashboard', 'url' => 'https://dashboard.example.com']
            ]
        ]
    ]
]);
```

#### Email with Custom Recipients

```php
Notify::provider('email')->send('<h1>Monthly Report</h1><p>Your report is ready!</p>', [
    'to' => 'custom@example.com',
    'subject' => 'Your Monthly Report',
    'from' => 'reports@example.com',
    'from_name' => 'Report System'
]);
```

### Send to Multiple Providers

```php
$result = Notify::sendToMultiple(
    ['slack', 'discord', 'telegram'],
    'Critical: Database backup failed!'
);

// Returns an array with results from each provider
// [
//     'slack' => ['success' => true, ...],
//     'discord' => ['success' => true, ...],
//     'telegram' => ['success' => false, 'error' => '...']
// ]
```

### Using in Controllers

```php
<?php

namespace App\Http\Controllers;

use Mortogo321\LaravelNotify\Facades\Notify;

class DeploymentController extends Controller
{
    public function deploy()
    {
        // Your deployment logic...

        try {
            // Notify about successful deployment
            Notify::provider('slack')->send('Deployment completed successfully!', [
                'attachments' => [
                    [
                        'color' => 'good',
                        'title' => 'Deployment Status',
                        'text' => 'All services are running normally',
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            // Handle notification error
            logger()->error('Failed to send notification: ' . $e->getMessage());
        }

        return response()->json(['status' => 'success']);
    }
}
```

### Using in Jobs

```php
<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Mortogo321\LaravelNotify\Facades\Notify;

class ProcessDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        // Process data...

        // Notify when complete
        Notify::send('Data processing completed!');
    }

    public function failed(\Throwable $exception): void
    {
        // Notify on failure
        Notify::provider('slack')->send('Job failed: ' . $exception->getMessage(), [
            'attachments' => [
                [
                    'color' => 'danger',
                    'title' => 'Job Failure',
                    'text' => $exception->getMessage(),
                ]
            ]
        ]);
    }
}
```

### Custom Providers

You can extend the package with custom providers:

```php
use Mortogo321\LaravelNotify\Providers\AbstractProvider;
use Mortogo321\LaravelNotify\Facades\Notify;

class CustomProvider extends AbstractProvider
{
    protected string $name = 'custom';

    public function send(string $message, array $options = []): mixed
    {
        // Your custom implementation
        return ['success' => true];
    }
}

// Register the custom provider
Notify::extend('custom', new CustomProvider([
    'api_key' => 'your-api-key'
]));

// Use it
Notify::provider('custom')->send('Hello from custom provider!');
```

### Helper Functions

You can also create a helper function in your `app/helpers.php`:

```php
if (!function_exists('notify')) {
    function notify(string $message, ?string $provider = null, array $options = [])
    {
        if ($provider) {
            return app('notify')->provider($provider)->send($message, $options);
        }

        return app('notify')->send($message, $options);
    }
}

// Usage
notify('Hello World!');
notify('Hello Slack!', 'slack');
notify('Hello Discord!', 'discord', ['username' => 'Custom Bot']);
```

## Available Methods

### NotifyManager

- `provider(?string $provider = null)` - Get a specific provider instance
- `send(string $message, array $options = [])` - Send using default provider
- `sendToMultiple(array $providers, string $message, array $options = [])` - Send to multiple providers
- `extend(string $name, NotificationProvider $provider)` - Register custom provider
- `getProviders()` - Get list of registered providers

### All Providers

- `send(string $message, array $options = [])` - Send notification
- `getName()` - Get provider name
- `isEnabled()` - Check if provider is enabled

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security

If you discover any security-related issues, please email the maintainer instead of using the issue tracker.

## Credits

- [Mor](https://github.com/mortogo321)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
