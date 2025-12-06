<?php

declare(strict_types=1);

namespace Mortogo321\LaravelNotify\Providers;

use GuzzleHttp\Exception\GuzzleException;
use Mortogo321\LaravelNotify\Exceptions\NotificationException;

class SlackProvider extends AbstractProvider
{
    protected string $name = 'slack';

    /**
     * Create a new Slack provider instance.
     *
     * @param array<string, mixed> $config
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->validateConfig(['webhook_url']);
    }

    /**
     * Send notification to Slack.
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
     * Build the Slack message payload.
     *
     * @param string $message
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    protected function buildPayload(string $message, array $options): array
    {
        $payload = [
            'text' => $message,
            'username' => $options['username'] ?? $this->getConfig('username', 'Laravel Notify'),
            'icon_emoji' => $options['icon_emoji'] ?? $this->getConfig('icon_emoji', ':bell:'),
            'channel' => $options['channel'] ?? $this->getConfig('channel'),
        ];

        if (isset($options['attachments'])) {
            $payload['attachments'] = $options['attachments'];
        }

        if (isset($options['blocks'])) {
            $payload['blocks'] = $options['blocks'];
        }

        return $payload;
    }

    /**
     * Get the configured channel.
     *
     * @return string|null
     */
    public function getChannel(): ?string
    {
        return $this->getConfig('channel');
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

        return preg_replace('/\/T[A-Z0-9]+\/B[A-Z0-9]+\/[a-zA-Z0-9]+$/', '/T****/B****/****', $url);
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
     * Get the configured icon emoji.
     *
     * @return string
     */
    public function getIconEmoji(): string
    {
        return $this->getConfig('icon_emoji', ':bell:');
    }

    /**
     * List available channels using Slack API.
     * Requires a bot token with channels:read scope.
     *
     * @param string $botToken
     * @param array<string, mixed> $options
     * @return array{success: bool, channels?: array<int, array{id: string, name: string}>, error?: string}
     * @throws NotificationException
     */
    public function listChannels(string $botToken, array $options = []): array
    {
        try {
            $response = $this->client->get('https://slack.com/api/conversations.list', [
                'headers' => [
                    'Authorization' => "Bearer {$botToken}",
                ],
                'query' => array_filter([
                    'types' => $options['types'] ?? 'public_channel,private_channel',
                    'limit' => $options['limit'] ?? 100,
                    'cursor' => $options['cursor'] ?? null,
                ]),
            ]);

            $result = json_decode((string) $response->getBody(), true);

            if (! ($result['ok'] ?? false)) {
                return [
                    'success' => false,
                    'error' => $result['error'] ?? 'Unknown error',
                ];
            }

            $channels = array_map(fn($channel) => [
                'id' => $channel['id'],
                'name' => $channel['name'],
                'is_private' => $channel['is_private'] ?? false,
                'num_members' => $channel['num_members'] ?? 0,
            ], $result['channels'] ?? []);

            return [
                'success' => true,
                'channels' => $channels,
                'next_cursor' => $result['response_metadata']['next_cursor'] ?? null,
            ];
        } catch (GuzzleException $e) {
            throw NotificationException::sendFailed($this->name, $e->getMessage(), $e);
        }
    }

    /**
     * Get channel info by ID using Slack API.
     * Requires a bot token with channels:read scope.
     *
     * @param string $botToken
     * @param string $channelId
     * @return array{success: bool, channel?: array<string, mixed>, error?: string}
     * @throws NotificationException
     */
    public function getChannelInfo(string $botToken, string $channelId): array
    {
        try {
            $response = $this->client->get('https://slack.com/api/conversations.info', [
                'headers' => [
                    'Authorization' => "Bearer {$botToken}",
                ],
                'query' => [
                    'channel' => $channelId,
                ],
            ]);

            $result = json_decode((string) $response->getBody(), true);

            if (! ($result['ok'] ?? false)) {
                return [
                    'success' => false,
                    'error' => $result['error'] ?? 'Unknown error',
                ];
            }

            return [
                'success' => true,
                'channel' => $result['channel'],
            ];
        } catch (GuzzleException $e) {
            throw NotificationException::sendFailed($this->name, $e->getMessage(), $e);
        }
    }
}
