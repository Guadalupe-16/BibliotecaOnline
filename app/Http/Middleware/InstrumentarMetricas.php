<?php

namespace App\Http\Middleware;

use App\Services\Metricas;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Mide cada solicitud para Prometheus (Issue #165). El registro ocurre en terminate(),
 * despues de enviar la respuesta, para no agregar latencia al usuario.
 */
class InstrumentarMetricas
{
    public function __construct(private Metricas $metricas)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $request->attributes->set('metricas_inicio', microtime(true));

        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        $ruta = $request->route()?->uri();

        if ($ruta !== null && in_array($ruta, config('metricas.excluir'), true)) {
            return;
        }

        $inicio = $request->attributes->get(
            'metricas_inicio',
            defined('LARAVEL_START') ? LARAVEL_START : microtime(true)
        );

        $this->metricas->registrarPeticion(
            $request->method(),
            // Patron de la ruta ("libros/{libro}"), no la URL real: evita alta cardinalidad y datos
            // personales en los labels. Una URL sin ruta registrada (404) se agrupa como "sin_ruta".
            $ruta === null ? 'sin_ruta' : '/' . ltrim($ruta, '/'),
            $response->getStatusCode(),
            microtime(true) - $inicio,
        );
    }
}
