# Resumen para el documento final

Materia prima para redactar el documento de la actividad "Gestión del Proceso de Desarrollo de Software".
Issue #139 · Datos verificados el 2026-09-25 sobre `develop` (`016fd3a`). Todo dato de Issues, PRs, ramas y
commits proviene del historial de Git del repositorio; lo que no se pudo verificar dice "No verificado".

## 0. Portada e integrantes

Integrantes que trabajaron en esta actividad (según el historial de commits de los PR #142 a #148):

| Integrante | Bloques |
|---|---|
| Guadalupe Amavizca Quinter | #134, #135, #136, #137, #139 |
| Javier Antonio Romo Bernal | #138, #140, #141 |

El equipo original tenía cuatro integrantes; Ibarra Rubalcava Joel Armando y Martínez Delgado Jorge Humberto
figuran en el historial anterior, pero no participaron en los PR de esta actividad. Fotos de portada: se agregan en
Google Docs.

## 1. Proyecto

| Dato | Valor |
|---|---|
| Nombre | BibliotecaOnline (repositorio `Guadalupe-16/BibliotecaOnline`) |
| Propósito | Consulta de catálogo, búsqueda, favoritos e importación desde Open Library, con paneles de administración |
| Stack | Laravel 12.52.0, PHP ^8.2, Livewire 4.1.4, Blade, Alpine.js 3.15.8, Tailwind 4.2.0, Vite 7.3.1, MySQL en desarrollo y SQLite en pruebas |
| Roles | `usuario`, `admin`, `superadmin` |
| Pruebas | PHPUnit 11.5.55, Vitest 4.1.0, Playwright 1.58.2, Selenium IDE |
| CI | GitHub Actions: `ESLint + Prettier`, `PHPUnit Tests`, `E2E Playwright` |
| Detalle | [`ingenieria-inversa.md`](../sdd/estado-actual/ingenieria-inversa.md) |

## 2. Punto 1 — Parámetros de configuración de herramientas

- Documento: [`parametros-configuracion.md`](parametros-configuracion.md) (Issue #139).
- 15 herramientas con planeación de uso, instalación, parámetros e implementación.
- **VALIDADO**: Git, GitHub, Spec Kit, Composer, npm/Vite, PHPUnit, ESLint/Prettier, Playwright, GitHub Actions.
- **IMPLEMENTADO** sin validar: Vitest (no corre en CI), Selenium IDE, Dev Container.
- **PLANEADO**: Playwright MCP, Driver.js, Terraform.

## 3. Punto 2 — Plan de pruebas

| Elemento | Ubicación |
|---|---|
| Plan de pruebas | [`plan-de-pruebas.md`](plan-de-pruebas.md) (Issue #140, Javier) |
| Spec 001 | [`specs/001-pruebas-e2e-playwright/`](../../specs/001-pruebas-e2e-playwright/spec.md) |
| PR mínimo de Spec Kit, skills y specs | PR #144 (Issue #136) |
| Guía de instalación | [`guia-instalacion-spec-kit.md`](../sdd/guia-instalacion-spec-kit.md) |

### Comandos y resultados reales

Ejecución final del 2026-09-25 sobre `develop` (`016fd3a`), máquina Windows 11 con PHP 8.3.14 y Node 22.13.1:

| Suite | Comando | Resultado |
|---|---|---|
| PHPUnit | `php artisan test` | 118 pasadas (209 aserciones). **Primera ejecución del día: 1 fallo y 117 pasadas en 32 s; no se guardó cuál prueba falló.** Siete ejecuciones posteriores: 118 pasadas cada una, en unos 8 s |
| Vitest | `npm run test` | 4 archivos y 18 tests aprobados |
| ESLint | `npm run lint` | 0 errores, 1 advertencia (`esFavoritoActual` sin uso en `favorito.test.js:3`) |
| Prettier | `npm run format:check` | Sin problemas |
| Playwright | `npx playwright test` (con `npm run build` y servidor en `:8000`) | 12 pasadas en 12.0 s |

Notas:
- El fallo intermitente de PHPUnit no se reprodujo; queda como observación sin causa identificada.
- Playwright pasó de 14 a 12 pruebas porque el PR #146 retiró `example.spec.js`. Los documentos
  `ingenieria-inversa.md`, `trazabilidad.md` y `casos-de-prueba.md` citan 14 porque se escribieron antes de ese cambio.
- Sin `npm run build`, 3 pruebas E2E fallan (medido antes de #146).
- Selenium IDE: no se ejecutó.

### Evidencia de CI (registrada en el plan de pruebas)

| Run | Evento | Resultado | Enlace |
|---|---|---|---|
| 36205499377 | PR #146 | `ESLint + Prettier`, `PHPUnit Tests` y `E2E Playwright` en verde | https://github.com/Guadalupe-16/BibliotecaOnline/actions/runs/36205499377 |
| 36205506706 | Push a `develop` | Todos en verde | https://github.com/Guadalupe-16/BibliotecaOnline/actions/runs/36205506706 |

Estos dos runs los registró Javier en el plan de pruebas; no se volvieron a consultar en GitHub.

## 4. Punto 3 — Casos de prueba

- Documento: [`casos-de-prueba.md`](casos-de-prueba.md) (Issue #137, Guadalupe).
- 36 casos: 34 con prueba existente y 2 sin prueba (CP-006 logout y CP-036 rutas `/usuarios`).
- Caso detallado CP-001 con su versión en lenguaje neutral, en Selenium IDE (test `login-exitoso`) y una propuesta en
  Playwright sin ejecutar.
- Selenium IDE: 7 tests grabados, sin aserciones. Playwright: 12 pruebas tras #146.
- PR: #147.

## 5. Punto 4 — Control de versiones y CI/CD

| Elemento | Ubicación |
|---|---|
| Flujo de versiones | [`flujo-control-versiones.md`](flujo-control-versiones.md) (Issue #141, Javier) |
| Workflow de CI | `.github/workflows/ci.yml` (Issue #138, Javier) |
| Diagrama | `gitGraph` y flujo del cambio dentro del documento de flujo |
| Ramas reales | `main`, `develop` y ramas `tipo/NNN-descripcion` (`feat`, `fix`, `docs`, `chore`, `ci`, `perf`, `test`) |
| PR | #143 (flujo) y #146 (CI, devcontainer y spec 003) |

## 6. Punto 5 — Estrategia de despliegue

- Documento: [`estrategia-despliegue.md`](estrategia-despliegue.md) (Issue #141, Javier; PR #143).
- Estado real: no existe hosting, staging ni producción. CI, devcontainer, respaldos y `/up` están IMPLEMENTADOS;
  ambientes, gates, rollback y Terraform están **PLANEADOS**.
- Terraform: [`propuesta-terraform.md`](propuesta-terraform.md), solo diseño y sin archivos `.tf`.

## 7. SDD

| Elemento | Ruta |
|---|---|
| Ingeniería inversa | [`docs/sdd/estado-actual/ingenieria-inversa.md`](../sdd/estado-actual/ingenieria-inversa.md) |
| Diagrama ER (Mermaid) | [`docs/sdd/estado-actual/er-diagram.md`](../sdd/estado-actual/er-diagram.md) |
| Trazabilidad | [`docs/sdd/estado-actual/trazabilidad.md`](../sdd/estado-actual/trazabilidad.md) |
| Análisis de brechas | [`docs/sdd/estado-actual/analisis-brechas.md`](../sdd/estado-actual/analisis-brechas.md) |
| Propuesta SDD y Kiro vs Spec Kit | [`docs/sdd/sdd-proposal.md`](../sdd/sdd-proposal.md) |
| Guía de implementación | [`docs/sdd/sdd-implementation.md`](../sdd/sdd-implementation.md) |
| Instalación de Spec Kit | [`docs/sdd/guia-instalacion-spec-kit.md`](../sdd/guia-instalacion-spec-kit.md) |
| Constitución | [`.specify/memory/constitution.md`](../../.specify/memory/constitution.md) (v1.0.0) |
| Skills | `.claude/skills/speckit-*` (10) |
| Spec 001 — pruebas E2E | [`specs/001-pruebas-e2e-playwright/`](../../specs/001-pruebas-e2e-playwright/spec.md) — implementación PLANEADA |
| Spec 002 — guía Driver.js | [`specs/002-guia-interactiva-driverjs/`](../../specs/002-guia-interactiva-driverjs/spec.md) — PLANEADA, sin código |
| Spec 003 — infraestructura y CI/CD | [`specs/003-infraestructura-cicd/`](../../specs/003-infraestructura-cicd/spec.md) — IMPLEMENTADA en parte (CI y devcontainer) |

Cada spec tiene `spec.md`, `plan.md`, `research.md`, `data-model.md`, `quickstart.md`, `contracts/`, `tasks.md`,
`analysis.md` y `checklists/requirements.md`.

### Brechas principales (de `analisis-brechas.md`)

- **S1**: las rutas `/usuarios` solo exigen sesión; cualquier usuario puede editarlas o eliminar usuarios. **Abierta**;
  no hay Issue.
- **T4**: los tests de Vitest no importan código de la aplicación.
- **I3**: el hook de commits no se versiona y falla en silencio si la rama no lleva número de Issue.
- **S2**: un token de GitHub aparece en la URL del remoto `origin` de una máquina local; debe revocarse.

## 8. Evidencias por bloque

| Bloque | Issue | PR | Rama | Commits | Responsable | Archivos | Estado |
|---|---|---|---|---|---|---|---|
| Vitest excluye E2E | #134 | #142 | `fix/134-vitest-excluir-e2e` | `a17b3ce` | Guadalupe | `vite.config.js` | Cerrado |
| Ingeniería inversa, ER, trazabilidad y brechas | #135 | #145 | `docs/135-estado-actual` | `23d5203`, `5f2eaf4` | Guadalupe | `docs/sdd/estado-actual/*` (4) | Cerrado |
| Adopción de Spec Kit y SDD | #136 | #144 | `chore/136-spec-kit-sdd` | `f108e4f`, `65d5080` | Guadalupe | `.specify/`, `.claude/skills/`, `docs/sdd/*` (3), plantilla de PR, `specs/README.md` | Cerrado |
| Casos de prueba y spec 002 | #137 | #147 | `docs/137-casos-prueba-spec-002` | `7279677`, `7d78af4` | Guadalupe | `casos-de-prueba.md`, `specs/002-*` | Cerrado |
| CI, devcontainer y spec 003 | #138 | #146 | `ci/138-e2e-devcontainer-spec-003` | `d36976e`, `2964c7b`, `dfcccd4` | Javier | `ci.yml`, `.devcontainer/`, `propuesta-terraform.md`, `specs/003-*` | Cerrado |
| Parámetros de configuración y resumen | #139 | Pendiente | `docs/139-parametros-herramientas` | Pendiente | Guadalupe | `parametros-configuracion.md`, este resumen, `specs/README.md` | En curso |
| Plan de pruebas y spec 001 | #140 | #148 | `docs/140-plan-pruebas-spec-001` | `0636b91`, `c1d388c`, `098feca` | Javier | `plan-de-pruebas.md`, `specs/001-*` | Cerrado |
| Flujo de versiones y despliegue | #141 | #143 | `docs/141-flujo-control-versiones` | `768cc16` | Javier | `flujo-control-versiones.md`, `estrategia-despliegue.md` | Cerrado |

Un solo commit del PR #148 (`4ed483e`, "Merge branch 'develop'") aparece con el autor `ElJavierB0`, probablemente otra
identidad de Git de Javier (no verificado). El estado "Cerrado" del Issue se infiere de `Closes` y `Fixes` en PR y
commits; no se verificó en la interfaz de GitHub.

## 9. Checklist de la actividad

| Requisito | Estado |
|---|---|
| Parámetros de configuración | ✅ Cumplido tras el merge de #139 |
| Plan de pruebas y suite ejecutada | ✅ Cumplido (resultados reales arriba) |
| Casos de prueba y Selenium/Playwright | ✅ Cumplido |
| Flujo de versiones, CI y diagramas | ✅ Cumplido |
| Estrategia de despliegue | ✅ Documentada; ambientes PLANEADOS |
| Repositorio configurado (README, `.env.example`, plantillas, CI) | 🟡 Parcial: el README no incluye el contenido del hook, `.env.example` usa `APP_NAME=Laravel` y `APP_LOCALE=en` |
| PR mínimo de Spec Kit, skills y specs | ✅ #144 |
| Al menos un PR de planeación por integrante | ✅ Guadalupe (#142, #144, #145, #147) y Javier (#143, #146, #148); el de #139 está pendiente |
| Comparación Kiro vs Spec Kit | ✅ En `sdd-proposal.md` |
| Piloto a) pruebas, b) Driver.js, c) infraestructura | ✅ Specs completas; b) sin implementar |
| Constitución, specs, plans, tasks y analysis | ✅ |
| Devcontainer y Codespaces | 🟡 Devcontainer creado; no validado en Codespaces |
| Terraform | 🟡 Solo propuesta |
| AU: cuarto módulo | ❌ No realizado (Issue #119 sin spec) |
| SA: video explicativo | ❌ No realizado |

## 10. Pendientes manuales

- Abrir el PR del Issue #139 y anotar su número y commits en la tabla de la sección 8.
- Confirmar en GitHub que los Issues #134 a #141 estén cerrados.
- Fotos de portada en Google Docs y redacción del documento final.
- Revocar el token de GitHub del remoto `origin` y usar credential manager o SSH.
- Crear un Issue `fix:` para las rutas `/usuarios` (brecha S1).
- Versionar o documentar el contenido del hook `prepare-commit-msg` (I3).
- Investigar el fallo intermitente de PHPUnit visto en la primera ejecución del 2026-09-25.
- Decidir si se hace el módulo extra AU y el video SA.
- Confirmar la configuración de protección de ramas y de los checks requeridos en GitHub.
