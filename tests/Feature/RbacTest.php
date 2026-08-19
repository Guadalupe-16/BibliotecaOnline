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

    public function test_admin_no_puede_asignar_el_rol_superadmin(): void
    {
        $admin   = User::factory()->create(['rol' => 'admin']);
        $usuario = User::factory()->create(['rol' => 'usuario']);

        $this->actingAs($admin)
            ->put("/admin/roles/{$usuario->id}", ['rol' => 'superadmin'])
            ->assertSessionHas('error');

        $this->assertEquals('usuario', $usuario->fresh()->rol);
    }

    public function test_admin_no_puede_retirar_el_rol_a_un_superadmin(): void
    {
        $admin      = User::factory()->create(['rol' => 'admin']);
        $superadmin = User::factory()->create(['rol' => 'superadmin']);

        $this->actingAs($admin)
            ->put("/admin/roles/{$superadmin->id}", ['rol' => 'usuario'])
            ->assertSessionHas('error');

        $this->assertEquals('superadmin', $superadmin->fresh()->rol);
    }

    public function test_superadmin_si_puede_asignar_el_rol_superadmin(): void
    {
        $superadmin = User::factory()->create(['rol' => 'superadmin']);
        $usuario    = User::factory()->create(['rol' => 'usuario']);

        $this->actingAs($superadmin)
            ->put("/admin/roles/{$usuario->id}", ['rol' => 'superadmin'])
            ->assertSessionHas('success');

        $this->assertEquals('superadmin', $usuario->fresh()->rol);
    }

    public function test_superadmin_puede_degradar_a_otro_superadmin(): void
    {
        $superadmin = User::factory()->create(['rol' => 'superadmin']);
        $otro       = User::factory()->create(['rol' => 'superadmin']);

        $this->actingAs($superadmin)
            ->put("/admin/roles/{$otro->id}", ['rol' => 'usuario'])
            ->assertSessionHas('success');

        $this->assertEquals('usuario', $otro->fresh()->rol);
    }

    public function test_el_panel_no_ofrece_la_opcion_superadmin_a_un_admin(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        User::factory()->create(['rol' => 'usuario']);

        $this->actingAs($admin)
            ->get('/admin/roles')
            ->assertOk()
            ->assertDontSee('value="superadmin"', false);
    }

    public function test_el_panel_ofrece_la_opcion_superadmin_a_un_superadmin(): void
    {
        $superadmin = User::factory()->create(['rol' => 'superadmin']);
        User::factory()->create(['rol' => 'usuario']);

        $this->actingAs($superadmin)
            ->get('/admin/roles')
            ->assertOk()
            ->assertSee('value="superadmin"', false);
    }
}
