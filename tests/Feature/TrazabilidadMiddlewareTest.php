<?php

namespace Tests\Feature;

use App\Models\RequestTrace;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Issue #166 / specs/005-trazabilidad/: el middleware RegistrarTrazabilidad
 * debe registrar cada petición web sin bloquear la respuesta al usuario.
 */
class TrazabilidadMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_una_peticion_genera_una_traza(): void
    {
        $this->get(route('catalogo'))->assertOk();

        $this->assertSame(1, RequestTrace::count());

        $traza = RequestTrace::first();
        $this->assertSame('GET', $traza->metodo);
        $this->assertSame('catalogo', $traza->ruta);
        $this->assertSame(200, $traza->status_http);
        $this->assertSame('ok', $traza->resultado);
    }

    public function test_se_genera_un_trace_id_y_se_expone_en_la_respuesta(): void
    {
        $respuesta = $this->get(route('catalogo'));

        $traza = RequestTrace::first();

        $this->assertNotEmpty($traza->trace_id);
        $respuesta->assertHeader('X-Trace-Id', $traza->trace_id);
    }

    public function test_metodo_y_ruta_se_guardan_correctamente(): void
    {
        $this->get(route('buscar'));

        $traza = RequestTrace::first();

        $this->assertSame('GET', $traza->metodo);
        $this->assertSame('buscar', $traza->ruta);
    }

    public function test_status_http_se_registra_para_un_recurso_inexistente(): void
    {
        // Ruta que SÍ existe (coincide con el patrón /libros/{libro}) pero cuyo
        // binding de modelo falla: pasa por el middleware y responde 404.
        // Una URL que no coincide con ninguna ruta nunca entra al pipeline de
        // middleware, así que no generaría traza (limitación documentada en
        // specs/005-trazabilidad/spec.md, Edge Cases).
        $this->get('/libros/999999')->assertNotFound();

        $traza = RequestTrace::first();

        $this->assertSame(404, $traza->status_http);
        // 404 es un error de cliente, no un fallo del servidor: resultado sigue 'ok'.
        $this->assertSame('ok', $traza->resultado);
        $this->assertSame('error_cliente', $traza->categoria);
    }

    public function test_la_duracion_en_milisegundos_se_registra(): void
    {
        $this->get(route('catalogo'));

        $traza = RequestTrace::first();

        $this->assertIsInt($traza->duracion_ms);
        $this->assertGreaterThanOrEqual(0, $traza->duracion_ms);
    }

    public function test_el_usuario_autenticado_se_registra(): void
    {
        $usuario = User::factory()->create();

        $this->actingAs($usuario)->get(route('catalogo'));

        $traza = RequestTrace::first();

        $this->assertSame($usuario->id, $traza->user_id);
    }

    public function test_una_peticion_anonima_registra_usuario_nulo(): void
    {
        $this->get(route('catalogo'));

        $traza = RequestTrace::first();

        $this->assertNull($traza->user_id);
        $this->assertSame(200, $traza->status_http);
    }

    public function test_informacion_sensible_no_se_persiste(): void
    {
        $passwordSecreta = 'ClaveSuperSecreta123!';

        $this->post('/login', [
            'email'    => 'no-existe@example.com',
            'password' => $passwordSecreta,
        ]);

        $traza = RequestTrace::first();

        $this->assertNotNull($traza);

        $valores = collect($traza->getAttributes())->implode(' ');
        $this->assertStringNotContainsString($passwordSecreta, $valores);

        // request_traces no tiene ninguna columna de payload/headers/cookies.
        $columnas = array_keys($traza->getAttributes());
        $this->assertEqualsCanonicalizing(
            ['id', 'trace_id', 'metodo', 'ruta', 'user_id', 'ip', 'status_http', 'duracion_ms', 'resultado', 'error_referencia', 'user_agent', 'created_at'],
            $columnas
        );
    }

    public function test_un_fallo_al_registrar_la_traza_no_afecta_la_respuesta(): void
    {
        // Simula que la persistencia de la traza falla (p. ej. la tabla no
        // está disponible): la petición debe seguir respondiendo con
        // normalidad al usuario (spec.md, Historia 3).
        Schema::drop('request_traces');

        $this->get(route('catalogo'))->assertOk();
    }
}
