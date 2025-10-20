<?php

namespace Mortogo321\LaravelNotify\Providers;

use Mortogo321\LaravelNotify\Exceptions\NotificationException;

class SlackProvider extends AbstractProvider
{
    protected string $name = 'slack';

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->validateConfig(['webhook_url']);
    }

    /**
     * Send notification to Slack.
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
                'text' => $message,
                'username' => $options['username'] ?? $this->getConfig('username', 'Laravel Notify'),
                'icon_emoji' => $options['icon_emoji'] ?? $this->getConfig('icon_emoji', ':bell:'),
                'channel' => $options['channel'] ?? $this->getConfig('channel'),
            ];

            // Add attachments if provided
            if (isset($options['attachments'])) {
                $payload['attachments'] = $options['attachments'];
            }

            // Add blocks if provided (Slack Block Kit)
            if (isset($options['blocks'])) {
                $payload['blocks'] = $options['blocks'];
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
            throw new NotificationException("Failed to send Slack notification: {$e->getMessage()}");
        }
    }
}
