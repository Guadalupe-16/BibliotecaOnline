# Research: Trazabilidad técnica de solicitudes

**Spec**: [spec.md](spec.md) | **Issue**: #164 (planeación) — implementación en Issue separado

## 1. Diferencia con auditoría (por qué es una spec separada)

BibliotecaOnline ya tiene `app/Models/ActivityLog.php` + `App\Jobs\LogActivityJob`: registra **quién
hizo qué** (`accion`, `descripcion`, `user_id`, `ip`) para 4 acciones de negocio (login, logout, visita
al catálogo, búsqueda en Open Library — ver spec 006). Eso es **auditoría de negocio**: responde por qué
alguien hizo algo, desde la perspectiva de un humano que revisa el sistema.

**Trazabilidad técnica** responde una pregunta distinta: qué pasó a nivel de la petición HTTP en sí
(ruta, método, duración, código de estado, error), independientemente de si esa petición correspondía a
una acción "de negocio" auditable. Una petición a `/catalogo` que auditoría registra una vez ("visitó el
catálogo") puede, a nivel técnico, tardar 40 ms o 4000 ms, responder 200 o 500 — eso es trazabilidad, no
auditoría. Mezclar ambas cosas en `activity_logs` obligaría a auditar *cada* petición (miles de filas
sin valor de negocio) o a no medir latencia/errores en las que sí se auditan.

Por eso esta spec propone una tabla y un visor **nuevos y separados** de `activity_logs`, sin duplicar
sus columnas (`accion`/`descripcion` de negocio no aparecen aquí).

## 2. Identificador de traza: `trace_id` vs correlación con `X-Request-Id`

Se evalúan dos opciones:

| Opción | Descripción | Decisión |
|---|---|---|
| Generar `trace_id` propio (UUID) por petición | Simple, no depende de infraestructura externa (no hay balanceador de carga ni proxy hoy que inyecte un ID) | **Elegida** |
| Adoptar el header estándar `X-Request-Id` de un proxy/CDN | Requiere que algo en el borde de la red lo genere; hoy no existe (el entorno de liberación no tiene proxy, `docker-compose.release.yml` expone el contenedor `app` directo) | Descartada por ahora; se puede adoptar después si se agrega un proxy, sin romper el diseño (el middleware usaría el header si viene, y si no, generaría uno) |

Se usa `Illuminate\Support\Str::uuid()` generado en el middleware, expuesto también en la respuesta como
header `X-Trace-Id` para que, si se agrega un proxy en el futuro, ese ID se pueda correlacionar con sus
propios logs.

## 3. Dónde vive la traza: tabla nueva vs archivo de log

| Opción | Descripción | Decisión |
|---|---|---|
| Tabla `request_traces` (MySQL/SQLite) | Permite el visor con filtros/búsqueda/paginación que pide la actividad, reutilizando Eloquent y Livewire igual que `ActivityLogger` | **Elegida** |
| Solo canal de log (`storage/logs`) | Más barato de escribir, pero no permite un visor con filtros sin parsear archivos de texto; no cumple el requisito de "visor administrativo" de la actividad | Descartada como único mecanismo; se puede complementar con log estructurado para depuración, pero el visor exige tabla |

## 4. Evitar que la trazabilidad haga más lenta cada petición

El mismo problema que ya resolvió `ActivityLog::registrar()` (LogActivityJob en cola, no escritura
síncrona). Se reutiliza el patrón: el middleware calcula los datos en `terminate()` (después de enviar
la respuesta al cliente) y despacha un job en cola (`QUEUE_CONNECTION=database`, ya en uso) en vez de un
`INSERT` síncrono. Alternativa descartada: escribir síncrono "porque son pocos campos" — se rechaza
porque introduciría I/O de base de datos en el camino crítico de cada petición, algo que el propio
proyecto ya evitó para auditoría.

## 5. Evitar crecimiento sin límite

La tabla crece con cada petición (a diferencia de `activity_logs`, que solo crece con acciones de
negocio). Se propone:

- **Retención por tiempo**: un comando `artisan` programado (Laravel Scheduler, ya disponible en
  `routes/console.php`) que borra trazas más antiguas que N días (propuesto: 30 días, decisión de
  equipo a confirmar en la implementación).
- **Índice** por `created_at` y por `trace_id` para que el borrado y las búsquedas sean eficientes.

Se descarta no tener retención ("guardar todo para siempre"): en un entorno con tráfico continuo, la
tabla crecería sin cota y degradaría tanto las consultas del visor como los `INSERT` del job.

## 6. Qué no se guarda

Coherente con la constitución (principio VI, sin secretos) y con FR de esta spec: no se guardan
contraseñas, tokens, cookies ni el cuerpo (body) de la petición o respuesta. El `user_agent` se evalúa
como opcional: aporta valor para depurar problemas específicos de navegador, pero no es indispensable;
se marca como campo opcional en `data-model.md`, a decidir en la implementación si el volumen adicional
se justifica.
