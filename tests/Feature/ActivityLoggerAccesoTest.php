<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLoggerAccesoTest extends TestCase
{
    use RefreshDatabase;

    public function test_invitado_es_redirigido_a_login(): void
    {
        $this->get('/admin/logs')->assertRedirect('/login');
    }

    public function test_usuario_normal_recibe_403(): void
    {
        $usuario = User::factory()->create(['rol' => 'usuario']);

        $this->actingAs($usuario)->get('/admin/logs')->assertStatus(403);
    }

    public function test_admin_puede_ver_el_visor(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);

        $this->actingAs($admin)->get('/admin/logs')->assertOk();
    }

    public function test_superadmin_puede_ver_el_visor(): void
    {
        $superadmin = User::factory()->create(['rol' => 'superadmin']);

        $this->actingAs($superadmin)->get('/admin/logs')->assertOk();
    }
}
