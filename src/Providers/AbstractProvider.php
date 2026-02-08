<?php

declare(strict_types=1);

namespace Mortogo321\LaravelNotify\Providers;

use Illuminate\Support\Facades\Http;
use Mortogo321\LaravelNotify\Contracts\NotificationProvider;
use Mortogo321\LaravelNotify\Exceptions\NotificationException;

abstract class AbstractProvider implements NotificationProvider
{
    /**
     * @var array<string, mixed>
     */
    protected array $config;

    protected string $name;

    protected int $timeout;

    protected bool $verifySsl;

    /**
     * Create a new provider instance.
     *
     * @param  array<string, mixed>  $config
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->timeout = $config['timeout'] ?? 30;
        $this->verifySsl = $config['verify_ssl'] ?? true;
    }

    /**
     * Get the provider name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Check if the provider is enabled.
     */
    public function isEnabled(): bool
    {
        return (bool) ($this->config['enabled'] ?? true);
    }

    /**
     * Get configuration value.
     */
    protected function getConfig(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * Get all configuration values.
     *
     * @return array<string, mixed>
     */
    public function getAllConfig(): array
    {
        return $this->config;
    }

    /**
     * Validate required configuration keys.
     *
     * @param  array<int, string>  $keys
     *
     * @throws NotificationException
     */
    protected function validateConfig(array $keys): void
    {
        foreach ($keys as $key) {
            if (empty($this->config[$key])) {
                throw NotificationException::missingConfig($this->name, $key);
            }
        }
    }

    /**
     * Build a disabled response.
     *
     * @return array{success: bool, message: string}
     */
    protected function disabledResponse(): array
    {
        return [
            'success' => false,
            'message' => "Provider [{$this->name}] is disabled",
        ];
    }

    /**
     * Make an HTTP POST request.
     *
     * @param  array<string, mixed>  $payload
     * @return array{success: bool, status_code: int, response: mixed}
     *
     * @throws NotificationException
     */
    protected function post(string $url, array $payload): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withOptions(['verify' => $this->verifySsl])
                ->asJson()
                ->post($url, array_filter($payload, fn ($value) => $value !== null));

            return [
                'success' => true,
                'status_code' => $response->status(),
                'response' => $response->json() ?? $response->body(),
            ];
        } catch (\Exception $e) {
            throw NotificationException::sendFailed($this->name, $e->getMessage(), $e);
        }
    }

    /**
     * Make an HTTP GET request.
     *
     * @param  array<string, mixed>  $query
     * @param  array<string, string>  $headers
     * @return array<string, mixed>
     *
     * @throws NotificationException
     */
    protected function get(string $url, array $query = [], array $headers = []): array
    {
        try {
            $request = Http::timeout($this->timeout)
                ->withOptions(['verify' => $this->verifySsl]);

            if ($headers) {
                $request = $request->withHeaders($headers);
            }

            $response = $request->get($url, $query);

            return $response->json() ?? [];
        } catch (\Exception $e) {
            throw NotificationException::sendFailed($this->name, $e->getMessage(), $e);
        }
    }
}
