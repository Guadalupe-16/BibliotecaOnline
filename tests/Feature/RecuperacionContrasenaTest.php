<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class RecuperacionContrasenaTest extends TestCase
{
    use RefreshDatabase;

    public function test_formulario_forgot_password_se_muestra(): void
    {
        $respuesta = $this->get('/forgot-password');

        $respuesta->assertStatus(200);
    }

    public function test_envia_enlace_a_email_existente(): void
    {
        $usuario = User::factory()->create();

        $respuesta = $this->post('/forgot-password', [
            'email' => $usuario->email,
        ]);

        $respuesta->assertSessionHas('status');
    }

    public function test_error_si_email_no_existe(): void
    {
        $respuesta = $this->post('/forgot-password', [
            'email' => 'noexiste@correo.com',
        ]);

        $respuesta->assertSessionHasErrors('email');
    }

    public function test_formulario_reset_password_se_muestra(): void
    {
        $usuario = User::factory()->create();
        $token = Password::createToken($usuario);

        $respuesta = $this->get("/reset-password/{$token}");

        $respuesta->assertStatus(200);
    }

    public function test_resetea_contrasena_correctamente(): void
    {
        $usuario = User::factory()->create();
        $token = Password::createToken($usuario);

        $respuesta = $this->post('/reset-password', [
            'token'                 => $token,
            'email'                 => $usuario->email,
            'password'              => 'NuevaPass123!',
            'password_confirmation' => 'NuevaPass123!',
        ]);

        $respuesta->assertRedirect('/login');
        $respuesta->assertSessionHas('status');
    }

    public function test_error_si_token_invalido(): void
    {
        $usuario = User::factory()->create();

        $respuesta = $this->post('/reset-password', [
            'token'                 => 'token-invalido',
            'email'                 => $usuario->email,
            'password'              => 'NuevaPass123!',
            'password_confirmation' => 'NuevaPass123!',
        ]);

        $respuesta->assertSessionHasErrors('email');
    }
}
