# Data Model: Infraestructura CI/CD (spec 003)

Esta feature no agrega tablas ni modelos de Laravel. Las "entidades" son piezas de infraestructura
con atributos y reglas verificables.

## Pipeline de CI (`.github/workflows/ci.yml`)

| Atributo | Valor |
|---|---|
| Disparadores | `push` y `pull_request` sobre `main` y `develop` (sin cambios) |
| Jobs | `lint-format`, `php-tests` (existentes, sin cambios), `e2e-playwright` (nuevo) |
| Dependencias entre jobs | Ninguna: los tres corren en paralelo |

Reglas:
- Los pasos de `lint-format` y `php-tests` no se modifican (SC-003).
- Cada job produce su propio check en el PR (FR-002).

## Job `e2e-playwright`

Contrato detallado: [contracts/ci-e2e-job.md](contracts/ci-e2e-job.md).

Estados de un run:

```text
preparando ──► esperando /up ──► ejecutando pruebas ──► publicando reporte ──► éxito | fallo
                    │ (60 s agotados)
                    └──────────► publicando log del servidor ──► fallo
```

## Reporte E2E (artefacto)

| Atributo | Valor |
|---|---|
| Nombre | `playwright-report` |
| Contenido | Reporte HTML de Playwright (`playwright-report/`) |
| Cuándo se publica | Siempre que el job no se cancele |
| Retención | 14 días |
| Artefacto extra | `laravel-server-log` solo si el job falla |

## Entorno de desarrollo (`.devcontainer/`)

Contrato detallado: [contracts/devcontainer.md](contracts/devcontainer.md).

| Atributo | Valor |
|---|---|
| Imagen base | `mcr.microsoft.com/devcontainers/php:1-8.2-bookworm` |
| Features | Node 20 |
| Puertos | 8000 (Laravel), 5173 (Vite) |
| Preparación | `.devcontainer/post-create.sh` |
| Secretos | Ninguno |

## Propuesta de infraestructura (`docs/planeacion/propuesta-terraform.md`)

| Atributo | Valor |
|---|---|
| Estado | PLANEADO |
| Ambientes | `staging`, `production` |
| Artefactos en el repo | Solo Markdown; 0 archivos `.tf`, `.tfvars`, `.tfstate` |
