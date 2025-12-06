<?php

declare(strict_types=1);

namespace Mortogo321\LaravelNotify\Providers;

use GuzzleHttp\Exception\GuzzleException;
use Mortogo321\LaravelNotify\Exceptions\NotificationException;

class TelegramProvider extends AbstractProvider
{
    protected string $name = 'telegram';

    protected const API_BASE_URL = 'https://api.telegram.org/bot';

    /**
     * Create a new Telegram provider instance.
     *
     * @param array<string, mixed> $config
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->validateConfig(['bot_token', 'chat_id']);
    }

    /**
     * Send notification to Telegram.
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
        $url = $this->buildApiUrl('sendMessage');

        return $this->post($url, $payload);
    }

    /**
     * Build the Telegram message payload.
     *
     * @param string $message
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    protected function buildPayload(string $message, array $options): array
    {
        $payload = [
            'chat_id' => $options['chat_id'] ?? $this->getConfig('chat_id'),
            'text' => $message,
            'parse_mode' => $options['parse_mode'] ?? $this->getConfig('parse_mode', 'HTML'),
            'disable_web_page_preview' => $options['disable_preview'] ?? $this->getConfig('disable_preview', false),
            'disable_notification' => $options['disable_notification'] ?? $this->getConfig('disable_notification', false),
        ];

        if (isset($options['reply_markup'])) {
            $payload['reply_markup'] = json_encode($options['reply_markup']);
        }

        return $payload;
    }

    /**
     * Build Telegram API URL.
     *
     * @param string $method
     * @return string
     */
    protected function buildApiUrl(string $method): string
    {
        return self::API_BASE_URL . $this->getConfig('bot_token') . '/' . $method;
    }

    /**
     * Get the configured chat ID.
     *
     * @return string|null
     */
    public function getChatId(): ?string
    {
        return $this->getConfig('chat_id');
    }

    /**
     * Get the bot token (masked for security).
     *
     * @return string|null
     */
    public function getBotToken(): ?string
    {
        $token = $this->getConfig('bot_token');

        if (! $token) {
            return null;
        }

        return preg_replace('/^(\d+):(.{5}).*(.{5})$/', '$1:$2****$3', $token);
    }

    /**
     * Get the configured parse mode.
     *
     * @return string
     */
    public function getParseMode(): string
    {
        return $this->getConfig('parse_mode', 'HTML');
    }

    /**
     * Get bot information from Telegram API.
     *
     * @return array{success: bool, bot?: array<string, mixed>, error?: string}
     * @throws NotificationException
     */
    public function getMe(): array
    {
        try {
            $response = $this->client->get($this->buildApiUrl('getMe'));

            $result = json_decode((string) $response->getBody(), true);

            if (! ($result['ok'] ?? false)) {
                return [
                    'success' => false,
                    'error' => $result['description'] ?? 'Unknown error',
                ];
            }

            return [
                'success' => true,
                'bot' => $result['result'],
            ];
        } catch (GuzzleException $e) {
            throw NotificationException::sendFailed($this->name, $e->getMessage(), $e);
        }
    }

    /**
     * Get chat information by chat ID.
     *
     * @param string|int|null $chatId
     * @return array{success: bool, chat?: array<string, mixed>, error?: string}
     * @throws NotificationException
     */
    public function getChat(string|int|null $chatId = null): array
    {
        $chatId = $chatId ?? $this->getConfig('chat_id');

        try {
            $response = $this->client->get($this->buildApiUrl('getChat'), [
                'query' => ['chat_id' => $chatId],
            ]);

            $result = json_decode((string) $response->getBody(), true);

            if (! ($result['ok'] ?? false)) {
                return [
                    'success' => false,
                    'error' => $result['description'] ?? 'Unknown error',
                ];
            }

            return [
                'success' => true,
                'chat' => $result['result'],
            ];
        } catch (GuzzleException $e) {
            throw NotificationException::sendFailed($this->name, $e->getMessage(), $e);
        }
    }

    /**
     * Get updates to find chat IDs from recent messages.
     *
     * @param array<string, mixed> $options
     * @return array{success: bool, updates?: array<int, array<string, mixed>>, chats?: array<int, array<string, mixed>>, error?: string}
     * @throws NotificationException
     */
    public function getUpdates(array $options = []): array
    {
        try {
            $response = $this->client->get($this->buildApiUrl('getUpdates'), [
                'query' => array_filter([
                    'offset' => $options['offset'] ?? null,
                    'limit' => $options['limit'] ?? 100,
                    'timeout' => $options['timeout'] ?? 0,
                ]),
            ]);

            $result = json_decode((string) $response->getBody(), true);

            if (! ($result['ok'] ?? false)) {
                return [
                    'success' => false,
                    'error' => $result['description'] ?? 'Unknown error',
                ];
            }

            $updates = $result['result'] ?? [];
            $chats = [];

            foreach ($updates as $update) {
                $message = $update['message'] ?? $update['channel_post'] ?? null;

                if ($message && isset($message['chat'])) {
                    $chat = $message['chat'];
                    $chatId = $chat['id'];

                    if (! isset($chats[$chatId])) {
                        $chats[$chatId] = [
                            'id' => $chatId,
                            'type' => $chat['type'],
                            'title' => $chat['title'] ?? null,
                            'username' => $chat['username'] ?? null,
                            'first_name' => $chat['first_name'] ?? null,
                        ];
                    }
                }
            }

            return [
                'success' => true,
                'updates' => $updates,
                'chats' => array_values($chats),
            ];
        } catch (GuzzleException $e) {
            throw NotificationException::sendFailed($this->name, $e->getMessage(), $e);
        }
    }

