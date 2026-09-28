<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminAuditoriaTest extends TestCase
{
    use RefreshDatabase;

    public function test_desactivar_cuenta_queda_auditado(): void
    {
        $superadmin = User::factory()->create(['rol' => 'superadmin', 'name' => 'Sofia Super']);
        $usuario    = User::factory()->create(['name' => 'Luis Lector', 'activo' => true]);

        $this->actingAs($superadmin)->post("/superadmin/usuarios/{$usuario->id}/toggle-estado");

        $this->assertDatabaseHas('activity_logs', [
            'accion'      => 'superadmin_toggle_estado',
            'user_id'     => $superadmin->id,
            'descripcion' => 'El usuario Sofia Super desactivó la cuenta de Luis Lector.',
        ]);
    }

    public function test_activar_cuenta_queda_auditado(): void
    {
        $superadmin = User::factory()->create(['rol' => 'superadmin', 'name' => 'Sofia Super']);
        $usuario    = User::factory()->create(['name' => 'Luis Lector', 'activo' => false]);

        $this->actingAs($superadmin)->post("/superadmin/usuarios/{$usuario->id}/toggle-estado");

        $this->assertDatabaseHas('activity_logs', [
            'accion'      => 'superadmin_toggle_estado',
            'descripcion' => 'El usuario Sofia Super activó la cuenta de Luis Lector.',
        ]);
    }

    public function test_cambiar_rol_desde_superadmin_queda_auditado(): void
    {
        $superadmin = User::factory()->create(['rol' => 'superadmin', 'name' => 'Sofia Super']);
        $usuario    = User::factory()->create(['rol' => 'usuario', 'name' => 'Luis Lector']);

        $this->actingAs($superadmin)->post("/superadmin/usuarios/{$usuario->id}/cambiar-rol/admin");

        $this->assertDatabaseHas('activity_logs', [
            'accion'      => 'superadmin_cambio_rol',
            'user_id'     => $superadmin->id,
            'descripcion' => 'El usuario Sofia Super cambió el rol de Luis Lector de usuario a admin (panel de superadministrador).',
        ]);
    }

    public function test_rol_invalido_no_se_audita(): void
    {
        $superadmin = User::factory()->create(['rol' => 'superadmin']);
        $usuario    = User::factory()->create(['rol' => 'usuario']);

        $this->actingAs($superadmin)->post("/superadmin/usuarios/{$usuario->id}/cambiar-rol/dios");

        $this->assertDatabaseMissing('activity_logs', ['accion' => 'superadmin_cambio_rol']);
    }
}
