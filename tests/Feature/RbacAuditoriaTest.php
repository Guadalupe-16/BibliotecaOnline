<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacAuditoriaTest extends TestCase
{
    use RefreshDatabase;

    public function test_cambiar_rol_desde_panel_rbac_queda_auditado(): void
    {
        $admin   = User::factory()->create(['rol' => 'admin', 'name' => 'Ana Admin']);
        $usuario = User::factory()->create(['rol' => 'usuario', 'name' => 'Luis Lector']);

        $this->actingAs($admin)->put("/admin/roles/{$usuario->id}", ['rol' => 'admin']);

        $this->assertDatabaseHas('activity_logs', [
            'accion'      => 'rbac_cambio_rol',
            'user_id'     => $admin->id,
            'descripcion' => 'El usuario Ana Admin cambió el rol de Luis Lector de usuario a admin (panel de roles).',
        ]);
    }

    public function test_cambio_de_rol_rechazado_no_se_audita(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);

        // Cambiar el propio rol no esta permitido: no debe quedar registro de un cambio que no ocurrio
        $this->actingAs($admin)->put("/admin/roles/{$admin->id}", ['rol' => 'usuario']);

        $this->assertDatabaseMissing('activity_logs', ['accion' => 'rbac_cambio_rol']);
    }
}
