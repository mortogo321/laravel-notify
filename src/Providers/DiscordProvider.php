<?php

declare(strict_types=1);

namespace Mortogo321\LaravelNotify\Providers;

use GuzzleHttp\Exception\GuzzleException;
use Mortogo321\LaravelNotify\Exceptions\NotificationException;

class DiscordProvider extends AbstractProvider
{
    protected string $name = 'discord';

    /**
     * Create a new Discord provider instance.
     *
     * @param array<string, mixed> $config
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->validateConfig(['webhook_url']);
    }

    /**
     * Send notification to Discord.
     *
     * @param string $message
     * @param array<string, mixed> $options
     * @return array{success: bool, message?: string, status_code?: int, response?: mixed}
     * @throws NotificationException
     */
    public function send(string $message, array $options = []): array
    {
        if (! $this->isEnabled()) {
            return $this->disabledResponse();
        }

        $payload = $this->buildPayload($message, $options);

        return $this->post($this->getConfig('webhook_url'), $payload);
    }

    /**
     * Build the Discord message payload.
     *
     * @param string $message
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    protected function buildPayload(string $message, array $options): array
    {
        $payload = [
            'content' => $message,
            'username' => $options['username'] ?? $this->getConfig('username', 'Laravel Notify'),
            'avatar_url' => $options['avatar_url'] ?? $this->getConfig('avatar_url'),
        ];

        if (isset($options['embeds'])) {
            $payload['embeds'] = $options['embeds'];
        }

        if (isset($options['tts'])) {
            $payload['tts'] = $options['tts'];
        }

        return $payload;
    }

    /**
     * Get the webhook URL (masked for security).
     *
     * @return string|null
     */
    public function getWebhookUrl(): ?string
    {
        $url = $this->getConfig('webhook_url');

        if (! $url) {
            return null;
        }

        return preg_replace('/\/webhooks\/(\d+)\/([a-zA-Z0-9_-]+)$/', '/webhooks/$1/****', $url);
    }

    /**
     * Get the configured username.
     *
     * @return string
     */
    public function getUsername(): string
    {
        return $this->getConfig('username', 'Laravel Notify');
    }

    /**
     * Get the configured avatar URL.
     *
     * @return string|null
     */
    public function getAvatarUrl(): ?string
    {
        return $this->getConfig('avatar_url');
    }

    /**
     * Extract webhook ID from the configured webhook URL.
     *
     * @return string|null
     */
    public function getWebhookId(): ?string
    {
        $url = $this->getConfig('webhook_url');

        if (! $url || ! preg_match('/\/webhooks\/(\d+)\//', $url, $matches)) {
            return null;
        }

        return $matches[1];
    }

    /**
     * Get webhook info from Discord API.
     *
     * @return array{success: bool, webhook?: array<string, mixed>, error?: string}
     * @throws NotificationException
     */
    public function getWebhookInfo(): array
    {
        try {
            $response = $this->client->get($this->getConfig('webhook_url'));

            $result = json_decode((string) $response->getBody(), true);

            return [
                'success' => true,
                'webhook' => [
                    'id' => $result['id'] ?? null,
                    'name' => $result['name'] ?? null,
                    'channel_id' => $result['channel_id'] ?? null,
                    'guild_id' => $result['guild_id'] ?? null,
                    'avatar' => $result['avatar'] ?? null,
                ],
            ];
        } catch (GuzzleException $e) {
            throw NotificationException::sendFailed($this->name, $e->getMessage(), $e);
        }
    }

    /**
     * Get the channel ID from webhook info.
     *
     * @return string|null
     * @throws NotificationException
     */
    public function getChannelId(): ?string
    {
        $info = $this->getWebhookInfo();

        return $info['webhook']['channel_id'] ?? null;
    }

    /**
     * Get the guild (server) ID from webhook info.
     *
     * @return string|null
     * @throws NotificationException
     */
    public function getGuildId(): ?string
    {
        $info = $this->getWebhookInfo();

        return $info['webhook']['guild_id'] ?? null;
    }

    /**
     * List guild channels using Discord Bot API.
     * Requires a bot token with proper permissions.
     *
     * @param string $botToken
     * @param string $guildId
     * @return array{success: bool, channels?: array<int, array<string, mixed>>, error?: string}
     * @throws NotificationException
     */
    public function listGuildChannels(string $botToken, string $guildId): array
    {
        try {
            $response = $this->client->get("https://discord.com/api/v10/guilds/{$guildId}/channels", [
                'headers' => [
                    'Authorization' => "Bot {$botToken}",
                ],
            ]);

            $result = json_decode((string) $response->getBody(), true);

            $channels = array_map(fn($channel) => [
                'id' => $channel['id'],
                'name' => $channel['name'],
                'type' => $channel['type'],
                'position' => $channel['position'] ?? 0,
            ], $result ?? []);

            return [
                'success' => true,
                'channels' => $channels,
            ];
        } catch (GuzzleException $e) {
            throw NotificationException::sendFailed($this->name, $e->getMessage(), $e);
        }
    }
}
