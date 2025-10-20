<?php

namespace Mortogo321\LaravelNotify\Tests;

use Mortogo321\LaravelNotify\Exceptions\ProviderNotFoundException;
use Mortogo321\LaravelNotify\Facades\Notify;

class NotifyManagerTest extends TestCase
{
    /** @test */
    public function it_can_get_available_providers(): void
    {
        $providers = Notify::getProviders();

        $this->assertIsArray($providers);
    }

    /** @test */
    public function it_throws_exception_when_provider_not_found(): void
    {
        $this->expectException(ProviderNotFoundException::class);

        Notify::provider('non_existent_provider');
    }

    /** @test */
    public function it_throws_exception_when_no_default_provider_set(): void
    {
        config(['notify.default' => null]);

        $this->expectException(ProviderNotFoundException::class);

        Notify::send('Test message');
    }
}
