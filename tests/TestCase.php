<?php

namespace JeffersonGoncalves\Socialite\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use JeffersonGoncalves\Socialite\SocialiteServiceProvider;
use Laravel\Socialite\SocialiteServiceProvider as LaravelSocialiteServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
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
        config()->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        config()->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        config()->set('services.github', [
            'client_id' => 'client-id',
            'client_secret' => 'client-secret',
            'redirect' => null,
        ]);
    }

    protected function defineDatabaseMigrations(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamps();
        });

        (include __DIR__.'/../database/migrations/create_social_accounts_table.php.stub')->up();
    }
}
