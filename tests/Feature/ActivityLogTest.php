<?php

namespace Tests\Feature;

use App\Jobs\LogActivityJob;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_registrar_crea_registro_con_usuario_e_ip(): void
    {
        $usuario = User::factory()->create();
        $this->actingAs($usuario);

        ActivityLog::registrar('prueba', 'Registro de prueba.');

        $this->assertDatabaseHas('activity_logs', [
            'accion'      => 'prueba',
            'descripcion' => 'Registro de prueba.',
            'user_id'     => $usuario->id,
            'ip'          => '127.0.0.1',
        ]);
    }

    public function test_registrar_sin_sesion_deja_usuario_nulo(): void
    {
        ActivityLog::registrar('anonima');

        $this->assertDatabaseHas('activity_logs', [
            'accion'      => 'anonima',
            'descripcion' => null,
            'user_id'     => null,
        ]);
    }

    public function test_registrar_despacha_el_job_a_la_cola(): void
    {
        Queue::fake();

        ActivityLog::registrar('encolada', 'Se procesa en segundo plano.');

        Queue::assertPushed(LogActivityJob::class, fn ($job) => $job->accion === 'encolada'
            && $job->descripcion === 'Se procesa en segundo plano.');
        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_iniciar_y_cerrar_sesion_queda_auditado(): void
    {
        $usuario = User::factory()->create(['password' => 'Secreta123!']);

        $this->post('/login', ['email' => $usuario->email, 'password' => 'Secreta123!']);
        $this->post('/logout');

        $this->assertDatabaseHas('activity_logs', ['accion' => 'login', 'user_id' => $usuario->id]);
        $this->assertDatabaseHas('activity_logs', ['accion' => 'logout', 'user_id' => $usuario->id]);
    }
}
