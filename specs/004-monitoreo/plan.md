# Implementation Plan: Monitoreo de la aplicación

**Branch**: Por definir | **Date**: 2026-09-27 | **Spec**: [spec.md](spec.md)

**Input**: Feature specification from `specs/004-monitoreo/spec.md`

**Issue**: #164 (planeación). Este plan describe el diseño técnico; la implementación real se ejecuta
en un Issue separado que enlace esta spec.

## Summary

Exponer un endpoint `GET /metrics` en formato Prometheus con contadores/histogramas de solicitudes
HTTP, errores y trabajos de cola, y construir en Grafana un dashboard y al menos una alerta sobre esas
métricas. Herramienta elegida: **Prometheus + Grafana**, justificada en [research.md](research.md)
frente a Nagios, Zabbix y Datadog. No se instala nada en este Issue.

## Technical Context

**Language/Version**: PHP 8.2 (Laravel 12), YAML/PromQL para la configuración de Prometheus (propuesta,
fuera del repo de aplicación o en un directorio `monitoring/` a definir)

**Primary Dependencies (propuestas, no instaladas)**: una librería de cliente Prometheus para PHP
(candidata: `promphp/prometheus_client_php` o un paquete Laravel que la envuelva) + un *storage
adapter* compartido entre procesos (APCu o Redis; se descarta almacenamiento en archivo por no ser
seguro entre workers concurrentes de Apache)

**Storage**: las métricas viven en memoria del proceso de métricas (APCu/Redis), no en MySQL; los
contadores de cola se leen de las tablas ya existentes `jobs` y `failed_jobs` (driver `database`)

**Testing**: pruebas Feature de Laravel que verifiquen que `/metrics` responde 200, contiene los
nombres de métrica esperados y no requiere sesión; sin pruebas de Prometheus/Grafana en sí (herramientas
externas, se validan por inspección manual del dashboard)

**Target Platform**: mismo contenedor `app` de `docker-compose.release.yml` (ruta HTTP adicional);
Prometheus y Grafana como servicios nuevos del *stack* de monitoreo (PLANEADO, no en este Issue)

**Project Type**: aplicación web monolítica Laravel + servicios de observabilidad externos

**Performance Goals**: el endpoint `/metrics` no MUST agregar latencia perceptible a las peticiones de
usuario (la instrumentación ocurre en middleware ligero; el costo se paga solo al ser scrapeado)

**Constraints**: sin secretos en el repositorio; labels sin datos personales (spec FR-003); el
dashboard vive en Grafana, no en una vista Laravel (spec FR-006)

**Scale/Scope**: 1 endpoint nuevo, 1 middleware de instrumentación (posiblemente compartido con la
spec 005 de trazabilidad, ver Riesgos), 1 dashboard de Grafana, ≥1 regla de alerta

## Constitution Check

*GATE: Debe pasar antes de research (ya cubierto arriba) y tras el diseño.*

| Principio | Cumplimiento | Estado |
|---|---|---|
| I. Especificación antes de código | Esta spec se escribe antes de instalar Prometheus/Grafana o tocar código | ✅ |
| II. Trazabilidad | Issue #164 (planeación) → `specs/004-monitoreo/` → Issue de implementación futuro → rama `tipo/NNN-...` → PR con `Closes #NNN` | ✅ |
| III. Protección de ramas | Esta rama de planeación va a `develop` por PR; la implementación futura también | ✅ |
| IV. Pruebas obligatorias | La implementación futura incluye pruebas Feature del endpoint `/metrics` (ver tasks.md de esa fase) | ✅ (planeado) |
| V. Migraciones para el esquema | No aplica: no se crean tablas nuevas para monitoreo | ✅ N/A |
| VI. Seguridad por rol / sin secretos | `/metrics` no expone datos personales ni secretos (FR-003); no requiere `.env` nuevo con credenciales de terceros (se descarta Datadog por esto) | ✅ |
| VII. CI en verde | No aplica todavía (sin código); la implementación futura no debe romper `ci.yml` | ✅ N/A |
| VIII. Evidencia honesta | Todo este módulo se etiqueta PLANEADO; no se afirma que exista dashboard ni alerta funcionando | ✅ |

**Re-check post-diseño**: sin cambios; ninguna violación introducida.

## Project Structure

### Documentation (this feature)

```text
specs/004-monitoreo/
├── spec.md
├── plan.md          # Este archivo
├── research.md       # Comparación de herramientas y decisiones técnicas
└── tasks.md          # /speckit-tasks
```

No se agregan `data-model.md` ni `contracts/`: las métricas no son un modelo de datos de la aplicación
(no hay tabla ni Eloquent model nuevo) y su "contrato" es el formato estándar de exposición de
Prometheus, ya documentado en `research.md` §3.

### Áreas del sistema que probablemente se modificarán (propuesto, implementación futura)

