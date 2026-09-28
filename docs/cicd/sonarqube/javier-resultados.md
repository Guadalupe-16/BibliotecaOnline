# Análisis SonarQube — PR de Javier (#80, registro de activity logs)

> Issue: #155 · Fecha del análisis: 2026-09-27 · Autor del PR: Javier Antonio Romo Bernal (`ElJavierB0`)
> Entorno: SonarQube Community Build 26.9 local (ver [`../sonarqube-instalacion.md`](../sonarqube-instalacion.md)),
> SonarScanner CLI 8.1 nativo, perfiles de calidad *Sonar way* (PHP, JS, CSS).

## 1. PR analizado

| Campo | Valor |
|---|---|
| PR | [#80 — Feat/69 registro activity logs](https://github.com/Guadalupe-16/BibliotecaOnline/pull/80) |
| Issue | #69 (registro de activity logs) |
| Unidad | Primera unidad (merge del 2026-03-13, antes del release `v2.0.0`) |
| Rama | `feat/69-registro-activity-logs` → `develop` |
| Commit de merge | `363bd03` (padre en `develop`: `e4c1cf5`) |
| Tamaño | 10 archivos, +184 / −3 |

**Por qué este PR:** de los PRs de Javier en la primera unidad es el que toca más capas de la
aplicación: modelo, migración, controladores, `AppServiceProvider`, configuración, rutas y vistas
Livewire. Además, su módulo figura "sin pruebas" en la
[trazabilidad](../../sdd/estado-actual/trazabilidad.md).

Archivos del PR:

```text
app/Http/Controllers/CatalogoController.php
app/Http/Controllers/OpenLibraryController.php
app/Models/ActivityLog.php                                   (nuevo)
app/Providers/AppServiceProvider.php
config/app.php
database/migrations/2026_03_13_061637_create_activity_logs_table.php   (nuevo)
resources/views/components/⚡activity-logger.blade.php        (nuevo)
resources/views/livewire/activity-logger.blade.php            (nuevo)
resources/views/logs/index.blade.php                          (nuevo)
routes/web.php
```

## 2. Método

SonarQube Community Build no ofrece análisis de Pull Requests (es una función de las ediciones de
pago). Para aislar el efecto del PR se analizó el código **dos veces** como proyectos separados:

| Proyecto en SonarQube | Código | Commit |
|---|---|---|
| `BibliotecaOnline-PR80-base` | `develop` justo antes del merge | `e4c1cf5` |
| `BibliotecaOnline-PR80-pr` | `develop` con el PR mergeado | `363bd03` |

El diff entre ambos commits es exactamente el del PR (10 archivos, +184 / −3). Cada versión se
obtuvo con `git worktree` y se analizó con la configuración de `sonar-project.properties`, salvo
`sonar.tests=tests` (en marzo aún no existía `resources/js/tests`):

```bash
sonar-scanner -Dsonar.host.url=http://localhost:9001 \
  -Dsonar.projectKey=BibliotecaOnline-PR80-pr -Dsonar.tests=tests
```

Resultado de ambas ejecuciones: `EXECUTION SUCCESS`. Después se compararon las métricas y los
issues de los 10 archivos del PR (API `api/measures/component` y `api/issues/search`).

## 3. Métricas

| Métrica | Antes (`e4c1cf5`) | Después (`363bd03`) | Cambio por el PR |
|---|---|---|---|
| Quality Gate (Sonar way) | Passed | **Passed** | = |
| Líneas de código (ncloc) | 1 613 | 1 688 | +75 |
| Archivos analizados | 70 | 75 | +5 |
| **Bugs** | 10 | 12 | **+2** |
| **Vulnerabilities** | 0 | 0 | 0 |
| **Security Hotspots** | 0 | 0 | 0 |
| **Code Smells** | 38 | 39 | **+1** |
| **Technical Debt** | 100 min | 102 min | **+2 min** |
| **Duplications** | 2.0 % (93 líneas) | 2.0 % (93 líneas) | 0 |
| **Coverage** | 0.0 % | 0.0 % | No disponible: sin reporte de cobertura importado |
| Reliability / Security / Maintainability | C / A / A | C / A / A | = |

## 4. Hallazgos de SonarQube introducidos por el PR

3 issues nuevos en los archivos del PR (antes había 2, ambos preexistentes en los controladores:
`php:S113`, falta salto de línea final).

| # | Tipo | Severidad | Regla | Ubicación | Mensaje | Valoración |
|---|---|---|---|---|---|---|
| S-1 | Bug | Major | `Web:InputWithoutLabelCheck` | `resources/views/livewire/activity-logger.blade.php:3` | El `<input>` de filtro no tiene `id` ni `<label>` asociado | **Real** (accesibilidad): un lector de pantalla no anuncia qué filtra el campo; el `placeholder` no sustituye a la etiqueta |
| S-2 | Bug | Major | `php:S1848` | `resources/views/components/⚡activity-logger.blade.php:7` | "Instanciación de objeto inútil" de `new class extends Component` | **Falso positivo en su contexto**: es la sintaxis de componente de un solo archivo de Livewire, que Livewire toma del `new class` |
| S-3 | Code smell | Minor | `php:S1780` | `resources/views/components/⚡activity-logger.blade.php:24` | Cerrar con `?>` al final del archivo | **Real, menor**: en este archivo no hay HTML después del `?>`, así que sobra |

## 5. Revisión manual (lo que SonarQube no marcó)

| # | Hallazgo | Archivo | Impacto |
|---|---|---|---|
| M-1 | `registrar(string $accion, string $descripcion = null)`: parámetro *nullable* implícito. PHP 8.4 lo marca como obsoleto | `app/Models/ActivityLog.php` | Advertencias de deprecación al subir a PHP 8.4; la forma correcta es `?string $descripcion = null` |
| M-2 | Escritura síncrona en BD en cada visita al catálogo y a Open Library, dentro de la petición del usuario | `CatalogoController`, `OpenLibraryController` | Latencia extra por petición y crecimiento rápido de la tabla |
| M-3 | El filtro `where(...)->orWhere(...)` no está agrupado en un closure | `⚡activity-logger.blade.php` | Hoy funciona porque no hay otras condiciones; si se agrega una (p. ej. por usuario), el `orWhere` la anularía |
| M-4 | El PR no agrega pruebas para `ActivityLog::registrar` ni para los listeners de login/logout | — | Incumple el principio IV de la constitución actual; el módulo sigue sin pruebas |
| M-5 | Se guarda la IP de cada visitante | Migración `activity_logs` | Dato personal: requiere política de retención y aviso de privacidad |

## 6. Estado actual en `develop` (`801bbeb`)

| Hallazgo | ¿Sigue? | Detalle |
|---|---|---|
| S-1 input sin label | **Sí**, ×2 | Tras el PR #105 la vista tiene 2 campos de texto (filtro por usuario y por acción), ninguno con `<label>` |
| S-2 / S-3 | **Sí**, y el archivo quedó **sin uso** | El PR #105 creó `app/Livewire/ActivityLogger.php`. Verificado con `php artisan tinker`: `<livewire:activity-logger />` se resuelve a `App\Livewire\ActivityLogger`, así que `⚡activity-logger.blade.php` es código muerto |
| M-1 nullable implícito | **Sí** | Misma firma en `ActivityLog::registrar` |
| M-2 escritura síncrona | **Resuelto** | El PR #132 movió el registro a una cola (`LogActivityJob::dispatch`) |
| M-4 sin pruebas | **Sí** | Ningún archivo de `tests/` referencia `ActivityLog` ni `LogActivityJob` |

## 7. Recomendaciones

| Prioridad | Acción | Resuelve |
|---|---|---|
| Alta | Agregar pruebas Feature: login y logout crean un registro; `registrar` encola `LogActivityJob` (`Queue::fake()`); el panel `/admin/logs` exige rol | M-4 |
| Media | Agregar `<label>` (visible o `sr-only`) con `for`/`id` a los 2 filtros de texto de `livewire/activity-logger.blade.php` | S-1 |
| Media | Eliminar `resources/views/components/⚡activity-logger.blade.php` (código muerto desde #105) | S-2, S-3 |
| Baja | Cambiar la firma a `?string $descripcion = null` | M-1 |
| Baja | Agrupar condiciones de búsqueda en `->where(fn ($q) => ...)` | M-3 |
| Baja | Definir retención de `activity_logs` (p. ej. `model:prune` a 90 días) | M-5 |

## 8. Conclusión

El PR #80 **pasa el Quality Gate** y su impacto en la calidad global es pequeño: +2 bugs, +1 code
smell y +2 min de deuda por 75 líneas nuevas, sin vulnerabilidades, hotspots ni duplicación. De los
3 hallazgos de SonarQube, uno es un problema real de accesibilidad, uno es un detalle menor y uno es
un falso positivo propio de los componentes de un solo archivo de Livewire. La revisión manual
agrega el punto más importante: **el módulo no tiene pruebas**, y parte del código del PR quedó sin
uso tras el PR #105. La latencia por escritura síncrona ya se corrigió en el PR #132.

## 9. Evidencia

- Captura del dashboard del proyecto `BibliotecaOnline-PR80-pr`: [`javier-captura.png`](javier-captura.png)
- Dashboards locales: `http://localhost:9001/dashboard?id=BibliotecaOnline-PR80-pr` y
  `...?id=BibliotecaOnline-PR80-base`
