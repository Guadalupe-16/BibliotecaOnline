<?php

namespace Tests\Feature;

use App\Models\Libro;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoritoControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_toggle_agrega_favorito(): void
    {
        $usuario = User::factory()->create();
        $libro   = Libro::factory()->create();

        $respuesta = $this->actingAs($usuario)
            ->postJson(route('favoritos.toggle', $libro));

        $respuesta->assertStatus(200);
        $respuesta->assertJson(['esFavorito' => true]);
        $this->assertDatabaseHas('favoritos', [
            'user_id'  => $usuario->id,
            'libro_id' => $libro->id,
        ]);
    }

    public function test_toggle_quita_favorito_si_ya_existe(): void
    {
        $usuario = User::factory()->create();
        $libro   = Libro::factory()->create();

        // Agregar primero
        $this->actingAs($usuario)->postJson(route('favoritos.toggle', $libro));

        // Quitar
        $respuesta = $this->actingAs($usuario)
            ->postJson(route('favoritos.toggle', $libro));

        $respuesta->assertStatus(200);
        $respuesta->assertJson(['esFavorito' => false]);
        $this->assertDatabaseMissing('favoritos', [
            'user_id'  => $usuario->id,
            'libro_id' => $libro->id,
        ]);
    }

    public function test_favoritos_requiere_autenticacion(): void
    {
        $libro = Libro::factory()->create();

        $respuesta = $this->postJson(route('favoritos.toggle', $libro));

        $respuesta->assertStatus(401);
    }

    public function test_index_muestra_favoritos_del_usuario(): void
    {
        $usuario = User::factory()->create();
        $libro   = Libro::factory()->create();

        $this->actingAs($usuario)->postJson(route('favoritos.toggle', $libro));

        $respuesta = $this->actingAs($usuario)->get(route('favoritos'));

        $respuesta->assertStatus(200);
    }
}
