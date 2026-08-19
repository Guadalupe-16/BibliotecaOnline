<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Issue #116: la ruta temporal /login-super iniciaba sesion como superadmin
 * sin pedir credenciales. Estas pruebas confirman que la ruta ya no existe
 * y que el panel sigue protegido por autenticacion y por rol.
 */
class RutaLoginSuperTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_ruta_login_super_ya_no_esta_registrada(): void
    {
        $rutas = collect(app('router')->getRoutes())->map(fn ($ruta) => $ruta->uri());

        $this->assertNotContains('login-super', $rutas);
    }

    public function test_la_ruta_login_super_responde_no_encontrado(): void
    {
        $respuesta = $this->get('/login-super');

        $respuesta->assertNotFound();
    }

    public function test_la_ruta_login_super_no_inicia_sesion_como_superadmin(): void
    {
        User::factory()->create([
            'email' => 'super@test.com',
            'rol' => 'superadmin',
        ]);

        $this->get('/login-super');

        $this->assertGuest();
    }

    public function test_el_panel_superadmin_exige_autenticacion(): void
    {
        $respuesta = $this->get('/superadmin');

        $respuesta->assertRedirect('/login');
    }

    public function test_un_usuario_normal_no_puede_entrar_al_panel_superadmin(): void
    {
        $usuario = User::factory()->create(['rol' => 'usuario']);

        $respuesta = $this->actingAs($usuario)->get('/superadmin');

        $this->assertTrue(
            in_array($respuesta->getStatusCode(), [302, 403], true),
            'El panel superadmin debe rechazar a un usuario sin el rol superadmin.'
        );
    }

    public function test_un_superadmin_autenticado_si_entra_al_panel(): void
    {
        $superadmin = User::factory()->create(['rol' => 'superadmin']);

        $respuesta = $this->actingAs($superadmin)->get('/superadmin');

        $respuesta->assertOk();
    }
}
