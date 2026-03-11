<?php

namespace Database\Seeders;

use App\Models\Autor;
use App\Models\Categoria;
use App\Models\Libro;
use Illuminate\Database\Seeder;

class BibliotecaSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            ['nombre' => 'Ciencia Ficcion',  'color' => '#6366f1'],
            ['nombre' => 'Fantasia',          'color' => '#8b5cf6'],
            ['nombre' => 'Historia',          'color' => '#f59e0b'],
            ['nombre' => 'Programacion',      'color' => '#10b981'],
            ['nombre' => 'Literatura',        'color' => '#ef4444'],
            ['nombre' => 'Ciencia',           'color' => '#3b82f6'],
        ];

        foreach ($categorias as $c) {
            Categoria::firstOrCreate(['nombre' => $c['nombre']], $c);
        }

        $autores = [
            ['nombre' => 'Gabriel Garcia Marquez', 'nacionalidad' => 'Colombia'],
            ['nombre' => 'Frank Herbert',           'nacionalidad' => 'Estados Unidos'],
            ['nombre' => 'J.R.R. Tolkien',          'nacionalidad' => 'Reino Unido'],
            ['nombre' => 'Robert C. Martin',        'nacionalidad' => 'Estados Unidos'],
            ['nombre' => 'George Orwell',           'nacionalidad' => 'Reino Unido'],
        ];

        foreach ($autores as $a) {
            Autor::firstOrCreate(['nombre' => $a['nombre']], $a);
        }

        $libros = [
            [
                'titulo'              => 'Cien Anos de Soledad',
                'descripcion'         => 'La historia de la familia Buendia en el pueblo de Macondo.',
                'anio_publicacion'    => 1967,
                'copias_disponibles'  => 3,
                'autor_id'            => Autor::where('nombre', 'Gabriel Garcia Marquez')->first()->id,
                'categoria_id'        => Categoria::where('nombre', 'Literatura')->first()->id,
            ],
            [
                'titulo'              => 'Dune',
                'descripcion'         => 'Una epica de ciencia ficcion ambientada en el planeta desertico Arrakis.',
                'anio_publicacion'    => 1965,
                'copias_disponibles'  => 2,
                'autor_id'            => Autor::where('nombre', 'Frank Herbert')->first()->id,
                'categoria_id'        => Categoria::where('nombre', 'Ciencia Ficcion')->first()->id,
            ],
            [
                'titulo'              => 'El Senor de los Anillos',
                'descripcion'         => 'La gran aventura de la Tierra Media para destruir el Anillo Unico.',
                'anio_publicacion'    => 1954,
                'copias_disponibles'  => 4,
                'autor_id'            => Autor::where('nombre', 'J.R.R. Tolkien')->first()->id,
                'categoria_id'        => Categoria::where('nombre', 'Fantasia')->first()->id,
            ],
            [
                'titulo'              => 'Clean Code',
                'descripcion'         => 'Guia practica para escribir codigo limpio y mantenible.',
                'anio_publicacion'    => 2008,
                'copias_disponibles'  => 5,
                'autor_id'            => Autor::where('nombre', 'Robert C. Martin')->first()->id,
                'categoria_id'        => Categoria::where('nombre', 'Programacion')->first()->id,
            ],
            [
                'titulo'              => '1984',
                'descripcion'         => 'Una distopia sobre un regimen totalitario que controla cada aspecto de la vida.',
                'anio_publicacion'    => 1949,
                'copias_disponibles'  => 3,
                'autor_id'            => Autor::where('nombre', 'George Orwell')->first()->id,
                'categoria_id'        => Categoria::where('nombre', 'Literatura')->first()->id,
            ],
        ];

        foreach ($libros as $l) {
            Libro::firstOrCreate(['titulo' => $l['titulo']], $l);
        }
    }
}
