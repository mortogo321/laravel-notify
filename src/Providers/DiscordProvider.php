<?php

namespace Mortogo321\LaravelNotify\Providers;

use Mortogo321\LaravelNotify\Exceptions\NotificationException;

class DiscordProvider extends AbstractProvider
{
    protected string $name = 'discord';

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->validateConfig(['webhook_url']);
    }

    /**
     * Send notification to Discord.
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
            $payload = [
                'content' => $message,
                'username' => $options['username'] ?? $this->getConfig('username', 'Laravel Notify'),
                'avatar_url' => $options['avatar_url'] ?? $this->getConfig('avatar_url'),
            ];

            // Add embeds if provided
            if (isset($options['embeds'])) {
                $payload['embeds'] = $options['embeds'];
            }

            // Add TTS if specified
            if (isset($options['tts'])) {
                $payload['tts'] = $options['tts'];
            }

            $response = $this->client->post($this->getConfig('webhook_url'), [
                'json' => array_filter($payload),
            ]);

            return [
                'success' => true,
                'status_code' => $response->getStatusCode(),
                'response' => (string) $response->getBody(),
            ];
        } catch (\Exception $e) {
            throw new NotificationException("Failed to send Discord notification: {$e->getMessage()}");
        }
    }
}
