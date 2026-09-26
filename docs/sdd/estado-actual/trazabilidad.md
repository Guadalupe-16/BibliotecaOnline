# Trazabilidad de BibliotecaOnline

Issue #135 · Fecha: 2026-09-25

Relaciona cada módulo con su Issue, ruta, código, prueba y Pull Request.

**Fuentes y criterio:**
- Los números de PR salen del historial de merges de Git (`git log --merges`).
- El número de Issue se toma del nombre de la rama (`tipo/NNN-descripcion`), que es la convención del
  proyecto; **no se consultó GitHub para confirmar el título de cada Issue**. Donde la rama no lleva
  número de Issue, se indica "No identificado".
- Rutas, código y pruebas se comprobaron en el repositorio.

## 1. Matriz módulo → Issue → PR

| Módulo | Issue (por rama) | Ruta | Código principal | Prueba | PR | Estado |
|---|---|---|---|---|---|---|
| Login / logout | #32; #124 (throttling); #116 (ruta temporal eliminada) | `/login`, `/logout` | `AuthController`, `auth/login.blade.php`, `AppServiceProvider` | `ThrottlingAuthTest`, `RutaLoginSuperTest`, `login.spec.js`, `login.test.js` | #45; #131; #126 | Implementado |
| Registro | #36 | `/register` | `AuthController@registrar`, `auth/register.blade.php` | `VerificacionEmailPinTest` (registro crea usuario) | #56 | Implementado; sin E2E |
| Verificación de correo con PIN | #80 | `/verificar-email/{id}` | `VerificacionEmailController`, `VerificacionPinMail` | `VerificacionEmailPinTest` | #98 | Implementado |
| Recuperación de contraseña | #37; #14 | `/forgot-password`, `/reset-password` | `RecuperacionContrasenaController`, `auth/forgot-password`, `auth/reset-password` | `RecuperacionContrasenaTest` | #65; #97 | Implementado; sin E2E |
| Catálogo | #61 | `/catalogo` | `CatalogoController`, `libros/catalogo.blade.php` | `CatalogoTest`, `catalogo.spec.js` | #66 | Implementado |
| Detalle de libro | #68 | `/libros/{libro}` | `LibroController`, `libros/detalle.blade.php` | `LibroDetalleTest`, `catalogo.spec.js` | #70 | Implementado |
| Búsqueda dinámica | #71; #100 (campo dependiente) | `/buscar` | `Livewire\BuscadorLibros`, `livewire/buscador-libros.blade.php` | `BuscadorLibrosTest`, `busqueda.test.js`, `filtros.test.js` | #73; #101 | Implementado |
| Favoritos | #38 | `/favoritos`, `/favoritos/{libro}/toggle` | `FavoritoController`, `Favorito`, `favoritos/index.blade.php` | `FavoritoControllerTest`, `FavoritoTest`, `favorito.test.js` | #48; #85; #133 | Implementado; sin E2E |
| Perfil de usuario | #90 | `/perfil` | `PerfilController`, `perfil/index.blade.php` | `PerfilControllerTest` | #110 | Implementado |
| CRUD de usuarios | #40 | `/usuarios`, `/usuarios/{id}` | `UsuarioController`, `UsuarioRepository`, `usuarios/*.blade.php` | `UsuarioControllerTest` | #83; #112 | Implementado; **sin restricción de rol** |
| Control de acceso por roles | #39 | middleware `role:` | `VerificarRol`, `User::tieneRol` | `RbacTest` | #74; #78 | Implementado |
| RBAC (panel de roles) | #81 | `/admin/roles` | `RbacController`, `admin/rbac/index.blade.php` | `RbacTest`, `MigracionRolSuperadminTest` | #99; #114 | Implementado |
| Restricción de asignación de superadmin | #117 | `/admin/roles/{id}` | `User::puedeAsignarRol`, `RbacController` | `RbacTest` | #130 | Implementado |
| Panel de superadministrador | #91 | `/superadmin/*` | `SuperAdminController`, `superadmin/index.blade.php` | `SuperAdminControllerTest` | #93 | Implementado |
| Gráfica de usuarios | #92 | `/superadmin/grafica`, `/superadmin/stats/usuarios` | `SuperAdminController@grafica`, `superadmin/grafica.blade.php` | `GraficaUsuariosTest` | #111 | Implementado |
| Activity Logs (registro) | #69 | (evento y controladores) | `ActivityLog`, `LogActivityJob`, `AppServiceProvider` | No identificado | #80; #132 (cola) | Implementado; sin pruebas |
| Activity Logs (panel) | #104 | `/admin/logs` | `Livewire\ActivityLogger`, `logs/index.blade.php` | Selenium `panel-logs` | #105 | Implementado; sin pruebas automáticas |
| Open Library | #58; #118 | `/open-library`, `/open-library/buscar`, `/open-library/importar` | `OpenLibraryController`, `OpenLibraryService`, `ImportarLibroJob` | `OpenLibraryControllerTest`, `OpenLibraryServiceTest` | #59; #121 | Implementado |
| Sitemap / SEO | #81 (número compartido con RBAC) | `/sitemap.xml`, `/robots.txt` | `SitemapController`, `sitemap.blade.php` | No identificado | #82 | Implementado; sin pruebas |
| Chat de soporte | #27 | (componente en layout) | `⚡chat-soporte.blade.php`, `MensajeSoporte` | No identificado | #63 | Implementado; sin pruebas |
| Menú principal | #42 | (layout) | `components/navigation.blade.php`, `layouts/app.blade.php` | `navegacion.spec.js` | #47 | Implementado |
| Páginas About y Contacto | #43; #44 | No identificada | `about.blade.php`, `contacto.blade.php` | `contacto.spec.js` (no prueba contacto) | #72; #77 | Vistas existentes; sin ruta en `routes/web.php` ni en `route:list` |
| Página 404 personalizada | "404" (no es un Issue) | (error 404) | `errors/404.blade.php` | No identificado | #86 | Implementado |
| Backup y restauración de BD | #30 | (scripts) | `scripts/backup.sh`, `scripts/restore.sh` | No identificado | #31 | Scripts |
| Préstamos | No identificado | Sin rutas | `Prestamo`, `prestamos` (migración) | `PrestamoTest` (Unit) | No identificado | Solo modelo |

