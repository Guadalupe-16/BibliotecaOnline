<?php

namespace Tests\Unit;

use App\Models\Libro;
use App\Models\User;
use App\Models\Favorito;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_es_favorito_retorna_true_si_libro_es_favorito(): void
    {
        $usuario = User::factory()->create();
        $libro   = Libro::factory()->create();

        Favorito::create([
            'user_id'  => $usuario->id,
            'libro_id' => $libro->id,
        ]);

        $this->assertTrue($usuario->esFavorito($libro->id));
    }

    public function test_es_favorito_retorna_false_si_libro_no_es_favorito(): void
    {
        $usuario = User::factory()->create();
        $libro   = Libro::factory()->create();

        $this->assertFalse($usuario->esFavorito($libro->id));
    }

    public function test_usuario_tiene_muchos_favoritos(): void
    {
        $usuario = User::factory()->create();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $usuario->favoritos);
    }
}
