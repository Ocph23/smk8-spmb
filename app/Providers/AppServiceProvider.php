<?php

namespace App\Providers;

use App\Models\Inbox;
use App\Observers\InboxObserver;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        Inertia::share([
            'routeName' => function () {
                return app('router')->currentRouteName();
            },
        ]);

        Inbox::observe(InboxObserver::class);
    }
}
