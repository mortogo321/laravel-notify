<?php

declare(strict_types=1);

namespace Mortogo321\LaravelNotify\Tests;

use Mortogo321\LaravelNotify\NotifyServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    /**
     * Get package providers.
     *
     * @param \Illuminate\Foundation\Application $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            NotifyServiceProvider::class,
        ];
    }

    /**
     * Get package aliases.
     *
     * @param \Illuminate\Foundation\Application $app
     * @return array<string, class-string>
     */
    protected function getPackageAliases($app): array
    {
        return [
            'Notify' => \Mortogo321\LaravelNotify\Facades\Notify::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('notify', [
            'default' => 'slack',
            'providers' => [
                'slack' => [
                    'enabled' => true,
                    'webhook_url' => 'https://hooks.slack.com/services/TEST/TEST/TEST',
                    'username' => 'Test Bot',
                    'icon_emoji' => ':robot:',
                    'channel' => '#test-channel',
                    'timeout' => 30,
                    'verify_ssl' => true,
                ],
                'discord' => [
                    'enabled' => true,
                    'webhook_url' => 'https://discord.com/api/webhooks/123456789/test-token',
                    'username' => 'Test Bot',
                    'avatar_url' => 'https://example.com/avatar.png',
                    'timeout' => 30,
                    'verify_ssl' => true,
                ],
                'telegram' => [
                    'enabled' => true,
                    'bot_token' => '123456789:ABCDefGHIjklMNOpqrsTUVwxyz',
                    'chat_id' => '-1001234567890',
                    'parse_mode' => 'HTML',
                    'disable_preview' => false,
                    'disable_notification' => false,
                    'timeout' => 30,
                    'verify_ssl' => true,
                ],
                'email' => [
                    'enabled' => true,
                    'to' => 'test@example.com',
                    'from' => 'noreply@example.com',
                    'from_name' => 'Test App',
                    'subject' => 'Test Notification',
                ],
            ],
        ]);
    }
}
