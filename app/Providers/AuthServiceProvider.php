<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Admin Gate
        Gate::define('admin-only', function (User $user) {
            return $user->isAdmin();
        });

        // Staff and Admin Gate (can record/edit data and import)
        Gate::define('manage-statistics', function (User $user) {
            return $user->isAdmin() || $user->isStaff();
        });

        // Viewer Gate (view-only)
        Gate::define('export-reports', function (User $user) {
            return $user->isAdmin() || $user->isStaff() || $user->role === 'viewer';
        });
    }
}

