<?php

namespace Database\Factories;

use App\Models\Libro;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class PrestamoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'libro_id' => Libro::factory(),
            'fecha_prestamo' => Carbon::today(),
            'fecha_devolucion_esperada' => Carbon::today()->addDays(14),
            'fecha_devolucion_real' => null,
            'estado' => 'activo',
        ];
    }
}