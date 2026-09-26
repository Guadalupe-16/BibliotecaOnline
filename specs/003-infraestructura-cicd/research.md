# Research: Infraestructura CI/CD (spec 003)

**Fecha**: 2026-09-25 · **Issue**: #138

Etiquetas: **IMPLEMENTADO**, **VALIDADO**, **PLANEADO** (constitución, principio VIII).

---

## 1. Evidencia: simulación local del job E2E — VALIDADO (local)

Se simuló el job en una copia limpia del repositorio (git worktree de `develop` en `1aabc46`), con
`.env` creado desde `.env.example`, sin tocar el entorno de desarrollo de nadie.

| Paso | Comando | Resultado |
|---|---|---|
| Dependencias JS | `npm ci` | OK — `@playwright/test` 1.58.2 |
| Navegador | `npx playwright install chromium` | Chrome Headless Shell 145 (revisión 1208) |
| Base de datos | `touch database/database.sqlite && php artisan migrate --seed --force` | OK — `BibliotecaSeeder` y `RolesSeeder` sin servicios externos |
| Servidor | `php artisan serve --host=127.0.0.1 --port=8100` + espera a `/up` | Respondió en 2 s |
| E2E **sin** build | `npx playwright test` | **3 fallidas, 11 pasadas** (28.0 s) |
| Build | `npm run build` | OK (157 ms) |
| E2E **con** build | `npx playwright test` | **14 pasadas** (6.1 s) |
| E2E tras retirar `example.spec.js` y hacer `baseURL` configurable (T002–T004) | `PLAYWRIGHT_BASE_URL=http://127.0.0.1:8100 npx playwright test` | **12 pasadas** (5.5 s) |

Pruebas que fallan sin build (todas esperan elementos renderizados por Vite/Tailwind/Alpine):

- `catalogo.spec.js` › muestra el buscador dinámico
- `navegacion.spec.js` › muestra el menu lateral correctamente
- `navegacion.spec.js` › navega al formulario de contacto

Esto **confirma** la afirmación del Issue #138 (3 de 14 fallan sin build).

Entorno de la simulación: macOS, PHP 8.3.31, Node 25.9.0. El CI usará PHP 8.2 y Node 20; la
equivalencia se valida con el run real (SC-001).

Hallazgo adicional: el `node_modules` de al menos una máquina del equipo no tenía
`@playwright/test` instalado aunque está en `package-lock.json`; `npm ci` lo resuelve.

---

## 2. Base de datos del job E2E

- **Decision**: SQLite en archivo `database/database.sqlite` (ruta por defecto de Laravel cuando
  `DB_CONNECTION=sqlite` sin `DB_DATABASE`), migrado con `php artisan migrate --seed --force`.
- **Rationale**: `php artisan serve` y `php artisan migrate` son procesos distintos; con
  `:memory:` (lo que usa `php-tests`) cada proceso vería una base vacía. `.env.example` ya usa
  SQLite, así que no hay que reescribir variables. `SESSION_DRIVER`, `CACHE_STORE` y
  `QUEUE_CONNECTION` son `database` y sus tablas las crean las migraciones.
- **Alternatives considered**: MySQL como service container (más fiel a producción, pero más lento
  y la constitución establece SQLite para pruebas); `:memory:` (no funciona entre procesos).

## 3. `tests/e2e/example.spec.js`

- **Decision**: eliminar el archivo.
- **Rationale**: es la plantilla que genera `npm init playwright`; sus 2 pruebas navegan a
  `https://playwright.dev/` y no verifican nada de BibliotecaOnline. En CI introduce una dependencia
  de red externa que puede fallar por causas ajenas al proyecto (FR-006). No se elimina para pasar
  el CI (pasó en la simulación), así que no contradice el principio VII.
- **Alternatives considered**: `testIgnore` en `playwright.config.js` (deja código muerto);
  mantenerlo (riesgo de falsos rojos). Tras eliminarlo la suite queda en **12 pruebas** de la app.

## 4. Espera del servidor

- **Decision**: bucle `curl -fsS http://127.0.0.1:8000/up` cada segundo, máximo 60 intentos; si se
  agota, imprimir el log del servidor y salir con error.
- **Rationale**: `/up` ya existe (`bootstrap/app.php`, `health: '/up'`) y responde 200 solo cuando
  Laravel arranca (FR-004). Localmente respondió en 2 s; 60 s da margen para runners lentos.
- **Alternatives considered**: `sleep N` (arbitrario, prohibido por el Issue); `webServer` de
  Playwright (válido, pero mezcla la preparación de Laravel dentro de la config de pruebas y
  cambiaría el comportamiento local de todos los integrantes); acción `wait-on` (dependencia extra).

