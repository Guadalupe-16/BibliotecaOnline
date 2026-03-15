<?php

namespace Tests\Unit;

use App\Models\Libro;
use App\Models\Prestamo;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrestamoTest extends TestCase
{
    use RefreshDatabase;

    public function test_esta_vencido_retorna_true_cuando_paso_la_fecha(): void
    {
        $prestamo = Prestamo::factory()->create([
            'estado' => 'activo',
            'fecha_devolucion_esperada' => Carbon::today()->subDays(1),
        ]);

        $this->assertTrue($prestamo->estaVencido());
    }

    public function test_esta_vencido_retorna_false_cuando_no_ha_vencido(): void
    {
        $prestamo = Prestamo::factory()->create([
            'estado' => 'activo',
            'fecha_devolucion_esperada' => Carbon::today()->addDays(5),
        ]);

        $this->assertFalse($prestamo->estaVencido());
    }

    public function test_dias_restantes_retorna_valor_correcto(): void
    {
        $prestamo = Prestamo::factory()->create([
            'fecha_devolucion_esperada' => Carbon::today()->addDays(7),
        ]);

        $this->assertEquals(7, $prestamo->diasRestantes());
    }

    public function test_crear_prestamo_guarda_en_bd(): void
    {
        $user = User::factory()->create();
        $libro = Libro::factory()->create();

        $prestamo = Prestamo::crearPrestamo($user->id, $libro->id, 14);

        $this->assertDatabaseHas('prestamos', [
            'user_id' => $user->id,
            'libro_id' => $libro->id,
            'estado' => 'activo',
        ]);
        $this->assertEquals(14, $prestamo->diasRestantes());
    }

    public function test_prestamo_pertenece_a_un_libro(): void
    {
        $prestamo = Prestamo::factory()->create();

        $this->assertInstanceOf(Libro::class, $prestamo->libro);
    }

    public function test_prestamo_pertenece_a_un_usuario(): void
    {
        $prestamo = Prestamo::factory()->create();

        $this->assertInstanceOf(User::class, $prestamo->user);
    }
}