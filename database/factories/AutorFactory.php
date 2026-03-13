<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AutorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nombre' => $this->faker->name(),
            'nacionalidad' => $this->faker->country(),
            'biografia' => $this->faker->paragraph(),
        ];
    }
}