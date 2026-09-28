---

description: "Lista de tareas de la spec 004 — monitoreo de la aplicación"

---

# Tasks: Monitoreo de la aplicación

**Input**: Documentos de diseño en `specs/004-monitoreo/`

**Prerequisites**: plan.md, spec.md, research.md

**Estado**: Todas las tareas están **pendientes**. Este Issue (#164) es solo de planeación; estas
tareas se ejecutarán en el Issue de implementación que enlace esta spec.

**Tests**: se generan tareas de prueba Feature para el endpoint `/metrics`; el dashboard y las alertas
de Grafana se validan por inspección manual (no hay framework de pruebas para PromQL en este stack).

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Puede ejecutarse en paralelo (archivos distintos, sin dependencias pendientes)
- **[Story]**: Historia de usuario (US1, US2, US3)

---

## Phase 1: Setup

**Purpose**: Elegir y validar la librería de instrumentación antes de escribir código de producto

- [ ] T001 Evaluar y elegir la librería de cliente Prometheus para PHP/Laravel (candidatas en
  research.md §2), confirmar compatibilidad con PHP 8.2 y Laravel 12, y registrar la decisión con
  versión exacta en un `research.md` de la implementación
- [ ] T002 Decidir el *storage adapter* (APCu vs Redis) para compartir contadores entre procesos de
  Apache y documentar la decisión (ninguno de los dos está instalado hoy; verificar disponibilidad de
  la extensión APCu en la imagen `php:8.2-apache` del Dockerfile)

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Instrumentación mínima que las historias 1 y 2 necesitan

- [ ] T003 Agregar la dependencia elegida en T001 a `composer.json` y `composer.lock`
- [ ] T004 Crear `app/Http/Middleware/InstrumentarMetricas.php`: registra
  `http_requests_total{method,route,status}` y observa `http_request_duration_seconds{route}` en
  `terminate()`, excluyendo las rutas `/metrics` y `/up`
- [ ] T005 Registrar el middleware en `bootstrap/app.php` para el grupo `web`
- [ ] T006 [P] Crear la ruta `GET /metrics` en `routes/web.php`, fuera de cualquier grupo `auth`,
  devolviendo el formato de texto de Prometheus
- [ ] T007 [P] Escribir el Feature test que verifica que `GET /metrics` responde `200` con
  `Content-Type` de Prometheus y contiene los nombres de métrica de spec.md FR-002

**Checkpoint**: `/metrics` responde con contadores/histogramas reales tras tráfico de prueba

---

## Phase 3: User Story 1 - Ver el estado general de la aplicación (Priority: P1) 🎯 MVP

**Goal**: Dashboard de Grafana con los paneles mínimos de spec.md FR-004

**Independent Test**: abrir el dashboard con tráfico real y ver los 5 paneles con datos

- [ ] T008 [US1] Crear `monitoring/prometheus.yml` con el `scrape_config` apuntando a `/metrics` del
  contenedor `app`
- [ ] T009 [US1] Agregar contador de trabajos de cola procesados/fallidos: instrumentar los eventos
  `JobProcessed` y `JobFailed` de Laravel (o leer `jobs`/`failed_jobs` con un comando programado), según
  lo que T001 haga más simple
- [ ] T010 [US1] Crear `monitoring/grafana/dashboard-bibliotecaonline.json` con los paneles: solicitudes
  por minuto, errores 4xx/5xx, latencia promedio, p95 (`histogram_quantile`), disponibilidad (`up`)
- [ ] T011 [US1] Documentar en `docs/monitoreo/` cómo levantar Prometheus + Grafana localmente para
  validar el dashboard (servicios adicionales, no en `docker-compose.release.yml` todavía; ver plan.md
  Riesgos)

**Checkpoint**: dashboard visible con datos reales de una sesión de prueba local

---

## Phase 4: User Story 2 - Recibir una alerta ante un problema real (Priority: P2)

**Goal**: al menos una alerta se dispara ante la condición simulada

**Independent Test**: detener el contenedor `app` o forzar 5xx y ver la alerta en estado `firing`

- [ ] T012 [US2] Crear `monitoring/alerts.yml` con la regla de disponibilidad (`up == 0` durante N
  minutos) y la regla de latencia basada en el umbral NS-1 (`p95 < 5000 ms`,
  `docs/cicd/pipeline-liberacion-despliegue.md` §5)
- [ ] T013 [US2] Definir y documentar el umbral de tasa de error 5xx como decisión del equipo (spec.md
  FR-005) y agregarlo a `monitoring/alerts.yml`
- [ ] T014 [US2] Validar manualmente cada alerta: simular la condición y registrar el resultado (estado
  `firing` alcanzado) en un `quickstart.md` de la implementación

**Checkpoint**: las 2–3 alertas definidas se disparan ante su condición simulada

---

## Phase 5: User Story 3 - No exponer información sensible (Priority: P1)

**Goal**: `/metrics` no filtra datos personales ni requiere exposición pública sin control

**Independent Test**: inspeccionar la salida de `/metrics` y la configuración de red del entorno de
liberación

- [ ] T015 [US3] Revisar cada métrica instrumentada en T004/T009 y confirmar que ningún label usa IP,
  user-agent completo o ID de usuario (solo ruta/método/estado)
- [ ] T016 [US3] Decidir y documentar cómo se restringe el acceso a `/metrics` en el entorno de
  liberación (no publicar el puerto, o autenticación a nivel de proxy) antes de agregar servicios de
  monitoreo a `docker-compose.release.yml`

**Checkpoint**: revisión de seguridad documentada, sin hallazgos pendientes

---

## Dependencies & Execution Order

- **Setup (T001–T002)**: bloquea todo lo demás (elige la librería con la que se escribe el middleware).
- **Foundational (T003–T007)**: bloquea US1, US2 y US3 (todas dependen del endpoint `/metrics`).
- **US1 (T008–T011)**: puede avanzar en paralelo con US2 una vez completado Foundational.
- **US2 (T012–T014)**: depende de que existan las métricas de US1 (T009 en particular, para el gauge de
  cola, si alguna alerta la usa).
- **US3 (T015–T016)**: transversal; T015 puede hacerse tan pronto exista T004, en paralelo con US1/US2.

## Implementation Strategy

1. **MVP = Foundational + US1**: endpoint de métricas + dashboard visible.
2. US2 (alertas) y US3 (seguridad) se agregan en el mismo PR de implementación, sin bloquear el MVP.
3. El stack de Prometheus/Grafana como servicios permanentes de `docker-compose.release.yml` queda
   fuera de esta primera implementación (ver plan.md Riesgos); se valida localmente primero.