```text
app/Http/Middleware/
└── InstrumentarMetricas.php        # PROPUESTO: registra contador + histograma por request

routes/
└── web.php                          # PROPUESTO: Route::get('/metrics', ...) sin middleware 'auth'

app/Console/Commands/
└── ExportarMetricasCola.php        # PROPUESTO (opcional): job/gauge de jobs/failed_jobs si no se
                                      # instrumenta directamente vía eventos JobProcessed/JobFailed

monitoring/                          # PROPUESTO, fuera de la app Laravel
├── prometheus.yml                   # scrape_configs apuntando a /metrics
├── alerts.yml                       # reglas de alerta (PromQL)
└── grafana/
    └── dashboard-bibliotecaonline.json

docker-compose.release.yml           # PROPUESTO: servicios prometheus/grafana, no en este Issue
```

**Structure Decision**: la instrumentación vive dentro de la app Laravel (middleware + ruta), mientras
que la configuración de Prometheus/Grafana vive en un directorio `monitoring/` separado del código de
la aplicación, siguiendo el mismo patrón que `docker/` para la imagen de liberación (configuración de
infraestructura fuera de `app/`).

## Middleware, servicios y rutas (propuesto)

- **Middleware**: uno solo, registrado globalmente para rutas `web` (excluyendo `/metrics` y `/up` para
  no medir el propio scraping), que en `terminate()` incrementa el contador y observa la duración —
  igual patrón que Laravel usa para `TerminableMiddleware`, evitando bloquear la respuesta al usuario.
- **Servicio**: una clase `MetricsRegistry` (o el *registry* que provea la librería elegida) inyectada
  donde se necesite, para no acoplar el middleware a una librería concreta.
- **Rutas**: `GET /metrics`, sin grupo `auth` (Prometheus no maneja cookies); protegida a nivel de red o
  proxy (decisión de la implementación, ver `research.md` §4).

## Pruebas (propuesto para la implementación)

- Feature test: `GET /metrics` responde `200`, `Content-Type: text/plain; version=0.0.4`, y el cuerpo
  contiene los nombres de métrica de FR-002.
- Feature test: una petición a una ruta cualquiera incrementa el contador correspondiente (se lee el
  *registry* en memoria dentro del mismo proceso de test, o se usa el adaptador en memoria de la
  librería para pruebas).
- Sin pruebas automatizadas de Grafana/Prometheus en sí; se validan por inspección manual documentada
  en un futuro `quickstart.md` de la implementación.

## Seguridad

- `/metrics` no autenticado por sesión, pero tampoco expuesto públicamente sin control: en el entorno
  de liberación, el puerto de métricas no se publica al host o se restringe por IP/proxy.
- Ningún label incluye IP, user-agent completo ni ID de usuario (FR-003).
- No se agregan credenciales de terceros al `.env` (se descartó Datadog precisamente por esto).

## Rendimiento

- El middleware de instrumentación debe añadir un overhead despreciable (microsegundos) frente al
  tiempo de una petición típica; se mide en la implementación con el mismo mecanismo de k6 ya usado en
  `scripts/test-release.sh`.
- El histograma de duración usa *buckets* razonables para una app web (p. ej. 5 ms a 5 s), alineados
  con el umbral NS-1 (`p95 < 5000 ms`).

## Compatibilidad con CI/CD

- El job `e2e-playwright` y el pipeline de liberación no cambian por este módulo; en la implementación,
  se agrega como mucho una prueba Feature nueva al job `php-tests` existente.
- El stack de Prometheus/Grafana, al ser PLANEADO para un entorno permanente que hoy no existe
  (`docs/cicd/pipeline-liberacion-despliegue.md` §13), no se agrega a `docker-compose.release.yml` en
  este Issue ni en su implementación inmediata, salvo que el equipo decida lo contrario en esa spec.

## Riesgos

| Riesgo | Mitigación propuesta |
|---|---|
| Instrumentar dos veces (métricas de esta spec + trazas de la spec 005) duplica middleware y overhead | Evaluar en la implementación un único middleware que alimente tanto el contador/histograma de Prometheus como el registro de trazabilidad, o documentar por qué se mantienen separados |
| Alta cardinalidad si se usan URLs en vez de nombres de ruta | FR-003 lo prohíbe explícitamente; revisar en code review de la implementación |
| Exponer `/metrics` públicamente sin querer | Definir su protección de red/proxy como tarea explícita antes de desplegar (tasks.md) |
| Librería de cliente Prometheus para PHP mal mantenida o incompatible con PHP 8.2/Laravel 12 | Confirmar versión y compatibilidad en el `research.md` de la implementación antes de agregarla a `composer.json` |

## Criterios de validación

- Existen `spec.md`, `plan.md`, `research.md` y `tasks.md` en `specs/004-monitoreo/` (este Issue).
- Ningún archivo de código de la aplicación fue modificado por este Issue.
- La implementación futura se considera completa cuando: `/metrics` responde con las métricas de
  FR-002, el dashboard de Grafana muestra datos reales y al menos una alerta se dispara ante la
  condición simulada correspondiente (ver User Story 2 de `spec.md`).
