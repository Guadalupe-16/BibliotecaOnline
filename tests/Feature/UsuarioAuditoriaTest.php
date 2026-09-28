<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsuarioAuditoriaTest extends TestCase
{
    use RefreshDatabase;

    public function test_actualizar_usuario_queda_auditado(): void
    {
        $actor   = User::factory()->create(['name' => 'Ana Admin']);
        $usuario = User::factory()->create(['name' => 'Luis Lector']);

        $this->actingAs($actor)->put("/usuarios/{$usuario->id}", [
            'name'  => 'Luis Actualizado',
            'email' => $usuario->email,
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'accion'      => 'usuario_actualizado',
            'user_id'     => $actor->id,
            'descripcion' => "El usuario Ana Admin actualizó los datos de Luis Actualizado (#{$usuario->id}).",
        ]);
    }

    public function test_eliminar_usuario_queda_auditado(): void
    {
        $actor   = User::factory()->create(['name' => 'Ana Admin']);
        $usuario = User::factory()->create(['name' => 'Luis Lector']);

        $this->actingAs($actor)->delete("/usuarios/{$usuario->id}");

        $this->assertDatabaseHas('activity_logs', [
            'accion'      => 'usuario_eliminado',
            'user_id'     => $actor->id,
            'descripcion' => "El usuario Ana Admin eliminó al usuario Luis Lector (#{$usuario->id}).",
        ]);
    }

    public function test_la_auditoria_no_guarda_datos_sensibles(): void
    {
        $actor   = User::factory()->create();
        $usuario = User::factory()->create();

        $this->actingAs($actor)->put("/usuarios/{$usuario->id}", [
            'name'  => 'Nuevo Nombre',
            'email' => 'nuevo@example.com',
        ]);

        $descripcion = \App\Models\ActivityLog::where('accion', 'usuario_actualizado')->value('descripcion');
        $this->assertStringNotContainsString('nuevo@example.com', $descripcion);
        $this->assertStringNotContainsString('password', strtolower($descripcion));
    }
}
