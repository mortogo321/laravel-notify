<?php

namespace Mortogo321\LaravelNotify\Contracts;

interface NotificationProvider
{
    /**
     * Send a notification message.
     *
     * @param string $message
     * @param array $options
     * @return mixed
     */
    public function send(string $message, array $options = []): mixed;

    /**
     * Get the provider name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Check if the provider is enabled.
     *
     * @return bool
     */
    public function isEnabled(): bool;
}
