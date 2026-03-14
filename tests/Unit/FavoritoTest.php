<?php

namespace Tests\Unit;

use App\Models\Favorito;
use App\Models\Libro;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoritoTest extends TestCase
{
    use RefreshDatabase;

    public function test_favorito_pertenece_a_un_usuario(): void
    {
        $usuario = User::factory()->create();
        $libro   = Libro::factory()->create();

        $favorito = Favorito::create([
            'user_id'  => $usuario->id,
            'libro_id' => $libro->id,
        ]);

        $this->assertInstanceOf(User::class, $favorito->usuario);
    }

    public function test_favorito_pertenece_a_un_libro(): void
    {
        $usuario = User::factory()->create();
        $libro   = Libro::factory()->create();

        $favorito = Favorito::create([
            'user_id'  => $usuario->id,
            'libro_id' => $libro->id,
        ]);

        $this->assertInstanceOf(Libro::class, $favorito->libro);
    }
}
