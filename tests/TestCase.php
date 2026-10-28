<?php

namespace JeffersonGoncalves\Socialite\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use JeffersonGoncalves\PestPluginDatabaseMigrations\LoadsPackageMigrations;
use JeffersonGoncalves\Socialite\SocialiteServiceProvider;
use Laravel\Socialite\SocialiteServiceProvider as LaravelSocialiteServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    use LoadsPackageMigrations;
    use RefreshDatabase;

    protected function getPackageProviders($app): array
    {
        return [
            LaravelSocialiteServiceProvider::class,
            SocialiteServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
        config()->set('database.connections.testing', $this->databaseConnection());
        config()->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        config()->set('services.github', [
            'client_id' => 'client-id',
            'client_secret' => 'client-secret',
            'redirect' => null,
        ]);
    }

    /**
     * In-memory SQLite locally; CI sets SOCIALITE_TEST_DB_* to run against MySQL and PostgreSQL.
     * Not the plain DB_* names: Testbench sets DB_CONNECTION=testing itself.
     *
     * @return array<string, mixed>
     */
    protected function databaseConnection(): array
    {
        $driver = env('SOCIALITE_TEST_DB_DRIVER', 'sqlite');

        if ($driver === 'sqlite') {
            return ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''];
        }

        return [
            'driver' => $driver,
            'host' => env('SOCIALITE_TEST_DB_HOST', '127.0.0.1'),
            'port' => env('SOCIALITE_TEST_DB_PORT'),
            'database' => env('SOCIALITE_TEST_DB_DATABASE', 'testing'),
            'username' => env('SOCIALITE_TEST_DB_USERNAME', 'root'),
            'password' => env('SOCIALITE_TEST_DB_PASSWORD', ''),
            'charset' => $driver === 'pgsql' ? 'utf8' : 'utf8mb4',
            'prefix' => '',
        ];
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
        $this->loadPackageMigrations(SocialiteServiceProvider::class);
    }
}
