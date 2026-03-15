<?php

namespace Tests\Unit;

use App\Models\Autor;
use App\Models\Libro;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutorTest extends TestCase
{
    use RefreshDatabase;

    public function test_total_libros_retorna_cantidad_correcta(): void
    {
        $autor = Autor::factory()->create();
        Libro::factory()->count(3)->create(['autor_id' => $autor->id]);

        $this->assertEquals(3, $autor->totalLibros());
    }

    public function test_total_libros_retorna_cero_sin_libros(): void
    {
        $autor = Autor::factory()->create();

        $this->assertEquals(0, $autor->totalLibros());
    }

    public function test_autor_tiene_muchos_libros(): void
    {
        $autor = Autor::factory()->create();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $autor->libros);
    }
}