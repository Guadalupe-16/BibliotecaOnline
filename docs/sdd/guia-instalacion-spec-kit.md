# Guía de instalación de GitHub Spec Kit

Registro reproducible de cómo se instaló Spec Kit en BibliotecaOnline (Issue #136).
Todos los datos provienen de la instalación real realizada en este repositorio.

## 1. Datos de la instalación

| Dato | Valor |
|---|---|
| Fecha | 2026-09-25 |
| Herramienta | GitHub Spec Kit, CLI `specify-cli` |
| Versión | 1.0.12 (tag `v1.0.12`, commit `e77daa9`) |
| Repositorio oficial | https://github.com/github/spec-kit |
| Documentación consultada | https://github.github.io/spec-kit/ (installation, guides/existing-projects, reference/core) |
| Agente / integración | Claude Code (`--integration claude`) |
| Tipo de scripts | PowerShell (`--script ps`, valor por defecto en Windows) |
| Rama | `chore/136-spec-kit-sdd` |

## 2. Prerrequisitos

| Requisito | Versión usada | Nota |
|---|---|---|
| Python | 3.12.2 | La documentación pide 3.11 o superior |
| uv | 0.12.19 | Instalado con `pip install uv` |
| Git | 2.47.1 | Solo obligatorio si se usa la extensión `git` de Spec Kit (no instalada) |
| Agente de IA | Claude Code | Integración `claude` |
| Sistema operativo | Windows 11 | Los scripts PowerShell funcionan sin WSL |

## 3. Comandos ejecutados

```bash
# 1. Instalar uv (gestor de herramientas de Python)
pip install uv

# 2. Instalar el CLI de Spec Kit fijando la versión (con la "v" del tag)
uv tool install specify-cli --from git+https://github.com/github/spec-kit.git@v1.0.12

# 3. Verificar
specify version

# 4. Inicializar Spec Kit dentro del proyecto existente, desde la raíz del repositorio
#    y sobre una rama de adopción (chore/136-spec-kit-sdd)
specify init --here --force --non-interactive --integration claude --script ps
```

Se usó `--here --force` porque el proyecto ya existe: `--here` inicializa en el directorio actual y
`--force` permite fusionar archivos en un directorio no vacío. En esta instalación el comando solo
agregó las carpetas `.claude/` y `.specify/`; no modificó ningún archivo existente del proyecto.

## 4. Estructura generada (31 archivos)

```text
.claude/
├── settings.local.json          # configuración LOCAL: no se versiona (ver sección 7)
└── skills/                      # 10 skills speckit-*/SKILL.md
.specify/
├── .gitignore                   # excluye feature.json y configuración local de extensiones
├── init-options.json            # opciones con las que se inicializó
├── integration.json             # integración activa (claude), versión 1.0.12
├── integrations/                # manifiestos con hash de los archivos instalados
├── memory/
│   ├── constitution.md          # constitución del proyecto (ver sección 6)
│   └── .constitution-template.json
├── scripts/powershell/          # check-prerequisites, common, create-new-feature,
│                                #   resolve-template, setup-plan, setup-tasks
├── templates/                   # spec, plan, tasks, checklist, constitution
└── workflows/                   # workflow speckit y registro de workflows
specs/                           # una carpeta por requerimiento (la crea el proyecto)
```

## 5. Skills disponibles

Con la integración `claude`, Spec Kit instala los comandos como skills en `.claude/skills/`.
Se invocan como `/speckit-<nombre>`:

| Skill | Función |
|---|---|
| `/speckit-constitution` | Crear o actualizar los principios del proyecto |
| `/speckit-specify` | Crear la spec (qué y por qué) desde una descripción |
| `/speckit-clarify` | Preguntas dirigidas para resolver ambigüedades (opcional, antes de plan) |
| `/speckit-plan` | Plan técnico a partir de la spec |
| `/speckit-checklist` | Listas de calidad de requisitos (opcional) |
| `/speckit-tasks` | Lista de tareas ordenada por dependencias |
| `/speckit-analyze` | Informe de consistencia entre spec, plan y tasks (solo lectura) |
| `/speckit-implement` | Ejecutar las tareas de `tasks.md` |
| `/speckit-converge` | Comparar el código contra spec/plan/tasks y agregar tareas pendientes |
| `/speckit-taskstoissues` | Convertir tareas en Issues de GitHub (requiere MCP de GitHub) |

Orden recomendado: constitution → specify → clarify → plan → checklist → tasks → analyze →
implement → converge. Los skills quedan disponibles al abrir Claude Code en la carpeta del proyecto.

## 6. Configuración

- **Constitución:** `.specify/memory/constitution.md` versión 1.0.0, con los ocho principios de
  BibliotecaOnline (especificación antes de código, trazabilidad, protección de ramas, pruebas
  obligatorias, migraciones, seguridad por rol, CI en verde y evidencia honesta).
- **Numeración de specs:** secuencial (`feature_numbering: sequential` en `init-options.json`):
  `001-nombre`, `002-nombre`, etc., dentro de `specs/`.
- **Ramas:** Spec Kit 1.0.12 ya no crea ramas por defecto; eso lo hace la extensión opcional `git`
  (`specify extension add git`), que **no se instaló**. Las ramas del proyecto siguen la convención
  `tipo/NNN-descripcion` exigida por el hook local `prepare-commit-msg`.
- **Feature activa:** se guarda en `.specify/feature.json`, que está en `.specify/.gitignore`.

## 7. Qué se versiona y qué no

| Ruta | Versionar | Motivo |
|---|---|---|
| `.specify/` (salvo lo ignorado por su `.gitignore`) | Sí | Plantillas, scripts y constitución compartidos |
| `.claude/skills/` | Sí | Todo el equipo obtiene los mismos skills al clonar |
| `.claude/settings.local.json` | **No** | Permisos locales de cada persona; se agregó a `.gitignore` del proyecto |
| `specs/` | Sí | Especificaciones del proyecto |

Se revisaron los 31 archivos generados en busca de secretos: no contienen tokens ni credenciales
(las coincidencias con la palabra "token" son texto de las instrucciones de los skills).

## 8. Problemas encontrados

1. **Guías antiguas desactualizadas.** Muchos tutoriales usan `specify init --ai claude`. En la
   versión 1.0.12 el flag es `--integration claude` y los comandos son skills `/speckit-*` (con
   guion), no comandos `/speckit.*`.
2. **`specify` requiere `uv` en el PATH.** Se instaló con `pip install uv`; si `specify` no se
   reconoce, reabrir la terminal.
3. **`constitution.md` nace como plantilla con marcadores** (`[PRINCIPLE_1_NAME]`, etc.). Se
   completó a mano con los principios del proyecto.
4. **Extensión `git` no instalada.** Por eso Spec Kit no crea ramas ni hace `git init`; es lo
   deseado, porque el flujo de ramas del proyecto ya está definido.
5. No se verificó el flag `--no-git`; no fue necesario.

## 9. Verificación final

- `specify version` reporta 1.0.12.
- `.specify/integration.json` indica `"default_integration": "claude"` y versión `1.0.12`.
- Existen los 10 skills en `.claude/skills/`.
- `.specify/memory/constitution.md` no contiene marcadores sin reemplazar.
- El árbol de trabajo solo agregó `.claude/`, `.specify/` y los archivos de documentación de este
  Issue; no se modificó código de la aplicación.

## 10. Reproducir en otra máquina

```bash
git clone <repositorio> && cd BibliotecaOnline
pip install uv
uv tool install specify-cli --from git+https://github.com/github/spec-kit.git@v1.0.12
specify version
```

No hace falta volver a ejecutar `specify init`: `.specify/` y `.claude/skills/` ya vienen en el
repositorio. Solo se abre Claude Code en la carpeta del proyecto.
