<?php

declare(strict_types=1);

namespace Mortogo321\LaravelNotify\Tests\Providers;

use Mortogo321\LaravelNotify\Exceptions\NotificationException;
use Mortogo321\LaravelNotify\Providers\EmailProvider;
use Mortogo321\LaravelNotify\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class EmailProviderTest extends TestCase
{
    private EmailProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->provider = new EmailProvider([
            'enabled' => true,
            'to' => 'test@example.com',
            'from' => 'noreply@example.com',
            'from_name' => 'Test App',
            'subject' => 'Test Notification',
            'cc' => 'cc@example.com',
            'bcc' => 'bcc@example.com',
        ]);
    }

    #[Test]
    public function it_can_be_instantiated(): void
    {
        $this->assertInstanceOf(EmailProvider::class, $this->provider);
        $this->assertEquals('email', $this->provider->getName());
    }

    #[Test]
    public function it_throws_exception_when_to_is_missing(): void
    {
        $this->expectException(NotificationException::class);
        $this->expectExceptionMessage('Missing required configuration key: to for email provider');

        new EmailProvider(['enabled' => true]);
    }

    #[Test]
    public function it_returns_disabled_response_when_disabled(): void
    {
        $provider = new EmailProvider([
            'enabled' => false,
            'to' => 'test@example.com',
        ]);

        $result = $provider->send('Test message');

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('disabled', $result['message']);
    }

    #[Test]
    public function it_can_get_to_address(): void
    {
        $this->assertEquals('test@example.com', $this->provider->getTo());
    }

    #[Test]
    public function it_can_get_to_address_from_array(): void
    {
        $provider = new EmailProvider([
            'to' => ['test1@example.com', 'test2@example.com'],
        ]);

        $this->assertEquals('test1@example.com, test2@example.com', $provider->getTo());
    }

    #[Test]
    public function it_can_get_from_address(): void
    {
        $this->assertEquals('noreply@example.com', $this->provider->getFrom());
    }

    #[Test]
    public function it_can_get_from_name(): void
    {
        $this->assertEquals('Test App', $this->provider->getFromName());
    }

    #[Test]
    public function it_can_get_subject(): void
    {
        $this->assertEquals('Test Notification', $this->provider->getSubject());
    }

    #[Test]
    public function it_can_get_cc(): void
    {
        $this->assertEquals('cc@example.com', $this->provider->getCc());
    }

    #[Test]
    public function it_can_get_bcc(): void
    {
        $this->assertEquals('bcc@example.com', $this->provider->getBcc());
    }

    #[Test]
    public function it_returns_default_values_when_not_configured(): void
    {
        $provider = new EmailProvider([
            'to' => 'test@example.com',
        ]);

        $this->assertEquals('Laravel Notify', $provider->getFromName());
        $this->assertEquals('Laravel Notification', $provider->getSubject());
        $this->assertNull($provider->getFrom());
        $this->assertNull($provider->getCc());
        $this->assertNull($provider->getBcc());
    }

    #[Test]
    public function it_can_validate_email_address(): void
    {
        $this->assertTrue($this->provider->validateEmail('valid@example.com'));
        $this->assertTrue($this->provider->validateEmail('test.user+tag@example.co.uk'));
        $this->assertFalse($this->provider->validateEmail('invalid'));
        $this->assertFalse($this->provider->validateEmail('invalid@'));
        $this->assertFalse($this->provider->validateEmail('@example.com'));
    }

    #[Test]
    public function it_can_validate_multiple_email_addresses(): void
    {
        $result = $this->provider->validateEmails([
            'valid@example.com',
            'invalid',
            'another@test.com',
            'bad@',
        ]);

        $this->assertCount(2, $result['valid']);
        $this->assertCount(2, $result['invalid']);
        $this->assertContains('valid@example.com', $result['valid']);
        $this->assertContains('another@test.com', $result['valid']);
        $this->assertContains('invalid', $result['invalid']);
        $this->assertContains('bad@', $result['invalid']);
    }

    #[Test]
    public function it_can_check_enabled_status(): void
    {
        $this->assertTrue($this->provider->isEnabled());

        $disabledProvider = new EmailProvider([
            'enabled' => false,
            'to' => 'test@example.com',
        ]);

        $this->assertFalse($disabledProvider->isEnabled());
    }
}