    /**
     * Get chat administrators.
     *
     * @param string|int|null $chatId
     * @return array{success: bool, administrators?: array<int, array<string, mixed>>, error?: string}
     * @throws NotificationException
     */
    public function getChatAdministrators(string|int|null $chatId = null): array
    {
        $chatId = $chatId ?? $this->getConfig('chat_id');

        try {
            $response = $this->client->get($this->buildApiUrl('getChatAdministrators'), [
                'query' => ['chat_id' => $chatId],
            ]);

            $result = json_decode((string) $response->getBody(), true);

            if (! ($result['ok'] ?? false)) {
                return [
                    'success' => false,
                    'error' => $result['description'] ?? 'Unknown error',
                ];
            }

            return [
                'success' => true,
                'administrators' => $result['result'],
            ];
        } catch (GuzzleException $e) {
            throw NotificationException::sendFailed($this->name, $e->getMessage(), $e);
        }
    }

    /**
     * Get chat member count.
     *
     * @param string|int|null $chatId
     * @return array{success: bool, count?: int, error?: string}
     * @throws NotificationException
     */
    public function getChatMemberCount(string|int|null $chatId = null): array
    {
        $chatId = $chatId ?? $this->getConfig('chat_id');

        try {
            $response = $this->client->get($this->buildApiUrl('getChatMemberCount'), [
                'query' => ['chat_id' => $chatId],
            ]);

            $result = json_decode((string) $response->getBody(), true);

            if (! ($result['ok'] ?? false)) {
                return [
                    'success' => false,
                    'error' => $result['description'] ?? 'Unknown error',
                ];
            }

            return [
                'success' => true,
                'count' => $result['result'],
            ];
        } catch (GuzzleException $e) {
            throw NotificationException::sendFailed($this->name, $e->getMessage(), $e);
        }
    }

    /**
     * Set webhook URL for receiving updates.
     *
     * @param string $url
     * @param array<string, mixed> $options
     * @return array{success: bool, error?: string}
     * @throws NotificationException
     */
    public function setWebhook(string $url, array $options = []): array
    {
        try {
            $response = $this->client->post($this->buildApiUrl('setWebhook'), [
                'json' => array_filter([
                    'url' => $url,
                    'max_connections' => $options['max_connections'] ?? null,
                    'allowed_updates' => $options['allowed_updates'] ?? null,
                    'secret_token' => $options['secret_token'] ?? null,
                ]),
            ]);

            $result = json_decode((string) $response->getBody(), true);

            return [
                'success' => $result['ok'] ?? false,
                'error' => $result['description'] ?? null,
            ];
        } catch (GuzzleException $e) {
            throw NotificationException::sendFailed($this->name, $e->getMessage(), $e);
        }
    }

    /**
     * Delete webhook.
     *
     * @return array{success: bool, error?: string}
     * @throws NotificationException
     */
    public function deleteWebhook(): array
    {
        try {
            $response = $this->client->post($this->buildApiUrl('deleteWebhook'));

            $result = json_decode((string) $response->getBody(), true);

            return [
                'success' => $result['ok'] ?? false,
                'error' => $result['description'] ?? null,
            ];
        } catch (GuzzleException $e) {
            throw NotificationException::sendFailed($this->name, $e->getMessage(), $e);
        }
    }

    /**
     * Get current webhook info.
     *
     * @return array{success: bool, webhook?: array<string, mixed>, error?: string}
     * @throws NotificationException
     */
    public function getWebhookInfo(): array
    {
        try {
            $response = $this->client->get($this->buildApiUrl('getWebhookInfo'));

            $result = json_decode((string) $response->getBody(), true);

            if (! ($result['ok'] ?? false)) {
                return [
                    'success' => false,
                    'error' => $result['description'] ?? 'Unknown error',
                ];
            }

            return [
                'success' => true,
                'webhook' => $result['result'],
            ];
        } catch (GuzzleException $e) {
            throw NotificationException::sendFailed($this->name, $e->getMessage(), $e);
        }
    }
}
