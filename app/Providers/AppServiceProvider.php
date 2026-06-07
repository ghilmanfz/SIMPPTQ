<?php

namespace App\Providers;

use App\Support\Branding;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Otorisasi terpusat: sebuah "ability" (mis. @can('santri_manage'))
        // dianggap diizinkan bila role user memiliki permission tersebut.
        Gate::before(function ($user, string $ability) {
            if ($user === null) {
                return null;
            }

            return $user->hasPermissionTo($ability) ? true : null;
        });

        // Bagikan konfigurasi branding/landing ke seluruh view (layout, landing, login).
        View::composer('*', function ($view): void {
            $view->with('branding', Branding::data());
        });
    }
}
