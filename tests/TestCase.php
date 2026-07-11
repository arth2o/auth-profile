<?php

namespace Arth2o\AuthProfile\Tests;

use Arth2o\AuthProfile\AuthProfileServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    use RefreshDatabase;

    protected function getPackageProviders($app): array
    {
        return [
            AuthProfileServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Use SQLite in-memory for testing
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Set the user model to the test user model
        $app['config']->set('auth-profile.user_model', \Arth2o\AuthProfile\Tests\Models\User::class);
    }

    protected function defineDatabaseMigrations(): void
    {
        // Create the base users table first
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
    }
}
