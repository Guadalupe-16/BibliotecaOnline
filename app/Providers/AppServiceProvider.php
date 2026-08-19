<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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
            if ($event->user) {
                ActivityLog::registrar('logout', "El usuario {$event->user->name} cerró sesión.");
            }
        });

        $this->registrarLimitesDePeticiones();
    }

    /**
     * Limites de intentos para los formularios de autenticacion (issue #124).
     * Evitan fuerza bruta de contrasenas, de PIN y el alta masiva de cuentas.
     */
    protected function registrarLimitesDePeticiones(): void
    {
        RateLimiter::for('login', function (Request $request) {
            $clave = Str::lower((string) $request->input('email')) . '|' . $request->ip();

            return Limit::perMinute(5)
                ->by($clave)
                ->response($this->respuestaDemasiadosIntentos(
                    'Demasiados intentos de inicio de sesión.'
                ));
        });

        RateLimiter::for('registro', function (Request $request) {
            return Limit::perMinute(5)
                ->by((string) $request->ip())
                ->response($this->respuestaDemasiadosIntentos(
                    'Demasiados registros desde esta conexión.'
                ));
        });

        RateLimiter::for('verificacion-pin', function (Request $request) {
            return Limit::perMinute(5)
                ->by($request->route('id') . '|' . $request->ip())
                ->response($this->respuestaDemasiadosIntentos(
                    'Demasiados intentos con el PIN.'
                ));
        });

        RateLimiter::for('reenvio-pin', function (Request $request) {
            return Limit::perMinute(2)
                ->by($request->route('id') . '|' . $request->ip())
                ->response($this->respuestaDemasiadosIntentos(
                    'Ya solicitaste el PIN hace poco.'
                ));
        });
    }

    /**
     * Devuelve al formulario con un mensaje en español y los segundos de espera,
     * en vez de la pantalla generica "429 Too Many Requests".
     */
    protected function respuestaDemasiadosIntentos(string $mensaje): callable
    {
        return function (Request $request, array $headers) use ($mensaje) {
            $segundos = $headers['Retry-After'] ?? 60;

            return back()
                ->withInput($request->except(['password', 'password_confirmation']))
                ->with('error', "{$mensaje} Espera {$segundos} segundos e inténtalo de nuevo.");
        };
    }
}
