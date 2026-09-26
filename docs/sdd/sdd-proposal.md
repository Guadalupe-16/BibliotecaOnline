# Propuesta de Spec-Driven Development (SDD) para BibliotecaOnline

Issue: #136 · Fecha: 2026-09-25

## 1. Qué es SDD y cómo se aplica aquí

Spec-Driven Development (SDD) invierte el orden habitual: primero se escribe una **especificación**
que describe qué debe hacer el sistema y por qué (historias de usuario, criterios de aceptación),
luego un **plan** técnico y una lista de **tareas**, y solo después se implementa. La especificación
es la fuente de verdad; el código y las pruebas se derivan de ella y se contrastan contra ella.

Aplicado a BibliotecaOnline significa que un requerimiento como "guía interactiva para usuarios
nuevos" deja de ser una línea en un Issue y pasa a ser `specs/002-.../spec.md` con criterios
verificables, un plan que respeta el stack Laravel + Livewire + Alpine, y tareas que se convierten en
commits, pruebas y un PR trazables al Issue.

## 2. Situación actual

Resumen verificado en el repositorio (detalle en
[`estado-actual/ingenieria-inversa.md`](estado-actual/ingenieria-inversa.md), Issue #135):

| Aspecto | Estado |
|---|---|
| Stack | Laravel 12.52.0, Livewire 4.1.4, Alpine.js 3.15.8, Tailwind 4.2.0, Vite 7.3.1 |
| Pruebas | PHPUnit (7 archivos Unit, 17 Feature), 4 archivos Vitest, 5 specs de Playwright y un proyecto Selenium IDE |
| Línea base (2026-09-25, `develop`) | `php artisan test`: 118 pruebas pasan; `npx playwright test`: 14 pasan (requiere `npm run build`); `npm run test` en verde tras #142 |
| CI | `.github/workflows/ci.yml`: ESLint + Prettier y PHPUnit; no ejecuta Playwright ni Vitest |
| Flujo Git | `main` y `develop`, ramas `tipo/NNN-descripcion`, hook local `prepare-commit-msg`, Issues con plantillas, PRs a `develop` |
| Requisitos | Se gestionan como Issues; no hay specs versionadas |

## 3. Qué falta para un SDD formal

1. Herramienta de especificación instalada y configurada (ahora: Spec Kit, Issue #136).
2. Constitución con las reglas del proyecto (`.specify/memory/constitution.md`).
3. Vínculo Issue → spec → tareas → pruebas → PR (hoy solo existe Issue → PR).
4. Definition of Ready y Definition of Done escritas (ver `sdd-implementation.md`).
5. Plantilla de PR que exija spec y evidencias (`.github/PULL_REQUEST_TEMPLATE.md`).
6. Pruebas E2E dentro del CI para que la spec se verifique automáticamente.

## 4. Comparación Kiro vs GitHub Spec Kit

Datos de la documentación oficial de cada herramienta, consultada el 2026-09-25. Lo que no se pudo
verificar se indica.

| Criterio | Kiro | GitHub Spec Kit |
|---|---|---|
| Qué es | Entorno de desarrollo con agentes, de un equipo de AWS; existe como IDE, CLI, Web y móvil (vista previa) | Kit publicado por GitHub (github/spec-kit): CLI `specify` + plantillas + skills para agentes de IA |
| Filosofía | El flujo de spec vive dentro del producto | Agnóstico del agente: se integra con Claude Code, Copilot, Gemini, Codex, Cursor y otros |
| Flujo | Requisitos → Diseño → Tareas → Implementación, con confirmación de cada fase; variantes Design-First y Quick Spec | constitution → specify → clarify → plan → checklist → tasks → analyze → implement → converge |
| Artefactos | `requirements.md` (o `bugfix.md`), `design.md`, `tasks.md`; steering en `.kiro/steering/` | `spec.md`, `plan.md`, `research.md`, `tasks.md`, `checklists/`, constitución en `.specify/memory/` |
| Notación de requisitos | EARS (`WHEN ... THE SYSTEM SHALL ...`) | Historias de usuario con escenarios Given/When/Then |
| Reglas del proyecto | Archivos de steering (`product.md`, `tech.md`, `structure.md`) y hooks | Constitución |
| Ventajas | Flujo integrado, ejecución paralela de tareas, integración GitHub en Kiro Web (label `kiro` o `/kiro` en Issues) | Versionable en el repositorio, funciona con el agente ya usado, análisis de consistencia (`analyze`) y constitución explícita |
| Limitaciones | Créditos de pago más allá del plan gratuito; parte de las funciones solo en el IDE; no se verificó la licencia ni la ruta exacta de los archivos de spec | Herramienta más nueva y cambiante (la sintaxis de 1.0.12 difiere de tutoriales previos); requiere Python y uv |
| Ajuste a BibliotecaOnline | Exigiría adoptar su IDE/plan para todo el equipo | Se integra en el repositorio existente y en el flujo de PR sin cambiar de herramienta |

No se afirma nada sobre calidad relativa de los resultados de una y otra: no se probaron en
paralelo sobre este proyecto.

## 5. Decisión

**Se adopta GitHub Spec Kit.** Motivos:

1. Es un requisito explícito de la actividad.
2. Vive en el repositorio (`.specify/`, `.claude/skills/`, `specs/`), por lo que cualquier
   integrante lo obtiene al clonar y sus specs se revisan en PR como cualquier otro archivo.
3. Es agnóstico del agente y ya funciona con Claude Code, que es el usado en el proyecto.
4. La constitución convierte las reglas del equipo (ramas, pruebas, seguridad por rol) en criterios
   que `analyze` puede contrastar.
5. Se instala desde su repositorio con `uv`, sin cuenta ni plan de pago adicionales para el CLI (el agente usado, Claude Code, es independiente de Spec Kit).

## 6. Propuesta de adopción

```mermaid
flowchart LR
    I[Issue] --> S[/speckit-specify/] --> C[/speckit-clarify/] --> P[/speckit-plan/]
    P --> T[/speckit-tasks/] --> B[Rama tipo/NNN] --> IM[Implementación]
    IM --> TE[Pruebas] --> A[/speckit-analyze/] --> PR[PR a develop]
    PR --> CI[CI] --> R[Revisión] --> M[Merge]
```

Pilotos previstos: `001-pruebas-e2e-playwright`, `002-guia-interactiva-driverjs` y
`003-infraestructura-cicd` (estado: PLANEADO hasta que sus PRs se integren).

## 7. Skills de la versión instalada

Spec Kit 1.0.12 con la integración `claude` instala 10 skills en `.claude/skills/`:
`speckit-constitution`, `speckit-specify`, `speckit-clarify`, `speckit-plan`, `speckit-checklist`,
`speckit-tasks`, `speckit-analyze`, `speckit-implement`, `speckit-converge` y
`speckit-taskstoissues`. Descripción de cada uno en
[`guia-instalacion-spec-kit.md`](guia-instalacion-spec-kit.md#5-skills-disponibles).
