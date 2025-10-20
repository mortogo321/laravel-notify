<?php

namespace Mortogo321\LaravelNotify;

use Mortogo321\LaravelNotify\Contracts\NotificationProvider;
use Mortogo321\LaravelNotify\Exceptions\ProviderNotFoundException;
use Mortogo321\LaravelNotify\Providers\SlackProvider;
use Mortogo321\LaravelNotify\Providers\DiscordProvider;
use Mortogo321\LaravelNotify\Providers\TelegramProvider;
use Mortogo321\LaravelNotify\Providers\EmailProvider;

class NotifyManager
{
    protected array $providers = [];
    protected array $config;
    protected ?string $defaultProvider = null;

    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->defaultProvider = $config['default'] ?? null;
        $this->registerProviders();
    }

    /**
     * Register all available providers.
     *
     * @return void
     */
    protected function registerProviders(): void
    {
        $providerClasses = [
            'slack' => SlackProvider::class,
            'discord' => DiscordProvider::class,
            'telegram' => TelegramProvider::class,
            'email' => EmailProvider::class,
        ];

        foreach ($providerClasses as $name => $class) {
            if (isset($this->config['providers'][$name])) {
                $this->providers[$name] = new $class($this->config['providers'][$name]);
            }
        }
    }

    /**
     * Send notification using specific provider.
     *
     * @param string|null $provider
     * @return NotificationProvider
     * @throws ProviderNotFoundException
     */
    public function provider(?string $provider = null): NotificationProvider
    {
        $provider = $provider ?? $this->defaultProvider;

        if (!$provider) {
            throw new ProviderNotFoundException('No provider specified and no default provider set');
        }

        if (!isset($this->providers[$provider])) {
            throw new ProviderNotFoundException("Provider [{$provider}] not found or not configured");
        }

        return $this->providers[$provider];
    }

    /**
     * Send notification using default provider.
     *
     * @param string $message
     * @param array $options
     * @return mixed
     */
    public function send(string $message, array $options = []): mixed
    {
        return $this->provider()->send($message, $options);
    }

    /**
     * Send notification to multiple providers.
     *
     * @param array $providers
     * @param string $message
     * @param array $options
     * @return array
     */
    public function sendToMultiple(array $providers, string $message, array $options = []): array
    {
        $results = [];

        foreach ($providers as $provider) {
            try {
                $results[$provider] = $this->provider($provider)->send($message, $options);
            } catch (\Exception $e) {
                $results[$provider] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Register a custom provider.
     *
     * @param string $name
     * @param NotificationProvider $provider
     * @return void
     */
    public function extend(string $name, NotificationProvider $provider): void
    {
        $this->providers[$name] = $provider;
    }

    /**
     * Get all registered providers.
     *
     * @return array
     */
    public function getProviders(): array
    {
        return array_keys($this->providers);
    }

    /**
     * Magic method to call provider methods.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call(string $method, array $parameters)
    {
        return $this->provider()->$method(...$parameters);
    }
}
