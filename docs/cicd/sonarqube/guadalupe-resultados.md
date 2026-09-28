# Análisis SonarQube — PR de Guadalupe (#59, API de Open Library)

> Issue: #154 · Fecha del análisis: 2026-09-27 · Autora del PR: Guadalupe Amavizca Quinter (`Guadalupe-16`)
> Análisis ejecutado por: Javier Antonio Romo Bernal
> Entorno: SonarQube Community Build 26.9 local (ver [`../sonarqube-instalacion.md`](../sonarqube-instalacion.md)),
> SonarScanner CLI 8.1 nativo, perfiles de calidad *Sonar way* (PHP, JS, CSS).

## 1. PR analizado

| Campo | Valor |
|---|---|
| PR | [#59 — Feat/58 api open library](https://github.com/Guadalupe-16/BibliotecaOnline/pull/59) |
| Issue | #58 (integración con la API de Open Library) |
| Unidad | Primera unidad (merge del 2026-03-12) |
| Rama | `feat/58-api-open-library` → `develop` |
| Commit de merge | `6b0e17c` (padre en `develop`: `1f1815b`) |
| Tamaño | 11 archivos, +476 / −51 |

**Por qué este PR:** es el PR de Guadalupe de la primera unidad con más lógica de aplicación.
Integra un servicio HTTP externo, recibe datos del usuario (búsqueda e importación) y escribe en la
base de datos, que es donde el análisis estático busca bugs, vulnerabilidades y hotspots. Además,
sus rutas se corrigieron después por un problema de seguridad (issue #118), lo que permite contrastar
lo que detecta SonarQube con un defecto real.

Archivos del PR:

```text
app/Http/Controllers/OpenLibraryController.php     (nuevo)
app/Http/Requests/BuscarLibroRequest.php           (nuevo)
app/Http/Requests/ImportarLibroRequest.php         (nuevo)
app/Jobs/ImportarLibroJob.php                      (nuevo)
app/Services/OpenLibraryService.php                (nuevo, 141 líneas)
database/factories/CategoriaFactory.php            (nuevo)
resources/views/auth/login.blade.php
resources/views/layouts/app.blade.php
resources/views/libros/buscar.blade.php            (nuevo)
routes/web.php
tests/Feature/OpenLibraryServiceTest.php           (nuevo, 3 pruebas)
```

## 2. Método

Igual que en el análisis del PR de Javier ([`javier-resultados.md`](javier-resultados.md) §2):
SonarQube Community Build no analiza Pull Requests, así que se analizó el código **antes** y
**después** del merge como dos proyectos y se compararon métricas e issues de los 11 archivos del PR.

| Proyecto en SonarQube | Código | Commit |
|---|---|---|
| `BibliotecaOnline-PR59-base` | `develop` justo antes del merge | `1f1815b` |
| `BibliotecaOnline-PR59-pr` | `develop` con el PR mergeado | `6b0e17c` |

El diff entre ambos commits es exactamente el del PR (11 archivos, +476 / −51). Ambas ejecuciones
terminaron con `EXECUTION SUCCESS` usando `sonar-project.properties` con `sonar.tests=tests`.

## 3. Métricas

| Métrica | Antes (`1f1815b`) | Después (`6b0e17c`) | Cambio por el PR |
|---|---|---|---|
| Quality Gate (Sonar way) | Passed | **Passed** | = |
| Líneas de código (ncloc) | 1 104 | 1 325 | +221 |
| Archivos analizados | 42 | 49 | +7 |
| **Bugs** | 0 | 2 | **+2** |
| **Vulnerabilities** | 0 | 0 | 0 |
| **Security Hotspots** | 0 | 0 | 0 |
| **Code Smells** | 15 | 20 | **+5** |
| **Technical Debt** | 61 min | 66 min | **+5 min** |
| **Duplications** | 3.2 % (93 líneas) | 2.9 % (93 líneas) | −0.3 pts (mismas líneas duplicadas, más código total) |
| **Coverage** | 0.0 % | 0.0 % | No disponible: sin reporte de cobertura importado |
| Reliability / Security / Maintainability | **A** / A / A | **C** / A / A | Reliability baja de A a C por los 2 bugs *Major* |

## 4. Hallazgos de SonarQube introducidos por el PR

7 issues nuevos en los archivos del PR (antes había 1, preexistente: `routes/web.php` sin salto de
línea final).

| # | Tipo | Severidad | Regla | Ubicación | Mensaje | Valoración |
|---|---|---|---|---|---|---|
| S-1 | Bug | Major | `Web:InputWithoutLabelCheck` | `resources/views/libros/buscar.blade.php:7` | El campo de búsqueda no tiene `id` ni `<label>` | **Real** (accesibilidad): solo tiene `placeholder` |
| S-2 | Bug | Major | `Web:InputWithoutLabelCheck` | `resources/views/libros/buscar.blade.php:59` | El `<select>` de categoría del formulario de importación no tiene `<label>` | **Real** (accesibilidad): el usuario de lector de pantalla no sabe qué elige |
| S-3 | Code smell | Minor | `php:S113` | `OpenLibraryController.php`, `BuscarLibroRequest.php`, `ImportarLibroRequest.php`, `ImportarLibroJob.php`, `CategoriaFactory.php` (5 archivos) | Archivo sin salto de línea final | **Real, cosmético**: se corrige con la configuración del editor o con Laravel Pint |

**Lo que SonarQube no marcó:** `OpenLibraryService.php`, el archivo más grande y el que habla con
la API externa, no tuvo ningún issue: usa `Http::timeout(10)`, maneja fallos con `failed()` y
registra errores en el log. Tampoco hubo vulnerabilidades ni hotspots, pero eso **no significa que
el PR fuera seguro** (ver §5, M-1).

**Limitación de la edición usada:** el propio dashboard advierte *"Limited security analysis —
SonarQube Community Build does not scan for critical injection vulnerabilities (SQL injection, XSS,
and more)"*. El análisis de flujo de datos (*taint analysis*) que detecta inyecciones solo existe en
las ediciones de pago. Por eso "0 vulnerabilidades" aquí significa "ninguna detectable por las
reglas de la Community Build", no "ninguna vulnerabilidad".

## 5. Revisión manual (lo que SonarQube no detecta)

| # | Severidad | Hallazgo | Archivo |
|---|---|---|---|
| M-1 | **Alta** | **Rutas sin autenticación.** `GET /open-library/buscar` y `POST /open-library/importar` se registraron sin middleware `auth`, y `ImportarLibroRequest::authorize()` devuelve `true`. Cualquier visitante sin sesión podía importar libros a la base y encolar trabajos que llaman a la API externa. El análisis estático no evalúa reglas de autorización de negocio, por eso no aparece como vulnerabilidad | `routes/web.php`, `ImportarLibroRequest.php` |
| M-2 | Media | **`olid` sin formato ni longitud.** Solo se valida como `string` y se interpola en la URL `https://openlibrary.org/works/{$olid}.json`. El host está fijo (no permite SSRF a otros dominios), pero sí manipular la ruta dentro de openlibrary.org (p. ej. `../authors/...`) y guardar valores arbitrarios | `ImportarLibroRequest.php`, `OpenLibraryService::importarLibro` |
| M-3 | Media | **El OLID se guarda en la columna `isbn`** (`Libro::firstOrCreate(['isbn' => $olid], ...)`). Un libro importado queda con un "ISBN" que no lo es | `OpenLibraryService::importarLibro` |
| M-4 | Baja | **Accesos sin respaldo a datos externos.** `$datos['title']` y `$datos['name']` se usan sin `??`; si la API responde sin esos campos, el job falla con error de índice | `OpenLibraryService::importarLibro`, `obtenerOCrearAutor` |
| M-5 | Baja | **Año frágil.** `(int) substr($fecha, -4)` asume que la fecha termina en el año; con formatos como `1965-03` produce `5` | `OpenLibraryService` |
| M-6 | Baja | **Código sin uso.** `buscarPorIsbn()` no se llama desde la aplicación | `OpenLibraryService` |
| M-7 | Media | **Pruebas parciales.** Las 3 pruebas cubren el servicio con `Http::fake` (búsqueda, fallo de la API, importación), lo cual es un acierto; pero no hay pruebas del controlador, de la validación ni del acceso a las rutas, justo donde estaba M-1 | `tests/Feature/OpenLibraryServiceTest.php` |

## 6. Estado actual en `develop` (`e52765e`)

| Hallazgo | ¿Sigue? | Detalle |
|---|---|---|
| M-1 rutas sin auth | **Resuelto** | Issue #118 → PR #121: `buscar` e `importar` dentro de `auth`, e `importar` además con `role:admin,superadmin` |
| M-7 pruebas de acceso | **Resuelto** | El #121 agregó `OpenLibraryControllerTest` con pruebas de autenticación, rol y validación (8 pruebas) |
| M-2 validación de `olid` | **Sigue** | La regla sigue siendo `['required', 'string']` |
| M-3 OLID en `isbn` | **Sigue** | Misma línea en `importarLibro` |
| M-6 `buscarPorIsbn` | **Sigue sin usarse en la app** | Hoy solo lo usan pruebas |
| S-1 / S-2 inputs sin label | **Sigue** | `libros/buscar.blade.php` no tiene ningún `<label>` |
| S-3 salto de línea final | **Sigue** | Regla `php:S113`, la más frecuente del proyecto (32 casos en el análisis de `develop`) |

## 7. Recomendaciones

| Prioridad | Acción | Resuelve |
|---|---|---|
| Alta | Validar `olid` con `regex:/^OL\d+W$/` y `max:20` | M-2 |
| Media | Guardar el OLID en su propia columna (`olid`, única) o documentar que `isbn` puede contener un OLID | M-3 |
| Media | Agregar `<label>` (visible o `sr-only`) con `for`/`id` a los campos de `libros/buscar.blade.php` | S-1, S-2 |
| Baja | Usar `?? 'Sin título'` / `?? null` en los campos de la respuesta y extraer el año con una expresión regular (`/\d{4}/`) | M-4, M-5 |
| Baja | Eliminar `buscarPorIsbn()` o usarlo en un flujo real de búsqueda por ISBN | M-6 |
| Baja | Ejecutar Laravel Pint (`./vendor/bin/pint`) para normalizar el salto de línea final | S-3 |
| Proceso | Revisar que cada ruta nueva que escribe datos tenga `auth`/`role:` (constitución, principio VI); SonarQube no lo detecta | M-1 |

## 8. Conclusión

El PR #59 **pasa el Quality Gate** y agrega una integración externa bien estructurada: servicio
separado del controlador, Form Requests, job en cola, timeouts y pruebas con `Http::fake`.
SonarQube solo encontró 2 bugs de accesibilidad y 5 detalles cosméticos, sin vulnerabilidades. Sin
embargo, **el problema más serio del PR, las rutas de importación abiertas a cualquier visitante,
no es detectable por análisis estático**, y la Community Build tampoco busca inyecciones: el
problema se encontró en revisión y se corrigió después en el PR #121. Es un buen ejemplo de que SonarQube complementa, pero no reemplaza, la revisión de código y
las pruebas de autorización. La validación del `olid` y el uso de la columna `isbn` siguen pendientes.

## 9. Evidencia

- Captura del dashboard del proyecto `BibliotecaOnline-PR59-pr`: [`guadalupe-captura.png`](guadalupe-captura.png)
- Dashboards locales: `http://localhost:9001/dashboard?id=BibliotecaOnline-PR59-pr` y
  `...?id=BibliotecaOnline-PR59-base`
