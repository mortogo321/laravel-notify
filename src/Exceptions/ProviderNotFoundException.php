<?php

declare(strict_types=1);

namespace Mortogo321\LaravelNotify\Exceptions;

use Exception;

class ProviderNotFoundException extends Exception
{
    /**
     * The provider that was not found.
     */
    protected ?string $provider = null;

    /**
     * Create a new exception for a provider not found.
     */
    public static function make(string $provider): static
    {
        $exception = new static("Provider [{$provider}] not found or not configured");
        $exception->provider = $provider;

        return $exception;
    }

    /**
     * Create a new exception for no default provider.
     */
    public static function noDefault(): static
    {
        return new static('No provider specified and no default provider set');
    }

    /**
     * Get the provider that was not found.
     */
    public function getProvider(): ?string
    {
        return $this->provider;
    }
}