**Infraestructura y calidad:**

| Elemento | Issue (por rama) | Código | PR |
|---|---|---|---|
| CI con GitHub Actions | #88 | `.github/workflows/ci.yml` | #89 |
| Tests unitarios de modelos | #76 | `tests/Unit/` | #87 |
| Cobertura HTML | #102 | `docs/coverage/` | #103 |
| Selenium IDE | #106 | `docs/selenium/BibliotecaOnline.side` | #107 |
| Tests E2E Playwright | #84 | `tests/e2e/`, `playwright.config.js` | #113; #115 |
| Conclusiones y release | #108 | `docs/conclusiones.md`, tag `v2.0.0` | #109 |
| Índices de búsqueda | #120 | migración `2026_08_18_133601...` | #122 |
| Migración duplicada | #123 | (eliminada) | #127 |
| Suite en rojo | No identificado (rama `fix/tests-rojos-develop`) | `tests/` | #129 |
| Vitest excluye E2E | #134 | `vite.config.js` | #142 |
| Adopción de Spec Kit | #136 | `.specify/`, `.claude/skills/`, `docs/sdd/` | #144 |
| Flujo Git y despliegue | #141 | `docs/planeacion/` | #143 |

## 2. Matriz de cobertura por nivel de prueba

Conteos de `php artisan test` (118), Vitest (18), Playwright (14) y del proyecto Selenium IDE (7 tests).
"CI" indica si el pipeline actual ejecuta al menos una prueba automática del módulo (solo PHPUnit).

| Módulo | Unit | Feature | E2E | Selenium | CI |
|---|---:|---:|---:|---:|---|
| Login / logout / throttling | 0 | 11 | 3 | 1 | PHPUnit |
| Registro | 0 | (en verificación) | 0 | 0 | PHPUnit |
| Verificación con PIN | 0 | 6 | 0 | 0 | PHPUnit |
| Recuperación de contraseña | 0 | 6 | 0 | 0 | PHPUnit |
| Catálogo, detalle y navegación | 0 | 5 | 9 | 1 | PHPUnit |
| Búsqueda dinámica | 0 | 3 | 0 | 1 | PHPUnit |
| Favoritos | 2 | 4 | 0 | 1 | PHPUnit |
| Perfil | 0 | 6 | 0 | 0 | PHPUnit |
| CRUD de usuarios | 0 | 6 | 0 | 1 | PHPUnit |
| RBAC y roles | 0 | 17 | 0 | 0 | PHPUnit |
| Superadmin y gráfica | 0 | 12 | 0 | 0 | PHPUnit |
| Activity Logs | 0 | 0 | 0 | 1 | No |
| Open Library | 0 | 15 | 0 | 1 | PHPUnit |
| Sitemap / SEO | 0 | 0 | 0 | 0 | No |
| Chat de soporte | 0 | 0 | 0 | 0 | No |
| Modelos (Autor, Categoria, Libro, Prestamo, User) | 23 | 0 | 0 | 0 | PHPUnit |
| Ejemplos de plantilla (`ExampleTest`) | 1 | 1 | 2 (`example.spec.js`) | 0 | PHPUnit (los PHP) |
| **Total** | **26** | **92** | **14** | **7 tests** | |

Notas:
- Los E2E de "Catálogo, detalle y navegación" agrupan `catalogo.spec.js` (3), `navegacion.spec.js` (4)
  y `contacto.spec.js` (2); los 3 de login están en `login.spec.js`; 2 más son el ejemplo de
  Playwright. Total: 3+4+2+3+2 = 14.
- Vitest (18 tests) no aparece como columna: sus 4 archivos no importan código de la aplicación (ver
  [`ingenieria-inversa.md`](ingenieria-inversa.md), sección 8). Por archivo: búsqueda 6, filtros 4,
  favoritos 4 y login 4.
- Selenium IDE es documentación de pruebas grabadas; no se ejecuta en CI ni se ejecutó en esta revisión.
- Ningún E2E ni Vitest corre en CI todavía (Issue #138).

## 3. Trazabilidad requisitos → specs

Aún no hay specs versionadas con requisitos formales. Las primeras están planeadas:

| Spec | Issue | Estado |
|---|---|---|
| `specs/001-pruebas-e2e-playwright/` | #140 | PLANEADO |
| `specs/002-guia-interactiva-driverjs/` | #137 | PLANEADO |
| `specs/003-infraestructura-cicd/` | #138 | PLANEADO |

A partir de esas specs la cadena será Issue → Spec → Tasks → código → prueba → PR.
