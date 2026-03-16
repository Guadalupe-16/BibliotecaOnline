<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsuarioControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_muestra_lista_de_usuarios(): void
    {
        $usuario = User::factory()->create();
        User::factory()->count(3)->create();

        $respuesta = $this->actingAs($usuario)->get(route('usuarios.index'));

        $respuesta->assertStatus(200);
    }

    public function test_index_requiere_autenticacion(): void
    {
        $respuesta = $this->get(route('usuarios.index'));

        $respuesta->assertRedirect(route('login'));
    }

    public function test_edit_muestra_formulario_de_edicion(): void
    {
        $usuario = User::factory()->create();

        $respuesta = $this->actingAs($usuario)->get(route('usuarios.edit', $usuario->id));

        $respuesta->assertStatus(200);
    }

    public function test_update_actualiza_datos_del_usuario(): void
    {
        $usuario = User::factory()->create();

        $respuesta = $this->actingAs($usuario)->put(route('usuarios.update', $usuario->id), [
            'name'  => 'Nombre Actualizado',
            'email' => 'nuevo@correo.com',
        ]);

        $respuesta->assertRedirect(route('usuarios.index'));
        $this->assertDatabaseHas('users', [
            'id'    => $usuario->id,
            'name'  => 'Nombre Actualizado',
            'email' => 'nuevo@correo.com',
        ]);
    }

    public function test_update_valida_campos_requeridos(): void
    {
        $usuario = User::factory()->create();

        $respuesta = $this->actingAs($usuario)->put(route('usuarios.update', $usuario->id), [
            'name'  => '',
            'email' => '',
        ]);

        $respuesta->assertSessionHasErrors(['name', 'email']);
    }

    public function test_destroy_elimina_usuario(): void
    {
        $admin    = User::factory()->create();
        $objetivo = User::factory()->create();

        $respuesta = $this->actingAs($admin)->delete(route('usuarios.destroy', $objetivo->id));

        $respuesta->assertRedirect(route('usuarios.index'));
        $this->assertDatabaseMissing('users', ['id' => $objetivo->id]);
    }
}