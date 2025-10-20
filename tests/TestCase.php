<?php

namespace Mortogo321\LaravelNotify\Tests;

use Mortogo321\LaravelNotify\NotifyServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            NotifyServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'Notify' => \Mortogo321\LaravelNotify\Facades\Notify::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }
}
