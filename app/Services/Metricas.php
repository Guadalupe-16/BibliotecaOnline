<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Prometheus\CollectorRegistry;
use Prometheus\RenderTextFormat;
use Throwable;

/**
 * Registro de metricas de la aplicacion en formato Prometheus (Issue #165, spec 004).
 *
 * Labels permitidos: method, route (patron de la ruta, nunca la URL real), status y clase.
 * Nunca IP, user-agent, ID de usuario ni parametros (spec 004, FR-003).
 */
class Metricas
{
    public function __construct(private CollectorRegistry $registro)
    {
    }

    public function registrarPeticion(string $metodo, string $ruta, int $estado, float $segundos): void
    {
        $ns = config('metricas.namespace');

        $this->registro->getOrRegisterCounter(
            $ns, 'http_requests_total', 'Solicitudes HTTP atendidas', ['method', 'route', 'status']
        )->inc([$metodo, $ruta, (string) $estado]);

        $this->registro->getOrRegisterHistogram(
            $ns, 'http_request_duration_seconds', 'Duracion de las solicitudes HTTP en segundos',
            ['method', 'route'], config('metricas.buckets')
        )->observe($segundos, [$metodo, $ruta]);

        if ($estado >= 400) {
            $this->registro->getOrRegisterCounter(
                $ns, 'http_errors_total', 'Respuestas de error HTTP por clase (4xx, 5xx)', ['clase']
            )->inc([intdiv($estado, 100) . 'xx']);
        }
    }

    /** Metricas en formato de texto de Prometheus, incluidas las que se calculan al momento. */
    public function renderizar(): string
    {
        $ns = config('metricas.namespace');

        $this->registro->getOrRegisterGauge(
            $ns, 'app_info', 'Version de la liberacion desplegada', ['version', 'env']
        )->set(1, [(string) config('metricas.version'), (string) config('app.env')]);

        // Cola: se lee de la BD en cada scrape (valido para todos los procesos, incluido el worker)
        try {
            $pendientes = DB::table('jobs')->count();
            $fallidos   = DB::table('failed_jobs')->count();
            $bdArriba   = 1;
        } catch (Throwable) {
            $pendientes = $fallidos = 0;
            $bdArriba   = 0;
        }

        $this->registro->getOrRegisterGauge($ns, 'queue_jobs_pending', 'Trabajos en cola sin procesar')
            ->set($pendientes);
        $this->registro->getOrRegisterGauge($ns, 'queue_jobs_failed', 'Trabajos fallidos en failed_jobs')
            ->set($fallidos);
        $this->registro->getOrRegisterGauge($ns, 'database_up', '1 si la base de datos responde')
            ->set($bdArriba);

        return (new RenderTextFormat())->render($this->registro->getMetricFamilySamples());
    }
}
