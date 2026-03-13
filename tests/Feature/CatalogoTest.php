<?php

namespace Tests\Feature;

use App\Models\Libro;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogoTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalogo_carga_correctamente(): void
    {
        Libro::factory()->count(3)->create();

        $respuesta = $this->get(route('catalogo'));

        $respuesta->assertStatus(200);
        $respuesta->assertViewIs('libros.catalogo');
    }

    public function test_catalogo_muestra_libros(): void
    {
        $libro = Libro::factory()->create(['titulo' => 'El Principito']);

        $respuesta = $this->get(route('catalogo'));

        $respuesta->assertSee('El Principito');
    }

    public function test_catalogo_muestra_mensaje_cuando_no_hay_libros(): void
    {
        $respuesta = $this->get(route('catalogo'));

        $respuesta->assertSee('No hay libros en el catálogo aún.');
    }
}