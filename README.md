# Laravel Notify

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mortogo321/laravel-notify.svg?style=flat-square)](https://packagist.org/packages/mortogo321/laravel-notify)
[![Total Downloads](https://img.shields.io/packagist/dt/mortogo321/laravel-notify.svg?style=flat-square)](https://packagist.org/packages/mortogo321/laravel-notify)
[![GitHub License](https://img.shields.io/github/license/mortogo321/laravel-notify.svg?style=flat-square)](https://github.com/mortogo321/laravel-notify/blob/main/LICENSE.md)
[![PHP Version](https://img.shields.io/packagist/php-v/mortogo321/laravel-notify.svg?style=flat-square)](https://packagist.org/packages/mortogo321/laravel-notify)

A flexible Laravel package for sending notifications and alerts to multiple providers including Slack, Discord, Telegram, and Email.

## Features

- **Multiple Providers**: Support for Slack, Discord, Telegram, and Email notifications
- **Easy Provider Switching**: Seamlessly switch between providers with a simple API
- **Send to Multiple Providers**: Broadcast notifications to multiple providers at once
- **Highly Configurable**: Customize each provider with extensive options
- **Extensible Architecture**: Easily add custom notification providers
- **Facade Support**: Clean and intuitive facade for easy integration
- **Type-Safe**: Built with modern PHP 8.1+ features

## Installation

Install the package via Composer:

```bash
composer require mortogo321/laravel-notify
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag=notify-config
```

## Configuration

Add your notification provider credentials to your `.env` file:

```env
NOTIFY_DEFAULT_PROVIDER=slack

# Slack Configuration
NOTIFY_SLACK_ENABLED=true
NOTIFY_SLACK_WEBHOOK_URL=https://hooks.slack.com/services/YOUR/WEBHOOK/URL
NOTIFY_SLACK_USERNAME="Laravel Notify"
NOTIFY_SLACK_ICON=:bell:
NOTIFY_SLACK_CHANNEL=#general

# Discord Configuration
NOTIFY_DISCORD_ENABLED=true
NOTIFY_DISCORD_WEBHOOK_URL=https://discord.com/api/webhooks/YOUR/WEBHOOK/URL
NOTIFY_DISCORD_USERNAME="Laravel Notify"

# Telegram Configuration
NOTIFY_TELEGRAM_ENABLED=true
NOTIFY_TELEGRAM_BOT_TOKEN=your-bot-token
NOTIFY_TELEGRAM_CHAT_ID=your-chat-id
NOTIFY_TELEGRAM_PARSE_MODE=HTML

# Email Configuration
NOTIFY_EMAIL_ENABLED=true
NOTIFY_EMAIL_TO=admin@example.com
NOTIFY_EMAIL_FROM=noreply@example.com
NOTIFY_EMAIL_FROM_NAME="Laravel Notify"
NOTIFY_EMAIL_SUBJECT="Laravel Notification"
```

### Getting Provider Credentials

**Slack**: Get your webhook URL from https://api.slack.com/apps (Incoming Webhooks)

**Discord**: Server Settings > Integrations > Webhooks > New Webhook

**Telegram**: Message `@BotFather` on Telegram, create a bot, then get your chat ID from `https://api.telegram.org/bot<TOKEN>/getUpdates`

**Email**: Uses Laravel's built-in mail system (configure in `config/mail.php`)

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

## API Reference

### NotifyManager Methods

```php
// Get a specific provider instance
$provider = Notify::provider('slack');

// Send using default provider
Notify::send(string $message, array $options = []): mixed;

// Send to multiple providers
Notify::sendToMultiple(array $providers, string $message, array $options = []): array;

// Register a custom provider
Notify::extend(string $name, NotificationProvider $provider): void;

// Get list of registered providers
Notify::getProviders(): array;
```

### Provider Methods

All providers implement the following methods:

```php
// Send notification
$provider->send(string $message, array $options = []): mixed;

// Get provider name
$provider->getName(): string;

// Check if provider is enabled
$provider->isEnabled(): bool;
```

## Testing

Run the test suite:

```bash
composer test
```

## Troubleshooting

### Notification not sending

- Verify your provider credentials in `.env`
- Check that the provider is enabled (`NOTIFY_*_ENABLED=true`)
- Ensure the webhook URL or API credentials are correct
- Check logs for detailed error messages

### Provider not found error

- Ensure the provider is configured in `config/notify.php`
- Verify the provider name matches exactly (e.g., 'slack', not 'Slack')
- Check that a default provider is set if not specifying one

## Security

- Never expose webhook URLs or API tokens in client-side code
- Store all credentials in `.env` file (never commit to version control)
- Use environment-specific credentials for different environments
- Consider rate limiting for production use

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## License

MIT License. Please see [License File](LICENSE.md) for more information.

## Credits

- [Mor](https://github.com/mortogo321)
- All contributors

## Support

For issues, questions, or contributions, please visit the [GitHub repository](https://github.com/mortogo321/laravel-notify).
