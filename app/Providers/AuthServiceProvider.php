<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Superadmin bypasses all gates
        Gate::before(function ($user, $ability) {
            if ($user && $user->hasRole('superadmin')) {
                return true;
            }
        });

        // Dynamic gate resolution using our granular permission system
        Gate::after(function ($user, $ability) {
            if ($user) {
                return $user->hasPermission($ability);
            }
            return false;
        });
    }
}
