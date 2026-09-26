# BibliotecaOnline Constitution

Principios que rigen cómo se especifica, construye, prueba y entrega cualquier cambio en
BibliotecaOnline (Laravel 12, Blade, Livewire, Alpine.js, Tailwind, Vite). Toda spec, plan y lista
de tareas generada con Spec Kit debe cumplirlos.

## Core Principles

### I. Especificación antes de código
Todo requerimiento nuevo significativo (módulo, flujo de usuario, cambio de esquema o de seguridad)
cuenta con una spec aprobada en `specs/<NNN-nombre>/spec.md` antes de escribir código. Los cambios
triviales (typos, ajustes de estilo, dependencias) no requieren spec, pero sí Issue.

### II. Trazabilidad
Cada cambio enlaza Issue → Spec → Tasks → código → pruebas → PR. Los nombres de rama y de commit
llevan el número de Issue (`tipo/NNN-descripcion`) y el PR incluye `Closes #NNN`.

### III. Protección de ramas
No se hacen commits directos a `main`. Se evitan los commits directos a `develop`: todo cambio entra
por una rama de trabajo y un Pull Request hacia `develop`; `main` solo se actualiza mediante un PR
desde `develop`. No se hace force push sobre ramas compartidas.

### IV. Pruebas obligatorias
Todo cambio funcional incluye pruebas proporcionales al riesgo: PHPUnit (Unit/Feature) para reglas,
rutas, autorización y base de datos; Playwright para flujos críticos de navegador; Vitest para lógica
JavaScript. Una corrección de bug incluye la prueba que lo reproduce.

### V. Migraciones para el esquema
Todo cambio de esquema de base de datos se hace mediante migraciones de Laravel, versionadas y
reversibles cuando sea posible. Nunca se edita la base de datos a mano ni se modifica una migración
ya aplicada en un ambiente compartido.

### VI. Seguridad por rol
Toda ruta que modifica datos o expone información sensible se protege con `auth` y, cuando aplique,
con el middleware `role:` (roles `usuario`, `admin`, `superadmin`). Los cambios de autenticación,
autorización o RBAC incluyen pruebas negativas (usuario sin sesión, sin rol y con rol insuficiente).
No se guardan secretos, tokens ni credenciales en el repositorio.

### VII. CI en verde
No se hace merge con checks requeridos fallando. Si una prueba falla, se corrige la causa; no se
desactiva ni se elimina para pasar el pipeline.

### VIII. Evidencia honesta
No se marca como implementado lo que solo está planeado. Los resultados de pruebas, cobertura,
enlaces y números de Issue o PR provienen de ejecuciones y registros reales; lo no verificado se
declara como tal. Los documentos usan las etiquetas IMPLEMENTADO, VALIDADO o PLANEADO.

## Restricciones adicionales

- **Stack**: PHP 8.2+, Laravel 12, Livewire 4, Alpine.js, Tailwind CSS 4 y Vite. Una tecnología nueva
  se justifica en el `research.md` de su spec.
- **Base de datos**: MySQL en desarrollo y SQLite en memoria en las pruebas; las migraciones deben
  funcionar en ambos motores.
- **Idioma**: documentación, specs y mensajes de interfaz en español.
- **Simplicidad**: se prefiere la solución más simple que cumpla la spec; la complejidad añadida se
  justifica por escrito en el plan.

## Flujo de desarrollo y puertas de calidad

Flujo: Issue → `/speckit-specify` → `/speckit-clarify` → `/speckit-plan` → `/speckit-tasks` →
rama → implementación → pruebas → `/speckit-analyze` → PR a `develop` → CI → revisión → merge.

Puertas: el PR enlaza su Issue y su spec; `php artisan test`, `npm run lint`,
`npm run format:check` y `npm run test` pasan; el CI está en verde; al menos una persona distinta de
la autora revisa el PR antes del merge.

## Governance

Esta constitución prevalece sobre otras prácticas del proyecto. Las enmiendas se proponen mediante
un PR que explique el motivo y, si cambia un principio, actualice las specs afectadas. La versión
sigue versionado semántico: MAJOR por eliminar o redefinir un principio, MINOR por agregar uno o
ampliarlo, PATCH por redacción. Todo PR debe verificar el cumplimiento de estos principios; la
guía de uso está en `docs/sdd/sdd-implementation.md`.

**Version**: 1.0.0 | **Ratified**: 2026-09-25 | **Last Amended**: 2026-09-25
