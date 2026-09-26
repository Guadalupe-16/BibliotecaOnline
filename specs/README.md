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

Todavía no hay specs en `develop`. Las primeras están planeadas: `001-pruebas-e2e-playwright`,
`002-guia-interactiva-driverjs` y `003-infraestructura-cicd`.
