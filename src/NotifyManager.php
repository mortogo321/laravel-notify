<?php

declare(strict_types=1);

namespace Mortogo321\LaravelNotify;

use Mortogo321\LaravelNotify\Contracts\NotificationProvider;
use Mortogo321\LaravelNotify\Exceptions\ProviderNotFoundException;
use Mortogo321\LaravelNotify\Providers\DiscordProvider;
use Mortogo321\LaravelNotify\Providers\EmailProvider;
use Mortogo321\LaravelNotify\Providers\SlackProvider;
use Mortogo321\LaravelNotify\Providers\TelegramProvider;

class NotifyManager
{
    /**
     * Registered providers.
     *
     * @var array<string, NotificationProvider>
     */
    protected array $providers = [];

    /**
     * Configuration array.
     *
     * @var array<string, mixed>
     */
    protected array $config;

    /**
     * Default provider name.
     */
    protected ?string $defaultProvider = null;

    /**
     * Provider class mapping.
     *
     * @var array<string, class-string<NotificationProvider>>
     */
    protected array $providerClasses = [
        'slack' => SlackProvider::class,
        'discord' => DiscordProvider::class,
        'telegram' => TelegramProvider::class,
        'email' => EmailProvider::class,
    ];

    /**
     * Create a new NotifyManager instance.
     *
     * @param  array<string, mixed>  $config
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->defaultProvider = $config['default'] ?? null;
        $this->registerProviders();
    }

    /**
     * Register all available providers.
     */
    protected function registerProviders(): void
    {
        foreach ($this->providerClasses as $name => $class) {
            if (isset($this->config['providers'][$name])) {
                $this->providers[$name] = new $class($this->config['providers'][$name]);
            }
        }
    }

    /**
     * Get a specific provider instance.
     *
     * @throws ProviderNotFoundException
     */
    public function provider(?string $provider = null): NotificationProvider
    {
        $provider = $provider ?? $this->defaultProvider;

        if (! $provider) {
            throw ProviderNotFoundException::noDefault();
        }

        if (! isset($this->providers[$provider])) {
            throw ProviderNotFoundException::make($provider);
        }

        return $this->providers[$provider];
    }

    /**
     * Send notification using default provider.
     *
     * @param  array<string, mixed>  $options
     * @return array{success: bool, message?: string, status_code?: int, response?: mixed}
     *
     * @throws ProviderNotFoundException
     */
    public function send(string $message, array $options = []): array
    {
        return $this->provider()->send($message, $options);
    }

    /**
     * Send notification to multiple providers.
     *
     * @param  array<int, string>  $providers
     * @param  array<string, mixed>  $options
     * @return array<string, array{success: bool, message?: string, status_code?: int, response?: mixed, error?: string}>
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
     */
    public function extend(string $name, NotificationProvider $provider): void
    {
        $this->providers[$name] = $provider;
    }

    /**
     * Get all registered provider names.
     *
     * @return array<int, string>
     */
    public function getProviders(): array
    {
        return array_keys($this->providers);
    }

    /**
     * Check if a provider is registered.
     */
    public function hasProvider(string $name): bool
    {
        return isset($this->providers[$name]);
    }

    /**
     * Get the default provider name.
     */
    public function getDefaultProvider(): ?string
    {
        return $this->defaultProvider;
    }

    /**
     * Set the default provider.
     *
     * @throws ProviderNotFoundException
     */
    public function setDefaultProvider(string $provider): void
    {
        if (! isset($this->providers[$provider])) {
            throw ProviderNotFoundException::make($provider);
        }

        $this->defaultProvider = $provider;
    }

    /**
     * Get configuration value.
     */
    public function getConfig(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->config;
        }

        return $this->config[$key] ?? $default;
    }

    /**
     * Magic method to call provider methods.
     *
     * @param  array<int, mixed>  $parameters
     *
     * @throws ProviderNotFoundException
     */
    public function __call(string $method, array $parameters): mixed
    {
        return $this->provider()->$method(...$parameters);
    }
}
