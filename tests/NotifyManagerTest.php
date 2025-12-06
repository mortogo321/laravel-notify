<?php

declare(strict_types=1);

namespace Mortogo321\LaravelNotify\Tests;

use Mortogo321\LaravelNotify\Exceptions\ProviderNotFoundException;
use Mortogo321\LaravelNotify\Facades\Notify;
use Mortogo321\LaravelNotify\NotifyManager;
use Mortogo321\LaravelNotify\Providers\DiscordProvider;
use Mortogo321\LaravelNotify\Providers\EmailProvider;
use Mortogo321\LaravelNotify\Providers\SlackProvider;
use Mortogo321\LaravelNotify\Providers\TelegramProvider;

class NotifyManagerTest extends TestCase
{
    /** @test */
    public function it_can_get_available_providers(): void
    {
        $providers = Notify::getProviders();

        $this->assertIsArray($providers);
        $this->assertContains('slack', $providers);
        $this->assertContains('discord', $providers);
        $this->assertContains('telegram', $providers);
        $this->assertContains('email', $providers);
    }

    /** @test */
    public function it_throws_exception_when_provider_not_found(): void
    {
        $this->expectException(ProviderNotFoundException::class);
        $this->expectExceptionMessage('Provider [non_existent_provider] not found or not configured');

        Notify::provider('non_existent_provider');
    }

    /** @test */
    public function it_throws_exception_when_no_default_provider_set(): void
    {
        config(['notify.default' => null]);

        $manager = new NotifyManager(config('notify'));

        $this->expectException(ProviderNotFoundException::class);
        $this->expectExceptionMessage('No provider specified and no default provider set');

        $manager->send('Test message');
    }

    /** @test */
    public function it_can_get_provider_instance(): void
    {
        $slack = Notify::provider('slack');
        $discord = Notify::provider('discord');
        $telegram = Notify::provider('telegram');
        $email = Notify::provider('email');

        $this->assertInstanceOf(SlackProvider::class, $slack);
        $this->assertInstanceOf(DiscordProvider::class, $discord);
        $this->assertInstanceOf(TelegramProvider::class, $telegram);
        $this->assertInstanceOf(EmailProvider::class, $email);
    }

    /** @test */
    public function it_can_get_default_provider(): void
    {
        $provider = Notify::provider();

        $this->assertInstanceOf(SlackProvider::class, $provider);
        $this->assertEquals('slack', $provider->getName());
    }

    /** @test */
    public function it_can_check_if_provider_exists(): void
    {
        $this->assertTrue(Notify::hasProvider('slack'));
        $this->assertTrue(Notify::hasProvider('discord'));
        $this->assertFalse(Notify::hasProvider('non_existent'));
    }

    /** @test */
    public function it_can_get_and_set_default_provider(): void
    {
        $this->assertEquals('slack', Notify::getDefaultProvider());

        Notify::setDefaultProvider('discord');
        $this->assertEquals('discord', Notify::getDefaultProvider());

        $provider = Notify::provider();
        $this->assertInstanceOf(DiscordProvider::class, $provider);
    }

    /** @test */
    public function it_throws_exception_when_setting_invalid_default_provider(): void
    {
        $this->expectException(ProviderNotFoundException::class);

        Notify::setDefaultProvider('non_existent');
    }

    /** @test */
    public function it_can_extend_with_custom_provider(): void
    {
        $customProvider = new class(['enabled' => true, 'webhook_url' => 'https://test.com']) extends SlackProvider
        {
            protected string $name = 'custom';
        };

        Notify::extend('custom', $customProvider);

        $this->assertTrue(Notify::hasProvider('custom'));
        $this->assertEquals('custom', Notify::provider('custom')->getName());
    }

    /** @test */
    public function it_can_get_config(): void
    {
        $config = Notify::getConfig();

        $this->assertIsArray($config);
        $this->assertArrayHasKey('default', $config);
        $this->assertArrayHasKey('providers', $config);

        $default = Notify::getConfig('default');
        $this->assertEquals('slack', $default);

        $nonExistent = Notify::getConfig('non_existent', 'fallback');
        $this->assertEquals('fallback', $nonExistent);
    }

    /** @test */
    public function providers_report_correct_enabled_status(): void
    {
        $slack = Notify::provider('slack');
        $this->assertTrue($slack->isEnabled());

        config(['notify.providers.slack.enabled' => false]);
        $manager = new NotifyManager(config('notify'));
        $slack = $manager->provider('slack');
        $this->assertFalse($slack->isEnabled());
    }

    /** @test */
    public function providers_report_correct_name(): void
    {
        $this->assertEquals('slack', Notify::provider('slack')->getName());
        $this->assertEquals('discord', Notify::provider('discord')->getName());
        $this->assertEquals('telegram', Notify::provider('telegram')->getName());
        $this->assertEquals('email', Notify::provider('email')->getName());
    }
}
