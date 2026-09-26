# Análisis de brechas de BibliotecaOnline

Issue #135 · Fecha: 2026-09-25

Pregunta que responde: **¿qué le falta a BibliotecaOnline para trabajar formalmente con SDD y una
cadena completa de calidad y CI/CD?** Se basa en
[`ingenieria-inversa.md`](ingenieria-inversa.md) y [`trazabilidad.md`](trazabilidad.md).

Estados: **Cerrada** (ya resuelta en `develop`), **En curso** (tiene Issue abierto en la actividad) y
**Abierta** (sin trabajo asignado).

## Resumen

| Tipo | Total | Cerradas | En curso | Abiertas |
|---|---:|---:|---:|---:|
| Proceso (P) | 6 | 3 | 3 | 0 |
| Pruebas (T) | 8 | 1 | 4 | 3 |
| CI/CD y despliegue (D) | 4 | 0 | 3 | 1 |
| Infraestructura (I) | 3 | 0 | 2 | 1 |
| UX (X) | 1 | 0 | 1 | 0 |
| Seguridad y calidad de código (S) | 4 | 0 | 0 | 4 |

## A. Proceso

| ID | Tipo | Estado actual | Riesgo / impacto | Propuesta | Estado |
|---|---|---|---|---|---|
| P1 | Proceso | No existía Spec Kit ni herramienta de especificación | Requisitos solo en Issues, sin criterios verificables | Instalar GitHub Spec Kit | Cerrada (#136, PR #144) |
| P2 | Proceso | No existía constitución | Reglas del equipo sin documentar | `.specify/memory/constitution.md` v1.0.0 | Cerrada (#136) |
| P3 | Proceso | No había plantilla de PR ni Definition of Done | PRs sin evidencia homogénea | `.github/PULL_REQUEST_TEMPLATE.md` y DoR/DoD en `sdd-implementation.md` | Cerrada (#136) |
| P4 | Proceso | Issues sin vínculo a specs | No se puede seguir Issue → spec → prueba | Enlazar cada PR a su spec; primeras specs 001-003 | En curso (#137, #138, #140) |
| P5 | Proceso | Trazabilidad solo manual | Difícil auditar cobertura de requisitos | `trazabilidad.md` (este Issue) y mantenerla al cerrar cada módulo | En curso (#135) |
| P6 | Proceso | Convenciones de ramas inconsistentes en el historial (`feature/`, `feat/`, ramas sin número) y `ASIGNACIONES.md` desactualizado | El hook local rechaza `feature/*`; confusión al nombrar ramas | Homogeneizar a `tipo/NNN-descripcion` y actualizar documentación | En curso (#141) |

## B. Pruebas

| ID | Tipo | Estado actual | Riesgo / impacto | Propuesta | Estado |
|---|---|---|---|---|---|
| T1 | Testing | Playwright no corre en CI | Regresiones de navegador no detectadas en PR | Job `e2e-playwright` con `npm run build` previo | En curso (#138) |
| T2 | Testing | Vitest no corre en CI | Cambios en JS sin validación automática | Añadir `npm run test` al CI | En curso (#138) |
| T3 | Testing | Vitest fallaba al recoger specs de Playwright | `npm run test` en rojo | `include` en `vite.config.js` | Cerrada (#134, PR #142) |
| T4 | Testing | Tests Vitest definen sus propias funciones y no importan código de `resources/js` | Falsa sensación de cobertura JS | Extraer la lógica a módulos y probarla, o reemplazar por pruebas del componente real | Abierta |
| T5 | Testing | E2E cubren solo login (3 casos), catálogo y navegación; nada de registro, recuperación, favoritos, roles ni paneles | Flujos críticos sin prueba de navegador | Ampliar cobertura según `specs/001` | En curso (#140) |
| T6 | Testing | Sin pruebas para `ActivityLog`, `LogActivityJob`, el panel `ActivityLogger`, sitemap ni chat de soporte | Módulos sin red de seguridad | Añadir pruebas Feature | Abierta |
| T7 | Testing | Selenium IDE y Playwright no están ligados a requisitos formales; `example.spec.js` prueba un sitio externo y `contacto.spec.js` no prueba contacto | Casos duplicados y sin origen claro | Matriz de casos con trazabilidad y limpieza de specs de ejemplo | En curso (#137) |
| T8 | Testing | Advertencia de ESLint (`esFavoritoActual` sin uso) | Ruido en lint | Eliminar la variable o usarla | Abierta |

## C. CI/CD y despliegue

| ID | Tipo | Estado actual | Riesgo / impacto | Propuesta | Estado |
|---|---|---|---|---|---|
| D1 | Despliegue | CI existe con ESLint, Prettier y PHPUnit; sin build ni E2E | Cobertura parcial del pipeline | Ampliar `ci.yml` | En curso (#138) |
| D2 | Despliegue | Sin estrategia formal de ambientes ni despliegue | No hay camino definido a producción | `docs/planeacion/estrategia-despliegue.md` con etiquetas IMPLEMENTADO / VALIDADO / PLANEADO | En curso (#141; PR #143 integrado) |
| D3 | Despliegue | `main` está 87 commits por detrás de `develop`; el tag `v2.0.0` no está en `main` | Rama de producción desactualizada | PR periódico de `develop` a `main` cuando esté estable | En curso (#141) |
| D4 | Despliegue | No se identificó hosting ni ambiente de staging o producción en el repositorio | El software no está desplegado según los archivos revisados | Definir proveedor y ambientes | Abierta |

## D. Infraestructura

| ID | Tipo | Estado actual | Riesgo / impacto | Propuesta | Estado |
|---|---|---|---|---|---|
| I1 | Infraestructura | No hay `.devcontainer/` | Entorno de desarrollo difícil de reproducir (el README pide instalar todo a mano y crear el hook) | `.devcontainer/devcontainer.json` para Codespaces | En curso (#138) |
| I2 | Infraestructura | No hay infraestructura como código ni Terraform | Infraestructura no reproducible | Diseñar módulos; PLANEADO, sin recursos reales | En curso (#138) |
| I3 | Infraestructura | El hook `prepare-commit-msg` no se versiona y falla en silencio en ramas sin número | Cada clon debe crearlo a mano; error difícil de diagnosticar | Versionar el hook (por ejemplo con `core.hooksPath`) y devolver el código de salida correcto | Abierta |

## E. UX

| ID | Tipo | Estado actual | Riesgo / impacto | Propuesta | Estado |
|---|---|---|---|---|---|
| X1 | UX | No hay guía para usuarios nuevos | Curva de aprendizaje | Tour con Driver.js (`specs/002`) | En curso (#137); PLANEADO, sin instalar |

## F. Seguridad y calidad de código

Brechas encontradas en la revisión; no forman parte de la actividad y requieren su propio Issue.

| ID | Tipo | Estado actual | Riesgo / impacto | Propuesta | Estado |
|---|---|---|---|---|---|
| S1 | Seguridad | `/usuarios`, `/usuarios/{id}/edit`, `PUT` y `DELETE /usuarios/{id}` solo exigen `auth` | Cualquier usuario con sesión puede listar, editar y eliminar usuarios | Añadir `role:admin,superadmin` y pruebas negativas (constitución, principio VI) | Abierta |
| S2 | Seguridad | Token de GitHub en la URL del remoto `origin` de la configuración local de Git | Exposición de credencial si se comparte el clon | Revocar el token y usar credential manager o SSH | Abierta |
| S3 | Calidad | `POST /logout` definida dos veces; `/admin` devuelve texto plano; `dashboard` sin controlador; vistas `about` y `contacto` sin ruta | Código muerto o inconsistente | Depurar rutas | Abierta |
| S4 | Calidad | `.env.example` con `APP_NAME=Laravel`, `APP_LOCALE=en`, SQLite, mientras el desarrollo usa MySQL | Instalación ambigua | Alinear `.env.example` y README | Abierta |

## Prioridad sugerida

1. **S1** (seguridad de rutas) y **S2** (token expuesto).
2. **T1, T2 y D1** (E2E y Vitest en CI), porque protegen todo lo demás.
3. **T4 y T5** (calidad real de las pruebas de JS y cobertura E2E).
4. **D2, D4 e I1-I3** (despliegue y entornos).
5. Resto.
