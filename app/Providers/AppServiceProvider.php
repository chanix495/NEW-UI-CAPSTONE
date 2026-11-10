<?php

namespace App\Providers;

use App\Models\OwnerSetting;
use Illuminate\Support\Facades\View;
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
        // The signed-in user's saved appearance (theme, dark mode, font size) for every page
        View::composer('components.app-layout', function ($view) {
            $view->with('appearance', auth()->check() ? OwnerSetting::forUser(auth()->id())->appearance() : null);
        });

        // The signed-in user's language / region / notification preferences
        View::composer(['partials.navbar', 'pages.pos', 'pages.dashboard'], function ($view) {
            if (auth()->check()) {
                $view->with('prefs', OwnerSetting::forUser(auth()->id()));
            }
        });
    }
}
