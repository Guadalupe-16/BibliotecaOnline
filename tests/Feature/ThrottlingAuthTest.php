<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ThrottlingAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_se_bloquea_despues_de_cinco_intentos_fallidos(): void
    {
        $usuario = User::factory()->create(['email_verified_at' => now()]);

        for ($intento = 1; $intento <= 5; $intento++) {
            $this->post('/login', [
                'email'    => $usuario->email,
                'password' => 'contrasena-incorrecta',
            ]);
        }

        // El sexto intento se bloquea aunque la contraseña sea la correcta.
        $respuesta = $this->post('/login', [
            'email'    => $usuario->email,
            'password' => 'password',
        ]);

        $respuesta->assertRedirect();
        $respuesta->assertSessionHas('error');
        $this->assertGuest();
    }

    public function test_el_limite_de_login_no_afecta_a_otro_usuario(): void
    {
        $bloqueado = User::factory()->create(['email_verified_at' => now()]);
        $otro      = User::factory()->create(['email_verified_at' => now()]);

        for ($intento = 1; $intento <= 6; $intento++) {
            $this->post('/login', [
                'email'    => $bloqueado->email,
                'password' => 'contrasena-incorrecta',
            ]);
        }

        $respuesta = $this->post('/login', [
            'email'    => $otro->email,
            'password' => 'password',
        ]);

        $respuesta->assertRedirect(route('catalogo'));
        $this->assertAuthenticatedAs($otro);
    }

    public function test_registro_se_bloquea_despues_de_cinco_altas_seguidas(): void
    {
        Mail::fake();

        for ($numero = 1; $numero <= 5; $numero++) {
            $this->post('/register', $this->datosDeRegistro($numero));
        }

        $respuesta = $this->post('/register', $this->datosDeRegistro(6));

        $respuesta->assertSessionHas('error');
        $this->assertDatabaseMissing('users', ['email' => 'usuario6@test.com']);
    }

    public function test_verificacion_de_pin_se_bloquea_despues_de_cinco_intentos(): void
    {
        $usuario = User::factory()->create(['email_verified_at' => null]);
        Cache::put("email_pin_{$usuario->id}", 123456, now()->addMinutes(15));

        for ($intento = 1; $intento <= 5; $intento++) {
            $this->post("/verificar-email/{$usuario->id}", ['pin' => '000000']);
        }

        // El sexto intento se bloquea aunque el PIN sea el correcto.
        $respuesta = $this->post("/verificar-email/{$usuario->id}", ['pin' => '123456']);

        $respuesta->assertSessionHas('error');
        $this->assertNull($usuario->fresh()->email_verified_at);
    }

    public function test_reenvio_de_pin_se_bloquea_despues_de_dos_solicitudes(): void
    {
        Mail::fake();
        $usuario = User::factory()->create(['email_verified_at' => null]);

        $this->post("/verificar-email/{$usuario->id}/reenviar");
        $this->post("/verificar-email/{$usuario->id}/reenviar");

        $respuesta = $this->post("/verificar-email/{$usuario->id}/reenviar");

        $respuesta->assertSessionHas('error');
        Mail::assertSentCount(2);
    }

    private function datosDeRegistro(int $numero): array
    {
        return [
            'nombre'                => 'Usuario',
            'apellidos'             => "Numero {$numero}",
            'email'                 => "usuario{$numero}@test.com",
            'username'              => "usuario{$numero}",
            'password'              => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terminos'              => '1',
        ];
    }
}
