---

description: "Lista de tareas de la spec 005 — trazabilidad técnica de solicitudes"

---

# Tasks: Visor de trazabilidad técnica de solicitudes

**Input**: Documentos de diseño en `specs/005-trazabilidad/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md

**Estado**: Implementado en el Issue #166 (rama `feat/166-trazabilidad`). Todas las tareas de este
documento fueron ejecutadas; ver `docs/monitoreo/trazabilidad.md` para el resumen funcional y
"Decisiones o desviaciones del spec" en el reporte del Issue #166 para los ajustes de diseño hechos
durante la implementación.

**Tests**: se generan tareas de prueba Feature (middleware, control de acceso, comando de poda) y,
opcionalmente, un test de Livewire para el visor.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Puede ejecutarse en paralelo (archivos distintos, sin dependencias pendientes)
- **[Story]**: Historia de usuario (US1, US2, US3)

---

## Phase 1: Setup

**Purpose**: Confirmar decisiones de diseño antes de crear la migración

- [X] T001 Confirmar con el equipo el umbral de retención (propuesto 30 días, research.md §5) y si
  `user_agent` se incluye en esta primera versión (research.md §6)
  — **Decisión (Issue #166)**: se mantiene el default propuesto de 30 días
  (`TRAZABILIDAD_RETENCION_DIAS`, configurable) y se incluye `user_agent`, truncado a 255 caracteres.

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Esquema, modelo y persistencia asíncrona que todas las historias necesitan

- [X] T002 Crear la migración `database/migrations/xxxx_xx_xx_create_request_traces_table.php` según
  data-model.md (campos, índices en `created_at`, `ruta`+`status_http`, único en `trace_id`)
- [X] T003 [P] Crear `app/Models/RequestTrace.php` (fillable, relación `user()`, sin `updated_at`)
- [X] T004 [P] Crear `app/Jobs/LogRequestTraceJob.php` (`ShouldQueue`, mismo patrón que
  `App\Jobs\LogActivityJob`)
- [X] T005 Crear `app/Http/Middleware/RegistrarTrazabilidad.php`: genera `trace_id`, mide duración en
  `terminate()`, calcula `resultado` (`ok`/`error` según `status_http >= 500` o excepción no
  controlada) y despacha `LogRequestTraceJob` con datos ya serializados (sin objetos `Request`/
  `Response` completos)
- [X] T006 Registrar el middleware en el grupo `web` en `bootstrap/app.php`
  — **Desviación (Issue #166)**: se antepone (`prepend`) en vez de anexar (`append`) al grupo `web`,
  para capturar también peticiones que fallan por *route model binding* antes de llegar al
  controlador (ver "Decisiones o desviaciones del spec" del reporte del Issue). `/up` ya queda
  excluido de forma natural (no pasa por el grupo `web`); `/metrics` no existe todavía (spec 004 sin
  implementar).
- [X] T007 [P] Feature test: una petición cualquiera genera una fila en `request_traces` con los campos
  esperados (usar `Bus::fake()`/`Queue::fake()` o ejecutar el job de forma síncrona en el test)

**Checkpoint**: cada petición queda trazada de forma asíncrona, sin bloquear la respuesta

---

## Phase 3: User Story 1 - Diagnosticar una petición lenta o fallida (Priority: P1) 🎯 MVP

**Goal**: Visor con listado, filtros, paginación y detalle

**Independent Test**: generar una petición fallida y encontrarla en el visor filtrando por resultado

- [X] T008 [US1] Crear `app/Livewire/TrazabilidadViewer.php` (`WithPagination`; filtros por ruta,
  resultado, rango de fechas y, opcionalmente, usuario; mismo patrón que
  `app/Livewire/ActivityLogger.php`)
- [X] T009 [US1] Crear `resources/views/livewire/trazabilidad-viewer.blade.php` con el listado paginado
  y un enlace/expansión al detalle de cada traza
- [X] T010 [US1] Crear `resources/views/trazas/index.blade.php` (layout con `@include('components.navigation')`,
  igual patrón que `resources/views/logs/index.blade.php`)
- [X] T011 [US1] Feature test: filtrar por rango de fechas y por resultado devuelve solo las trazas
  esperadas (test de Livewire sobre `TrazabilidadViewer`)

**Checkpoint**: el visor permite encontrar una traza específica por sus filtros

---

## Phase 4: User Story 2 - Acceso restringido al visor (Priority: P1)

**Goal**: solo `admin`/`superadmin` acceden al visor

**Independent Test**: `403` para rol `usuario`, `200` para `admin`/`superadmin`

- [X] T012 [US2] Agregar la ruta `GET /admin/trazas` en `routes/web.php`, dentro del grupo
  `Route::middleware(['auth', 'role:admin,superadmin'])` ya existente (junto a `admin.logs`)
- [X] T013 [US2] Feature test: `GET /admin/trazas` responde `403` para un usuario con rol `usuario`,
  redirige a login sin sesión, y responde `200` para `admin`/`superadmin`
- [X] T014 [US2] Agregar el enlace al visor en la navegación administrativa (`components.navigation`),
  visible solo para los roles permitidos

**Checkpoint**: control de acceso verificado por prueba automatizada

---

## Phase 5: User Story 3 - La trazabilidad no degrada el sistema (Priority: P1)

**Goal**: retención automática y confirmación de que no hay escritura síncrona ni datos sensibles

**Independent Test**: ejecutar el comando de poda y verificar que borra solo lo antiguo

- [X] T015 [US3] Crear `app/Console/Commands/PodarTrazasCommand.php` (`trazas:podar`, umbral
  configurable por variable de entorno, default confirmado en T001)
- [X] T016 [US3] Programar el comando en `routes/console.php` con `Schedule::command('trazas:podar')->daily()`
- [X] T017 [US3] Test del comando: registros más antiguos que el umbral se borran, registros recientes
  se conservan
- [X] T018 [US3] Revisar (code review checklist) que `LogRequestTraceJob` y `RequestTrace` no incluyen
  contraseñas, tokens, cookies ni cuerpos de petición/respuesta (FR-003); dejar constancia en el PR

**Checkpoint**: retención automática probada; revisión de datos sensibles sin hallazgos

---

## Dependencies & Execution Order

- **Setup (T001)**: bloquea T002 (define columnas opcionales) y T015 (define el umbral default).
- **Foundational (T002–T007)**: bloquea US1, US2 y US3.
- **US1 (T008–T011)**: puede avanzar en paralelo con US2 una vez completado Foundational.
- **US2 (T012–T014)**: depende de que exista la ruta base del visor (T008–T010) para protegerla y
  enlazarla.
- **US3 (T015–T018)**: independiente de US1/US2 en su mayoría; T018 se hace al final, sobre el código
  ya escrito en Foundational.

## Implementation Strategy

1. **MVP = Foundational + US1**: trazas capturadas + visor funcional.
2. US2 (control de acceso) se implementa junto con US1 en el mismo PR, no después: una ruta sin
   protección de rol no debe llegar a `develop`.
3. US3 (retención y revisión de datos sensibles) cierra el PR antes de solicitar revisión.
