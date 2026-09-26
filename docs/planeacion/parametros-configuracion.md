# Parámetros de configuración de herramientas

Issue #139 · Fecha: 2026-09-25 · Datos leídos de los archivos de configuración del repositorio en `develop`
(`016fd3a`) y de las instalaciones locales del equipo.

Estados: **IMPLEMENTADO** (existe en el repositorio), **VALIDADO** (existe y se comprobó que funciona),
**PLANEADO** (propuesta, no existe todavía). Las versiones salen de `composer.lock`, `package-lock.json`,
los workflows o del comando `--version` ejecutado; lo que no se pudo comprobar se declara.

## Resumen

| # | Herramienta | Estado |
|---|---|---|
| 1 | Git y hook `prepare-commit-msg` | VALIDADO |
| 2 | GitHub (Issues, PRs, plantillas, tablero) | VALIDADO |
| 3 | GitHub Spec Kit y skills de Claude Code | VALIDADO |
| 4 | Composer | VALIDADO |
| 5 | npm y Vite | VALIDADO |
| 6 | PHPUnit | VALIDADO |
| 7 | Vitest | IMPLEMENTADO (no corre en CI) |
| 8 | ESLint y Prettier | VALIDADO |
| 9 | Playwright | VALIDADO |
| 10 | Playwright MCP | PLANEADO |
| 11 | Selenium IDE | IMPLEMENTADO (no ejecutado en esta revisión) |
| 12 | GitHub Actions | VALIDADO |
| 13 | Dev Container / Codespaces | IMPLEMENTADO (no validado en Codespaces) |
| 14 | Driver.js | PLANEADO |
| 15 | Terraform | PLANEADO |

---

## 1. Git y hook `prepare-commit-msg`

**Planeación de uso**: control de versiones. Ramas `main` y `develop`, ramas de trabajo `tipo/NNN-descripcion`,
PRs hacia `develop`. Estado: existente. Flujo completo en [`flujo-control-versiones.md`](flujo-control-versiones.md).

**Instalación**: Git 2.47.1 (comprobado con `git --version`). El hook no se instala solo: cada clon debe crear
`.git/hooks/prepare-commit-msg` y darle permiso de ejecución (README, paso 5).

**Parámetros**

| Parámetro | Valor | Efecto |
|---|---|---|
| Tipos de rama válidos | `feat\|fix\|chore\|docs\|style\|refactor\|perf\|test\|ci\|build\|revert` | Un tipo distinto (por ejemplo `feature/`) hace fallar el commit |
| Formato de commit | `tipo(scope): mensaje` | El hook reescribe el mensaje con el tipo de la rama y el scope (palabra tras el número de Issue) |
| Trailers automáticos | `Branch: <rama>` y `Fixes: #N` | Enlaza el commit con el Issue |
| Límite de título | 72 caracteres | Rechaza títulos más largos |
| Límite de líneas del cuerpo | 100 caracteres | Rechaza el commit |
| Número de Issue | Obligatorio de hecho | Sin número, el hook termina con código 1 y no muestra error |

**Implementación**: el hook vive en `.git/hooks/` (no versionado). El README solo indica `touch` y `chmod`, y su
paso 5 contiene la nota "PREGUNTAR POR CODIGO": no incluye el contenido del hook. **Evidencia**: los commits de los
PR #142 a #148 tienen el formato `tipo(scope): ...`; en los de Guadalupe se comprobó además `Branch:` y `Fixes:`. **Problema conocido**: brecha I3
en [`analisis-brechas.md`](../sdd/estado-actual/analisis-brechas.md).

## 2. GitHub

**Planeación de uso**: alojar el repositorio `Guadalupe-16/BibliotecaOnline`, gestionar Issues, Pull Requests y un
tablero de proyecto. Estado: existente.

**Instalación**: cuenta de GitHub por integrante; `origin` apunta al repositorio.

**Parámetros**

