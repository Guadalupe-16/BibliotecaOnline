# Implementation Plan: Pruebas E2E con Playwright — flujos críticos

**Branch**: `docs/140-plan-pruebas-spec-001` | **Date**: 2026-09-25 | **Spec**: [spec.md](spec.md)

**Input**: Feature specification from `specs/001-pruebas-e2e-playwright/spec.md`

**Issue**: #140

## Summary

Evolucionar la suite de `tests/e2e/` sin reescribirla. Se corrigen las 3 pruebas defectuosas
(detalle sin aserciones y 2 con nombre engañoso), se reorganiza el grupo de contacto y se agregan
pruebas para autenticación con sesión, búsqueda, favoritos y roles. Para respetar el límite de
5 logins por minuto se usa un proyecto `setup` de Playwright que inicia sesión una vez por rol y
guarda el estado en `playwright/.auth/<rol>.json`; las pruebas lo reutilizan con `storageState`. El
workflow de CI (spec 003) no cambia.

## Technical Context

**Language/Version**: JavaScript (ES modules), Node 20 en CI

**Primary Dependencies**: `@playwright/test` 1.58.2 (lockfile); aplicación Laravel 12 + Livewire 4 +
Alpine.js bajo prueba

**Storage**: SQLite en archivo sembrado con `DatabaseSeeder` (el job E2E de la spec 003 ya lo hace)

**Testing**: Playwright, proyecto `chromium`, más un proyecto `setup` para autenticación

**Target Platform**: job `E2E Playwright` en GitHub Actions (`ubuntu-latest`) y ejecución local

**Project Type**: Aplicación web monolítica; solo cambian archivos de pruebas y su configuración

**Performance Goals**: Suite completa < 2 min en CI (hoy 12 pruebas en ~6 s local, job en 1 min 9 s)

**Constraints**: Sin esperas fijas; sin credenciales externas; `workers: 1` (la base es compartida y
los favoritos mutan datos); máximo un login por rol por ejecución

**Scale/Scope**: De 12 a ~30 pruebas en 7 archivos

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Principio | Cumplimiento | Estado |
|---|---|---|
| I. Especificación antes de código | Spec 001 antes de escribir las pruebas nuevas | ✅ |
| II. Trazabilidad | Issue #140 → spec 001 → tasks con archivos de `tests/e2e/` | ✅ |
| III. Protección de ramas | Rama `docs/140-...`, PR a `develop` | ✅ |
| IV. Pruebas obligatorias | Playwright para flujos críticos de navegador (lo que pide el principio) | ✅ |
| V. Migraciones | Sin cambios de esquema | ✅ N/A |
| VI. Seguridad por rol | US4 agrega pruebas negativas (sin sesión, sin rol, rol insuficiente) | ✅ |
| VII. CI en verde | El defecto de `/usuarios` no se oculta con `test.skip`/`fixme`: se registra como Issue y su prueba entra con la corrección (FR-010) | ✅ |
| VIII. Evidencia honesta | Resultados reales en `docs/planeacion/plan-de-pruebas.md`; Playwright MCP como PLANEADO | ✅ |
| Restricción "Base de datos" | E2E con SQLite en archivo: misma desviación justificada en spec 003 (plan.md › Complexity Tracking) | ⚠️ heredada |

**Re-check post-diseño**: sin violaciones nuevas.

## Project Structure

### Documentation (this feature)

```text
specs/001-pruebas-e2e-playwright/
├── spec.md
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   └── suite-e2e.md
├── checklists/requirements.md
├── tasks.md
└── analysis.md
```

### Source Code (repository root)

```text
playwright.config.js            # + proyecto "setup" y dependencia del proyecto chromium
.gitignore                      # + /playwright/.auth

tests/e2e/
├── auth.setup.js               # NUEVO: login una vez por rol → playwright/.auth/<rol>.json
├── helpers/usuarios.js         # NUEVO: credenciales de prueba del RolesSeeder
├── login.spec.js               # existente + login exitoso y logout (US1)
├── acceso.spec.js              # NUEVO: redirecciones de invitado (US1) y roles (US4)
├── catalogo.spec.js            # existente; detalle corregido (US2)
├── busqueda.spec.js            # NUEVO: resultados, sin resultados, filtro (US2)
├── favoritos.spec.js           # NUEVO (US3)
├── navegacion.spec.js          # existente; 2 pruebas renombradas/corregidas
└── contacto.spec.js            # se elimina: sus 2 pruebas duplican catálogo y login
```

**Structure Decision**: Un archivo por historia/área dentro de `tests/e2e/`, reutilizando los
existentes. La autenticación se centraliza en el proyecto `setup` recomendado por Playwright en lugar
de iniciar sesión en cada prueba.

## Complexity Tracking

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| Proyecto `setup` + `storageState` | Límite de 5 intentos de login por minuto (correo + IP) | Login en cada prueba: con ~15 pruebas con sesión se bloquea la suite |
| Eliminar `contacto.spec.js` | Sus 2 pruebas duplican catálogo y login bajo un nombre engañoso; no hay ruta `/contacto` que probar (trazabilidad) | Renombrarlo dejaría pruebas duplicadas |
