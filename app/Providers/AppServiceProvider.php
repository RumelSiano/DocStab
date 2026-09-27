<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
        Model::preventLazyLoading(! $this->app->isProduction());
        Gate::define('manage-users', fn (User $user): bool => $user->is_active && $user->role === 'admin');
        Gate::define('manage-catalogs', fn (User $user): bool => $user->is_active && $user->role === 'admin');
        Gate::define('view-feedback', fn (User $user): bool => $user->is_active && $user->role === 'admin');
        Gate::define('view-history', fn (User $user): bool => $user->is_active && $user->isStaff());
        Gate::define('view-users', fn (User $user): bool => $user->is_active && $user->isStaff());
    }
}
