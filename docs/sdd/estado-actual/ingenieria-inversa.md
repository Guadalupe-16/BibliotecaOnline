# Ingeniería inversa del estado actual de BibliotecaOnline

Issue #135 · Rama base: `develop` (commit `1aabc46`) · Fecha: 2026-09-25

Todo lo descrito se obtuvo leyendo el código, las migraciones, `php artisan route:list`, los archivos
de prueba, los workflows y el historial Git del repositorio. Los conteos provienen de comandos
ejecutados ese día. Lo que no se pudo comprobar figura como "No identificado".

## 1. Identificación del sistema

| Dato | Valor |
|---|---|
| Nombre | BibliotecaOnline (README: "Biblioteca Digital Online") |
| Propósito | Aplicación web para consultar el catálogo de una biblioteca, buscar libros, guardarlos como favoritos e importar libros desde Open Library, con paneles de administración |
| Problema que resuelve | Consulta y gestión en línea de libros, usuarios y roles, con registro de actividad |
| Roles | `usuario`, `admin`, `superadmin` (campo `users.rol`); además visitantes sin sesión |
| Origen | Proyecto académico en equipo (README y `docs/conclusiones.md`) |
| Primer y último commit | 2026-02-18 y 2026-09-25; 184 commits en `develop` |
| Releases | Tags `v1.0.0` (commit `86cef0a`) y `v2.0.0` (commit `e9b776b`) |

## 2. Stack tecnológico real

Versiones leídas de `composer.lock` y `package-lock.json`.

| Capa | Tecnología | Versión instalada | Fuente |
|---|---|---|---|
| Lenguaje | PHP | requerido `^8.2`; ejecutado en local con 8.3.14 | `composer.json` |
| Framework | Laravel | v12.52.0 | `composer.lock` |
| UI reactiva | Livewire | v4.1.4 | `composer.lock` |
| Plantillas | Blade | (incluido en Laravel) | `resources/views/` |
| JS ligero | Alpine.js | 3.15.8 | `package-lock.json` |
| Estilos | Tailwind CSS | 4.2.0 (plugin `@tailwindcss/vite`) | `package-lock.json` |
| Build | Vite | 7.3.1 (`laravel-vite-plugin` 2.x) | `package-lock.json` |
| Base de datos | MySQL en desarrollo (`.env` local); SQLite en memoria en pruebas | `phpunit.xml` | |
| Pruebas PHP | PHPUnit | 11.5.55 | `composer.lock` |
| Pruebas JS | Vitest 4.1.0 + jsdom | | `package-lock.json` |
| Pruebas E2E | Playwright | 1.58.2 (Chromium) | `package-lock.json` |
| Pruebas grabadas | Selenium IDE (`docs/selenium/BibliotecaOnline.side`) | | |
| Calidad JS | ESLint 9.39.4 + Prettier 3.8.1 | | `package-lock.json` |
| Cola | `QUEUE_CONNECTION=database` en `.env.example` | | |
| CI | GitHub Actions (`.github/workflows/ci.yml`) | | |

Observación: `.env.example` define `DB_CONNECTION=sqlite`, mientras el `.env` usado en desarrollo local
apunta a MySQL (`biblioteca_online`).

## 3. Arquitectura

Aplicación Laravel monolítica con arquitectura MVC y renderizado en servidor:

```text
Ruta (routes/web.php) → Middleware (auth, guest, role, throttle)
    → Controlador (app/Http/Controllers) → Modelo Eloquent (app/Models) → Base de datos
                                         → Vista Blade (resources/views)
Componentes Livewire (app/Livewire): BuscadorLibros, ActivityLogger, y componentes de una sola
    archivo en resources/views/components/⚡*.blade.php (chat de soporte, activity-logger)
```

Patrones presentes, con su alcance real:

- **Repository:** solo `UsuarioRepository`, usado únicamente por `UsuarioController`. No es un
  patrón global.
- **Service:** solo `OpenLibraryService` (cliente HTTP a Open Library), inyectado en
  `OpenLibraryController` y `ImportarLibroJob`.
