<?php

namespace App\Services;

use App\Models\Autor;
use App\Models\Libro;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenLibraryService
{
    private const URL_BASE = 'https://openlibrary.org';
    private const URL_PORTADAS = 'https://covers.openlibrary.org/b/id';

    public function buscarLibros(string $termino, int $limite = 10): array
    {
        $respuesta = Http::timeout(10)->get(self::URL_BASE . '/search.json', [
            'q'      => $termino,
            'limit'  => $limite,
            'fields' => 'key,title,author_name,cover_i,isbn,first_publish_year',
        ]);

        if ($respuesta->failed()) {
            Log::error('OpenLibrary: error al buscar libros', ['termino' => $termino]);
            return [];
        }

        return $this->mapearResultados($respuesta->json('docs', []));
    }

    public function buscarPorIsbn(string $isbn): ?array
    {
        $respuesta = Http::timeout(10)->get(self::URL_BASE . '/api/books', [
            'bibkeys' => "ISBN:{$isbn}",
            'format'  => 'json',
            'jscmd'   => 'details',
        ]);

        if ($respuesta->failed() || empty($respuesta->json())) {
            return null;
        }

        $datos = $respuesta->json("ISBN:{$isbn}.details");
        if (!$datos) {
            return null;
        }

        return [
            'titulo'           => $datos['title'] ?? null,
            'autor'            => $datos['authors'][0]['name'] ?? null,
            'descripcion'      => $datos['description']['value'] ?? $datos['description'] ?? null,
            'anio_publicacion'  => isset($datos['publish_date']) ? (int) substr($datos['publish_date'], -4) : null,
            'isbn'             => $isbn,
            'portada_url'      => isset($datos['covers'][0])
                ? self::URL_PORTADAS . '/' . $datos['covers'][0] . '-L.jpg'
                : null,
            'olid'             => str_replace('/works/', '', $datos['works'][0]['key'] ?? ''),
        ];
    }

    public function importarLibro(string $olid, int $categoriaId, int $copiasDisponibles = 1): ?Libro
    {
        $respuesta = Http::timeout(10)->get(self::URL_BASE . "/works/{$olid}.json");

        if ($respuesta->failed()) {
            Log::error('OpenLibrary: error al obtener obra', ['olid' => $olid]);
            return null;
        }

        $datos = $respuesta->json();

        $autor = $this->obtenerOCrearAutor($datos['authors'][0]['author']['key'] ?? null);

        $portadaId = $datos['covers'][0] ?? null;

        return Libro::firstOrCreate(
            ['isbn' => $olid],
            [
                'titulo'            => $datos['title'],
                'descripcion'       => $this->extraerDescripcion($datos),
                'portada_url'       => $portadaId
                    ? self::URL_PORTADAS . "/{$portadaId}-L.jpg"
                    : null,
                'anio_publicacion'  => isset($datos['first_publish_date'])
                    ? (int) substr($datos['first_publish_date'], -4)
                    : null,
                'copias_disponibles' => $copiasDisponibles,
                'autor_id'          => $autor?->id,
                'categoria_id'      => $categoriaId,
            ]
        );
    }

    private function obtenerOCrearAutor(?string $autorKey): ?Autor
    {
        if (!$autorKey) {
            return null;
        }

        $respuesta = Http::timeout(10)->get(self::URL_BASE . $autorKey . '.json');

        if ($respuesta->failed()) {
            return null;
        }

        $datos = $respuesta->json();

        return Autor::firstOrCreate(
            ['nombre' => $datos['name']],
            [
                'biografia'    => $datos['bio']['value'] ?? $datos['bio'] ?? null,
                'nacionalidad' => null,
            ]
        );
    }

    private function extraerDescripcion(array $datos): ?string
    {
        $descripcion = $datos['description'] ?? null;

        if (is_array($descripcion)) {
            return $descripcion['value'] ?? null;
        }

        return $descripcion;
    }

    private function mapearResultados(array $docs): array
    {
        return array_map(fn($doc) => [
            'titulo'           => $doc['title'] ?? null,
            'autor'            => $doc['author_name'][0] ?? null,
            'isbn'             => $doc['isbn'][0] ?? null,
            'anio_publicacion'  => $doc['first_publish_year'] ?? null,
            'portada_url'      => isset($doc['cover_i'])
                ? self::URL_PORTADAS . '/' . $doc['cover_i'] . '-M.jpg'
                : null,
            'olid'             => str_replace('/works/', '', $doc['key'] ?? ''),
        ], $docs);
    }
}
