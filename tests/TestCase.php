<?php

namespace JeffersonGoncalves\Socialite\Tests;

use JeffersonGoncalves\Socialite\SocialiteServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            SocialiteServiceProvider::class,
        ];
    }
}
