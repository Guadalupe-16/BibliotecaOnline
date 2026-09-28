<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\RequestTrace;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Validacion integral de monitoreo (#165), trazabilidad (#166) y auditoria (#167) — Issue #168.
 * Cada prueba ejercita una sola solicitud y comprueba que los tres modulos la registran de forma
 * coherente y sin pisarse entre si.
 */
class IntegracionObservabilidadTest extends TestCase
{
    use RefreshDatabase;

    public function test_una_accion_administrativa_queda_en_metricas_trazas_y_auditoria(): void
    {
        $superadmin = User::factory()->create(['rol' => 'superadmin', 'name' => 'Sofia Super']);
        $usuario    = User::factory()->create(['rol' => 'usuario', 'name' => 'Luis Lector']);

        $respuesta = $this->actingAs($superadmin)
            ->post("/superadmin/usuarios/{$usuario->id}/cambiar-rol/admin");

        // Trazabilidad: la respuesta trae su trace_id y existe la traza tecnica
        $traceId = $respuesta->headers->get('X-Trace-Id');
        $this->assertNotEmpty($traceId);
        $traza = RequestTrace::where('trace_id', $traceId)->firstOrFail();
        $this->assertSame('POST', $traza->metodo);
        $this->assertSame($superadmin->id, $traza->user_id);
        $this->assertSame(302, $traza->status_http);
        $this->assertSame('ok', $traza->resultado);

        // Auditoria: el mismo cambio queda como accion de negocio, con autor
        $this->assertDatabaseHas('activity_logs', [
            'accion'  => 'superadmin_cambio_rol',
            'user_id' => $superadmin->id,
        ]);

        // Monitoreo: la solicitud se cuenta con el patron de ruta, sin IDs ni usuario
        $this->get('/metrics')
            ->assertSee(
                'bibliotecaonline_http_requests_total{method="POST",route="/superadmin/usuarios/{id}/cambiar-rol/{rol}",status="302"} 1',
                false
            );
    }

    public function test_cada_modulo_guarda_solo_lo_que_le_corresponde(): void
    {
        $superadmin = User::factory()->create(['rol' => 'superadmin']);
        $usuario    = User::factory()->create(['rol' => 'usuario']);

        $this->actingAs($superadmin)->post("/superadmin/usuarios/{$usuario->id}/toggle-estado");

        // La auditoria no guarda datos tecnicos (spec 006, FR-007)...
        $descripcion = ActivityLog::where('accion', 'superadmin_toggle_estado')->value('descripcion');
        $this->assertStringNotContainsString('toggle-estado', $descripcion);
        $this->assertStringNotContainsString('302', $descripcion);

        // ...y las metricas no guardan IP ni usuario (spec 004, FR-003)
        $metricas = $this->get('/metrics')->getContent();
        $this->assertStringNotContainsString("/usuarios/{$usuario->id}/", $metricas);
        $this->assertDoesNotMatchRegularExpression('/\b(ip|user_id|usuario)="/', $metricas);
    }

    public function test_404_de_recurso_inexistente_se_traza_y_se_cuenta_pero_no_se_audita(): void
    {
        $respuesta = $this->get('/libros/999999');

        $respuesta->assertNotFound();
        $this->assertDatabaseHas('request_traces', [
            'trace_id'    => $respuesta->headers->get('X-Trace-Id'),
            'status_http' => 404,
        ]);
        $this->get('/metrics')
            ->assertSee('route="/libros/{libro}",status="404"', false)
            ->assertSee('bibliotecaonline_http_errors_total{clase="4xx"} 1', false);
        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_url_sin_ruta_no_se_traza_pero_el_monitoreo_la_cubre(): void
    {
        // Limitacion documentada de la trazabilidad (spec 005, Edge Cases): una URL que no coincide
        // con ninguna ruta no entra al grupo web. El middleware global de metricas si la cuenta.
        $respuesta = $this->get('/no-existe-esta-pagina');

        $respuesta->assertNotFound();
        $this->assertNull($respuesta->headers->get('X-Trace-Id'));
        $this->assertDatabaseCount('request_traces', 0);
        $this->get('/metrics')->assertSee('route="sin_ruta",status="404"', false);
    }

    public function test_el_scraping_de_metrics_no_genera_trazas_ni_auditoria(): void
    {
        $this->get('/metrics')->assertOk();
        $this->get('/metrics')->assertOk();

        $this->assertDatabaseCount('request_traces', 0);
        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_los_tres_visores_estan_protegidos_por_rol(): void
    {
        $usuario = User::factory()->create(['rol' => 'usuario']);
        $admin   = User::factory()->create(['rol' => 'admin']);

        $visores = ['/admin/logs', '/admin/trazas'];

        foreach ($visores as $visor) {
            $this->get($visor)->assertRedirect('/login');
        }
        foreach ($visores as $visor) {
            $this->actingAs($usuario)->get($visor)->assertForbidden();
        }
        foreach ($visores as $visor) {
            $this->actingAs($admin)->get($visor)->assertOk();
        }

        // /metrics no usa sesion: en produccion sin token no se expone
        config(['metricas.token' => 'secreto']);
        $this->get('/metrics')->assertForbidden();
    }
}
