<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_panel_requiere_autenticacion(): void
    {
        $respuesta = $this->get('/superadmin');
        $respuesta->assertRedirect('/login');
    }

    public function test_usuario_normal_no_puede_acceder(): void
    {
        $usuario = User::factory()->create(['rol' => 'usuario']);
        $respuesta = $this->actingAs($usuario)->get('/superadmin');
        $respuesta->assertStatus(403);
    }

    public function test_admin_no_puede_acceder(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        $respuesta = $this->actingAs($admin)->get('/superadmin');
        $respuesta->assertStatus(403);
    }

    public function test_superadmin_puede_acceder(): void
    {
        $superadmin = User::factory()->create(['rol' => 'superadmin']);
        $respuesta = $this->actingAs($superadmin)->get('/superadmin');
        $respuesta->assertStatus(200);
    }

    public function test_superadmin_puede_desactivar_usuario(): void
    {
        $superadmin = User::factory()->create(['rol' => 'superadmin']);
        $usuario = User::factory()->create(['rol' => 'usuario', 'activo' => true]);

        $respuesta = $this->actingAs($superadmin)
            ->post("/superadmin/usuarios/{$usuario->id}/toggle-estado");

        $respuesta->assertRedirect();
        $this->assertFalse($usuario->fresh()->activo);
    }

    public function test_superadmin_puede_cambiar_rol(): void
    {
        $superadmin = User::factory()->create(['rol' => 'superadmin']);
        $usuario = User::factory()->create(['rol' => 'usuario']);

        $respuesta = $this->actingAs($superadmin)
            ->post("/superadmin/usuarios/{$usuario->id}/cambiar-rol/admin");

        $respuesta->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id'  => $usuario->id,
            'rol' => 'admin',
        ]);
    }

    public function test_superadmin_no_puede_desactivarse_a_si_mismo(): void
    {
        $superadmin = User::factory()->create(['rol' => 'superadmin']);

        $respuesta = $this->actingAs($superadmin)
            ->post("/superadmin/usuarios/{$superadmin->id}/toggle-estado");

        $respuesta->assertRedirect();
        $respuesta->assertSessionHas('error');
    }
}