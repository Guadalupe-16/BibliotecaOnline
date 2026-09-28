<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        // /metrics fuera del grupo web: Prometheus no usa sesion ni cookies (Issue #165)
        then: function () {
            Route::get('/metrics', \App\Http\Controllers\MetricasController::class)->name('metricas');
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(\App\Http\Middleware\InstrumentarMetricas::class);

        $middleware->alias([
            'role' => \App\Http\Middleware\VerificarRol::class,
        ]);

        // Trazabilidad técnica de solicitudes (specs/005-trazabilidad/).
        // Se antepone (prepend) al resto del grupo 'web' para que el
        // trace_id y el cronómetro existan aunque un middleware posterior
        // (p. ej. SubstituteBindings, con un modelo inexistente) lance una
        // excepción antes de llegar al controlador: terminate() debe poder
        // registrar igualmente la traza con su status de error.
        $middleware->web(prepend: [
            \App\Http\Middleware\RegistrarTrazabilidad::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Deja disponible la excepción reportada para que
        // RegistrarTrazabilidad pueda registrar su referencia
        // (solo se reportan errores de servidor no controlados).
        $exceptions->reportable(function (Throwable $e) {
            app()->instance('trazabilidad.excepcion', $e);
        });
    })->create();