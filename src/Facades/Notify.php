<?php

declare(strict_types=1);

namespace Mortogo321\LaravelNotify\Facades;

use Illuminate\Support\Facades\Facade;
use Mortogo321\LaravelNotify\Contracts\NotificationProvider;
use Mortogo321\LaravelNotify\NotifyManager;

/**
 * @method static NotificationProvider provider(string|null $provider = null)
 * @method static array<string, mixed> send(string $message, array<string, mixed> $options = [])
 * @method static array<string, array<string, mixed>> sendToMultiple(array<int, string> $providers, string $message, array<string, mixed> $options = [])
 * @method static void extend(string $name, NotificationProvider $provider)
 * @method static array<int, string> getProviders()
 * @method static bool hasProvider(string $name)
 * @method static string|null getDefaultProvider()
 * @method static void setDefaultProvider(string $provider)
 * @method static mixed getConfig(string|null $key = null, mixed $default = null)
 *
 * @see NotifyManager
 */
class Notify extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'notify';
    }
}
