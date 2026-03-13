<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Services\OpenLibraryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OpenLibraryServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_buscar_libros_retorna_resultados_correctamente(): void
    {
        Http::fake([
            'openlibrary.org/search.json*' => Http::response([
                'docs' => [
                    [
                        'title' => 'Cien años de soledad',
                        'author_name' => ['Gabriel García Márquez'],
                        'isbn' => ['9780060883287'],
                        'first_publish_year' => 1967,
                        'cover_i' => 12345,
                        'key' => '/works/OL123W',
                    ],
                ],
            ], 200),
        ]);

        $servicio = new OpenLibraryService();
        $resultados = $servicio->buscarLibros('cien años de soledad');

        $this->assertCount(1, $resultados);
        $this->assertEquals('Cien años de soledad', $resultados[0]['titulo']);
        $this->assertEquals('Gabriel García Márquez', $resultados[0]['autor']);
        $this->assertEquals('OL123W', $resultados[0]['olid']);
    }

    public function test_buscar_libros_retorna_vacio_si_la_api_falla(): void
    {
        Http::fake([
            'openlibrary.org/search.json*' => Http::response([], 500),
        ]);

        $servicio = new OpenLibraryService();
        $resultados = $servicio->buscarLibros('algo');

        $this->assertEmpty($resultados);
    }

    public function test_importar_libro_crea_autor_y_libro_en_bd(): void
    {
        Http::fake([
            'openlibrary.org/works/OL123W.json' => Http::response([
                'title' => 'Cien años de soledad',
                'description' => 'Una novela de realismo mágico.',
                'covers' => [12345],
                'authors' => [['author' => ['key' => '/authors/OL1A']]],
            ], 200),
            'openlibrary.org/authors/OL1A.json' => Http::response([
                'name' => 'Gabriel García Márquez',
                'biography' => 'Escritor colombiano.',
            ], 200),
        ]);

        $categoria = Categoria::factory()->create();
        $servicio = new OpenLibraryService();

        $libro = $servicio->importarLibro('OL123W', $categoria->id);

        $this->assertNotNull($libro);
        $this->assertEquals('Cien años de soledad', $libro->titulo);
        $this->assertDatabaseHas('libros', ['titulo' => 'Cien años de soledad']);
        $this->assertDatabaseHas('autores', ['nombre' => 'Gabriel García Márquez']);
    }
}