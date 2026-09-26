---

description: "Lista de tareas de la spec 003 — infraestructura CI/CD"
---

# Tasks: Infraestructura CI/CD — pruebas E2E en CI y entorno reproducible

**Input**: Documentos de diseño en `specs/003-infraestructura-cicd/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/, quickstart.md

**Tests**: No se generan tareas de pruebas unitarias; la verificación de esta feature es el run real
del CI (SC-001) y los escenarios de `quickstart.md`.

**Organization**: Tareas agrupadas por historia de usuario.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Puede ejecutarse en paralelo (archivos distintos, sin dependencias pendientes)
- **[Story]**: Historia de usuario (US1, US2, US3)

---

## Phase 1: Setup

**Purpose**: Confirmar la línea base antes de cambiar el CI

- [X] T001 Simular el job E2E en una copia limpia (worktree) y registrar los resultados con/sin `npm run build` en specs/003-infraestructura-cicd/research.md §1

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Ajustes a la suite E2E que necesitan tanto el CI (US1) como el devcontainer (US2)

- [X] T002 [P] Hacer `baseURL` configurable con `process.env.PLAYWRIGHT_BASE_URL || 'http://127.0.0.1:8000'` en playwright.config.js (el valor por defecto no cambia; research.md §7)
- [X] T003 [P] Eliminar la plantilla tests/e2e/example.spec.js (visita playwright.dev; research.md §3)
- [X] T004 Verificar en local que la suite pasa con 12 pruebas tras T002–T003 (`npx playwright test`) y registrar el resultado en specs/003-infraestructura-cicd/research.md §1

**Checkpoint**: la suite solo contiene pruebas de la aplicación y admite otro puerto

---

## Phase 3: User Story 1 - Las pruebas de navegador se ejecutan en cada PR (Priority: P1) 🎯 MVP

**Goal**: Un tercer check `E2E Playwright` en cada push/PR a `main` y `develop`

**Independent Test**: Abrir el PR; aparecen 3 checks en verde y el artefacto `playwright-report` (quickstart.md §1)

- [X] T005 [US1] Agregar el job `e2e-playwright` (nombre visible `E2E Playwright`, `runs-on: ubuntu-latest`, `timeout-minutes: 15`, sin `needs`) en .github/workflows/ci.yml, sin modificar `lint-format` ni `php-tests`
- [X] T006 [US1] En el job de T005, agregar los pasos de preparación en .github/workflows/ci.yml: checkout, PHP 8.2 (`mbstring, sqlite3, pdo_sqlite`, `coverage: none`), Node 20 con caché npm, `composer install --no-interaction --prefer-dist`, `npm ci`, `.env` desde `.env.example` + `php artisan key:generate`, `touch database/database.sqlite` + `php artisan migrate --seed --force`, `npm run build`
- [X] T007 [US1] En .github/workflows/ci.yml, arrancar `php artisan serve --host=127.0.0.1 --port=8000` en segundo plano redirigiendo a `storage/logs/serve.log` y esperar `curl -fsS http://127.0.0.1:8000/up` con un máximo de 60 intentos de 1 s; si se agota, imprimir el log y `exit 1` (contracts/ci-e2e-job.md pasos 8–9)
- [X] T008 [US1] En .github/workflows/ci.yml, ejecutar `npx playwright install --with-deps chromium` y `npx playwright test`
- [X] T009 [US1] En .github/workflows/ci.yml, publicar `playwright-report/` como artefacto `playwright-report` con `actions/upload-artifact@v4`, `if: ${{ !cancelled() }}`, `retention-days: 14`, y `storage/logs/serve.log` como `laravel-server-log` con `if: failure()`
- [X] T010 [US1] Validar la sintaxis del workflow (p. ej. `npx --yes yaml-lint` o `actionlint` si está disponible) antes de subir .github/workflows/ci.yml

**Checkpoint**: tras el push, el run real muestra los 3 checks (valida SC-001, SC-002, SC-003, SC-004)

---

## Phase 4: User Story 2 - Entorno de desarrollo listo en un clic (Priority: P2)

**Goal**: Codespace / Dev Container con todas las herramientas y la app lista para arrancar

