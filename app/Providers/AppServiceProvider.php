<?php

namespace App\Providers;

use App\Auth\FirestoreUserProvider;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Driver "firestore" dipakai di config/auth.php -> providers.users.driver
        Auth::provider('firestore', function ($app, array $config) {
            return new FirestoreUserProvider($app->make(UserRepository::class));
        });
    }
}
