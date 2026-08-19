<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PerfilControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_perfil_requiere_autenticacion(): void
    {
        $respuesta = $this->get('/perfil');
        $respuesta->assertRedirect('/login');
    }

    public function test_perfil_carga_correctamente(): void
    {
        $usuario = User::factory()->create();
        $respuesta = $this->actingAs($usuario)->get('/perfil');
        $respuesta->assertStatus(200);
    }

    public function test_actualizar_nombre_correctamente(): void
    {
        $usuario = User::factory()->create(['name' => 'Nombre Viejo']);

        $respuesta = $this->actingAs($usuario)->post('/perfil', [
            'name' => 'Nombre Nuevo',
        ]);

        $respuesta->assertRedirect();
        $this->assertDatabaseHas('users', ['name' => 'Nombre Nuevo']);
    }

    public function test_actualizar_requiere_nombre(): void
    {
        $usuario = User::factory()->create();

        $respuesta = $this->actingAs($usuario)->post('/perfil', [
            'name' => '',
        ]);

        $respuesta->assertSessionHasErrors('name');
    }

    public function test_subir_foto_de_perfil(): void
    {
        Storage::fake('public');
        $usuario = User::factory()->create();

        // create() en lugar de image() para no depender de la extension GD,
        // que no viene activada en todas las instalaciones del equipo.
        $foto = UploadedFile::fake()->create('foto.jpg', 100, 'image/jpeg');

        $respuesta = $this->actingAs($usuario)->post('/perfil', [
            'name' => $usuario->name,
            'foto' => $foto,
        ]);

        $respuesta->assertRedirect();
        $this->assertNotNull($usuario->fresh()->foto);
    }

    public function test_foto_invalida_es_rechazada(): void
    {
        $usuario = User::factory()->create();

        $archivo = UploadedFile::fake()->create('documento.pdf', 100);

        $respuesta = $this->actingAs($usuario)->post('/perfil', [
            'name' => $usuario->name,
            'foto' => $archivo,
        ]);

        $respuesta->assertSessionHasErrors('foto');
    }
}