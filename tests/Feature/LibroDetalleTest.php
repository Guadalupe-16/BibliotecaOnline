<?php

namespace Tests\Feature;

use App\Models\Libro;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LibroDetalleTest extends TestCase
{
    use RefreshDatabase;

    public function test_detalle_libro_carga_correctamente(): void
    {
        $libro = Libro::factory()->create(['titulo' => 'El Principito']);

        $respuesta = $this->get(route('libros.show', $libro));

        $respuesta->assertStatus(200);
        $respuesta->assertViewIs('libros.detalle');
        $respuesta->assertSee('El Principito');
    }

    public function test_detalle_libro_devuelve_404_si_no_existe(): void
    {
        $respuesta = $this->get('/libros/9999');

        $respuesta->assertStatus(404);
    }
}