- **Jobs en cola:** `LogActivityJob` (registro asíncrono de actividad, disparado desde
  `ActivityLog::registrar`) e `ImportarLibroJob` (importación desde Open Library).
- **Eventos:** `AppServiceProvider` escucha `Login` y `Logout` de Laravel y registra actividad.
- **Form Requests:** `BuscarLibroRequest` e `ImportarLibroRequest`.
- **Middleware propio:** `VerificarRol` (alias `role`), registrado en `bootstrap/app.php`.
- **Rate limiting:** cuatro limitadores definidos en `AppServiceProvider` (`login`, `registro`,
  `verificacion-pin`, `reenvio-pin`).
- **Mail:** `VerificacionPinMail` (PIN de 6 dígitos, guardado en caché 15 minutos).

Los demás controladores acceden a los modelos Eloquent directamente.

## 4. Inventario de módulos

Estado verificado contra rutas, controladores y vistas.

| Módulo | Existe | Implementación principal |
|---|---|---|
| Autenticación (login/logout) | Sí | `AuthController`, vista `auth/login`, throttling |
| Registro | Sí | `AuthController@registrar`, vista `auth/register` |
| Verificación de correo con PIN | Sí | `VerificacionEmailController`, `VerificacionPinMail`, caché 15 min |
| Recuperación de contraseña | Sí | `RecuperacionContrasenaController` con `Password::sendResetLink` |
| Catálogo | Sí | `CatalogoController@index`, vista `libros/catalogo` |
| Detalle de libro | Sí | `LibroController@show`, vista `libros/detalle` |
| Búsqueda dinámica | Sí | Componente Livewire `BuscadorLibros` (término, categoría y autor dependiente) en `/buscar` |
| Favoritos | Sí | `FavoritoController` (`index`, `toggle`), modelo `Favorito` |
| Perfil de usuario | Sí | `PerfilController` (datos y foto en disco `public`) |
| CRUD de usuarios | Sí | `UsuarioController` + `UsuarioRepository` |
| RBAC (panel de roles) | Sí | `RbacController`, vista `admin/rbac/index` |
| Panel de superadministrador | Sí | `SuperAdminController` (listar, activar/desactivar, cambiar rol) |
| Gráfica de usuarios | Sí | `SuperAdminController@grafica` y `@statsUsuarios` |
| Activity Logs | Sí | `ActivityLog`, `LogActivityJob`, panel Livewire `ActivityLogger` en `/admin/logs` |
| Open Library | Sí | `OpenLibraryController`, `OpenLibraryService`, `ImportarLibroJob` |
| Sitemap y robots | Sí | `SitemapController`, ruta `/robots.txt` |
| Chat / formulario de soporte | Sí | Componente `⚡chat-soporte`, modelo `MensajeSoporte` |
| Páginas estáticas | Parcial | `welcome` (ruta `/`) y `errors/404` funcionan; las vistas `about` y `contacto` existen pero **no tienen ruta** en `routes/web.php` |
| Préstamos | Parcial | Modelo `Prestamo` y migración; **no hay rutas ni controlador** |
| `/admin` (panel) | Parcial | La ruta devuelve solo el texto "Panel de administrador" |
| Dashboard | Parcial | Vista `dashboard` sin controlador |

## 5. Modelos y entidades

| Modelo | Tabla | Responsabilidad | Relaciones Eloquent |
|---|---|---|---|
| `User` | `users` | Cuenta y rol; métodos de rol (`esAdmin`, `esSuperadmin`, `tieneRol`, `puedeAsignarRol`, ...) | `hasMany` Favorito |
| `Libro` | `libros` | Libro del catálogo; scopes `buscar` y `porCategoria` | `belongsTo` Autor, `belongsTo` Categoria, `hasMany` Prestamo |
| `Autor` | `autores` | Autor | `hasMany` Libro |
| `Categoria` | `categorias` | Categoría con color | `hasMany` Libro |
| `Favorito` | `favoritos` | Libro marcado por un usuario | `belongsTo` User (`user_id`), `belongsTo` Libro |
| `Prestamo` | `prestamos` | Préstamo de un libro (estados activo/devuelto/vencido) | `belongsTo` User, `belongsTo` Libro |
| `ActivityLog` | `activity_logs` | Registro de acciones | `belongsTo` User |
| `MensajeSoporte` | `mensaje_soportes` | Mensajes del chat de soporte | Ninguna |

