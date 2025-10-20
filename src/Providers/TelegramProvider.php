<?php

namespace Mortogo321\LaravelNotify\Providers;

use Mortogo321\LaravelNotify\Exceptions\NotificationException;

class TelegramProvider extends AbstractProvider
{
    protected string $name = 'telegram';

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->validateConfig(['bot_token', 'chat_id']);
    }

    /**
     * Send notification to Telegram.
     *
     * @param string $message
     * @param array $options
     * @return mixed
     * @throws NotificationException
     */
    public function send(string $message, array $options = []): mixed
    {
        if (!$this->isEnabled()) {
            return ['success' => false, 'message' => 'Provider is disabled'];
        }

        try {
            $botToken = $this->getConfig('bot_token');
            $chatId = $options['chat_id'] ?? $this->getConfig('chat_id');

            $url = "https://api.telegram.org/bot{$botToken}/sendMessage";

            $payload = [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => $options['parse_mode'] ?? $this->getConfig('parse_mode', 'HTML'),
                'disable_web_page_preview' => $options['disable_preview'] ?? $this->getConfig('disable_preview', false),
                'disable_notification' => $options['disable_notification'] ?? $this->getConfig('disable_notification', false),
            ];

            // Add reply markup if provided
            if (isset($options['reply_markup'])) {
                $payload['reply_markup'] = json_encode($options['reply_markup']);
            }

            $response = $this->client->post($url, [
                'json' => array_filter($payload),
            ]);

            $result = json_decode((string) $response->getBody(), true);

            return [
                'success' => $result['ok'] ?? false,
                'status_code' => $response->getStatusCode(),
                'response' => $result,
            ];
        } catch (\Exception $e) {
            throw new NotificationException("Failed to send Telegram notification: {$e->getMessage()}");
        }
    }
}
