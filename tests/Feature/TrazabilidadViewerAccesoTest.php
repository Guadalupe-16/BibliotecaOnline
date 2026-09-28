<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Issue #166 / specs/005-trazabilidad/spec.md FR-006: el visor de
 * trazabilidad solo debe ser accesible para admin y superadmin.
 */
class TrazabilidadViewerAccesoTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_invitado_es_redirigido_a_login(): void
    {
        $this->get('/admin/trazas')->assertRedirect('/login');
    }

    public function test_un_usuario_normal_no_puede_ver_el_visor(): void
    {
        $usuario = User::factory()->create(['rol' => 'usuario']);

        $this->actingAs($usuario)
            ->get('/admin/trazas')
            ->assertStatus(403);
    }

    public function test_un_admin_puede_ver_el_visor(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);

        $this->actingAs($admin)
            ->get('/admin/trazas')
            ->assertOk()
            ->assertSeeLivewire('trazabilidad-viewer');
    }

    public function test_un_superadmin_puede_ver_el_visor(): void
    {
        $superadmin = User::factory()->create(['rol' => 'superadmin']);

        $this->actingAs($superadmin)
            ->get('/admin/trazas')
            ->assertOk()
            ->assertSeeLivewire('trazabilidad-viewer');
    }
}
