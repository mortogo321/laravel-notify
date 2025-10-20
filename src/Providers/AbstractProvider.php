<?php

namespace Mortogo321\LaravelNotify\Providers;

use GuzzleHttp\Client;
use Mortogo321\LaravelNotify\Contracts\NotificationProvider;
use Mortogo321\LaravelNotify\Exceptions\NotificationException;

abstract class AbstractProvider implements NotificationProvider
{
    protected Client $client;
    protected array $config;
    protected string $name;

    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->client = new Client([
            'timeout' => $config['timeout'] ?? 30,
            'verify' => $config['verify_ssl'] ?? true,
        ]);
    }

    /**
     * Get the provider name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Check if the provider is enabled.
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->config['enabled'] ?? true;
    }

    /**
     * Get configuration value.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function getConfig(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * Validate required configuration keys.
     *
     * @param array $keys
     * @return void
     * @throws NotificationException
     */
    protected function validateConfig(array $keys): void
    {
        foreach ($keys as $key) {
            if (empty($this->config[$key])) {
                throw new NotificationException(
                    "Missing required configuration key: {$key} for {$this->name} provider"
                );
            }
        }
    }
}
