<?php

namespace Tests\Feature;

use App\Livewire\TrazabilidadViewer;
use App\Models\RequestTrace;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Issue #166 / specs/005-trazabilidad/spec.md FR-007: filtros principales
 * del visor (trace ID, método, status, ruta, usuario, fechas).
 */
class TrazabilidadViewerFiltrosTest extends TestCase
{
    use RefreshDatabase;

    protected function crearTraza(array $overrides = []): RequestTrace
    {
        return RequestTrace::create(array_merge([
            'trace_id'    => (string) \Illuminate\Support\Str::uuid(),
            'metodo'      => 'GET',
            'ruta'        => 'catalogo',
            'user_id'     => null,
            'ip'          => '127.0.0.1',
            'status_http' => 200,
            'duracion_ms' => 10,
            'resultado'   => 'ok',
            'created_at'  => now(),
        ], $overrides));
    }

    public function test_filtra_por_metodo_http(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);

        $this->crearTraza(['metodo' => 'GET', 'ruta' => 'catalogo']);
        $this->crearTraza(['metodo' => 'POST', 'ruta' => 'login']);

        Livewire::actingAs($admin)
            ->test(TrazabilidadViewer::class)
            ->set('filtroMetodo', 'POST')
            ->assertSee('login')
            ->assertDontSee('catalogo');
    }

    public function test_filtra_por_rango_de_status(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);

        $this->crearTraza(['ruta' => 'ok-route', 'status_http' => 200]);
        $this->crearTraza(['ruta' => 'server-error-route', 'status_http' => 500]);

        Livewire::actingAs($admin)
            ->test(TrazabilidadViewer::class)
            ->set('filtroStatus', '5xx')
            ->assertSee('server-error-route')
            ->assertDontSee('ok-route');
    }

    public function test_filtra_por_ruta(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);

        $this->crearTraza(['ruta' => 'catalogo']);
        $this->crearTraza(['ruta' => 'admin.logs']);

        Livewire::actingAs($admin)
            ->test(TrazabilidadViewer::class)
            ->set('filtroRuta', 'admin')
            ->assertSee('admin.logs')
            ->assertDontSee('catalogo');
    }

    public function test_filtra_por_trace_id(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);

        $traza1 = $this->crearTraza(['ruta' => 'primera-ruta']);
        $traza2 = $this->crearTraza(['ruta' => 'segunda-ruta']);

        Livewire::actingAs($admin)
            ->test(TrazabilidadViewer::class)
            ->set('filtroTraceId', $traza1->trace_id)
            ->assertSee('primera-ruta')
            ->assertDontSee('segunda-ruta');
    }

    public function test_filtra_por_usuario(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        $otro  = User::factory()->create(['name' => 'Ana Pérez']);

        $this->crearTraza(['ruta' => 'ruta-de-ana', 'user_id' => $otro->id]);
        $this->crearTraza(['ruta' => 'ruta-anonima', 'user_id' => null]);

        Livewire::actingAs($admin)
            ->test(TrazabilidadViewer::class)
            ->set('filtroUsuario', 'Ana')
            ->assertSee('ruta-de-ana')
            ->assertDontSee('ruta-anonima');
    }

    public function test_limpiar_filtros_reinicia_todos_los_campos(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);

        Livewire::actingAs($admin)
            ->test(TrazabilidadViewer::class)
            ->set('filtroMetodo', 'POST')
            ->set('filtroRuta', 'x')
            ->call('limpiarFiltros')
            ->assertSet('filtroMetodo', '')
            ->assertSet('filtroRuta', '');
    }
}
