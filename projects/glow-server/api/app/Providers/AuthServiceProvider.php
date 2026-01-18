<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;

use App\Domain\Auth\Guards\AccessTokenAuthentication;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot(AccessTokenAuthentication $accessTokenAuthentication)
    {
        $this->registerPolicies();

        Auth::viaRequest('access_token', $accessTokenAuthentication);
    }
}
