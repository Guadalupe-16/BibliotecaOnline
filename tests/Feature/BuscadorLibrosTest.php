<?php

namespace Tests\Feature;

use App\Models\Libro;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Livewire\BuscadorLibros;
use Tests\TestCase;

class BuscadorLibrosTest extends TestCase
{
    use RefreshDatabase;

    public function test_buscador_carga_correctamente(): void
    {
        $respuesta = $this->get(route('buscar'));

        $respuesta->assertStatus(200);
        $respuesta->assertSeeLivewire(BuscadorLibros::class);
    }

    public function test_buscador_filtra_por_titulo(): void
    {
        Libro::factory()->create(['titulo' => 'Cien Anos de Soledad']);
        Libro::factory()->create(['titulo' => 'El Principito']);

        Livewire::test(BuscadorLibros::class)
            ->set('termino', 'Cien')
            ->assertSee('Cien Anos de Soledad')
            ->assertDontSee('El Principito');
    }

    public function test_buscador_muestra_mensaje_sin_resultados(): void
    {
        Livewire::test(BuscadorLibros::class)
            ->set('termino', 'xyzxyzxyz')
            ->assertSee('No se encontraron libros');
    }
}