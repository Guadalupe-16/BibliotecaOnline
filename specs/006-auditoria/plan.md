# Implementation Plan: Formalizar y mejorar el visor de auditoría

**Branch**: Por definir | **Date**: 2026-09-27 | **Spec**: [spec.md](spec.md)

**Input**: Feature specification from `specs/006-auditoria/spec.md`

**Issue**: #164 (planeación). Este plan describe el diseño técnico; la implementación real se ejecuta
en un Issue separado que enlace esta spec.

## Summary

Ampliar la cobertura de `ActivityLog::registrar()` a las acciones administrativas que hoy no se
auditan (cambios de rol, activar/desactivar usuario, editar/eliminar usuario), sin tocar el esquema de
`activity_logs` ni el visor `ActivityLogger` más allá de lo necesario, y cerrar la brecha de pruebas
(hoy inexistentes) del módulo completo. No se instala ni implementa nada en este Issue.

## Contexto técnico verificado (estado actual)

| Pieza | Ubicación | Qué hace hoy |
|---|---|---|
| Registro | `App\Models\ActivityLog::registrar()` (`app/Models/ActivityLog.php:17-20`) | Despacha `LogActivityJob` con `accion`, `descripcion`, `auth()->id()`, `request()->ip()` |
| Persistencia | `App\Jobs\LogActivityJob::handle()` (`app/Jobs/LogActivityJob.php:23-31`) | Inserta en `activity_logs` |
| Visor | `App\Livewire\ActivityLogger` (`app/Livewire/ActivityLogger.php`) | Filtros: `filtroUsuario` (incluye "Anónimo"), `filtroAccion`, `fechaDesde`, `fechaHasta`; `paginate(20)` |
| Ruta | `routes/web.php:91` — `GET /admin/logs` dentro de `Route::middleware(['auth', 'role:admin,superadmin'])` (`routes/web.php:83`) | Acceso restringido a `admin`/`superadmin` |
| Llamadas existentes a `registrar()` | `AppServiceProvider` (eventos `Login`/`Logout`), `CatalogoController::index`, `OpenLibraryController::buscar` | 4 acciones: `login`, `logout`, `catalogo`, `busqueda` |
| Pruebas | — | Ninguna en `tests/` cubre `ActivityLog`, `LogActivityJob`, `ActivityLogger` ni `/admin/logs` |

## Acciones administrativas sin auditar (identificadas para esta mejora)

| Controlador / método | Archivo:línea | Acción propuesta (`accion`, `descripcion`) |
|---|---|---|
| `RbacController::actualizar` | `app/Http/Controllers/RbacController.php:36` (antes del `return back()` de éxito, línea 38) | `rbac_cambio_rol`, `"El usuario {actor->name} cambió el rol de {usuario->name} a {usuario->rol}."` |
| `SuperAdminController::toggleEstado` | `app/Http/Controllers/SuperAdminController.php:33-36` | `superadmin_toggle_estado`, `"El usuario {auth()->user()->name} {activó|desactivó} la cuenta de {usuario->name}."` |
| `SuperAdminController::cambiarRol` | `app/Http/Controllers/SuperAdminController.php:47-49` | `superadmin_cambio_rol`, `"El usuario {auth()->user()->name} cambió el rol de {usuario->name} a {nuevoRol}."` |
| `UsuarioController::update` | `app/Http/Controllers/UsuarioController.php:33` | `usuario_actualizado`, `"El usuario {auth()->user()->name} actualizó los datos de {usuario->name} (#{id})."` |
| `UsuarioController::destroy` | `app/Http/Controllers/UsuarioController.php:40` | `usuario_eliminado`, `"El usuario {auth()->user()->name} eliminó al usuario #{id}."` |

**Fuera de alcance de esta mejora** (documentado, no auditado): `FavoritoController::toggle` (acción de
usuario sobre sus propios datos, no administrativa) y `PerfilController::actualizar` (el propio usuario
editando su perfil) — se dejan fuera porque no son acciones de un administrador sobre otro usuario; si
el equipo decide auditarlas, se agregan en una iteración posterior sin cambiar el diseño.

**Nota de duplicación con `RbacController` y `SuperAdminController`**: ambos permiten cambiar el rol de
un usuario por rutas distintas (`/admin/roles` y `/superadmin/usuarios/{id}/cambiar-rol/{rol}`); se
proponen acciones distintas (`rbac_cambio_rol` vs `superadmin_cambio_rol`) para no perder de cuál ruta
vino el cambio, en vez de forzar una única acción genérica que oculte esa diferencia.

## Constitution Check

*GATE: Debe pasar antes de research (no aplica: sin tecnología nueva) y tras el diseño.*

| Principio | Cumplimiento | Estado |
|---|---|---|
| I. Especificación antes de código | Esta spec se escribe antes de tocar los controladores listados arriba | ✅ |
| II. Trazabilidad (del propio proceso SDD) | Issue #164 (planeación) → `specs/006-auditoria/` → Issue de implementación futuro → rama `tipo/NNN-...` → PR con `Closes #NNN` | ✅ |
| III. Protección de ramas | Rama de planeación y futura rama de implementación van a `develop` por PR | ✅ |
| IV. Pruebas obligatorias | Brecha real hoy (0 pruebas); esta mejora la cierra explícitamente (ver tasks.md) | ⚠️ hoy no se cumple; el plan corrige |
| V. Migraciones para el esquema | No aplica: no se propone ningún cambio de esquema (FR-001, SC-004) | ✅ N/A |
| VI. Seguridad por rol | El acceso a `/admin/logs` ya cumple (`role:admin,superadmin`); no se cambia | ✅ |
| VII. CI en verde | La implementación futura agrega pruebas al job `php-tests`, no lo rompe | ✅ N/A todavía |
| VIII. Evidencia honesta | La tabla "Estado actual" de spec.md se verificó leyendo el código citado, no se supuso | ✅ |