**Independent Test**: quickstart.md §3

- [X] T011 [P] [US2] Crear .devcontainer/devcontainer.json con `image: mcr.microsoft.com/devcontainers/php:1-8.2-bookworm`, feature `ghcr.io/devcontainers/features/node:1` (`version: "20"`), `forwardPorts: [8000, 5173]` con `portsAttributes` etiquetados, `postCreateCommand: bash .devcontainer/post-create.sh` y extensiones de VS Code útiles (PHP, Playwright, ESLint, Prettier, Tailwind); sin secretos
- [X] T012 [P] [US2] Crear .devcontainer/post-create.sh (`set -euo pipefail`): instalar `sqlite3` con apt, `composer install`, `npm ci`, copiar `.env.example` a `.env` **solo si no existe**, `php artisan key:generate`, crear `database/database.sqlite`, `php artisan migrate --seed --force`, `npx playwright install --with-deps chromium`
- [X] T013 [US2] Documentar el uso del devcontainer (puertos, comandos, variables, limitaciones y estado "no validado en Codespaces") en README.md, sección nueva "Entorno de desarrollo con Dev Containers / Codespaces"

**Checkpoint**: archivos creados; validación en Codespaces documentada (hecha o no hecha, FR-015)

---

## Phase 5: User Story 3 - Propuesta de infraestructura como código (Priority: P3)

**Goal**: Diseño Terraform PLANEADO para Staging y Producción, sin recursos ni credenciales

**Independent Test**: quickstart.md §4

- [X] T014 [P] [US3] Crear docs/planeacion/propuesta-terraform.md: objetivo, estado (PLANEADO), ambientes `staging`/`production`, recursos previstos (red, servidor de app, base de datos MySQL, almacenamiento de respaldos, DNS/TLS), estructura de módulos, variables y salidas, manejo de estado remoto y secretos, integración con el pipeline de `estrategia-despliegue.md`, riesgos y costos; HCL solo como bloques de ejemplo dentro del Markdown
- [X] T015 [US3] Verificar que no hay archivos `.tf`, `.tfvars` ni `.tfstate` versionados (`git ls-files`) y dejar constancia en specs/003-infraestructura-cicd/analysis.md

---

## Phase 6: Polish & Cross-Cutting Concerns

- [X] T016 [P] Actualizar docs/planeacion/flujo-control-versiones.md: CI con job E2E (IMPLEMENTADO) y SDD con Spec Kit (IMPLEMENTADO, PR #144)
- [X] T017 [P] Actualizar docs/planeacion/estrategia-despliegue.md: CI incluye E2E; devcontainer IMPLEMENTADO (no validado); enlace a propuesta-terraform.md
- [X] T018 [P] Agregar `playwright-report/` y `test-results/` a .gitignore si no están
- [X] T019 Ejecutar `/speckit-analyze` y guardar el informe en specs/003-infraestructura-cicd/analysis.md
- [X] T020 Tras el push, registrar el enlace, el resultado y la duración del job E2E (SC-004: < 10 min) del run real de GitHub Actions en specs/003-infraestructura-cicd/analysis.md y en la descripción del PR (SC-001)

---

## Dependencies & Execution Order

- **Setup (T001)**: completado.
- **Foundational (T002–T004)**: bloquea US1 y US2 (ambos ejecutan la suite).
- **US1 (T005–T010)**: secuencial (mismo archivo `ci.yml`).
- **US2 (T011–T013)**: independiente de US1; T011 y T012 en paralelo.
- **US3 (T014–T015)**: independiente de US1 y US2.
- **Polish**: T016–T018 en paralelo; T019 después de todo; T020 después del push.

## Parallel Example

```text
Tras la fase 2, en paralelo:
  US1: T005 → T010 (ci.yml)
  US2: T011 + T012, luego T013
  US3: T014, luego T015
```

## Implementation Strategy

1. **MVP = US1**: suite limpia + job E2E + run verde. Es lo que exige el criterio principal del Issue.
2. US2 y US3 se agregan en el mismo PR (el Issue los incluye), pero no bloquean el run de US1.
3. Cerrar con la documentación (Polish), el análisis y el enlace del run real.
