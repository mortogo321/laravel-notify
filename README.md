# Laravel Notify

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mortogo321/laravel-notify.svg?style=flat-square)](https://packagist.org/packages/mortogo321/laravel-notify)
[![Tests](https://github.com/mortogo321/laravel-notify/actions/workflows/tests.yml/badge.svg)](https://github.com/mortogo321/laravel-notify/actions/workflows/tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/mortogo321/laravel-notify.svg?style=flat-square)](https://packagist.org/packages/mortogo321/laravel-notify)
[![GitHub License](https://img.shields.io/github/license/mortogo321/laravel-notify.svg?style=flat-square)](https://github.com/mortogo321/laravel-notify/blob/main/LICENSE.md)
[![PHP Version](https://img.shields.io/packagist/php-v/mortogo321/laravel-notify.svg?style=flat-square)](https://packagist.org/packages/mortogo321/laravel-notify)

A flexible Laravel package for sending notifications and alerts to multiple providers including Slack, Discord, Telegram, LINE Notify, and Email.

## Features

- **Multiple Providers**: Support for Slack, Discord, Telegram, LINE Notify, and Email notifications
- **Easy Provider Switching**: Seamlessly switch between providers with a simple API
- **Send to Multiple Providers**: Broadcast notifications to multiple providers at once
- **Channel/Group Helpers**: Built-in methods to list channels, get chat IDs, and more
- **Highly Configurable**: Customize each provider with extensive options
- **Extensible Architecture**: Easily add custom notification providers
- **Facade Support**: Clean and intuitive facade for easy integration
- **Type-Safe**: Built with modern PHP 8.2+ strict types

## Requirements

- PHP 8.2, 8.3, or 8.4
- Laravel 11.x or 12.x

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

# LINE Notify Configuration
NOTIFY_LINE_ENABLED=true
NOTIFY_LINE_ACCESS_TOKEN=your-line-notify-access-token

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

**Telegram**: Message `@BotFather` on Telegram, create a bot, then use the helper methods below to get your chat ID

**LINE Notify**: Generate a personal access token at https://notify-bot.line.me/my/

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

// Send to LINE Notify
Notify::provider('line')->send('Daily report is ready.');

// Send to Email
Notify::provider('email')->send('Monthly report is ready.');
```

### Send to Multiple Providers

```php
$result = Notify::sendToMultiple(
    ['slack', 'discord', 'telegram', 'line'],
    'Critical: Database backup failed!'
);

// Returns an array with results from each provider
// [
//     'slack' => ['success' => true, ...],
//     'discord' => ['success' => true, ...],
//     'telegram' => ['success' => false, 'error' => '...'],
//     'line' => ['success' => true, ...],
// ]
```

### Provider Management

```php
// Check if provider exists
if (Notify::hasProvider('slack')) {
    Notify::provider('slack')->send('Hello!');
}

// Get all registered providers
$providers = Notify::getProviders(); // ['slack', 'discord', 'telegram', 'line', 'email']

// Get/set default provider
$default = Notify::getDefaultProvider(); // 'slack'
Notify::setDefaultProvider('discord');

// Get configuration
$config = Notify::getConfig(); // All config
$default = Notify::getConfig('default'); // 'slack'
```

## Channel & Group ID Helpers

Each provider includes helper methods to retrieve channel IDs, group IDs, and other useful information.

### Slack Helpers

```php
$slack = Notify::provider('slack');

// Get configured settings
$channel = $slack->getChannel();           // '#general'
$username = $slack->getUsername();         // 'Laravel Notify'
$icon = $slack->getIconEmoji();           // ':bell:'
$webhookUrl = $slack->getWebhookUrl();    // Masked URL for security

// List channels (requires bot token with channels:read scope)
$result = $slack->listChannels('xoxb-your-bot-token');
// [
//     'success' => true,
//     'channels' => [
//         ['id' => 'C1234567890', 'name' => 'general', 'is_private' => false, 'num_members' => 50],
//         ['id' => 'C0987654321', 'name' => 'dev-team', 'is_private' => true, 'num_members' => 10],
//     ],
//     'next_cursor' => '...'
// ]

// Get channel info by ID
$result = $slack->getChannelInfo('xoxb-your-bot-token', 'C1234567890');
// ['success' => true, 'channel' => [...]]
```

### Discord Helpers

```php
$discord = Notify::provider('discord');

// Get configured settings
$username = $discord->getUsername();       // 'Laravel Notify'
$avatarUrl = $discord->getAvatarUrl();    // Avatar URL
$webhookUrl = $discord->getWebhookUrl();  // Masked URL for security
$webhookId = $discord->getWebhookId();    // '123456789012345678'

// Get webhook info (channel ID, guild ID, etc.)
$info = $discord->getWebhookInfo();
// [
//     'success' => true,
//     'webhook' => [
//         'id' => '123456789012345678',
//         'name' => 'Laravel Notify',
//         'channel_id' => '987654321098765432',
//         'guild_id' => '111222333444555666',
//         'avatar' => '...'
//     ]
// ]

// Shorthand methods
$channelId = $discord->getChannelId();    // '987654321098765432'
$guildId = $discord->getGuildId();        // '111222333444555666'

// List guild channels (requires bot token)
$result = $discord->listGuildChannels('your-bot-token', $guildId);
// [
//     'success' => true,
//     'channels' => [
//         ['id' => '...', 'name' => 'general', 'type' => 0, 'position' => 0],
//         ['id' => '...', 'name' => 'announcements', 'type' => 0, 'position' => 1],
//     ]
// ]
```

### Telegram Helpers

```php
$telegram = Notify::provider('telegram');

// Get configured settings
$chatId = $telegram->getChatId();         // '-1001234567890'
$botToken = $telegram->getBotToken();     // Masked token for security
$parseMode = $telegram->getParseMode();   // 'HTML'

// Get bot info
$bot = $telegram->getMe();
// [
//     'success' => true,
//     'bot' => [
//         'id' => 123456789,
//         'first_name' => 'My Bot',
//         'username' => 'my_bot',
//         'can_join_groups' => true,
//         ...
//     ]
// ]

// Get chat info
$chat = $telegram->getChat();  // Uses configured chat_id
$chat = $telegram->getChat('-1009876543210');  // Or specify a different chat
// [
//     'success' => true,
//     'chat' => [
//         'id' => -1001234567890,
//         'type' => 'supergroup',
//         'title' => 'My Group',
//         ...
//     ]
// ]

// Get recent updates to find chat IDs
// First, send a message to your bot, then call:
$updates = $telegram->getUpdates();
// [
//     'success' => true,
//     'updates' => [...],
//     'chats' => [
//         ['id' => 123456789, 'type' => 'private', 'first_name' => 'John', 'username' => 'john_doe'],
//         ['id' => -1001234567890, 'type' => 'supergroup', 'title' => 'My Group'],
//     ]
// ]

// Get chat member count
$count = $telegram->getChatMemberCount();
// ['success' => true, 'count' => 150]

// Get chat administrators
$admins = $telegram->getChatAdministrators();
// ['success' => true, 'administrators' => [...]]

// Webhook management
$telegram->setWebhook('https://your-domain.com/webhook', [
    'secret_token' => 'your-secret',
    'allowed_updates' => ['message', 'callback_query'],
]);
$telegram->deleteWebhook();
$webhookInfo = $telegram->getWebhookInfo();
```

### LINE Notify Helpers

```php
$line = Notify::provider('line');

// Get masked access token
$token = $line->getAccessToken();  // 'test-****67890'

// Check API status
$status = $line->getStatus();
// [
//     'success' => true,
//     'status' => 200,
//     'target_type' => 'USER',
//     'target' => 'John Doe',
// ]
```

### Email Helpers

```php
$email = Notify::provider('email');

// Get configured settings
$to = $email->getTo();                    // 'admin@example.com'
$from = $email->getFrom();                // 'noreply@example.com'
$fromName = $email->getFromName();        // 'Laravel Notify'
$subject = $email->getSubject();          // 'Laravel Notification'
$cc = $email->getCc();                    // CC recipients
$bcc = $email->getBcc();                  // BCC recipients

// Validate email addresses
$isValid = $email->validateEmail('test@example.com');  // true
$isValid = $email->validateEmail('invalid-email');     // false

// Validate multiple emails
$result = $email->validateEmails([
    'valid@example.com',
    'also-valid@test.com',
    'invalid-email',
    'bad@',
]);
// [
//     'valid' => ['valid@example.com', 'also-valid@test.com'],
//     'invalid' => ['invalid-email', 'bad@']
// ]
```

## Advanced Options

### Slack with Attachments

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

### Slack with Block Kit

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

### Discord with Embeds

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

### Telegram with Custom Options

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

### LINE Notify with Sticker

```php
Notify::provider('line')->send('Deployment completed!', [
    'stickerPackageId' => 446,
    'stickerId' => 1988,
]);
```

### LINE Notify with Image

```php
Notify::provider('line')->send('Check this chart:', [
    'imageThumbnail' => 'https://example.com/chart-thumb.png',
    'imageFullsize' => 'https://example.com/chart-full.png',
]);
```

### Email with CC/BCC

```php
Notify::provider('email')->send('<h1>Monthly Report</h1><p>Your report is ready!</p>', [
    'to' => 'user@example.com',
    'subject' => 'Your Monthly Report',
    'from' => 'reports@example.com',
    'from_name' => 'Report System',
    'cc' => 'manager@example.com',
    'bcc' => 'archive@example.com',
]);
```

## Using in Controllers

```php
<?php

namespace App\Http\Controllers;

use Mortogo321\LaravelNotify\Facades\Notify;
use Mortogo321\LaravelNotify\Exceptions\NotificationException;

class DeploymentController extends Controller
{
    public function deploy()
    {
        // Your deployment logic...

        try {
            Notify::provider('slack')->send('Deployment completed successfully!', [
                'attachments' => [
                    [
                        'color' => 'good',
                        'title' => 'Deployment Status',
                        'text' => 'All services are running normally',
                    ]
                ]
            ]);
        } catch (NotificationException $e) {
            logger()->error('Failed to send notification: ' . $e->getMessage(), [
                'provider' => $e->getProvider(),
                'context' => $e->getContext(),
            ]);
        }

        return response()->json(['status' => 'success']);
    }
}
```

## Using in Jobs

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

        Notify::send('Data processing completed!');
    }

    public function failed(\Throwable $exception): void
    {
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

## Custom Providers

You can extend the package with custom providers:

```php
use Mortogo321\LaravelNotify\Providers\AbstractProvider;
use Mortogo321\LaravelNotify\Facades\Notify;

class CustomProvider extends AbstractProvider
{
    protected string $name = 'custom';

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->validateConfig(['api_key']);
    }

    public function send(string $message, array $options = []): array
    {
        if (! $this->isEnabled()) {
            return $this->disabledResponse();
        }

        // Your custom implementation
        return $this->post('https://api.custom-service.com/send', [
            'message' => $message,
            'api_key' => $this->getConfig('api_key'),
        ]);
    }
}

// Register the custom provider
Notify::extend('custom', new CustomProvider([
    'enabled' => true,
    'api_key' => 'your-api-key',
]));

// Use it
Notify::provider('custom')->send('Hello from custom provider!');
```

## Helper Functions

You can create a helper function in your `app/helpers.php`:

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

| Method | Description |
|--------|-------------|
| `provider(?string $name)` | Get a specific provider instance |
| `send(string $message, array $options = [])` | Send using default provider |
| `sendToMultiple(array $providers, string $message, array $options = [])` | Send to multiple providers |
| `extend(string $name, NotificationProvider $provider)` | Register a custom provider |
| `getProviders()` | Get list of registered provider names |
| `hasProvider(string $name)` | Check if provider is registered |
| `getDefaultProvider()` | Get the default provider name |
| `setDefaultProvider(string $name)` | Set the default provider |
| `getConfig(?string $key, mixed $default = null)` | Get configuration value(s) |

### Provider Methods

All providers implement these methods:

| Method | Description |
|--------|-------------|
| `send(string $message, array $options = [])` | Send notification |
| `getName()` | Get provider name |
| `isEnabled()` | Check if provider is enabled |
| `getAllConfig()` | Get all configuration values |

### Provider-Specific Helpers

#### Slack
| Method | Description |
|--------|-------------|
| `getChannel()` | Get configured channel |
| `getUsername()` | Get configured username |
| `getIconEmoji()` | Get configured icon emoji |
| `getWebhookUrl()` | Get masked webhook URL |
| `listChannels(string $botToken, array $options = [])` | List available channels |
| `getChannelInfo(string $botToken, string $channelId)` | Get channel info |

#### Discord
| Method | Description |
|--------|-------------|
| `getUsername()` | Get configured username |
| `getAvatarUrl()` | Get configured avatar URL |
| `getWebhookUrl()` | Get masked webhook URL |
| `getWebhookId()` | Extract webhook ID from URL |
| `getWebhookInfo()` | Get webhook details from Discord |
| `getChannelId()` | Get channel ID from webhook |
| `getGuildId()` | Get guild (server) ID from webhook |
| `listGuildChannels(string $botToken, string $guildId)` | List guild channels |

#### Telegram
| Method | Description |
|--------|-------------|
| `getChatId()` | Get configured chat ID |
| `getBotToken()` | Get masked bot token |
| `getParseMode()` | Get configured parse mode |
| `getMe()` | Get bot information |
| `getChat(?string $chatId)` | Get chat information |
| `getUpdates(array $options = [])` | Get updates with chat IDs |
| `getChatAdministrators(?string $chatId)` | Get chat administrators |
| `getChatMemberCount(?string $chatId)` | Get chat member count |
| `setWebhook(string $url, array $options = [])` | Set webhook URL |
| `deleteWebhook()` | Delete webhook |
| `getWebhookInfo()` | Get webhook info |

#### LINE Notify
| Method | Description |
|--------|-------------|
| `getAccessToken()` | Get masked access token |
| `getStatus()` | Get LINE Notify API status |

#### Email
| Method | Description |
|--------|-------------|
| `getTo()` | Get configured recipient(s) |
| `getFrom()` | Get configured sender |
| `getFromName()` | Get configured sender name |
| `getSubject()` | Get configured subject |
| `getCc()` | Get configured CC recipients |
| `getBcc()` | Get configured BCC recipients |
| `validateEmail(string $email)` | Validate an email address |
| `validateEmails(array $emails)` | Validate multiple emails |

## Upgrading

### From v1.x to v2.0

- **PHP 8.2+ required**: Update your PHP version if running 8.1
- **Laravel 11+ required**: Update Laravel if running 10.x
- **Guzzle removed**: If you relied on `$this->client` (Guzzle Client) in custom providers, switch to `$this->post()` and `$this->get()` helpers or use the `Http` facade directly
- **New `get()` helper**: `AbstractProvider` now provides a `get()` method for HTTP GET requests

## Testing

Run the test suite:

```bash
composer test
```

Run code quality checks:

```bash
# Code style (Laravel Pint)
composer pint

# Static analysis (PHPStan)
composer stan

# All quality checks
composer quality
```

## Roadmap

The following features are planned for future releases:

- **Queue Support**: Send notifications asynchronously
- **Schedule Support**: Schedule recurring notifications
- **Additional Providers**: SMS (Twilio), Microsoft Teams, PagerDuty, Webhook
- **Retry Logic**: Automatic retry with exponential backoff
- **Event System**: Listen to notification events
- **Rate Limiting**: Built-in rate limiting per provider
- **Notification Templates**: Reusable message templates

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

### Getting Telegram Chat ID

1. Create a bot via @BotFather
2. Add the bot to your group/channel
3. Send a message to the group
4. Use the helper: `Notify::provider('telegram')->getUpdates()`
5. Find your chat ID in the `chats` array

### Getting LINE Notify Token

1. Go to https://notify-bot.line.me/my/
2. Click "Generate token"
3. Select a chat room to receive notifications
4. Copy the generated token to your `.env` file

## Security

- Never expose webhook URLs or API tokens in client-side code
- Store all credentials in `.env` file (never commit to version control)
- Use environment-specific credentials for different environments
- Helper methods return masked tokens/URLs for safe logging

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## License

MIT License. Please see [License File](LICENSE.md) for more information.

## Credits

- [Mor](https://github.com/mortogo321)
- All contributors

## Support

For issues, questions, or contributions, please visit the [GitHub repository](https://github.com/mortogo321/laravel-notify).
