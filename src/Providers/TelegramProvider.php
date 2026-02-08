<?php

declare(strict_types=1);

namespace Mortogo321\LaravelNotify\Providers;

use Mortogo321\LaravelNotify\Exceptions\NotificationException;

class TelegramProvider extends AbstractProvider
{
    protected string $name = 'telegram';

    protected const API_BASE_URL = 'https://api.telegram.org/bot';

    /**
     * Create a new Telegram provider instance.
     *
     * @param  array<string, mixed>  $config
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->validateConfig(['bot_token', 'chat_id']);
    }

    /**
     * Send notification to Telegram.
     *
     * @param  array<string, mixed>  $options
     * @return array{success: bool, message?: string, status_code?: int, response?: mixed}
     *
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
     * @param  array<string, mixed>  $options
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
     */
    protected function buildApiUrl(string $method): string
    {
        return self::API_BASE_URL . $this->getConfig('bot_token') . '/' . $method;
    }

    /**
     * Get the configured chat ID.
     */
    public function getChatId(): ?string
    {
        return $this->getConfig('chat_id');
    }

    /**
     * Get the bot token (masked for security).
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
     */
    public function getParseMode(): string
    {
        return $this->getConfig('parse_mode', 'HTML');
    }

    /**
     * Get bot information from Telegram API.
     *
     * @return array{success: bool, bot?: array<string, mixed>, error?: string}
     *
     * @throws NotificationException
     */
    public function getMe(): array
    {
        $result = $this->get($this->buildApiUrl('getMe'));

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
    }

    /**
     * Get chat information by chat ID.
     *
     * @return array{success: bool, chat?: array<string, mixed>, error?: string}
     *
     * @throws NotificationException
     */
    public function getChat(string|int|null $chatId = null): array
    {
        $chatId = $chatId ?? $this->getConfig('chat_id');

        $result = $this->get($this->buildApiUrl('getChat'), ['chat_id' => $chatId]);

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
    }

    /**
     * Get updates to find chat IDs from recent messages.
     *
     * @param  array<string, mixed>  $options
     * @return array{success: bool, updates?: array<int, array<string, mixed>>, chats?: array<int, array<string, mixed>>, error?: string}
     *
     * @throws NotificationException
     */
    public function getUpdates(array $options = []): array
    {
        $result = $this->get($this->buildApiUrl('getUpdates'), array_filter([
            'offset' => $options['offset'] ?? null,
            'limit' => $options['limit'] ?? 100,
            'timeout' => $options['timeout'] ?? 0,
        ]));

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
    }

    /**
     * Get chat administrators.
     *
     * @return array{success: bool, administrators?: array<int, array<string, mixed>>, error?: string}
     *
     * @throws NotificationException
     */
    public function getChatAdministrators(string|int|null $chatId = null): array
    {
        $chatId = $chatId ?? $this->getConfig('chat_id');

        $result = $this->get($this->buildApiUrl('getChatAdministrators'), ['chat_id' => $chatId]);

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
    }

    /**
     * Get chat member count.
     *
     * @return array{success: bool, count?: int, error?: string}
     *
     * @throws NotificationException
     */
    public function getChatMemberCount(string|int|null $chatId = null): array
    {
        $chatId = $chatId ?? $this->getConfig('chat_id');

        $result = $this->get($this->buildApiUrl('getChatMemberCount'), ['chat_id' => $chatId]);

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
    }

    /**
     * Set webhook URL for receiving updates.
     *
     * @param  array<string, mixed>  $options
     * @return array{success: bool, error?: string}
     *
     * @throws NotificationException
     */
    public function setWebhook(string $url, array $options = []): array
    {
        $result = $this->post($this->buildApiUrl('setWebhook'), array_filter([
            'url' => $url,
            'max_connections' => $options['max_connections'] ?? null,
            'allowed_updates' => $options['allowed_updates'] ?? null,
            'secret_token' => $options['secret_token'] ?? null,
        ]));

        $response = $result['response'] ?? [];

        return [
            'success' => $response['ok'] ?? false,
            'error' => $response['description'] ?? null,
        ];
    }

    /**
     * Delete webhook.
     *
     * @return array{success: bool, error?: string}
     *
     * @throws NotificationException
     */
    public function deleteWebhook(): array
    {
        $result = $this->post($this->buildApiUrl('deleteWebhook'), []);

        $response = $result['response'] ?? [];

        return [
            'success' => $response['ok'] ?? false,
            'error' => $response['description'] ?? null,
        ];
    }

    /**
     * Get current webhook info.
     *
     * @return array{success: bool, webhook?: array<string, mixed>, error?: string}
     *
     * @throws NotificationException
     */
    public function getWebhookInfo(): array
    {
        $result = $this->get($this->buildApiUrl('getWebhookInfo'));

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
    }
}
