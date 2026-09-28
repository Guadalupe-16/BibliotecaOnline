---

description: "Lista de tareas de la spec 006 — auditoría"

---

# Tasks: Formalizar y mejorar el visor de auditoría

**Input**: Documentos de diseño en `specs/006-auditoria/`

**Prerequisites**: plan.md, spec.md

**Estado**: Todas las tareas están **pendientes**. Este Issue (#164) es solo de planeación; estas
tareas se ejecutarán en el Issue de implementación que enlace esta spec.

**Tests**: se generan tareas de prueba Feature para cada brecha identificada en spec.md ("Estado
actual") y para cada acción nueva auditada.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Puede ejecutarse en paralelo (archivos distintos, sin dependencias pendientes)
- **[Story]**: Historia de usuario (US1, US2, US3)

---

## Phase 1: Setup

**Purpose**: Confirmar el alcance exacto antes de tocar los controladores

- [X] T001 Confirmar con el equipo la lista de acciones a auditar de plan.md (5 acciones mínimas) y si
  se incluyen `FavoritoController::toggle` / `PerfilController::actualizar` (hoy fuera de alcance, spec.md)
  — *#167: se implementaron las 5 acciones del plan; `Favorito` y `Perfil` se mantienen fuera de
  alcance, como indica spec.md.*

---

## Phase 2: User Story 3 - Cobertura de pruebas del módulo (Priority: P1) 🎯 primero, cierra una brecha existente

**Goal**: el módulo actual (sin ninguna acción nueva todavía) queda cubierto por pruebas, antes de
agregarle más superficie

**Independent Test**: `php artisan test --filter=ActivityLog` pasa en verde

- [X] T002 [P] [US3] Crear `tests/Feature/ActivityLogTest.php`: `ActivityLog::registrar()` crea una fila
  con `accion`/`descripcion`/`user_id`/`ip` correctos (con sesión y sin sesión)
- [X] T003 [P] [US3] Crear `tests/Feature/ActivityLoggerAccesoTest.php`: `GET /admin/logs` → `403` para
  rol `usuario`, redirección a login sin sesión, `200` para `admin` y para `superadmin`
- [X] T004 [US3] Ejecutar `php artisan test --filter=ActivityLog` y confirmar verde antes de continuar

**Checkpoint**: el módulo existente queda con cobertura de pruebas antes de ampliarlo

---

## Phase 3: User Story 1 - Ver quién hizo qué en el sistema (Priority: P1)

**Goal**: las 5 acciones administrativas de plan.md quedan auditadas

**Independent Test**: cambiar el rol de un usuario y verlo en `/admin/logs`

- [X] T005 [US1] Agregar `ActivityLog::registrar('rbac_cambio_rol', ...)` en
  `app/Http/Controllers/RbacController.php` tras `$usuario->cambiarRol($request->rol)` (línea 36), antes
  del `return back()` de éxito
- [X] T006 [P] [US1] Crear `tests/Feature/RbacAuditoriaTest.php`: cambiar el rol de un usuario genera una
  fila `rbac_cambio_rol` en `activity_logs`
- [X] T007 [US1] Agregar `ActivityLog::registrar('superadmin_toggle_estado', ...)` en
  `SuperAdminController::toggleEstado` (`app/Http/Controllers/SuperAdminController.php:33-36`)
- [X] T008 [US1] Agregar `ActivityLog::registrar('superadmin_cambio_rol', ...)` en
  `SuperAdminController::cambiarRol` (`app/Http/Controllers/SuperAdminController.php:47-49`)
- [X] T009 [P] [US1] Crear `tests/Feature/SuperAdminAuditoriaTest.php`: activar/desactivar y cambiar rol
  desde `/superadmin` generan sus filas correspondientes
- [X] T010 [US1] Agregar `ActivityLog::registrar('usuario_actualizado', ...)` en
  `UsuarioController::update` (`app/Http/Controllers/UsuarioController.php:33`)
- [X] T011 [US1] Agregar `ActivityLog::registrar('usuario_eliminado', ...)` en
  `UsuarioController::destroy` (`app/Http/Controllers/UsuarioController.php:40`)
- [X] T012 [P] [US1] Crear `tests/Feature/UsuarioAuditoriaTest.php`: actualizar y eliminar un usuario
  generan sus filas correspondientes
- [X] T013 [US1] Confirmar (revisión manual + `php artisan test --filter=Activity`) que las 4 acciones
  auditadas hoy (`login`, `logout`, `catalogo`, `busqueda`) siguen registrándose sin cambios (spec.md
  Acceptance Scenario 4)

**Checkpoint**: las 5 acciones administrativas identificadas quedan auditadas y probadas

---

## Phase 4: User Story 2 - Encontrar un registro específico (Priority: P2)

**Goal**: mejoras de búsqueda en el visor, si el equipo las confirma en T001

**Independent Test**: filtrar por un criterio nuevo (p. ej. IP) y ver solo esos registros

- [X] T014 [US2] Evaluar si agregar un filtro por IP a `app/Livewire/ActivityLogger.php` (nuevo
  property `filtroIp` + condición `where('ip', 'like', ...)`), documentando la decisión si se descarta
- [X] T015 [US2] Si se implementa T014, actualizar `resources/views/livewire/activity-logger.blade.php`
  con el campo de filtro correspondiente
- [X] T016 [US2] Si se implementa T014, agregar una prueba de Livewire que confirme el filtrado por IP,
  y confirmar que los filtros existentes (usuario, acción, fechas) no presentan regresión

**Checkpoint**: filtros existentes intactos; filtro nuevo (si se implementa) probado

---

## Dependencies & Execution Order

- **Setup (T001)**: confirma el alcance de US1 y si US2 aplica.
- **US3 (T002–T004)**: se hace primero — cierra la brecha de pruebas existente antes de agregar más
  superficie de código sin probar.
- **US1 (T005–T013)**: depende de US3 completo (mismo patrón de test ya validado en T002).
- **US2 (T014–T016)**: independiente de US1; puede hacerse en paralelo si T001 la confirma.

## Implementation Strategy

1. **MVP = US3 + US1**: cerrar la brecha de pruebas y ampliar la cobertura de auditoría a las acciones
   administrativas reales. Es lo que exige el propósito de la actividad ("quién hizo qué").
2. US2 se agrega en el mismo PR solo si el equipo la confirma en T001; no bloquea el MVP.
3. Ninguna tarea de esta lista modifica el esquema de `activity_logs` (spec.md FR-008, SC-004).

---

## Registro de implementación (Issue #167, rama `feat/167-visor-auditoria`)

| Tareas | Resultado |
|---|---|
| T002–T004 | `ActivityLogTest` (4) y `ActivityLoggerAccesoTest` (4): verdes **antes** de tocar código |
| T005–T013 | 5 acciones auditadas. Pruebas escritas primero (7 fallaban), luego verdes: `RbacAuditoriaTest` (2), `SuperAdminAuditoriaTest` (4), `UsuarioAuditoriaTest` (3). Las descripciones incluyen el rol anterior y el panel de origen (mitigación de riesgos de plan.md) |
| T014–T016 | Filtro por IP (prefijo de subred) implementado. `ActivityLoggerFiltrosTest` (7) cubre IP y confirma que los filtros existentes no regresionan (FR-004) |
| Extra | `<label>` accesibles en los filtros; colores para las acciones nuevas; `registrar(?string $descripcion)` con nulabilidad explícita; eliminado el componente sin uso `components/⚡activity-logger.blade.php` (hallazgos de `docs/cicd/sonarqube/javier-resultados.md`) |
| Suite | PHPUnit: 118 → **142** pruebas, todas verdes |
| Evidencia | `docs/monitoreo/auditoria.md` y `docs/monitoreo/evidencias/auditoria-*.png` |
