<?php

namespace Arth2o\AuthProfile;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class AuthProfileServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('auth-profile')
            ->hasConfigFile()
            ->hasMigration('create_api_token_and_expires_at_columns_table')
            ->hasRoute('api');
    }

    public function packageBooted(): void
    {
        $this->app['router']->aliasMiddleware(
            'custom-token-auth',
            \Arth2o\AuthProfile\Http\Middleware\CustomTokenAuth::class
        );
    }
}
