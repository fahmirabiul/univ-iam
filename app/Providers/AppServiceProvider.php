<?php

namespace App\Providers;

use App\Models\UserProfile;
use App\Observers\UserProfileObserver;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Disable strict POSIX key permissions check for Windows/Docker volume mounts
        Passport::$validateKeyPermissions = false;

        // Custom OAuth2 Authorization Consent View (Vuexy Theme)
        Passport::authorizationView('auth.oauth.authorize');

        // Register Model Observers
        UserProfile::observe(UserProfileObserver::class);
    }
}
