<?php

declare(strict_types=1);

namespace Mortogo321\LaravelNotify\Contracts;

/**
 * Interface for notification providers.
 *
 * @template TResponse of array{success: bool, message?: string, status_code?: int, response?: mixed}
 */
interface NotificationProvider
{
    /**
     * Send a notification message.
     *
     * @param  string  $message  The message to send
     * @param  array<string, mixed>  $options  Additional options for the provider
     * @return array{success: bool, message?: string, status_code?: int, response?: mixed}
     */
    public function send(string $message, array $options = []): array;

    /**
     * Get the provider name.
     */
    public function getName(): string;

    /**
     * Check if the provider is enabled.
     */
    public function isEnabled(): bool;
}
