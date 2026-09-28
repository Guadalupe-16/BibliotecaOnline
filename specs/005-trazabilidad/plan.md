# Implementation Plan: Visor de trazabilidad técnica de solicitudes

**Branch**: Por definir | **Date**: 2026-09-27 | **Spec**: [spec.md](spec.md)

**Input**: Feature specification from `specs/005-trazabilidad/spec.md`

**Issue**: #164 (planeación). Este plan describe el diseño técnico; la implementación real se ejecuta
en un Issue separado que enlace esta spec.

## Summary

Agregar un middleware que capture datos técnicos de cada petición HTTP (método, ruta, duración, código
de estado, resultado, error) y los persista de forma asíncrona en una tabla nueva `request_traces`, con
un visor Livewire administrativo (listado, filtros, detalle) protegido para roles `admin`/`superadmin`,
siguiendo el mismo patrón de cola ya usado por `ActivityLog`/`LogActivityJob`. Se diferencia
explícitamente de la auditoría de negocio (spec 006). No se instala ni implementa nada en este Issue.

## Technical Context

**Language/Version**: PHP 8.2 (Laravel 12), Livewire 4, Blade

**Primary Dependencies**: ninguna nueva; se reutilizan `illuminate/queue` (driver `database`, ya
configurado) y `Illuminate\Support\Str::uuid()`

**Storage**: MySQL en desarrollo/producción, SQLite en pruebas (igual que el resto del proyecto,
restricción de la constitución); tabla nueva `request_traces` (ver `data-model.md`)

**Testing**: PHPUnit Feature (middleware registra una traza, control de acceso por rol) y, si se decide
cubrir el componente Livewire, un test de Livewire para filtros/paginación (mismo patrón que se debería
tener para `ActivityLogger`, hoy sin cobertura — ver spec 006)

**Target Platform**: la aplicación Laravel existente; sin servicios externos nuevos

**Project Type**: aplicación web monolítica Laravel (Blade + Livewire + Alpine)

**Performance Goals**: el middleware MUST agregar overhead despreciable a cada petición (la escritura es
asíncrona, FR-004); el listado del visor MUST paginar (no cargar todas las trazas a la vez)

**Constraints**: sin secretos ni datos sensibles en `request_traces` (FR-003); acceso solo
`admin`/`superadmin` (FR-006); retención obligatoria (FR-005)

**Scale/Scope**: 1 migración, 1 middleware, 1 job de persistencia, 1 comando de poda programado, 1 ruta
+ 1 componente Livewire + 1 vista para el visor

## Constitution Check

*GATE: Debe pasar antes de research (ya cubierto arriba) y tras el diseño.*

| Principio | Cumplimiento | Estado |
|---|---|---|
| I. Especificación antes de código | Esta spec se escribe antes de crear la migración, el middleware o el visor | ✅ |
| II. Trazabilidad (del propio proceso SDD) | Issue #164 (planeación) → `specs/005-trazabilidad/` → Issue de implementación futuro → rama `tipo/NNN-...` → PR con `Closes #NNN` | ✅ |
| III. Protección de ramas | Rama de planeación y futura rama de implementación van a `develop` por PR | ✅ |
| IV. Pruebas obligatorias | La implementación futura incluye pruebas Feature del middleware y del control de acceso (ver tasks.md) | ✅ (planeado) |
| V. Migraciones para el esquema | La tabla `request_traces` se crea con una migración de Laravel, versionada (ver data-model.md) | ✅ |
| VI. Seguridad por rol | Visor protegido con `auth` + `role:admin,superadmin`, igual patrón que `/admin/logs`; sin secretos guardados (FR-003) | ✅ |
| VII. CI en verde | La implementación futura no debe romper `ci.yml`; sus pruebas nuevas se suman al job `php-tests` | ✅ N/A todavía |
| VIII. Evidencia honesta | Todo el módulo se etiqueta PLANEADO; no se afirma que la tabla o el visor existan | ✅ |

**Re-check post-diseño**: sin cambios; ninguna violación introducida.

## Project Structure

### Documentation (this feature)

```text
specs/005-trazabilidad/
├── spec.md
├── plan.md              # Este archivo
├── research.md           # Decisiones: trace_id, tabla vs log, cola, retención
├── data-model.md          # Entidad RequestTrace
└── tasks.md               # /speckit-tasks
```

No se agrega `contracts/`: no hay una API externa que consuma estos datos; el único "contrato" es el
esquema de la tabla, ya cubierto en `data-model.md`.

### Áreas del sistema que probablemente se modificarán (propuesto, implementación futura)

