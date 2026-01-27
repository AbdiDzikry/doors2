<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
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
        Paginator::useTailwind();

        if (app()->environment(['production', 'staging'])) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Register observers
        \App\Models\Meeting::observe(\App\Observers\MeetingObserver::class);
    }

}
