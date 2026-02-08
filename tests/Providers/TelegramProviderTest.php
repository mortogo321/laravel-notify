<?php

declare(strict_types=1);

namespace Mortogo321\LaravelNotify\Tests\Providers;

use Mortogo321\LaravelNotify\Exceptions\NotificationException;
use Mortogo321\LaravelNotify\Providers\TelegramProvider;
use Mortogo321\LaravelNotify\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class TelegramProviderTest extends TestCase
{
    private TelegramProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->provider = new TelegramProvider([
            'enabled' => true,
            'bot_token' => '123456789:ABCDefGHIjklMNOpqrsTUVwxyz',
            'chat_id' => '-1001234567890',
            'parse_mode' => 'HTML',
            'disable_preview' => false,
            'disable_notification' => false,
            'timeout' => 30,
            'verify_ssl' => true,
        ]);
    }

    #[Test]
    public function it_can_be_instantiated(): void
    {
        $this->assertInstanceOf(TelegramProvider::class, $this->provider);
        $this->assertEquals('telegram', $this->provider->getName());
    }

    #[Test]
    public function it_throws_exception_when_bot_token_is_missing(): void
    {
        $this->expectException(NotificationException::class);
        $this->expectExceptionMessage('Missing required configuration key: bot_token for telegram provider');

        new TelegramProvider([
            'enabled' => true,
            'chat_id' => '-1001234567890',
        ]);
    }

    #[Test]
    public function it_throws_exception_when_chat_id_is_missing(): void
    {
        $this->expectException(NotificationException::class);
        $this->expectExceptionMessage('Missing required configuration key: chat_id for telegram provider');

        new TelegramProvider([
            'enabled' => true,
            'bot_token' => '123456789:ABCDefGHIjklMNOpqrsTUVwxyz',
        ]);
    }

    #[Test]
    public function it_returns_disabled_response_when_disabled(): void
    {
        $provider = new TelegramProvider([
            'enabled' => false,
            'bot_token' => '123456789:test',
            'chat_id' => '-123',
        ]);

        $result = $provider->send('Test message');

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('disabled', $result['message']);
    }

    #[Test]
    public function it_can_get_chat_id(): void
    {
        $this->assertEquals('-1001234567890', $this->provider->getChatId());
    }

    #[Test]
    public function it_can_get_masked_bot_token(): void
    {
        $maskedToken = $this->provider->getBotToken();

        $this->assertStringContainsString('123456789:', $maskedToken);
        $this->assertStringContainsString('****', $maskedToken);
        $this->assertStringNotContainsString('ABCDefGHIjklMNOpqrsTUVwxyz', $maskedToken);
    }

    #[Test]
    public function it_can_get_parse_mode(): void
    {
        $this->assertEquals('HTML', $this->provider->getParseMode());
    }

    #[Test]
    public function it_returns_default_values_when_not_configured(): void
    {
        $provider = new TelegramProvider([
            'bot_token' => '123456789:test',
            'chat_id' => '-123',
        ]);

        $this->assertEquals('HTML', $provider->getParseMode());
    }

    #[Test]
    public function it_can_check_enabled_status(): void
    {
        $this->assertTrue($this->provider->isEnabled());

        $disabledProvider = new TelegramProvider([
            'enabled' => false,
            'bot_token' => '123456789:test',
            'chat_id' => '-123',
        ]);

        $this->assertFalse($disabledProvider->isEnabled());
    }

    #[Test]
    public function it_can_get_all_config(): void
    {
        $config = $this->provider->getAllConfig();

        $this->assertIsArray($config);
        $this->assertArrayHasKey('bot_token', $config);
        $this->assertArrayHasKey('chat_id', $config);
        $this->assertArrayHasKey('parse_mode', $config);
    }
}
