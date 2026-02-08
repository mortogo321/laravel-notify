<?php

declare(strict_types=1);

namespace Mortogo321\LaravelNotify\Tests\Providers;

use Mortogo321\LaravelNotify\Exceptions\NotificationException;
use Mortogo321\LaravelNotify\Providers\SlackProvider;
use Mortogo321\LaravelNotify\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class SlackProviderTest extends TestCase
{
    private SlackProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->provider = new SlackProvider([
            'enabled' => true,
            'webhook_url' => 'https://hooks.slack.com/services/T12345/B12345/abcdefghijklmnop',
            'username' => 'Test Bot',
            'icon_emoji' => ':robot:',
            'channel' => '#test-channel',
            'timeout' => 30,
            'verify_ssl' => true,
        ]);
    }

    #[Test]
    public function it_can_be_instantiated(): void
    {
        $this->assertInstanceOf(SlackProvider::class, $this->provider);
        $this->assertEquals('slack', $this->provider->getName());
    }

    #[Test]
    public function it_throws_exception_when_webhook_url_is_missing(): void
    {
        $this->expectException(NotificationException::class);
        $this->expectExceptionMessage('Missing required configuration key: webhook_url for slack provider');

        new SlackProvider(['enabled' => true]);
    }

    #[Test]
    public function it_returns_disabled_response_when_disabled(): void
    {
        $provider = new SlackProvider([
            'enabled' => false,
            'webhook_url' => 'https://hooks.slack.com/services/test',
        ]);

        $result = $provider->send('Test message');

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('disabled', $result['message']);
    }

    #[Test]
    public function it_can_get_channel(): void
    {
        $this->assertEquals('#test-channel', $this->provider->getChannel());
    }

    #[Test]
    public function it_can_get_masked_webhook_url(): void
    {
        $maskedUrl = $this->provider->getWebhookUrl();

        $this->assertStringContainsString('/T****/B****/****', $maskedUrl);
        $this->assertStringNotContainsString('T12345', $maskedUrl);
    }

    #[Test]
    public function it_can_get_username(): void
    {
        $this->assertEquals('Test Bot', $this->provider->getUsername());
    }

    #[Test]
    public function it_can_get_icon_emoji(): void
    {
        $this->assertEquals(':robot:', $this->provider->getIconEmoji());
    }

    #[Test]
    public function it_returns_default_values_when_not_configured(): void
    {
        $provider = new SlackProvider([
            'webhook_url' => 'https://hooks.slack.com/services/test',
        ]);

        $this->assertEquals('Laravel Notify', $provider->getUsername());
        $this->assertEquals(':bell:', $provider->getIconEmoji());
        $this->assertNull($provider->getChannel());
    }

    #[Test]
    public function it_can_check_enabled_status(): void
    {
        $this->assertTrue($this->provider->isEnabled());

        $disabledProvider = new SlackProvider([
            'enabled' => false,
            'webhook_url' => 'https://hooks.slack.com/services/test',
        ]);

        $this->assertFalse($disabledProvider->isEnabled());
    }

    #[Test]
    public function it_can_get_all_config(): void
    {
        $config = $this->provider->getAllConfig();

        $this->assertIsArray($config);
        $this->assertArrayHasKey('webhook_url', $config);
        $this->assertArrayHasKey('username', $config);
        $this->assertArrayHasKey('channel', $config);
    }
}
