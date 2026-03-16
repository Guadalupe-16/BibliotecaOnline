<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GraficaUsuariosTest extends TestCase
{
    use RefreshDatabase;

    public function test_grafica_requiere_autenticacion(): void
    {
        $respuesta = $this->get('/superadmin/grafica');
        $respuesta->assertRedirect('/login');
    }

    public function test_usuario_normal_no_puede_ver_grafica(): void
    {
        $usuario = User::factory()->create(['rol' => 'usuario']);
        $respuesta = $this->actingAs($usuario)->get('/superadmin/grafica');
        $respuesta->assertStatus(403);
    }

    public function test_superadmin_puede_ver_grafica(): void
    {
        $superadmin = User::factory()->create(['rol' => 'superadmin']);
        $respuesta = $this->actingAs($superadmin)->get('/superadmin/grafica');
        $respuesta->assertStatus(200);
    }

    public function test_endpoint_stats_retorna_json(): void
    {
        $superadmin = User::factory()->create(['rol' => 'superadmin']);

        $respuesta = $this->actingAs($superadmin)
            ->get('/superadmin/stats/usuarios');

        $respuesta->assertStatus(200);
        $respuesta->assertJsonStructure([
            'fechas',
            'totales',
        ]);
    }

    public function test_stats_retorna_datos_correctos(): void
    {
        $superadmin = User::factory()->create(['rol' => 'superadmin']);
        User::factory()->count(3)->create();

        $respuesta = $this->actingAs($superadmin)
            ->get('/superadmin/stats/usuarios');

        $respuesta->assertStatus(200);
        $data = $respuesta->json();
        $this->assertIsArray($data['fechas']);
        $this->assertIsArray($data['totales']);
    }
}