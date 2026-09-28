# Feature Specification: Formalizar y mejorar el visor de auditoría

**Feature Branch**: `feat/167-visor-auditoria` (implementación, Issue #167)

**Created**: 2026-09-27

**Status**: Implementado (Issue #167) — planeada en el Issue #164

**Input**: Actividad 3.1: diseñar con SDD el módulo de auditoría de BibliotecaOnline. El sistema **ya
existe parcialmente** (`app/Models/ActivityLog.php`, `App\Jobs\LogActivityJob`,
`app/Livewire/ActivityLogger.php`, ruta `/admin/logs`); esta spec formaliza qué hay, qué le falta y qué
se mejora, sin diseñar un segundo sistema de auditoría ni duplicar la trazabilidad técnica (spec 005).
Este Issue **no** modifica el código de auditoría; solo produce spec, plan y tasks.

**Issue**: #164 (planeación). La implementación se abrirá en un Issue nuevo que enlace este spec.

## Estado actual (verificado en el repositorio, no supuesto)

| Elemento | Archivo | Estado |
|---|---|---|
| Modelo | `app/Models/ActivityLog.php` | **IMPLEMENTADO** — campos `accion`, `descripcion`, `user_id`, `ip`; método estático `registrar()` |
| Persistencia asíncrona | `app/Jobs/LogActivityJob.php` | **IMPLEMENTADO** — `ShouldQueue`, inserta el registro |
| Esquema | `database/migrations/2026_03_13_061637_create_activity_logs_table.php` | **IMPLEMENTADO** — tabla `activity_logs` |
| Visor | `app/Livewire/ActivityLogger.php` + `resources/views/livewire/activity-logger.blade.php` + `resources/views/logs/index.blade.php` | **IMPLEMENTADO** — filtros por usuario (nombre, incluye "Anónimo"), acción, fecha desde/hasta; paginación de 20 |
| Ruta | `routes/web.php` línea 91, `GET /admin/logs` dentro de `Route::middleware(['auth', 'role:admin,superadmin'])` | **IMPLEMENTADO** — protegida por rol |
| Acciones auditadas hoy | `AuthController`/eventos `Login`/`Logout` en `app/Providers/AppServiceProvider.php`, `CatalogoController::index`, `OpenLibraryController::buscar` | **IMPLEMENTADO** — solo 4 acciones: `login`, `logout`, `catalogo`, `busqueda` |
| Pruebas del módulo | — | **NO EXISTE** ninguna prueba (`tests/`) para `ActivityLog`, `LogActivityJob`, `ActivityLogger` ni la ruta `/admin/logs` |
| Acciones administrativas sin auditar | `RbacController` (cambios de rol), `SuperAdminController` (activar/desactivar usuario, cambiar rol), `UsuarioController` (editar/eliminar usuario), `PerfilController` (actualizar perfil), `FavoritoController` (toggle) | **NO IMPLEMENTADO** — ninguna llama a `ActivityLog::registrar()` |

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Ver quién hizo qué en el sistema (Priority: P1)

Como administrador o superadministrador, quiero un registro de auditoría que cubra las acciones
administrativas relevantes (no solo login/catálogo/búsqueda), para poder responder "quién cambió esto y
cuándo" ante cualquier cambio sensible.

**Why this priority**: Es el propósito central de la auditoría; hoy el visor existe pero cubre solo 4
acciones, ninguna de ellas una modificación de datos (ver tabla de estado actual).

**Independent Test**: Con la mejora implementada, un `superadmin` cambia el rol de un usuario desde
`/superadmin`, y esa acción aparece en `/admin/logs` con su descripción, autor e IP.

**Acceptance Scenarios**:

1. **Given** un `superadmin` cambia el estado (activo/inactivo) o el rol de un usuario, **When** se
   consulta el visor de auditoría, **Then** aparece un registro con la acción, quién la realizó y
   cuándo.
2. **Given** un `admin` actualiza el rol de un usuario desde `/admin/roles`, **When** se consulta el
   visor, **Then** aparece el registro correspondiente.
3. **Given** un usuario edita o elimina otro usuario (`UsuarioController`), **When** se consulta el
   visor, **Then** la acción queda registrada.
4. **Given** las acciones ya auditadas hoy (login, logout, catálogo, búsqueda), **When** se implementa
   esta mejora, **Then** siguen registrándose sin cambios de comportamiento (no se elimina cobertura
   existente).

---

### User Story 2 - Encontrar un registro específico (Priority: P2)

Como administrador, quiero buscar y filtrar el registro de auditoría de forma más completa que hoy
(incluyendo IP y un rango de fechas más flexible), para investigar un incidente concreto.

**Why this priority**: El visor ya tiene filtros básicos (usuario, acción, fechas); esta historia agrega
lo que falta, no reconstruye el visor.

**Independent Test**: Buscar por una IP conocida y confirmar que el visor la incluye como criterio de
filtro, o documentar por qué se descarta (ver Requirements).

**Acceptance Scenarios**:

1. **Given** el visor actual, **When** se agregan mejoras de búsqueda, **Then** los filtros existentes
   (usuario, acción, fecha desde/hasta) siguen funcionando igual.
2. **Given** un registro con IP conocida, **When** se filtra por esa IP (si se implementa el filtro),
   **Then** solo aparecen los registros de esa IP.

---

### User Story 3 - Cobertura de pruebas del módulo (Priority: P1)

Como equipo, queremos que el módulo de auditoría tenga pruebas automatizadas (hoy no tiene ninguna),
para que un cambio futuro no rompa el registro o el control de acceso sin que el CI lo detecte.

**Why this priority**: Principio IV de la constitución (pruebas obligatorias); es una brecha real y
verificada, no una suposición.

**Independent Test**: Ejecutar `php artisan test --filter=ActivityLog` (o el nombre de test que se
defina) y ver que cubre registro y control de acceso.

**Acceptance Scenarios**:

1. **Given** el módulo mejorado, **When** se ejecuta la suite de PHPUnit, **Then** existen pruebas que
   verifican que `ActivityLog::registrar()` crea un registro y que la ruta `/admin/logs` está protegida
   por rol.
2. **Given** las nuevas acciones auditadas (User Story 1), **When** se ejecutan sus pruebas, **Then**
   cada una confirma que la acción queda registrada con los datos correctos.

### Edge Cases

- **Acción realizada por el propio sistema (sin usuario autenticado)**: ya soportado hoy (`user_id`
  nullable, filtro "Anónimo" en el visor); no se rediseña.
- **Volumen de `activity_logs`**: a diferencia de `request_traces` (spec 005), esta tabla solo crece con
  acciones de negocio (no con cada petición); no se define una poda automática en esta spec — se
  documenta como decisión abierta si el volumen lo justifica en el futuro.
- **Confusión con trazabilidad técnica**: esta spec MUST NOT agregar campos técnicos (ruta, método,
  duración, código de estado) a `activity_logs`; eso es responsabilidad de la spec 005.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: El diseño MUST reutilizar `ActivityLog`, `LogActivityJob` y la tabla `activity_logs`
  existentes; MUST NOT proponer una segunda tabla o mecanismo de auditoría paralelo.
- **FR-002**: El diseño MUST identificar las acciones administrativas que hoy no se auditan
  (`RbacController`, `SuperAdminController`, `UsuarioController` como mínimo) y proponer dónde agregar
  la llamada a `ActivityLog::registrar()` en cada una.
- **FR-003**: Las acciones nuevas auditadas MUST incluir una descripción legible (igual estilo que las
  existentes: "El usuario X inició sesión") y MUST NOT registrar datos sensibles (contraseñas, tokens).
- **FR-004**: El visor MUST conservar los filtros existentes (usuario, acción, fecha desde/hasta) sin
  regresión.
- **FR-005**: El acceso al visor MUST seguir restringido a `admin` y `superadmin` (sin cambios de rol
  respecto al estado actual, que ya cumple el principio VI).
- **FR-006**: El diseño MUST proponer pruebas automatizadas para: `ActivityLog::registrar()` (crea un
  registro con los datos correctos), el control de acceso de `/admin/logs` por rol, y cada acción nueva
  auditada.
- **FR-007**: Este módulo MUST NOT duplicar datos técnicos de petición (ruta, método, duración, status
  HTTP) que pertenecen a la spec 005 (trazabilidad).
- **FR-008**: Esta spec MUST NOT resultar en cambios de código sobre `app/Models/ActivityLog.php`,
  controladores ni vistas en este Issue; es solo diseño (Issue #164).

### Key Entities

- **ActivityLog** (existente, sin cambios de esquema propuestos): `accion`, `descripcion`, `user_id`,
  `ip`, `created_at`.
- **Acción auditable** (concepto, no tabla): una operación administrativa que modifica datos o afecta a
  otro usuario; el listado de cuáles se agregan vive en `plan.md`.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: El spec, el plan y las tasks de este módulo existen en `specs/006-auditoria/` y todas las
  tareas de `tasks.md` están pendientes (nada implementado en este Issue).
- **SC-002**: La tabla "Estado actual" de esta spec queda verificada contra el repositorio (rutas,
  archivos y acciones auditadas citados existen tal cual se describen).
- **SC-003**: El plan identifica al menos 3 acciones administrativas hoy no auditadas
  (`RbacController::actualizar`, `SuperAdminController::toggleEstado`,
  `SuperAdminController::cambiarRol` como mínimo) con el punto exacto de código donde agregar
  `ActivityLog::registrar()`.
- **SC-004**: 0 cambios de esquema de `activity_logs` propuestos (se reutiliza tal cual).
- **SC-005**: El plan de pruebas cubre las 3 brechas verificadas en "Estado actual": sin pruebas de
  modelo, sin pruebas de control de acceso, sin pruebas de las acciones nuevas.

## Assumptions

- La tabla `activity_logs` y su esquema actual son suficientes para las acciones nuevas propuestas (no
  requieren columnas adicionales); si una acción futura necesitara un dato que hoy no cabe en
  `descripcion`, se evalúa en la implementación sin bloquear esta planeación.
- Los roles `usuario`/`admin`/`superadmin` no cambian por esta spec; la restricción de acceso al visor
  ya es correcta hoy y se mantiene.
- No se propone retención/poda de `activity_logs` en esta spec: a diferencia de las trazas técnicas
  (spec 005), el volumen de acciones de negocio es mucho menor y no se ha identificado un problema de
  crecimiento; se deja como decisión abierta para el futuro si el volumen lo justifica.
- El detalle de una entrada de auditoría no requiere una vista nueva: la información ya cabe en la fila
  del listado (a diferencia de `request_traces`, que sí necesita un detalle expandido por su mayor
  número de campos técnicos).
