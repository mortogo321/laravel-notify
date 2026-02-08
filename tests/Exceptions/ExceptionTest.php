<?php

declare(strict_types=1);

namespace Mortogo321\LaravelNotify\Tests\Exceptions;

use Exception;
use Mortogo321\LaravelNotify\Exceptions\NotificationException;
use Mortogo321\LaravelNotify\Exceptions\ProviderNotFoundException;
use Mortogo321\LaravelNotify\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ExceptionTest extends TestCase
{
    #[Test]
    public function notification_exception_can_be_created(): void
    {
        $exception = new NotificationException('Test error');

        $this->assertEquals('Test error', $exception->getMessage());
    }

    #[Test]
    public function notification_exception_can_set_provider(): void
    {
        $exception = new NotificationException('Test error');
        $exception->setProvider('slack');

        $this->assertEquals('slack', $exception->getProvider());
    }

    #[Test]
    public function notification_exception_can_set_context(): void
    {
        $exception = new NotificationException('Test error');
        $exception->setContext(['key' => 'value']);

        $this->assertEquals(['key' => 'value'], $exception->getContext());
    }

    #[Test]
    public function notification_exception_send_failed_factory(): void
    {
        $previous = new Exception('Original error');
        $exception = NotificationException::sendFailed('slack', 'Connection timeout', $previous);

        $this->assertEquals('Failed to send slack notification: Connection timeout', $exception->getMessage());
        $this->assertEquals('slack', $exception->getProvider());
        $this->assertSame($previous, $exception->getPrevious());
    }

    #[Test]
    public function notification_exception_missing_config_factory(): void
    {
        $exception = NotificationException::missingConfig('telegram', 'bot_token');

        $this->assertEquals('Missing required configuration key: bot_token for telegram provider', $exception->getMessage());
        $this->assertEquals('telegram', $exception->getProvider());
    }

    #[Test]
    public function provider_not_found_exception_make_factory(): void
    {
        $exception = ProviderNotFoundException::make('custom');

        $this->assertEquals('Provider [custom] not found or not configured', $exception->getMessage());
        $this->assertEquals('custom', $exception->getProvider());
    }

    #[Test]
    public function provider_not_found_exception_no_default_factory(): void
    {
        $exception = ProviderNotFoundException::noDefault();

        $this->assertEquals('No provider specified and no default provider set', $exception->getMessage());
        $this->assertNull($exception->getProvider());
    }
}