`User` no declara `hasMany` hacia `Prestamo` ni `ActivityLog`; la relación existe solo desde el otro lado.
Diagrama completo en [`er-diagram.md`](er-diagram.md).

## 6. Rutas principales

`php artisan route:list --except-vendor` reporta **39 rutas**. Clasificación de
las definidas en `routes/web.php`:

| Grupo | Middleware | Rutas |
|---|---|---|
| Públicas | ninguno | `/`, `/catalogo`, `/libros/{libro}`, `/buscar`, `/open-library`, `/sitemap.xml`, `/robots.txt`, `/forgot-password` (GET y POST), `/verificar-email/{id}` (GET, POST y `/reenviar`, con throttle) |
| Guest | `guest` (+ `throttle`) | `/login`, `/register` (GET y POST), `/reset-password` (GET y POST) |
| Auth | `auth` | `/logout`, `/favoritos`, `/favoritos/{libro}/toggle`, `/perfil` (GET y POST), `/dashboard`, `/open-library/buscar`, **`/usuarios` (index, edit, update, destroy)** |
| Admin | `auth` + `role:admin,superadmin` | `/admin`, `/admin/roles`, `PUT /admin/roles/{id}`, `/admin/logs`, `POST /open-library/importar` |
| Superadmin | `auth` + `role:superadmin` | prefijo `/superadmin`: índice, `usuarios/{id}/toggle-estado`, `usuarios/{id}/cambiar-rol/{rol}`, `stats/usuarios`, `grafica` |

