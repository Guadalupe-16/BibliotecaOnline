<?php

namespace Database\Factories;

use App\Models\Autor;
use App\Models\Categoria;
use Illuminate\Database\Eloquent\Factories\Factory;

class LibroFactory extends Factory
{
    public function definition(): array
    {
        return [
            'titulo' => $this->faker->sentence(3),
            'isbn' => $this->faker->unique()->isbn13(),
            'descripcion' => $this->faker->paragraph(),
            'portada_url' => null,
            'anio_publicacion' => $this->faker->year(),
            'copias_disponibles' => $this->faker->numberBetween(0, 10),
            'autor_id' => Autor::factory(),
            'categoria_id' => Categoria::factory(),
        ];
    }
}