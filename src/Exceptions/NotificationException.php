<?php

declare(strict_types=1);

namespace Mortogo321\LaravelNotify\Exceptions;

use Exception;
use Throwable;

class NotificationException extends Exception
{
    /**
     * The provider that caused the exception.
     */
    protected ?string $provider = null;

    /**
     * Additional context for the exception.
     *
     * @var array<string, mixed>
     */
    protected array $context = [];

    /**
     * Create a new notification exception.
     *
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Set the provider that caused the exception.
     *
     * @param string $provider
     * @return static
     */
    public function setProvider(string $provider): static
    {
        $this->provider = $provider;

        return $this;
    }

    /**
     * Get the provider that caused the exception.
     *
     * @return string|null
     */
    public function getProvider(): ?string
    {
        return $this->provider;
    }

    /**
     * Set additional context for the exception.
     *
     * @param array<string, mixed> $context
     * @return static
     */
    public function setContext(array $context): static
    {
        $this->context = $context;

        return $this;
    }

    /**
     * Get the additional context.
     *
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Create a new exception for a failed send operation.
     *
     * @param string $provider
     * @param string $reason
     * @param Throwable|null $previous
     * @return static
     */
    public static function sendFailed(string $provider, string $reason, ?Throwable $previous = null): static
    {
        $exception = new static(
            "Failed to send {$provider} notification: {$reason}",
            0,
            $previous
        );

        return $exception->setProvider($provider);
    }

    /**
     * Create a new exception for missing configuration.
     *
     * @param string $provider
     * @param string $key
     * @return static
     */
    public static function missingConfig(string $provider, string $key): static
    {
        $exception = new static(
            "Missing required configuration key: {$key} for {$provider} provider"
        );

        return $exception->setProvider($provider);
    }
}
