<?php

namespace Mortogo321\LaravelNotify\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Mortogo321\LaravelNotify\Contracts\NotificationProvider provider(string|null $provider = null)
 * @method static mixed send(string $message, array $options = [])
 * @method static array sendToMultiple(array $providers, string $message, array $options = [])
 * @method static void extend(string $name, \Mortogo321\LaravelNotify\Contracts\NotificationProvider $provider)
 * @method static array getProviders()
 *
 * @see \Mortogo321\LaravelNotify\NotifyManager
 */
class Notify extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'notify';
    }
}
