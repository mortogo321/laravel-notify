<?php

declare(strict_types=1);

namespace Mortogo321\LaravelNotify\Providers;

use Illuminate\Support\Facades\Http;
use Mortogo321\LaravelNotify\Exceptions\NotificationException;

class LineProvider extends AbstractProvider
{
    protected string $name = 'line';

    protected const NOTIFY_API_URL = 'https://notify-api.line.me/api/notify';

    protected const STATUS_API_URL = 'https://notify-api.line.me/api/status';

    /**
     * Create a new LINE provider instance.
     *
     * @param  array<string, mixed>  $config
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->validateConfig(['access_token']);
    }

    /**
     * Send notification to LINE Notify.
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

        try {
            $response = Http::timeout($this->timeout)
                ->withOptions(['verify' => $this->verifySsl])
                ->withToken($this->getConfig('access_token'))
                ->asForm()
                ->post(self::NOTIFY_API_URL, array_filter($payload, fn ($value) => $value !== null));

            return [
                'success' => true,
                'status_code' => $response->status(),
                'response' => $response->json() ?? $response->body(),
            ];
        } catch (\Exception $e) {
            throw NotificationException::sendFailed($this->name, $e->getMessage(), $e);
        }
    }

    /**
     * Build the LINE Notify message payload.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    protected function buildPayload(string $message, array $options): array
    {
        $payload = [
            'message' => $message,
        ];

        if (isset($options['stickerPackageId']) && isset($options['stickerId'])) {
            $payload['stickerPackageId'] = $options['stickerPackageId'];
            $payload['stickerId'] = $options['stickerId'];
        }

        if (isset($options['imageThumbnail']) && isset($options['imageFullsize'])) {
            $payload['imageThumbnail'] = $options['imageThumbnail'];
            $payload['imageFullsize'] = $options['imageFullsize'];
        }

        return $payload;
    }

    /**
     * Get the access token (masked for security).
     */
    public function getAccessToken(): ?string
    {
        $token = $this->getConfig('access_token');

        if (! $token) {
            return null;
        }

        $length = strlen($token);

        if ($length <= 10) {
            return str_repeat('*', $length);
        }

        return substr($token, 0, 5) . '****' . substr($token, -5);
    }

    /**
     * Get LINE Notify API status.
     *
     * @return array{success: bool, status?: int, target_type?: string, target?: string, error?: string}
     *
     * @throws NotificationException
     */
    public function getStatus(): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withOptions(['verify' => $this->verifySsl])
                ->withToken($this->getConfig('access_token'))
                ->get(self::STATUS_API_URL);

            $result = $response->json() ?? [];

            if ($response->status() !== 200) {
                return [
                    'success' => false,
                    'error' => $result['message'] ?? 'Unknown error',
                ];
            }

            return [
                'success' => true,
                'status' => $result['status'] ?? null,
                'target_type' => $result['targetType'] ?? null,
                'target' => $result['target'] ?? null,
            ];
        } catch (\Exception $e) {
            throw NotificationException::sendFailed($this->name, $e->getMessage(), $e);
        }
    }
}
