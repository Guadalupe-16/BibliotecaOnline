<?php

namespace Tests\Feature;

use App\Models\RequestTrace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Issue #166 / specs/005-trazabilidad/research.md §5: retención de
 * request_traces mediante el comando `trazas:podar`.
 */
class PodarTrazasCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function crearTraza(Carbon $creadoEn): RequestTrace
    {
        return RequestTrace::create([
            'trace_id'    => (string) Str::uuid(),
            'metodo'      => 'GET',
            'ruta'        => 'catalogo',
            'status_http' => 200,
            'duracion_ms' => 5,
            'resultado'   => 'ok',
            'created_at'  => $creadoEn,
        ]);
    }

    public function test_elimina_trazas_mas_antiguas_que_el_umbral_configurado(): void
    {
        $antigua  = $this->crearTraza(now()->subDays(40));
        $reciente = $this->crearTraza(now()->subDays(5));

        $this->artisan('trazas:podar', ['--dias' => 30])
            ->assertExitCode(0);

        $this->assertDatabaseMissing('request_traces', ['id' => $antigua->id]);
        $this->assertDatabaseHas('request_traces', ['id' => $reciente->id]);
    }

    public function test_usa_la_configuracion_por_defecto_si_no_se_pasa_la_opcion(): void
    {
        config(['trazabilidad.retencion_dias' => 10]);

        $antigua  = $this->crearTraza(now()->subDays(20));
        $reciente = $this->crearTraza(now()->subDays(2));

        $this->artisan('trazas:podar')->assertExitCode(0);

        $this->assertDatabaseMissing('request_traces', ['id' => $antigua->id]);
        $this->assertDatabaseHas('request_traces', ['id' => $reciente->id]);
    }
}
