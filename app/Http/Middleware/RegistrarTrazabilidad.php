<?php

namespace App\Http\Middleware;

use App\Jobs\LogRequestTraceJob;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Captura datos tecnicos de cada peticion (spec 005: specs/005-trazabilidad/).
 * No debe impedir que la respuesta llegue al usuario: toda la persistencia
 * ocurre en terminate(), despues de enviar la respuesta, via cola (igual
 * patron que App\Jobs\LogActivityJob).
 */
class RegistrarTrazabilidad
{
    public function handle(Request $request, Closure $next): Response
    {
        $traceId = (string) Str::uuid();

        $request->attributes->set('trace_id', $traceId);
        $request->attributes->set('trace_inicio', microtime(true));

        $response = $next($request);

        $response->headers->set('X-Trace-Id', $traceId);

        return $response;
    }

    public function terminate(Request $request, Response $response): void
    {
        $traceId = $request->attributes->get('trace_id');
        $inicio  = $request->attributes->get('trace_inicio');

        if (! $traceId || ! $inicio) {
            return;
        }

        $statusHttp = $response->getStatusCode();

        $excepcion = app()->bound('trazabilidad.excepcion') ? app('trazabilidad.excepcion') : null;
        $huboExcepcion = $excepcion instanceof Throwable;

        $resultado = ($statusHttp >= 500 || $huboExcepcion) ? 'error' : 'ok';

        $errorReferencia = null;
        if ($resultado === 'error' && $huboExcepcion) {
            $errorReferencia = Str::limit(
                get_class($excepcion).': '.$excepcion->getMessage(),
                500,
                ''
            );
        }

        // La trazabilidad es "best effort": un fallo al despachar el job
        // (p. ej. la tabla request_traces no existe todavía, o la cola no
        // está disponible) no debe impedir que la respuesta ya entregada al
        // usuario se vea afectada.
        try {
            LogRequestTraceJob::dispatch(
                traceId: $traceId,
                metodo: $request->method(),
                ruta: $request->route()?->getName() ?? $request->path(),
                userId: $request->user()?->id,
                ip: $request->ip(),
                statusHttp: $statusHttp,
                duracionMs: (int) round((microtime(true) - $inicio) * 1000),
                resultado: $resultado,
                errorReferencia: $errorReferencia,
                userAgent: Str::limit((string) $request->userAgent(), 255, ''),
                ocurridoEn: Carbon::now()->toDateTimeString(),
            );
        } catch (Throwable $error) {
            Log::warning('No se pudo registrar la traza de la solicitud.', [
                'trace_id' => $traceId,
                'error'    => $error->getMessage(),
            ]);
        }
    }
}
