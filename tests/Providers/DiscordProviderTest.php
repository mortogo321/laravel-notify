<?php

declare(strict_types=1);

namespace Mortogo321\LaravelNotify\Tests\Providers;

use Mortogo321\LaravelNotify\Exceptions\NotificationException;
use Mortogo321\LaravelNotify\Providers\DiscordProvider;
use Mortogo321\LaravelNotify\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class DiscordProviderTest extends TestCase
{
    private DiscordProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->provider = new DiscordProvider([
            'enabled' => true,
            'webhook_url' => 'https://discord.com/api/webhooks/123456789012345678/abcdefghijklmnopqrstuvwxyz',
            'username' => 'Test Bot',
            'avatar_url' => 'https://example.com/avatar.png',
            'timeout' => 30,
            'verify_ssl' => true,
        ]);
    }

    #[Test]
    public function it_can_be_instantiated(): void
    {
        $this->assertInstanceOf(DiscordProvider::class, $this->provider);
        $this->assertEquals('discord', $this->provider->getName());
    }

    #[Test]
    public function it_throws_exception_when_webhook_url_is_missing(): void
    {
        $this->expectException(NotificationException::class);
        $this->expectExceptionMessage('Missing required configuration key: webhook_url for discord provider');

        new DiscordProvider(['enabled' => true]);
    }

    #[Test]
    public function it_returns_disabled_response_when_disabled(): void
    {
        $provider = new DiscordProvider([
            'enabled' => false,
            'webhook_url' => 'https://discord.com/api/webhooks/123/test',
        ]);

        $result = $provider->send('Test message');

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('disabled', $result['message']);
    }

    #[Test]
    public function it_can_get_masked_webhook_url(): void
    {
        $maskedUrl = $this->provider->getWebhookUrl();

        $this->assertStringContainsString('/webhooks/123456789012345678/****', $maskedUrl);
        $this->assertStringNotContainsString('abcdefghijklmnopqrstuvwxyz', $maskedUrl);
    }

    #[Test]
    public function it_can_get_username(): void
    {
        $this->assertEquals('Test Bot', $this->provider->getUsername());
    }

    #[Test]
    public function it_can_get_avatar_url(): void
    {
        $this->assertEquals('https://example.com/avatar.png', $this->provider->getAvatarUrl());
    }

    #[Test]
    public function it_can_get_webhook_id(): void
    {
        $this->assertEquals('123456789012345678', $this->provider->getWebhookId());
    }

    #[Test]
    public function it_returns_null_webhook_id_for_invalid_url(): void
    {
        $provider = new DiscordProvider([
            'webhook_url' => 'https://example.com/invalid',
        ]);

        $this->assertNull($provider->getWebhookId());
    }

    #[Test]
    public function it_returns_default_values_when_not_configured(): void
    {
        $provider = new DiscordProvider([
            'webhook_url' => 'https://discord.com/api/webhooks/123/test',
        ]);

        $this->assertEquals('Laravel Notify', $provider->getUsername());
        $this->assertNull($provider->getAvatarUrl());
    }

    #[Test]
    public function it_can_check_enabled_status(): void
    {
        $this->assertTrue($this->provider->isEnabled());

        $disabledProvider = new DiscordProvider([
            'enabled' => false,
            'webhook_url' => 'https://discord.com/api/webhooks/123/test',
        ]);

        $this->assertFalse($disabledProvider->isEnabled());
    }
}
