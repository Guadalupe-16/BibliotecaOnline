<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use App\Models\ActivityLog;


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
        Schema::defaultStringLength(191);

        Event::listen(Login::class, function ($event) {
            ActivityLog::registrar('login', "El usuario {$event->user->name} inició sesión.");
        });

        Event::listen(Logout::class, function ($event) {
            ActivityLog::registrar('logout', "El usuario {$event->user->name} cerró sesión.");
        });
    }
}
