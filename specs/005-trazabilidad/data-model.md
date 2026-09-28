# Data Model: Trazabilidad técnica de solicitudes

**Spec**: [spec.md](spec.md) | **Estado**: PLANEADO — ninguna tabla existe todavía

## Entidad: `RequestTrace` (tabla propuesta `request_traces`)

| Campo | Tipo | Nullable | Notas |
|---|---|---|---|
| `id` | bigint, PK | No | |
| `trace_id` | uuid/string(36), índice único | No | Generado por el middleware; expuesto como header `X-Trace-Id` |
| `metodo` | string(10) | No | `GET`, `POST`, etc. |
| `ruta` | string | No | Nombre de ruta (`Route::currentRouteName()`), no la URL cruda con parámetros |
| `user_id` | foreignId → `users.id`, nullOnDelete | Sí | Null si la petición es anónima |
| `ip` | string(45) | Sí | IPv4/IPv6 |
| `status_http` | unsignedSmallInteger | No | Código de respuesta |
| `duracion_ms` | unsignedInteger | No | Duración de la petición en milisegundos |
| `resultado` | enum/string(`ok`,`error`) | No | Derivado de `status_http` (`>=500` o excepción no controlada ⇒ `error`) |
| `error_referencia` | string, nullable | Sí | Clase de la excepción + mensaje truncado (sin stack trace ni datos del request) |
| `user_agent` | string, nullable | Sí | **Opcional** (research.md §6); se agrega solo si se decide que aporta valor suficiente frente al volumen adicional |
| `created_at` | timestamp | No | Sin `updated_at`: una traza no se edita |

**Índices**: `trace_id` (único), `created_at` (para retención y orden del listado), `ruta` + `status_http`
(para filtros del visor).

**Explícitamente excluido**: contraseñas, tokens, cookies, headers de autenticación, cuerpo de la
petición o de la respuesta.

## Relación con entidades existentes

- `user_id` referencia el mismo `App\Models\User` que ya usa `ActivityLog`; no se duplica información
  del usuario, solo su ID.
- No hay relación con `activity_logs`: son tablas independientes (ver research.md §1). Pueden
  correlacionarse manualmente por `user_id` + rango de tiempo si algún día se necesita, pero el diseño
  no crea una clave foránea entre ambas para no acoplar auditoría de negocio con trazabilidad técnica.

## Migración propuesta (nombre de archivo, no creado en este Issue)

`database/migrations/xxxx_xx_xx_create_request_traces_table.php`

## Retención

Job/comando propuesto `php artisan trazas:podar` (o `traces:prune`), programado en
`routes/console.php` con `Schedule::command(...)->daily()`, que borra registros con `created_at` más
antiguo que el umbral configurado (propuesto 30 días; confirmar en la implementación).
