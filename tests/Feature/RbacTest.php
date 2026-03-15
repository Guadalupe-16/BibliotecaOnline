<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_normal_no_puede_ver_panel_roles(): void
    {
        $usuario = User::factory()->create(['rol' => 'usuario']);

        $this->actingAs($usuario)
            ->get('/admin/roles')
            ->assertStatus(403);
    }

    public function test_admin_puede_ver_panel_roles(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);

        $this->actingAs($admin)
            ->get('/admin/roles')
            ->assertStatus(200);
    }

    public function test_superadmin_puede_ver_panel_roles(): void
    {
        $superadmin = User::factory()->create(['rol' => 'superadmin']);

        $this->actingAs($superadmin)
            ->get('/admin/roles')
            ->assertStatus(200);
    }

    public function test_admin_puede_cambiar_rol_de_usuario(): void
    {
        $admin   = User::factory()->create(['rol' => 'admin']);
        $usuario = User::factory()->create(['rol' => 'usuario']);

        $this->actingAs($admin)
            ->put("/admin/roles/{$usuario->id}", ['rol' => 'admin'])
            ->assertRedirect();

        $this->assertEquals('admin', $usuario->fresh()->rol);
    }

    public function test_no_se_puede_cambiar_el_propio_rol(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);

        $this->actingAs($admin)
            ->put("/admin/roles/{$admin->id}", ['rol' => 'usuario'])
            ->assertSessionHas('error');

        $this->assertEquals('admin', $admin->fresh()->rol);
    }

    public function test_rol_invalido_es_rechazado(): void
    {
        $admin   = User::factory()->create(['rol' => 'admin']);
        $usuario = User::factory()->create(['rol' => 'usuario']);

        $this->actingAs($admin)
            ->put("/admin/roles/{$usuario->id}", ['rol' => 'dios'])
            ->assertSessionHasErrors('rol');
    }

    public function test_invitado_no_puede_acceder_a_panel_roles(): void
    {
        $this->get('/admin/roles')->assertRedirect('/login');
    }
}