Puntos de atención:
- Las cuatro rutas de `/usuarios` solo exigen `auth`, no un rol (ver hallazgo en la sección 11).
- `POST /logout` está definida dos veces en `routes/web.php`.
- `/open-library` (formulario) es pública; `/open-library/buscar` exige sesión (issue #118).
- La ruta temporal `/login-super` se eliminó en el issue #116.

## 7. Base de datos

15 migraciones en `database/migrations/`.

| Migración | Efecto |
|---|---|
| `0001_01_01_000000_create_users_table` | `users`, `password_reset_tokens`, `sessions` |
| `0001_01_01_000001_create_cache_table` | `cache`, `cache_locks` |
| `0001_01_01_000002_create_jobs_table` | `jobs`, `job_batches`, `failed_jobs` |
| `2026_03_10_000001..4` | `autores`, `categorias`, `libros`, `prestamos` |
| `2026_03_13_011958_crear_tabla_favoritos` | `favoritos` con `unique(user_id, libro_id)` |
| `2026_03_13_044514_create_mensaje_soportes_table` | `mensaje_soportes` |
| `2026_03_13_061257_agregar_rol_a_users` | `users.rol` enum `admin, usuario` |
| `2026_03_13_061637_create_activity_logs_table` | `activity_logs` |
| `2026_03_14_161703_agregar_superadmin_a_rol_users` | enum de `rol` con `superadmin` |
| `2026_03_14_162208_agregar_activo_a_users` | `users.activo` boolean |
| `2026_03_15_144009_agregar_foto_a_users` | `users.foto` |
| `2026_08_18_133601_agregar_indices_busqueda_a_libros_y_autores` | `unique(autores.nombre)`; índice `fullText(libros.titulo)` solo en MySQL |

- **Claves foráneas:** `libros.autor_id` → `autores` (`nullOnDelete`); `libros.categoria_id` →
  `categorias` (`cascadeOnDelete`); `prestamos.user_id` → `users` y `prestamos.libro_id` → `libros`
  (`cascadeOnDelete`); `favoritos.user_id` y `favoritos.libro_id` (`cascade`);
  `activity_logs.user_id` → `users` (`nullOnDelete`).
- **Índices únicos:** `users.email`, `categorias.nombre`, `libros.isbn`, `autores.nombre`,
  `favoritos(user_id, libro_id)`.
- **Enums / estados:** `users.rol` (`admin`, `usuario`, `superadmin`), `prestamos.estado`
  (`activo`, `devuelto`, `vencido`), `mensaje_soportes.estado` (`pendiente`, `atendido`).
- **Seeders:** `DatabaseSeeder`, `BibliotecaSeeder` y `RolesSeeder` (crea usuarios con rol
  `superadmin`, `admin` y `usuario`).
- **Factories:** `Autor`, `Categoria`, `Libro`, `Prestamo` y `User`.

## 8. Pruebas actuales

Conteos obtenidos de los archivos y de las ejecuciones del 2026-09-25 sobre `develop`.

| Tipo | Archivos | Casos | Resultado observado |
|---|---:|---:|---|
| Unit (PHPUnit) | 7 | 26 | Aprobadas |
| Feature (PHPUnit) | 17 | 92 | Aprobadas |
| **PHPUnit total** | 24 | **118** (209 aserciones) | `php artisan test`: 118 passed |
| Vitest | 4 | 18 | `npm run test`: 4 archivos y 18 tests aprobados (tras #142) |
| Playwright E2E | 5 | 14 | `npx playwright test`: 14 passed, con `npm run build` previo |
| Selenium IDE | 1 proyecto | 7 tests | Documentados en `docs/selenium/BibliotecaOnline.side`; no se ejecutaron en esta revisión |
| ESLint | | | 0 errores, 1 advertencia (`esFavoritoActual` sin uso en `favorito.test.js`) |
| Prettier | | | Sin problemas |

Tests Selenium IDE: `login-exitoso`, `busqueda-libro`, `catalogo-navegacion`, `favoritos`,
`panel-logs`, `gestion-usuarios`, `buscar-open-library`.

Hallazgos sobre la calidad de las pruebas:
- **Los tests de Vitest no importan código de la aplicación.** Cada archivo define sus propias
  funciones (por ejemplo `construirUrlBusqueda` en `busqueda.test.js`) y las prueba; validan la lógica
  copiada en el test, no `resources/js/app.js`.
- Sin `npm run build`, 3 de los 14 tests Playwright fallan (falta el manifiesto de Vite).
- `tests/e2e/example.spec.js` es el ejemplo de Playwright y prueba `playwright.dev`, no la aplicación.
- `tests/e2e/contacto.spec.js` se llama "contacto" pero verifica catálogo y login.
- Sin pruebas identificadas para `ActivityLog`, `LogActivityJob`, el panel `ActivityLogger`, el
  sitemap ni el chat de soporte.
- Los E2E no cubren registro, recuperación de contraseña, favoritos, roles ni paneles de admin.
- Cobertura HTML generada en `docs/coverage/` (Issue #102); su porcentaje no se recalculó aquí.

## 9. CI actual

Archivo `.github/workflows/ci.yml` (67 líneas), nombre "CI — BibliotecaOnline".

- **Triggers:** `push` y `pull_request` sobre `main` y `develop`.
- **Job `lint-format` (ESLint + Prettier):** Node 20, `npm ci`, `npm run lint`, `npm run format:check`.
- **Job `php-tests` (PHPUnit Tests):** PHP 8.2 con `mbstring, sqlite3, pdo_sqlite, gd` y xdebug, `composer
  install`, copia `.env.example`, `key:generate`, `migrate --force` sobre SQLite en memoria y
  `php artisan test`.
- **Qué no ejecuta:** Vitest (`npm run test`), `npm run build`, Playwright, cobertura como artefacto,
  ni despliegue. Tampoco hay más workflows.
- Los E2E y Vitest están planeados en el Issue #138.

## 10. Flujo Git actual

- **Ramas permanentes:** `main` y `develop`. `develop` es la rama de integración; `main` quedó en el
  commit `86cef0a` (2026-03-13, PR #86) y `develop` la supera por 87 commits (0 en sentido contrario).
  El tag `v2.0.0` apunta a un commit posterior a `main` (`e9b776b`, PR #93).
- **Prefijos de rama en el remoto (unas 60 ramas):** `feat`, `feature`, `fix`, `chore`, `docs`, `perf`,
  `test`, más algunas sin prefijo estándar (`32-feat-vista-de-inicio-de-sesión`, `fix-arreglo`,
  `revert-74-feature`). El hook local exige `feat|fix|chore|docs|style|refactor|perf|test|ci|build|revert`,
  por lo que `feature/*` (usado antes en 17 ramas) ya no se puede commitear.
- **Hook `prepare-commit-msg`:** no versionado; reescribe el commit a `tipo(scope): mensaje`,
  añade `Branch:` y `Fixes: #N`, y falla en silencio si la rama no lleva número de Issue.
- **Issues y PRs:** los Pull Requests llegan hasta el #144. Los números de rama suelen coincidir con el
  Issue (por ejemplo `fix/124-throttling-auth`).
- **Autores en el historial (nombres tal cual en Git):** ElJavierB0, Guadalupe Amavizca,
  Guadalupe-16, Javier Antonio Romo Bernal, Javier Bernal, Joel Ibarra, joeljorex, Jorge Martinez,
  Jorge Martínez, Jorge Humberto Martínez Delgado y BibliotecaOnline Team; varias personas aparecen con
  más de un nombre.
- Documentación del flujo: [`docs/planeacion/flujo-control-versiones.md`](../../planeacion/flujo-control-versiones.md).

## 11. Hallazgos

### Fortalezas
- Suite PHPUnit amplia y en verde (118 pruebas) que corre en CI.
- RBAC con middleware `role:`, reglas de asignación en `User` y pruebas negativas (`RbacTest`, 13 casos).
- Throttling en login, registro y PIN (issue #124); ruta `/login-super` eliminada (#116).
- Registro de actividad asíncrono mediante cola.
- Índices de búsqueda y `eager loading` aplicados (#120, #133).
- Historial ordenado por Issues y PRs, con hook de commits.

### Riesgos
- **Seguridad:** `GET /usuarios`, `GET /usuarios/{id}/edit`, `PUT /usuarios/{id}` y
  `DELETE /usuarios/{id}` solo exigen `auth`; cualquier usuario con sesión puede listar, editar y
  eliminar usuarios. `UsuarioControllerTest` prueba estas rutas con un usuario normal y acepta el
  acceso, así que el comportamiento está fijado por las pruebas.
- Un token de GitHub aparece dentro de la URL del remoto `origin` en la configuración local de
  Git; no está en el repositorio, pero conviene revocarlo.
- `main` está desactualizada respecto de `develop` (87 commits).

### Inconsistencias
- `/logout` definida dos veces en `routes/web.php`.
- Vistas `about` y `contacto` sin ruta.
- `/admin` devuelve texto plano; `dashboard` no tiene controlador.
- `.env.example` usa SQLite y el desarrollo local usa MySQL; `APP_NAME=Laravel` y `APP_LOCALE=en` sin personalizar.
- `example.spec.js` prueba un sitio externo.
- `ASIGNACIONES.md` propone ramas `feature/`, que el hook actual rechaza.
- `Prestamo` existe sin flujo de uso.

### Deuda técnica
- Tests Vitest desacoplados del código real.
- Sin E2E para la mayoría de módulos; sin pruebas de ActivityLog ni sitemap.
- Sin `npm run build` ni E2E en CI; sin cobertura publicada por CI.
- Patrón Repository aplicado solo a usuarios.
- Advertencia de ESLint sin resolver.

### Falta de formalización SDD (antes del Issue #136)
- No había constitución, specs ni skills; los requisitos vivían solo en Issues.
- No existía trazabilidad formal Issue → requisito → prueba → PR.
- No había Definition of Ready/Done ni plantilla de PR.
- Con el Issue #136 ya se incorporaron Spec Kit, la constitución y la plantilla de PR; las specs
  001, 002 y 003 siguen PLANEADAS.

Análisis de brechas: [`analisis-brechas.md`](analisis-brechas.md) · Trazabilidad:
[`trazabilidad.md`](trazabilidad.md).
