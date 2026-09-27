<?php

namespace JeffersonGoncalves\Socialite;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class SocialiteServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-socialite')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigrations();
    }
}
