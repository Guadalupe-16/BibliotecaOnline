<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerificacionPinMail;
use Tests\TestCase;

class VerificacionEmailPinTest extends TestCase
{
    use RefreshDatabase;

    public function test_registro_crea_usuario_sin_verificar_y_envia_pin(): void
    {
        Mail::fake();

        $respuesta = $this->post('/register', [
            'nombre'               => 'Joel',
            'apellidos'            => 'Ibarra',
            'email'                => 'joel@test.com',
            'username'             => 'joel_ibarra',
            'password'             => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terminos'             => '1',
        ]);

        $respuesta->assertRedirect();
        $this->assertDatabaseHas('users', ['email' => 'joel@test.com']);

        $usuario = User::where('email', 'joel@test.com')->first();
        $this->assertNull($usuario->email_verified_at);

        Mail::assertSent(VerificacionPinMail::class);
    }

    public function test_usuario_sin_verificar_no_puede_loguearse(): void
    {
        $usuario = User::factory()->create(['email_verified_at' => null]);

        $respuesta = $this->post('/login', [
            'email'    => $usuario->email,
            'password' => 'password',
        ]);

        $respuesta->assertRedirect(route('verificar.email.mostrar', $usuario->id));
        $this->assertGuest();
    }

    public function test_usuario_verificado_puede_loguearse(): void
    {
        $usuario = User::factory()->create(['email_verified_at' => now()]);

        $respuesta = $this->post('/login', [
            'email'    => $usuario->email,
            'password' => 'password',
        ]);

        $respuesta->assertRedirect(route('catalogo'));
        $this->assertAuthenticatedAs($usuario);
    }

    public function test_pin_correcto_verifica_la_cuenta(): void
    {
        $usuario = User::factory()->create(['email_verified_at' => null]);
        Cache::put("email_pin_{$usuario->id}", 123456, now()->addMinutes(15));

        $respuesta = $this->post("/verificar-email/{$usuario->id}", ['pin' => '123456']);

        $respuesta->assertRedirect(route('catalogo'));
        $this->assertNotNull($usuario->fresh()->email_verified_at);
        $this->assertAuthenticatedAs($usuario);
    }

    public function test_pin_incorrecto_retorna_error(): void
    {
        $usuario = User::factory()->create(['email_verified_at' => null]);
        Cache::put("email_pin_{$usuario->id}", 123456, now()->addMinutes(15));

        $respuesta = $this->post("/verificar-email/{$usuario->id}", ['pin' => '999999']);

        $respuesta->assertSessionHasErrors('pin');
        $this->assertNull($usuario->fresh()->email_verified_at);
    }

    public function test_reenvio_pin_envia_nuevo_correo(): void
    {
        Mail::fake();
        $usuario = User::factory()->create(['email_verified_at' => null]);

        $respuesta = $this->post("/verificar-email/{$usuario->id}/reenviar");

        $respuesta->assertRedirect();
        Mail::assertSent(VerificacionPinMail::class);
    }
}