**No se justifica ninguna tecnología nueva** (constitución, Restricciones): esta mejora reutiliza
`ActivityLog::registrar()` tal cual existe; no requiere `research.md`.

**Re-check post-diseño**: sin cambios; ninguna violación nueva introducida por el diseño (la brecha de
pruebas ya existía antes de esta spec y este plan la corrige).

## Project Structure

### Documentation (this feature)

```text
specs/006-auditoria/
├── spec.md
├── plan.md       # Este archivo
└── tasks.md      # /speckit-tasks
```

No se agrega `research.md` (sin tecnología nueva que justificar) ni `data-model.md` (sin cambios de
esquema, FR-001/SC-004): el modelo de datos ya está documentado como "Estado actual" en `spec.md`.

### Áreas del sistema que probablemente se modificarán (propuesto, implementación futura)

```text
app/Http/Controllers/RbacController.php          # + ActivityLog::registrar() tras cambiar el rol
app/Http/Controllers/SuperAdminController.php    # + ActivityLog::registrar() en toggleEstado y cambiarRol
app/Http/Controllers/UsuarioController.php       # + ActivityLog::registrar() en update y destroy

tests/Feature/
├── ActivityLogTest.php                          # PROPUESTO: registrar() crea fila con datos correctos
├── ActivityLoggerAccesoTest.php                  # PROPUESTO: control de acceso por rol a /admin/logs
├── RbacAuditoriaTest.php                         # PROPUESTO: cambio de rol queda auditado
├── SuperAdminAuditoriaTest.php                   # PROPUESTO: toggle y cambio de rol quedan auditados
└── UsuarioAuditoriaTest.php                      # PROPUESTO: update/destroy quedan auditados
```

**Structure Decision**: no se crean modelos, migraciones ni componentes Livewire nuevos; el cambio es
quirúrgico (una línea de `ActivityLog::registrar()` por acción identificada) más las pruebas que hoy
faltan. Se prioriza la brecha de pruebas (User Story 3) por ser una violación actual del principio IV.

## Pruebas (propuesto para la implementación)

- `ActivityLogTest`: `ActivityLog::registrar('x', 'y')` (con `Queue::fake()` o ejecutando el job)
  produce una fila con `accion`, `descripcion`, `user_id`, `ip` correctos; `user_id` nulo si no hay
  sesión.
- `ActivityLoggerAccesoTest`: `GET /admin/logs` responde `403` para rol `usuario`, redirige a login sin
  sesión, `200` para `admin`/`superadmin` (cierra la brecha "sin pruebas de control de acceso").
- Una prueba Feature por acción nueva de la tabla anterior: ejecuta la acción administrativa (p. ej.
  `PUT /admin/roles/{id}`) y confirma que existe una fila nueva en `activity_logs` con la `accion`
  esperada.
- No se agregan pruebas de UI de Livewire nuevas más allá de las ya cubiertas por
  `ActivityLoggerAccesoTest`, salvo que el equipo decida ampliar filtros (User Story 2), en cuyo caso se
  añade una prueba por filtro nuevo.

## Seguridad

- Las descripciones nuevas (tabla de acciones) no incluyen contraseñas ni tokens, solo nombres e IDs ya
  visibles para un `admin`/`superadmin` en sus propias pantallas.
- No se amplía el acceso al visor ni se reduce: sigue en `role:admin,superadmin`.

## Rendimiento

- Sin impacto: se reutiliza la misma cola (`LogActivityJob`) que ya evita escritura síncrona; agregar 5
  llamadas más a `registrar()` no cambia el patrón de rendimiento existente.

## Compatibilidad con CI/CD

- Las pruebas nuevas se suman al job `php-tests` de `ci.yml` sin requerir pasos adicionales (mismo
  `php artisan test`).

## Riesgos

| Riesgo | Mitigación propuesta |
|---|---|
| Registrar demasiado detalle en `descripcion` (p. ej. el nuevo rol de otro usuario) podría filtrar información a quien lea el log sin deberlo | El acceso al log ya está restringido a `admin`/`superadmin`, que ya pueden ver esos datos en las pantallas de gestión de usuarios; no se introduce una fuga nueva |
| Que `RbacController` y `SuperAdminController` terminen auditando el mismo tipo de cambio con nombres de acción distintos genere confusión en el visor | Documentar la diferencia (qué ruta originó cada acción) en la descripción misma del registro, no solo en el nombre de la acción |
| Aumentar acciones auditadas sin acordar si se necesita retención podría, a largo plazo, hacer crecer `activity_logs` | Fuera de alcance de esta spec (ver spec.md Assumptions); revisar si el volumen lo justifica en una spec futura |

## Criterios de validación

- Existen `spec.md`, `plan.md` y `tasks.md` en `specs/006-auditoria/` (este Issue).
- Ningún archivo de código de la aplicación fue modificado por este Issue.
- La implementación futura se considera completa cuando: las 5 acciones de la tabla quedan auditadas,
  las pruebas propuestas pasan en CI, y los filtros existentes del visor no presentan regresión.
