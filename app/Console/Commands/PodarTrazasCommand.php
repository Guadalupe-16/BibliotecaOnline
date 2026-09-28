<?php

namespace App\Console\Commands;

use App\Models\RequestTrace;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Retencion de request_traces (specs/005-trazabilidad/data-model.md,
 * specs/005-trazabilidad/research.md §5): evita que la tabla crezca sin
 * limite, a diferencia de activity_logs, que solo crece con acciones de
 * negocio.
 */
class PodarTrazasCommand extends Command
{
    protected $signature = 'trazas:podar {--dias= : Dias de retencion (por defecto config(trazabilidad.retencion_dias))}';

    protected $description = 'Elimina trazas de request_traces más antiguas que el umbral de retención configurado';

    public function handle(): int
    {
        $dias = (int) ($this->option('dias') ?? config('trazabilidad.retencion_dias'));

        if ($dias <= 0) {
            $this->error('El número de días de retención debe ser mayor que 0.');

            return self::FAILURE;
        }

        $limite = Carbon::now()->subDays($dias);

        $eliminadas = RequestTrace::where('created_at', '<', $limite)->delete();

        $this->info("Se eliminaron {$eliminadas} trazas anteriores a {$limite->toDateTimeString()} ({$dias} días de retención).");

        return self::SUCCESS;
    }
}