| Parámetro | Valor | Efecto |
|---|---|---|
| Plantillas de Issue | `.github/ISSUE_TEMPLATE/`: `bug_report.yml`, `feature_request.yml`, `task.yml` | Estandarizan el reporte |
| Plantilla de PR | `.github/PULL_REQUEST_TEMPLATE.md` (agregada en #144) | Exige Issue, spec, cambios, validación y evidencia |
| Rama de integración | `develop` | Destino de los PR |
| Cierre de Issues | `Closes #N` en el PR y `Fixes: #N` en el commit | Cierra el Issue al hacer merge |
| Protección de ramas | No identificada: no se pudo consultar la configuración del repositorio | No se afirma que exista |

**Implementación**: PR #142 a #148 mezclados en `develop`. **Nota**: el remoto `origin` de `.git/config` de una de las
máquinas contiene un token en la URL; conviene revocarlo (brecha S2).

## 3. GitHub Spec Kit y skills de Claude Code

**Planeación de uso**: Spec-Driven Development: pasar de Issue a spec, plan, tareas, análisis y PR. Estado: nuevo
(#136).

**Instalación** (registro completo en [`guia-instalacion-spec-kit.md`](../sdd/guia-instalacion-spec-kit.md)):

```bash
pip install uv
uv tool install specify-cli --from git+https://github.com/github/spec-kit.git@v1.0.12
specify init --here --force --non-interactive --integration claude --script ps
```

Prerrequisitos: Python 3.11 o superior (3.12.2 usado), uv 0.12.19, Claude Code.

**Parámetros** (`.specify/init-options.json` e `.specify/integration.json`)

| Parámetro | Valor | Efecto |
|---|---|---|
| `integration` | `claude` | Instala skills en `.claude/skills/` |
| `script` | `ps` | Scripts PowerShell en `.specify/scripts/powershell/` |
| `feature_numbering` | `sequential` | Specs numeradas `001-nombre` |
| `speckit_version` | `1.0.12` | Versión instalada |
| Constitución | `.specify/memory/constitution.md` v1.0.0 | Reglas del proyecto |

**Implementación**: 10 skills `/speckit-*`; `specs/002-*` se generó con `/speckit-specify` y `/speckit-plan`. Existen
specs 001, 002 y 003 en `develop`. **Evidencia**: PR #144, #147, #148 y #146.

## 4. Composer

**Planeación de uso**: dependencias PHP y scripts de proyecto. Estado: existente.

**Instalación**: Composer 2.2.25 (local); PHP `^8.2` requerido (8.3.14 local, 8.2 en CI).

**Parámetros** (`composer.json`)

| Parámetro | Valor | Efecto |
|---|---|---|
| `laravel/framework` | `^12.0` (instalado 12.52.0) | Framework |
| `livewire/livewire` | `^4.1` (instalado 4.1.4) | Componentes reactivos |
| `phpunit/phpunit` | `^11.5.3` (instalado 11.5.55) | Pruebas |
| Script `test` | `config:clear` y `php artisan test` | Atajo de pruebas |
| Script `dev` | `concurrently` con `serve`, `queue:listen`, `pail` y `npm run dev` | Entorno de desarrollo |

**Implementación**: `composer.json` y `composer.lock`. **Evidencia**: `composer install` en CI y en el devcontainer.

## 5. npm y Vite

**Planeación de uso**: dependencias JavaScript y compilación de assets. Estado: existente.

**Instalación**: Node 22.13.1 (local) y 20 (CI y devcontainer); `npm ci`.

**Parámetros** (`package.json`, `vite.config.js`)

| Parámetro | Valor | Efecto |
|---|---|---|
| Vite | 7.3.1 con `laravel-vite-plugin` | Compila `resources/css/app.css` y `resources/js/app.js` |
| Tailwind | 4.2.0 (`@tailwindcss/vite`) | Estilos |
| Alpine.js | 3.15.8 | JS ligero |
| `npm run build` | `vite build` | Genera `public/build` (obligatorio para los E2E) |
| `test.include` (Vitest) | `resources/js/**/*.test.js` | Evita recoger los specs de Playwright (#142) |

**Evidencia**: `npm run build` compiló correctamente el 2026-09-25; job `E2E Playwright` de CI.

## 6. PHPUnit

**Planeación de uso**: pruebas Unit y Feature de PHP. Estado: existente.

**Parámetros** (`phpunit.xml`)

| Parámetro | Valor | Efecto |
|---|---|---|
| `DB_CONNECTION` / `DB_DATABASE` | `sqlite` / `:memory:` | Base aislada por prueba |
| `MAIL_MAILER` | `array` | No envía correos |
| `QUEUE_CONNECTION` | `sync` | Los jobs corren en línea |
| `SESSION_DRIVER` / `CACHE_STORE` | `array` | Sin persistencia |
| `BCRYPT_ROUNDS` | `4` | Hash rápido en pruebas |

**Comando**: `php artisan test`. **Evidencia**: 118 pruebas aprobadas (209 aserciones) el 2026-09-25; run de CI
36205499377 (PR #146) con `PHPUnit Tests` en verde según [`plan-de-pruebas.md`](plan-de-pruebas.md).

## 7. Vitest

**Planeación de uso**: pruebas de JavaScript. Estado: existente, con limitaciones.

**Parámetros** (`vite.config.js`): entorno `jsdom`, cobertura `v8` con reportes `text` y `html`, `include` limitado
a `resources/js/**/*.test.js`.

**Implementación**: 4 archivos y 18 tests aprobados el 2026-09-25 (versión 4.1.0). **Limitaciones**: los tests
definen sus propias funciones y no importan código de la aplicación (T4); no corren en CI.

## 8. ESLint y Prettier

**Planeación de uso**: calidad y formato del JavaScript. Estado: existente.

**Instalación**: ESLint 9.39.4 y Prettier 3.8.1 (`npm ci`).

**Parámetros**

| Herramienta | Parámetro | Valor |
|---|---|---|
| ESLint | `prettier/prettier` | `error` |
| ESLint | `no-unused-vars` y `no-console` | `warn` |
| ESLint | `globals` | `browser` |
| Prettier | `semi` / `singleQuote` | `false` / `true` |
| Prettier | `tabWidth`, `printWidth`, `trailingComma` | `2`, `100`, `es5` |

**Comandos**: `npm run lint`, `npm run format:check`. **Evidencia**: 0 errores y 1 advertencia de ESLint; Prettier sin
problemas (2026-09-25); job `ESLint + Prettier` de CI.

## 9. Playwright

**Planeación de uso**: pruebas E2E en navegador. Estado: existente; su evolución está en `specs/001-*`.

**Instalación**: `@playwright/test` 1.58.2 y `npx playwright install chromium` (en CI, con `--with-deps`).

**Parámetros** (`playwright.config.js`)

| Parámetro | Valor | Efecto |
|---|---|---|
| `testDir` | `./tests/e2e` | Carpeta de specs |
| `baseURL` | `PLAYWRIGHT_BASE_URL` o `http://127.0.0.1:8000` | Servidor bajo prueba |
| `workers` / `fullyParallel` | `1` / `false` | Ejecución serial |
| `retries` | `0` | Sin reintentos |
| `reporter` | `html` | Reporte en `playwright-report/` |
| Proyecto | `chromium` (Desktop Chrome) | Único navegador |

**Implementación**: `tests/e2e/` con 4 archivos tras #146 (12 pruebas según el plan de pruebas). Requiere
servidor levantado, base sembrada y `npm run build`. **Evidencia**: 14 pasadas localmente el 2026-09-25 (antes de
#146); 12 pasadas en la revisión de Javier; job `E2E Playwright` en verde en el run 36205499377.

## 10. Playwright MCP

**Planeación de uso**: diagnóstico y generación asistida de pruebas con un agente de IA. Estado: **PLANEADO**.

**Instalación prevista**: `npx @playwright/mcp@latest` con un `.mcp.json`. No está instalado ni configurado.
**Decisión y razones**: `specs/001-pruebas-e2e-playwright/research.md`, sección 7. No hay evidencia de uso.

## 11. Selenium IDE

**Planeación de uso**: pruebas grabadas de flujos. Estado: existente como documentación.

**Instalación**: extensión de navegador Selenium IDE; la versión de la extensión no está identificada.

**Parámetros**: `docs/selenium/BibliotecaOnline.side` (formato de proyecto `2.0`), URL base
`http://127.0.0.1:8001`, 7 tests grabados. **Implementación**: no corre en CI y no se ejecutó en esta revisión.
Ningún test tiene aserciones.

## 12. GitHub Actions

**Planeación de uso**: CI en cada push y PR a `main` y `develop`. Estado: existente, ampliado en #146.

**Parámetros** (`.github/workflows/ci.yml`)

| Job | Contenido |
|---|---|
| `lint-format` | Node 20, `npm ci`, `npm run lint`, `npm run format:check` |
| `php-tests` | PHP 8.2 con xdebug, `composer install`, `.env` de ejemplo, migraciones en SQLite, `php artisan test` |
| `e2e-playwright` | PHP 8.2 y Node 20, SQLite en archivo con seeders, `npm run build`, health check `/up`, Chromium, `npx playwright test`; publica `playwright-report` (14 días) y el log del servidor si falla; `timeout-minutes: 15` |

Acciones usadas: `actions/checkout@v4`, `actions/setup-node@v4`, `shivammathur/setup-php@v2`,
`actions/upload-artifact@v4`. **Evidencia**: run 36205499377 (PR #146) y 36205506706 (push a `develop`), con los
tres checks en verde, tal como consta en el plan de pruebas. No ejecuta Vitest.

## 13. Dev Container / Codespaces

**Planeación de uso**: entorno reproducible. Estado: IMPLEMENTADO; no se ha validado en Codespaces.

**Parámetros** (`.devcontainer/devcontainer.json`)

| Parámetro | Valor | Efecto |
|---|---|---|
| `image` | `mcr.microsoft.com/devcontainers/php:1-8.2-bookworm` | PHP 8.2 y Composer |
| `features` | `node:1`, versión 20 | Node y npm |
| `forwardPorts` | `8000`, `5173` | Laravel y Vite |
| `postCreateCommand` | `bash .devcontainer/post-create.sh` | Instala sqlite3, dependencias, `.env`, base SQLite con seeders y Chromium |
| Extensiones VS Code | Intelephense, Playwright, ESLint, Prettier, Tailwind CSS | Editor listo |

Sin secretos: el `.env` sale de `.env.example`. Documentado en el README y en `specs/003-infraestructura-cicd/`.

## 14. Driver.js

**Planeación de uso**: guía interactiva para usuarios nuevos. Estado: **PLANEADO**.

**Instalación prevista**: `npm install driver.js` (última versión estable 1.8.0, licencia MIT, según su
documentación). **No está instalado.** Diseño en `specs/002-guia-interactiva-driverjs/`; 33 tareas sin ejecutar.

## 15. Terraform

**Planeación de uso**: infraestructura como código para Staging y Production. Estado: **PLANEADO**.

**Instalación**: no instalado (el comando `terraform` no existe en la máquina revisada). El repositorio no
contiene archivos `.tf`. Solo existe [`propuesta-terraform.md`](propuesta-terraform.md), con bloques HCL ilustrativos
que no se han ejecutado con `terraform plan` ni `apply`. No se crean recursos ni se usan credenciales.

---

## Herramientas fuera de la lista

No se documentan herramientas que el proyecto no usa. `gh` (GitHub CLI) se instaló en una máquina durante esta
actividad pero no forma parte del flujo del repositorio.
