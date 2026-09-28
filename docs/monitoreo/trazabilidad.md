# Trazabilidad técnica de solicitudes — BibliotecaOnline

> Issue: #166 · Spec: [`specs/005-trazabilidad/`](../../specs/005-trazabilidad/) · Estado: **IMPLEMENTADO**

Este documento explica el módulo de trazabilidad técnica ya implementado: qué registra, dónde se
consulta, cómo funciona el `trace_id`, su seguridad, su retención y su configuración. Es distinto del
[registro de actividad](../../specs/006-auditoria/) (`ActivityLog`), que audita acciones de negocio
("quién hizo qué"), no el comportamiento técnico de cada petición.

## 1. Qué registra

Cada petición HTTP que pasa por el grupo de middleware `web` genera, de forma asíncrona, una fila en
`request_traces` con:

| Campo | Descripción |
|---|---|
| `trace_id` | UUID único de la petición, también devuelto en la respuesta como header `X-Trace-Id` |
| `metodo` | Verbo HTTP (`GET`, `POST`, …) |
| `ruta` | Nombre de la ruta (`Route::currentRouteName()`), o el path si la ruta no tiene nombre |
| `user_id` | Usuario autenticado, si existe sesión (`null` si es anónima) |
| `ip` | IP de origen |
| `status_http` | Código de respuesta |
| `duracion_ms` | Duración de la petición en milisegundos |
| `resultado` | `ok` o `error` — `error` solo si `status_http >= 500` o hubo una excepción no controlada |
| `error_referencia` | Clase + mensaje (truncado) de la excepción, solo cuando `resultado = error` |
| `user_agent` | Cabecera `User-Agent`, truncada a 255 caracteres |
| `created_at` | Instante real de la petición (no el de procesamiento de la cola) |

**No se registra**: contraseñas, tokens, cookies, ni el cuerpo de la petición o de la respuesta
(`tests/Feature/TrazabilidadMiddlewareTest.php::test_informacion_sensible_no_se_persiste` lo verifica).

Un campo derivado, `categoria` (`exitosa` / `error_cliente` / `error_servidor`), se calcula a partir de
`status_http` para el visor, sin duplicar el campo `resultado` ya definido en el spec.

## 2. Dónde se consulta

Visor administrativo en **`/admin/trazas`** (enlace "Trazabilidad" en el menú de administración de la
navegación), protegido para roles `admin` y `superadmin` — igual restricción que el
[Registro de actividad](../../specs/006-auditoria/) en `/admin/logs`.

Filtros disponibles: Trace ID, método HTTP, rango de status (2xx/3xx/4xx/5xx), ruta, usuario y rango de
fechas. La tabla pagina de 20 en 20 y cada fila se puede expandir para ver el detalle (Trace ID
completo, resultado, user agent y referencia de error).

## 3. Cómo funciona el `trace_id`

`App\Http\Middleware\RegistrarTrazabilidad` genera un UUID al inicio de cada petición (en `handle()`),
lo antepone al resto del grupo `web` (para capturar incluso peticiones que fallan por un *route model
binding* inexistente, p. ej. `/libros/999999`), y lo agrega a la respuesta como header `X-Trace-Id`. Al
terminar la petición (`terminate()`, después de enviar la respuesta), calcula la duración y despacha
`App\Jobs\LogRequestTraceJob` — mismo patrón de cola que `App\Jobs\LogActivityJob` — para no añadir
latencia a la petición del usuario.

Ese `X-Trace-Id` permite correlacionar: petición del navegador (header de la respuesta) → fila en
`request_traces` → detalle del error, si lo hubo.

**Limitación conocida**: una URL que no coincide con ninguna ruta definida (404 "ruta no encontrada",
no un recurso inexistente) nunca entra al pipeline de middleware y no genera traza. Un recurso que sí
coincide con el patrón de una ruta pero no existe (p. ej. `/libros/999999`) sí se traza, con
`status_http = 404`.

## 4. Seguridad

- El visor requiere `auth` + `role:admin,superadmin` (mismo mecanismo que el resto del panel
  administrativo).
- `request_traces` no tiene ninguna columna de payload, headers ni cookies; solo los campos listados en
  §1.
- Un fallo al registrar una traza (p. ej. la base de datos no está disponible) **no** afecta la
  respuesta al usuario: `terminate()` captura cualquier error del despacho del job y solo deja un
  `Log::warning()`, nunca una excepción visible
  (`tests/Feature/TrazabilidadMiddlewareTest.php::test_un_fallo_al_registrar_la_traza_no_afecta_la_respuesta`).

## 5. Retención

Comando `php artisan trazas:podar` (opción `--dias=N`, por defecto `config('trazabilidad.retencion_dias')`)
elimina las trazas más antiguas que el umbral configurado. Programado a diario en `routes/console.php`
(`Schedule::command('trazas:podar')->daily()`).

## 6. Configuración

| Variable (`.env`) | Default | Dónde se usa |
|---|---|---|
| `TRAZABILIDAD_RETENCION_DIAS` | `30` | `config/trazabilidad.php` → `PodarTrazasCommand` |

El valor por defecto está documentado en `.env.example`, no en `.env` (no se versiona ningún secreto ni
valor de entorno real).

## 7. Diferencia con auditoría (`ActivityLog`)

| | Trazabilidad (`request_traces`) | Auditoría (`activity_logs`) |
|---|---|---|
| Responde | Qué pasó con la petición HTTP en sí | Qué acción de negocio hizo un usuario |
| Volumen | Toda petición a una ruta `web` | Solo acciones auditadas explícitamente (login, logout, etc.) |
| Campos técnicos (ruta, status, duración) | Sí | No |
| Retención automática | Sí (`trazas:podar`) | No definida (volumen mucho menor) |

## 8. Dependencia con el módulo de monitoreo (spec 004)

El módulo de monitoreo (`specs/004-monitoreo/`, métricas Prometheus/Grafana) sigue **sin implementar**;
no comparte middleware con trazabilidad en esta implementación. Si se implementa después, evaluar si
conviene fusionar la instrumentación (ver `specs/004-monitoreo/plan.md`, Riesgos) — no es parte de este
Issue.
