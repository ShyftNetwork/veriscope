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

        Gate::define('grant-users', function ($user) {
            return $user->inGroup('admin');
        });

        Gate::define('viewWebSocketsDashboard', function ($user) {
            return true;
        });

        if (! $this->app->routesAreCached()) {

            Passport::tokensCan([
                'set_attestation' => 'Set Attestation',
                'create_shyft_user' => 'Create Shyft User',
                'get_jurisdictions' => 'Get Jurisdiction',
                'get_verified_trust_anchors' => 'Get Verified Trust Anchors',
                'get_trust_anchor_details' => 'Get Trust Anchor Details',
                'verify_trust_anchor' => 'Verify Trust Anchor'
            ]);

            Passport::setDefaultScope([
                'set_attestation',
                'create_shyft_user',
                'get_jurisdictions',
                'get_verified_trust_anchors',
                'get_trust_anchor_details',
                'verify_trust_anchor'
            ]);
        }
    }
}
