<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Libro;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OpenLibraryControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_pagina_buscar_carga_sin_termino(): void
    {
        $respuesta = $this->get(route('open-library.index'));

        $respuesta->assertStatus(200);
        $respuesta->assertViewIs('libros.buscar');
    }

    public function test_buscar_retorna_resultados(): void
    {
        Categoria::factory()->create();

        Http::fake([
            'openlibrary.org/search.json*' => Http::response([
                'docs' => [
                    [
                        'title'              => 'Harry Potter',
                        'author_name'        => ['J.K. Rowling'],
                        'isbn'               => ['9780439708180'],
                        'first_publish_year' => 1997,
                        'cover_i'            => 12345,
                        'key'                => '/works/OL82563W',
                    ],
                ],
            ], 200),
        ]);

        $respuesta = $this->get(route('open-library.buscar') . '?termino=harry+potter');

        $respuesta->assertStatus(200);
        $respuesta->assertViewIs('libros.buscar');
        $respuesta->assertViewHas('resultados');
    }

    public function test_buscar_requiere_termino(): void
    {
        $respuesta = $this->get(route('open-library.buscar'));

        $respuesta->assertRedirect();
    }

    public function test_importar_libro_despacha_job(): void
    {
        $categoria = Categoria::factory()->create();

        $respuesta = $this->post(route('open-library.importar'), [
            'olid'               => 'OL82563W',
            'categoria_id'       => $categoria->id,
            'copias_disponibles' => 2,
        ]);

        $respuesta->assertRedirect();
        $respuesta->assertSessionHas('exito');
    }

    public function test_importar_requiere_categoria_valida(): void
    {
        $respuesta = $this->post(route('open-library.importar'), [
            'olid'         => 'OL82563W',
            'categoria_id' => 9999,
        ]);

        $respuesta->assertRedirect();
        $respuesta->assertSessionHasErrors('categoria_id');
    }
}
