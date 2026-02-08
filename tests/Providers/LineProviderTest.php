<?php

declare(strict_types=1);

namespace Mortogo321\LaravelNotify\Tests\Providers;

use Mortogo321\LaravelNotify\Exceptions\NotificationException;
use Mortogo321\LaravelNotify\Providers\LineProvider;
use Mortogo321\LaravelNotify\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class LineProviderTest extends TestCase
{
    private LineProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->provider = new LineProvider([
            'enabled' => true,
            'access_token' => 'test-line-access-token-1234567890',
            'timeout' => 30,
            'verify_ssl' => true,
        ]);
    }

    #[Test]
    public function it_can_be_instantiated(): void
    {
        $this->assertInstanceOf(LineProvider::class, $this->provider);
        $this->assertEquals('line', $this->provider->getName());
    }

    #[Test]
    public function it_throws_exception_when_access_token_is_missing(): void
    {
        $this->expectException(NotificationException::class);
        $this->expectExceptionMessage('Missing required configuration key: access_token for line provider');

        new LineProvider(['enabled' => true]);
    }

    #[Test]
    public function it_returns_disabled_response_when_disabled(): void
    {
        $provider = new LineProvider([
            'enabled' => false,
            'access_token' => 'test-token',
        ]);

        $result = $provider->send('Test message');

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('disabled', $result['message']);
    }

    #[Test]
    public function it_can_get_masked_access_token(): void
    {
        $maskedToken = $this->provider->getAccessToken();

        $this->assertStringContainsString('****', $maskedToken);
        $this->assertStringContainsString('test-', $maskedToken);
        $this->assertStringContainsString('67890', $maskedToken);
        $this->assertStringNotContainsString('test-line-access-token-1234567890', $maskedToken);
    }

    #[Test]
    public function it_returns_null_for_missing_access_token(): void
    {
        $provider = new class(['access_token' => 'x']) extends LineProvider
        {
            public function __construct(array $config = [])
            {
                // Skip validation for this test
                $this->config = [];
                $this->timeout = 30;
                $this->verifySsl = true;
            }
        };

        $this->assertNull($provider->getAccessToken());
    }

    #[Test]
    public function it_can_check_enabled_status(): void
    {
        $this->assertTrue($this->provider->isEnabled());

        $disabledProvider = new LineProvider([
            'enabled' => false,
            'access_token' => 'test-token',
        ]);

        $this->assertFalse($disabledProvider->isEnabled());
    }

    #[Test]
    public function it_can_get_all_config(): void
    {
        $config = $this->provider->getAllConfig();

        $this->assertIsArray($config);
        $this->assertArrayHasKey('access_token', $config);
        $this->assertArrayHasKey('enabled', $config);
        $this->assertArrayHasKey('timeout', $config);
    }

    #[Test]
    public function it_masks_short_tokens(): void
    {
        $provider = new LineProvider([
            'access_token' => 'short',
            'enabled' => true,
        ]);

        $maskedToken = $provider->getAccessToken();

        $this->assertEquals('*****', $maskedToken);
    }
}
