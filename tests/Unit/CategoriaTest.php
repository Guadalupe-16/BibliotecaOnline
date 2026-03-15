<?php

namespace Tests\Unit;

use App\Models\Categoria;
use App\Models\Libro;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoriaTest extends TestCase
{
    use RefreshDatabase;

    public function test_total_libros_retorna_cantidad_correcta(): void
    {
        $categoria = Categoria::factory()->create();
        Libro::factory()->count(4)->create(['categoria_id' => $categoria->id]);

        $this->assertEquals(4, $categoria->totalLibros());
    }

    public function test_total_libros_retorna_cero_sin_libros(): void
    {
        $categoria = Categoria::factory()->create();

        $this->assertEquals(0, $categoria->totalLibros());
    }

    public function test_categoria_tiene_muchos_libros(): void
    {
        $categoria = Categoria::factory()->create();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $categoria->libros);
    }
}