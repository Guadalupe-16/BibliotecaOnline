# Specs de BibliotecaOnline

Cada requerimiento significativo vive en su propia carpeta `specs/<NNN-nombre>/`, generada con
[GitHub Spec Kit](https://github.com/github/spec-kit) (v1.0.12, integración `claude`).

## Contenido de una carpeta de spec

| Archivo | Lo genera | Para qué sirve |
|---|---|---|
| `spec.md` | `/speckit-specify` | Qué y por qué: historias de usuario, requisitos, criterios de aceptación |
| `plan.md` | `/speckit-plan` | Cómo: decisiones técnicas y estructura |
| `research.md` | `/speckit-plan` | Investigación y decisiones de tecnología |
| `tasks.md` | `/speckit-tasks` | Tareas ordenadas por dependencia |
| `checklists/` | `/speckit-checklist` | Listas de calidad de requisitos (opcional) |

`analysis.md` se guarda con la salida de `/speckit-analyze` (informe de consistencia entre spec,
plan y tasks).

## Cómo crear una spec nueva

1. Crear el Issue en GitHub y su rama `tipo/NNN-descripcion` desde `develop`.
2. En Claude Code, dentro del repositorio: `/speckit-specify <descripción del requerimiento>`.
3. Continuar con `/speckit-clarify`, `/speckit-plan`, `/speckit-tasks` y `/speckit-analyze`.
4. Incluir la carpeta `specs/<NNN-nombre>/` en el PR y enlazarla en su descripción.

Guía completa: [`docs/sdd/sdd-implementation.md`](../docs/sdd/sdd-implementation.md).
Reglas del proyecto: [`.specify/memory/constitution.md`](../.specify/memory/constitution.md).

## Specs existentes

| Spec | Issue | Estado |
|---|---|---|
| [`001-pruebas-e2e-playwright`](001-pruebas-e2e-playwright/spec.md) | #140 | Especificada; implementación de pruebas PLANEADO |
| [`003-infraestructura-cicd`](003-infraestructura-cicd/spec.md) | #138 | IMPLEMENTADO (PR #146) |
| [`002-guia-interactiva-driverjs`](002-guia-interactiva-driverjs/spec.md) | #137 | Especificada y planeada (spec, plan, research, data-model, contracts, quickstart, tasks, analysis). **PLANEADO**: Driver.js no está instalado ni implementado |
| [`004-monitoreo`](004-monitoreo/spec.md) | #164 | Especificada y planeada (spec, plan, research, tasks). **PLANEADO**: Prometheus + Grafana no están instalados; implementación en Issue separado |
| [`005-trazabilidad`](005-trazabilidad/spec.md) | #164 | Especificada y planeada (spec, plan, research, data-model, tasks). **PLANEADO**: sin middleware, tabla ni visor implementados; implementación en Issue separado |
| [`006-auditoria`](006-auditoria/spec.md) | #164 | Especificada y planeada (spec, plan, tasks) sobre el `ActivityLog` **ya existente**. **PLANEADO**: ampliación de cobertura y pruebas, sin cambios de código en este Issue |

Las tres specs de la actividad (001, 002 y 003) ya están en `develop`. Falta implementar el código de la 001 (pruebas E2E nuevas) y de la 002 (guía Driver.js); la 003 está implementada en CI y devcontainer.

Las specs 004, 005 y 006 (Issue #164) son la planeación SDD de monitoreo, trazabilidad y auditoría de la
Actividad 3.1; resumen conjunto en
[`docs/monitoreo/plan-general-sdd.md`](../docs/monitoreo/plan-general-sdd.md). Ninguna de las tres tiene
código implementado todavía.
