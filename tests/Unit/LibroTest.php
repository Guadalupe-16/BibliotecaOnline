<?php

namespace Tests\Unit;

use App\Models\Autor;
use App\Models\Categoria;
use App\Models\Libro;
use App\Models\Prestamo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LibroTest extends TestCase
{
    use RefreshDatabase;

    public function test_esta_disponible_retorna_true_cuando_hay_copias(): void
    {
        $libro = Libro::factory()->create(['copias_disponibles' => 3]);

        $this->assertTrue($libro->estaDisponible());
    }

    public function test_esta_disponible_retorna_false_cuando_no_hay_copias(): void
    {
        $libro = Libro::factory()->create(['copias_disponibles' => 0]);

        $this->assertFalse($libro->estaDisponible());
    }

    public function test_scope_buscar_filtra_por_titulo(): void
    {
        Libro::factory()->create(['titulo' => 'Cien Anos de Soledad']);
        Libro::factory()->create(['titulo' => 'El Principito']);

        $resultados = Libro::buscar('Cien')->get();

        $this->assertCount(1, $resultados);
        $this->assertEquals('Cien Anos de Soledad', $resultados->first()->titulo);
    }

    public function test_scope_buscar_filtra_por_nombre_de_autor(): void
    {
        $autor = Autor::factory()->create(['nombre' => 'Gabriel Garcia Marquez']);
        Libro::factory()->create(['autor_id' => $autor->id]);
        Libro::factory()->create();

        $resultados = Libro::buscar('Gabriel')->get();

        $this->assertCount(1, $resultados);
    }

    public function test_scope_por_categoria_filtra_correctamente(): void
    {
        $categoria = Categoria::factory()->create();
        Libro::factory()->count(2)->create(['categoria_id' => $categoria->id]);
        Libro::factory()->create();

        $resultados = Libro::porCategoria($categoria->id)->get();

        $this->assertCount(2, $resultados);
    }

    public function test_libro_pertenece_a_un_autor(): void
    {
        $libro = Libro::factory()->create();

        $this->assertInstanceOf(Autor::class, $libro->autor);
    }

    public function test_libro_pertenece_a_una_categoria(): void
    {
        $libro = Libro::factory()->create();

        $this->assertInstanceOf(Categoria::class, $libro->categoria);
    }

    public function test_libro_tiene_muchos_prestamos(): void
    {
        $libro = Libro::factory()->create();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $libro->prestamos);
    }
}