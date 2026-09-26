# Implementation Plan: Infraestructura CI/CD — pruebas E2E en CI y entorno reproducible

**Branch**: `ci/138-e2e-devcontainer-spec-003` | **Date**: 2026-09-25 | **Spec**: [spec.md](spec.md)

**Input**: Feature specification from `specs/003-infraestructura-cicd/spec.md`

**Issue**: #138

## Summary

Agregar un tercer job, `e2e-playwright`, a `.github/workflows/ci.yml`. El job prepara la
aplicación completa (SQLite en archivo con seeders, assets compilados), la levanta con
`php artisan serve`, espera al endpoint de salud `/up` y ejecuta la suite de Playwright, publicando
siempre el reporte como artefacto. Se agrega un devcontainer (imagen oficial de PHP 8.2 + Node 20)
con un script de preparación, y una propuesta de Terraform solo documental. La simulación local
del job (ver [research.md](research.md) §1) confirmó que sin `npm run build` fallan 3 de 14 pruebas
y con build pasan las 14.

## Technical Context

**Language/Version**: PHP 8.2 (CI; `composer.json` exige `^8.2`), Node.js 20 (CI), YAML (GitHub
Actions), JSON (devcontainer), HCL solo dentro de Markdown (propuesta de Terraform)

**Primary Dependencies**: Laravel 12, `@playwright/test` 1.58.2 (lockfile), Vite 7 + Tailwind 4,
`shivammathur/setup-php@v2`, `actions/setup-node@v4`, `actions/upload-artifact@v4`, imagen
`mcr.microsoft.com/devcontainers/php:1-8.2-bookworm`, feature `ghcr.io/devcontainers/features/node:1`

**Storage**: SQLite en archivo (`database/database.sqlite`) para E2E y devcontainer; PHPUnit sigue
con SQLite en memoria (sin cambios)

**Testing**: Playwright (Chromium) para E2E; PHPUnit y ESLint/Prettier existentes sin cambios

**Target Platform**: GitHub Actions `ubuntu-latest`; GitHub Codespaces / VS Code Dev Containers

**Project Type**: Aplicación web monolítica Laravel (Blade + Livewire + Alpine)

**Performance Goals**: Job E2E completo en menos de 10 minutos (SC-004); suite local: 14 pruebas en
~6 s con assets compilados

**Constraints**: Sin secretos ni credenciales; no modificar el comportamiento de `lint-format` ni de
`php-tests`; espera del servidor por health check con tiempo límite, no `sleep` fijo

**Scale/Scope**: 1 workflow (3 jobs), 4 archivos de pruebas E2E de la aplicación (12 pruebas) tras
retirar la plantilla, 1 devcontainer, 1 documento de propuesta

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Principio | Cumplimiento | Estado |
|---|---|---|
| I. Especificación antes de código | Esta spec se escribe antes de tocar `ci.yml` y `.devcontainer/` | ✅ |
| II. Trazabilidad | Issue #138 → `specs/003-infraestructura-cicd/` → rama `ci/138-...` → PR con `Closes #138` | ✅ |
| III. Protección de ramas | Trabajo en rama propia; PR hacia `develop` | ✅ |
| IV. Pruebas obligatorias | El cambio es de infraestructura de pruebas; su prueba es el run real del CI (SC-001) | ✅ |
| V. Migraciones para el esquema | No hay cambios de esquema; el CI solo ejecuta las migraciones existentes | ✅ N/A |
| VI. Seguridad por rol / sin secretos | `.env` generado desde `.env.example`; `APP_KEY` efímera por run; sin credenciales | ✅ |
| VII. CI en verde | Se retira `example.spec.js`: no es una prueba de la aplicación (plantilla que visita playwright.dev). No se elimina para "pasar" el CI —pasa hoy— sino para quitar una dependencia externa. Justificado en research.md §3 | ✅ con justificación |
| Restricción "Base de datos" (MySQL en desarrollo, SQLite en memoria en pruebas) | El devcontainer usa SQLite en desarrollo y el job E2E usa SQLite en archivo. Desviación justificada en Complexity Tracking; la enmienda de la constitución queda fuera del alcance del Issue #138 | ⚠️ justificada |
| VIII. Evidencia honesta | Resultados de la simulación local registrados con fecha; devcontainer declarado como no validado; Terraform como PLANEADO | ✅ |

Puerta adicional de la constitución: "`npm run test` pasa" — Vitest no se ejecuta en CI hoy. Queda
fuera del alcance del Issue #138 y se registra como brecha (research.md §6). No bloquea.

**Re-check post-diseño**: sin cambios; ninguna violación nueva introducida por el diseño.

## Project Structure

### Documentation (this feature)

```text
specs/003-infraestructura-cicd/
├── spec.md
├── plan.md              # Este archivo
├── research.md          # Decisiones y evidencia de la simulación local
├── data-model.md        # Entidades de infraestructura (jobs, artefactos, entorno)
├── quickstart.md        # Cómo validar el job, el devcontainer y la propuesta
├── contracts/
│   ├── ci-e2e-job.md        # Entradas, pasos y salidas del job e2e-playwright
│   └── devcontainer.md      # Qué garantiza el entorno de desarrollo
├── checklists/
│   └── requirements.md
├── tasks.md             # /speckit-tasks
└── analysis.md          # /speckit-analyze
```

### Source Code (repository root)

```text
.github/workflows/
└── ci.yml                     # + job e2e-playwright (lint-format y php-tests sin cambios)

.devcontainer/
├── devcontainer.json          # Imagen PHP 8.2 + Node 20, puertos 8000/5173
└── post-create.sh             # Dependencias, .env, APP_KEY, SQLite, migraciones, Chromium

playwright.config.js           # baseURL configurable con PLAYWRIGHT_BASE_URL (default sin cambios)
tests/e2e/
└── example.spec.js            # Se elimina (plantilla de Playwright, depende de playwright.dev)

docs/planeacion/
├── propuesta-terraform.md     # Diseño PLANEADO, sin archivos .tf ni estado
├── estrategia-despliegue.md   # Se actualiza: CI ahora incluye E2E
└── flujo-control-versiones.md # Se actualiza: CI ahora incluye E2E
```

**Structure Decision**: No se crean directorios de código de aplicación. Los cambios se limitan a
configuración de CI, del entorno de desarrollo y documentación. La propuesta de Terraform vive como
Markdown en `docs/planeacion/` (junto a la estrategia de despliegue) y no como archivos `.tf`, para
cumplir FR-013: nada en el repositorio puede crear recursos si alguien ejecuta `terraform apply`.

## Complexity Tracking

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| SQLite en desarrollo (devcontainer), contra "MySQL en desarrollo" | `.env.example` ya usa SQLite desde antes de esta spec; el devcontainer debe funcionar sin servicios extra ni secretos (FR-011) | Un contenedor MySQL exige Docker Compose, credenciales y más tiempo de arranque; se puede agregar en una spec posterior |
| SQLite en archivo en el job E2E, contra "SQLite en memoria en las pruebas" | `php artisan serve` y `migrate` son procesos distintos; con `:memory:` el servidor vería una base vacía (research.md §2) | No hay alternativa en memoria que funcione entre procesos; PHPUnit sigue en memoria sin cambios |
| Eliminar `tests/e2e/example.spec.js` (roza el principio VII) | Es la plantilla de `npm init playwright`; prueba playwright.dev, no la app, y hace depender el CI de un sitio externo | `testIgnore` en la config deja código muerto que alguien podría volver a activar; renombrarlo no elimina la dependencia |
