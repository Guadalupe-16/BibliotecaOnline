<?php

namespace Tests\Feature;

use App\Livewire\ActivityLogger;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ActivityLoggerFiltrosTest extends TestCase
{
    use RefreshDatabase;

    private User $ana;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ana = User::factory()->create(['name' => 'Ana Admin']);

        ActivityLog::create(['accion' => 'login', 'descripcion' => 'Entrada de Ana', 'user_id' => $this->ana->id, 'ip' => '192.168.1.10']);
        ActivityLog::create(['accion' => 'usuario_eliminado', 'descripcion' => 'Borrado desde la oficina', 'user_id' => $this->ana->id, 'ip' => '10.0.0.5']);
        ActivityLog::create(['accion' => 'catalogo', 'descripcion' => 'Visita anonima', 'user_id' => null, 'ip' => '172.16.0.1']);
    }

    public function test_filtro_por_ip_muestra_solo_esa_ip(): void
    {
        Livewire::test(ActivityLogger::class)
            ->set('filtroIp', '10.0.0.5')
            ->assertSee('Borrado desde la oficina')
            ->assertDontSee('Entrada de Ana')
            ->assertDontSee('Visita anonima');
    }

    public function test_filtro_por_ip_acepta_prefijo_de_subred(): void
    {
        Livewire::test(ActivityLogger::class)
            ->set('filtroIp', '192.168.')
            ->assertSee('Entrada de Ana')
            ->assertDontSee('Borrado desde la oficina');
    }

    public function test_filtro_por_accion_sigue_funcionando(): void
    {
        Livewire::test(ActivityLogger::class)
            ->set('filtroAccion', 'eliminado')
            ->assertSee('Borrado desde la oficina')
            ->assertDontSee('Entrada de Ana');
    }

    public function test_filtro_por_usuario_anonimo_sigue_funcionando(): void
    {
        Livewire::test(ActivityLogger::class)
            ->set('filtroUsuario', 'Anónimo')
            ->assertSee('Visita anonima')
            ->assertDontSee('Entrada de Ana');
    }

    public function test_filtro_por_fecha_sigue_funcionando(): void
    {
        ActivityLog::where('descripcion', 'Entrada de Ana')->update(['created_at' => now()->subDays(10)]);

        Livewire::test(ActivityLogger::class)
            ->set('fechaDesde', now()->subDay()->toDateString())
            ->assertSee('Borrado desde la oficina')
            ->assertDontSee('Entrada de Ana');
    }

    public function test_limpiar_filtros_restablece_la_ip(): void
    {
        Livewire::test(ActivityLogger::class)
            ->set('filtroIp', '10.0.0.5')
            ->call('limpiarFiltros')
            ->assertSet('filtroIp', '')
            ->assertSee('Entrada de Ana')
            ->assertSee('Visita anonima');
    }

    public function test_el_visor_muestra_quien_accion_ip_y_fecha(): void
    {
        Livewire::test(ActivityLogger::class)
            ->assertSee('Ana Admin')
            ->assertSee('usuario_eliminado')
            ->assertSee('10.0.0.5')
            ->assertSee(now()->timezone('America/Hermosillo')->format('d/m/Y'));
    }
}
