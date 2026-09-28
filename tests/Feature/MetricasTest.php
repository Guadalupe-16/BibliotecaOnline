<?php

namespace Tests\Feature;

use App\Models\Libro;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MetricasTest extends TestCase
{
    use RefreshDatabase;

    public function test_metrics_responde_en_formato_prometheus_sin_sesion(): void
    {
        $respuesta = $this->get('/metrics');

        $respuesta->assertOk();
        $this->assertStringStartsWith('text/plain; version=0.0.4', $respuesta->headers->get('Content-Type'));
        $respuesta->assertSee('bibliotecaonline_app_info', false);
        $respuesta->assertSee('bibliotecaonline_queue_jobs_pending', false);
        $respuesta->assertSee('bibliotecaonline_queue_jobs_failed', false);
        $respuesta->assertSee('bibliotecaonline_database_up 1', false);
    }

    public function test_una_solicitud_incrementa_contador_e_histograma(): void
    {
        $this->get('/login')->assertOk();

        $this->get('/metrics')
            ->assertSee('bibliotecaonline_http_requests_total{method="GET",route="/login",status="200"} 1', false)
            ->assertSee('bibliotecaonline_http_request_duration_seconds_count{method="GET",route="/login"} 1', false);
    }

    public function test_el_label_usa_el_patron_de_ruta_y_no_la_url_real(): void
    {
        $libro = Libro::factory()->create();

        $this->get("/libros/{$libro->id}")->assertOk();

        $this->get('/metrics')
            ->assertSee('route="/libros/{libro}"', false)
            ->assertDontSee("route=\"/libros/{$libro->id}\"", false);
    }

    public function test_errores_se_cuentan_por_clase_y_404_sin_ruta_se_agrupa(): void
    {
        $this->get('/no-existe-esta-ruta')->assertNotFound();

        $this->get('/metrics')
            ->assertSee('bibliotecaonline_http_errors_total{clase="4xx"} 1', false)
            ->assertSee('route="sin_ruta",status="404"', false);
    }

    public function test_no_se_miden_metrics_ni_up(): void
    {
        $this->get('/up');
        $this->get('/metrics');

        $this->get('/metrics')
            ->assertDontSee('route="/metrics"', false)
            ->assertDontSee('route="/up"', false);
    }

    public function test_las_metricas_no_exponen_ip_ni_usuario(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.77'])->get('/login');

        $cuerpo = $this->get('/metrics')->getContent();

        $this->assertStringNotContainsString('203.0.113.77', $cuerpo);
        $this->assertDoesNotMatchRegularExpression('/\b(ip|user|user_id|email)="/', $cuerpo);
    }

    public function test_con_token_configurado_exige_bearer(): void
    {
        config(['metricas.token' => 'token-de-prueba']);

        $this->get('/metrics')->assertForbidden();
        $this->withToken('otro')->get('/metrics')->assertForbidden();
        $this->withToken('token-de-prueba')->get('/metrics')->assertOk();
    }

    public function test_en_produccion_sin_token_no_se_expone(): void
    {
        config(['metricas.token' => null]);
        $this->app['env'] = 'production';

        $this->get('/metrics')->assertForbidden();
    }
}
