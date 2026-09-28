<?php

namespace App\Jobs;

use App\Models\RequestTrace;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class LogRequestTraceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $traceId,
        public string $metodo,
        public string $ruta,
        public ?int $userId,
        public ?string $ip,
        public int $statusHttp,
        public int $duracionMs,
        public string $resultado,
        public ?string $errorReferencia,
        public ?string $userAgent,
        public string $ocurridoEn,
    ) {}

    public function handle(): void
    {
        RequestTrace::create([
            'trace_id'         => $this->traceId,
            'metodo'           => $this->metodo,
            'ruta'             => $this->ruta,
            'user_id'          => $this->userId,
            'ip'               => $this->ip,
            'status_http'      => $this->statusHttp,
            'duracion_ms'      => $this->duracionMs,
            'resultado'        => $this->resultado,
            'error_referencia' => $this->errorReferencia,
            'user_agent'       => $this->userAgent,
            'created_at'       => $this->ocurridoEn,
        ]);
    }
}