```text
database/migrations/
└── xxxx_xx_xx_create_request_traces_table.php   # PROPUESTO

app/Models/
└── RequestTrace.php                              # PROPUESTO: fillable, casts, relación con User

app/Jobs/
└── LogRequestTraceJob.php                        # PROPUESTO: mismo patrón que LogActivityJob

app/Http/Middleware/
└── RegistrarTrazabilidad.php                     # PROPUESTO: captura datos en terminate()

bootstrap/app.php                                  # PROPUESTO: registrar el middleware en el grupo web

routes/console.php                                 # PROPUESTO: Schedule::command('trazas:podar')->daily()

app/Console/Commands/
└── PodarTrazasCommand.php                         # PROPUESTO: borra trazas más antiguas que el umbral

app/Livewire/
└── TrazabilidadViewer.php                         # PROPUESTO: análogo a ActivityLogger

resources/views/livewire/
└── trazabilidad-viewer.blade.php                  # PROPUESTO

resources/views/trazas/
└── index.blade.php                                # PROPUESTO: layout, igual patrón que resources/views/logs/index.blade.php

routes/web.php                                      # PROPUESTO: Route::get('/admin/trazas', ...) con role:admin,superadmin
```

**Structure Decision**: se replica el patrón ya validado de `ActivityLog` (Model + Job + Livewire +
vista + ruta protegida por rol), en vez de introducir una arquitectura distinta, para minimizar
complejidad nueva (principio de simplicidad de la constitución).

## Middleware, servicios y rutas (propuesto)

- **Middleware `RegistrarTrazabilidad`**: en `handle()` marca el inicio (`microtime(true)`) y genera el
  `trace_id`; en `terminate()` calcula duración y resultado, y despacha `LogRequestTraceJob` con los
  datos ya serializables (no el objeto `Request`/`Response` completo, para no arrastrar datos sensibles
  a la cola).
- **Job `LogRequestTraceJob`**: `ShouldQueue`, inserta el registro; mismo patrón que `LogActivityJob`.
- **Comando `PodarTrazasCommand`**: borra registros con `created_at` menor al umbral; programado
  diariamente vía `Schedule`.
- **Ruta**: `GET /admin/trazas`, dentro del grupo `Route::middleware(['auth', 'role:admin,superadmin'])`
  ya existente en `routes/web.php` (mismo grupo que `admin.logs`), evitando crear un segundo mecanismo
  de protección de rutas.

## Pruebas (propuesto para la implementación)

- Feature test: una petición cualquiera genera una fila en `request_traces` con los campos esperados
  (verificando el job de forma síncrona con `Queue::fake()` + `assertPushed`, o `Bus::fake()`).
- Feature test: acceso a `/admin/trazas` devuelve `403` para rol `usuario` y `200` para `admin`/
  `superadmin` (mismo patrón que debería existir, y hoy no existe, para `/admin/logs` — ver spec 006).
- Test del comando de poda: registros antiguos se borran, registros recientes se conservan.

## Seguridad

- Ningún campo de `request_traces` guarda contraseñas, tokens, cookies ni cuerpos de petición/respuesta
  (FR-003, ver data-model.md "Explícitamente excluido").
- El visor hereda la misma protección de rol que el resto del panel administrativo.
- El `trace_id` expuesto como header `X-Trace-Id` no revela información interna (es un UUID opaco).

## Rendimiento

- Persistencia asíncrona (FR-004): el middleware no bloquea la respuesta al cliente.
- Retención obligatoria (FR-005): evita que la tabla crezca sin límite y degrade el listado del visor.
- Índices por `created_at`, `ruta` y `status_http` para que los filtros del visor no requieran escaneos
  completos de la tabla (ver data-model.md).

## Compatibilidad con CI/CD

- La migración nueva se ejecuta igual que las existentes en `ci.yml` (`php artisan migrate --force`);
  no requiere cambios al workflow.
- El comando de poda no se ejecuta en CI (no hay scheduler corriendo en los jobs); solo aplica al
  entorno de liberación/producción.

## Riesgos

| Riesgo | Mitigación propuesta |
|---|---|
| Duplicar instrumentación con la spec 004 (monitoreo) | Evaluar en la implementación si un único middleware alimenta ambos mecanismos, o si se mantienen separados por responsabilidad distinta (métricas agregadas vs. trazas individuales) |
| Crecimiento de la tabla si el comando de poda falla o no se programa | Agregar una alerta de monitoreo (spec 004) sobre el tamaño de la tabla o la antigüedad del registro más viejo, como trabajo futuro |
| Confusión entre este visor y el de auditoría (spec 006) | Nombrar la ruta y el menú de navegación de forma explícita ("Trazas técnicas" vs "Registro de actividad") en la implementación |

## Criterios de validación

- Existen `spec.md`, `plan.md`, `research.md`, `data-model.md` y `tasks.md` en
  `specs/005-trazabilidad/` (este Issue).
- Ningún archivo de código de la aplicación fue modificado por este Issue.
- La implementación futura se considera completa cuando: toda petición queda registrada de forma
  asíncrona, el visor lista/filtra/pagina y muestra detalle, el acceso está restringido por rol, y el
  comando de poda tiene una prueba que confirma el borrado por antigüedad.
