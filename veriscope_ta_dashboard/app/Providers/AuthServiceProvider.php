<?php

namespace App\Providers;

use Laravel\Passport\Passport;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\User' => 'App\Policies\UserPolicy',
        'App\Constant' => 'App\Policies\ConstantPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // Passport::routes();

        Gate::define('grant-users', function ($user) {
            return $user->inGroup('admin');
        });

        Gate::define('viewWebSocketsDashboard', function ($user) {
            return true;
        });

        if (! $this->app->routesAreCached()) {
            Passport::routes();
        }
    }
}
