<?php

namespace JeffersonGoncalves\Socialite\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use JeffersonGoncalves\Socialite\SocialiteServiceProvider;
use Laravel\Socialite\SocialiteServiceProvider as LaravelSocialiteServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
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
        // The package ships a .php.stub; copy it next to the users migration so the migrator runs both in order.
        $path = sys_get_temp_dir().'/laravel-socialite-migrations';

        if (! is_dir($path)) {
            mkdir($path, 0755, true);
        }

        copy(__DIR__.'/database/migrations/0000_00_00_000000_create_users_table.php', $path.'/0000_00_00_000000_create_users_table.php');
        copy(__DIR__.'/../database/migrations/create_social_accounts_table.php.stub', $path.'/0000_00_00_000001_create_social_accounts_table.php');

        $this->loadMigrationsFrom($path);
    }
}