## 5. Estructura del job

- **Decision**: job independiente `e2e-playwright` sin `needs`, en paralelo con `lint-format` y
  `php-tests`, con `timeout-minutes: 15`. El reporte (`playwright-report/`) se sube con
  `actions/upload-artifact@v4` e `if: ${{ !cancelled() }}`, retención 14 días. El log del servidor se
  sube solo si el job falla.
- **Rationale**: un fallo E2E no oculta los demás checks (FR-002); el reporte está disponible pase o
  falle (FR-005). `forbidOnly: !!process.env.CI` ya está en la config y GitHub define `CI=true`
  (FR-007).
- **Alternatives considered**: `needs: [lint-format, php-tests]` (ahorra minutos si fallan antes,
  pero oculta información y alarga el tiempo total); un solo job con todo (un fallo de lint ocultaría
  el resultado E2E).

## 6. Brechas fuera de alcance

| Brecha | Por qué no se resuelve aquí | Estado |
|---|---|---|
| Vitest (`npm run test`) no corre en CI, aunque la constitución lo exige como puerta | No está en el alcance del Issue #138 | PLANEADO (proponer issue) |
| La prueba "detalle de un libro" pasa sin verificar nada: su selector `a` con texto `/ver\|detalle\|más/i` encuentra **0 enlaces** incluso con el seed (verificado con Playwright), y el `if` la deja pasar | Corregir las pruebas es alcance de la spec 001 (#140) | PLANEADO (#140) |
| Pruebas cuyo nombre no coincide con lo que verifican (p. ej. "navega a la pagina about" abre `/catalogo`) | Spec 001 (#140) | PLANEADO (#140) |
| Sin E2E de favoritos ni de roles | Spec 001 (#140) | PLANEADO (#140) |
| La constitución dice "MySQL en desarrollo", pero `.env.example` usa SQLite | Diferencia de documentación, no de CI | Registrar para revisión |

## 7. `baseURL` configurable

- **Decision**: `baseURL: process.env.PLAYWRIGHT_BASE_URL || 'http://127.0.0.1:8000'`.
- **Rationale**: el valor por defecto no cambia (CI y uso actual siguen igual), pero permite correr
  la suite contra otro puerto cuando el 8000 está ocupado (ocurrió en la simulación) o contra un
  host reenviado en el devcontainer.
- **Alternatives considered**: dejarlo fijo (obliga a editar el archivo para cambiar de puerto).

## 8. Devcontainer

- **Decision**: imagen `mcr.microsoft.com/devcontainers/php:1-8.2-bookworm` (incluye Composer y
  Xdebug; tag verificado en el registro de MCR) + feature `node:1` versión 20. Un script
  `.devcontainer/post-create.sh` instala `sqlite3`, dependencias, crea `.env` si no existe, genera
  `APP_KEY`, crea y migra SQLite con seed, y ejecuta `npx playwright install --with-deps chromium`.
  Puertos 8000 (Laravel) y 5173 (Vite).
- **Rationale**: misma versión de PHP y Node que el CI; imágenes oficiales mantenidas; el script
  versionado es más legible que un `postCreateCommand` en una sola línea.
- **Alternatives considered**: `Dockerfile` propio (más mantenimiento); Laravel Sail (usa MySQL y
  Docker Compose, más pesado de lo necesario).
- **Validación**: **no validado**. Docker no está instalado en la máquina donde se preparó y no se
  creó un Codespace. Queda como IMPLEMENTADO (archivo existe) pero no VALIDADO (FR-015).

## 9. Terraform

- **Decision**: documento de diseño `docs/planeacion/propuesta-terraform.md` con bloques HCL
  ilustrativos dentro de Markdown; ningún archivo `.tf`, `.tfvars` ni `.tfstate` en el repositorio.
- **Rationale**: no existe hosting (ver `docs/planeacion/estrategia-despliegue.md`); el Issue pide
  solo diseño. Un archivo `.tf` real podría ejecutarse por error (FR-013).
- **Alternatives considered**: módulo Terraform real sin aplicar (riesgo de ejecución accidental y
  de afirmar algo que no se validó).

## 10. Playwright: caché de navegadores en CI

- **Decision**: no cachear `~/.cache/ms-playwright` por ahora.
- **Rationale**: la documentación de Playwright desaconseja cachear navegadores porque el tiempo de
  restaurar la caché es similar al de descargarlos, y `--with-deps` debe instalar dependencias del
  sistema de todos modos. Simplicidad (constitución).
- **Alternatives considered**: `actions/cache` con clave por versión de Playwright.
