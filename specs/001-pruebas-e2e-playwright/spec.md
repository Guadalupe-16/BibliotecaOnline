# Feature Specification: Pruebas E2E con Playwright — flujos críticos de BibliotecaOnline

**Feature Branch**: `docs/140-plan-pruebas-spec-001`

**Created**: 2026-09-25

**Status**: Draft

**Input**: User description: "Especificar con Spec Kit la evolución de las pruebas E2E con Playwright
de BibliotecaOnline, sin rehacer lo que ya existe (`tests/e2e/`). Historias: autenticación, catálogo
y búsqueda, favoritos, y roles y seguridad."

**Issue**: #140

## Contexto: estado actual (verificado el 2026-09-25)

- `tests/e2e/` tiene 4 archivos con **12 pruebas**, que pasan en local y en CI (job `E2E Playwright`,
  spec 003, run 36205499377).
- Lo que ya cubren y se conserva: página de login, error con credenciales incorrectas, redirección
  de invitado a login desde `/favoritos`, página del catálogo, buscador visible, menú lateral.
- Defectos de la suite actual (research.md §2):
  - "muestra la página de detalle de un libro" **no verifica nada**: su selector encuentra 0 enlaces
    y un `if` la deja pasar.
  - 2 pruebas no prueban lo que dice su nombre ("navega a la pagina about" y "navega al formulario
    de contacto" solo abren `/catalogo`), y el grupo "Formulario de contacto" (`contacto.spec.js`)
    nunca abre el formulario: sus 2 pruebas repiten catálogo y login.
  - No hay ninguna prueba con sesión iniciada: ni favoritos, ni roles, ni logout.

## User Scenarios & Testing *(mandatory)*

Los "usuarios" de esta feature son los integrantes del equipo, que necesitan que la suite E2E
detecte cuando un flujo crítico se rompe. Cada historia describe un flujo de la aplicación que la
suite debe verificar desde el navegador.

### User Story 1 - Autenticación (Priority: P1)

La suite verifica que un lector, un administrador y un superadministrador pueden iniciar y cerrar
sesión, y que un visitante sin sesión no entra a páginas privadas.

**Why this priority**: Todos los demás flujos con sesión (favoritos, roles) dependen de que el login
funcione; hoy solo se prueba el caso de error.

**Independent Test**: Ejecutar solo las pruebas de autenticación contra una base con los usuarios
del seed; deben pasar sin depender de otras historias.

**Acceptance Scenarios**:

1. **Given** un usuario verificado con rol `usuario`, **When** inicia sesión con credenciales
   correctas, **Then** sale de `/login` y ve la interfaz de usuario autenticado.
2. **Given** credenciales incorrectas, **When** intenta iniciar sesión, **Then** sigue en `/login`
   y ve un mensaje de error (prueba existente, se conserva).
3. **Given** un usuario con sesión, **When** cierra sesión, **Then** vuelve a ser visitante y
   `/favoritos` lo redirige a `/login`.
4. **Given** un visitante, **When** abre `/favoritos`, `/perfil` o `/dashboard`, **Then** es
   redirigido a `/login`.

---

### User Story 2 - Catálogo y búsqueda (Priority: P1)

La suite verifica que un visitante puede explorar el catálogo, abrir el detalle de un libro real y
buscar por título o autor.

**Why this priority**: Es el flujo principal de la aplicación y la prueba de detalle actual pasa sin
verificar nada.

**Independent Test**: Con la base sembrada (5 autores y libros del `BibliotecaSeeder`), ejecutar
solo las pruebas de catálogo y búsqueda.

**Acceptance Scenarios**:

1. **Given** el catálogo sembrado, **When** un visitante abre `/catalogo`, **Then** ve al menos una
   tarjeta de libro.
2. **Given** el catálogo, **When** hace clic en la primera tarjeta, **Then** llega a
   `/libros/{id}` y ve el título de ese libro. La prueba **falla** si no hay libros (sin `if`).
3. **Given** `/buscar`, **When** escribe parte del título de un libro sembrado, **Then** la lista
   se actualiza y muestra ese libro.
4. **Given** `/buscar`, **When** escribe un texto que no coincide con ningún libro, **Then** ve el
   estado de "sin resultados".
5. **Given** `/buscar`, **When** filtra por una categoría, **Then** solo ve libros de esa categoría.

---

### User Story 3 - Favoritos (Priority: P2)

La suite verifica que un lector con sesión puede agregar un libro a favoritos desde su detalle,
verlo en `/favoritos` y quitarlo.

**Why this priority**: Es la función principal con sesión y no tiene ninguna prueba E2E
(trazabilidad: "Implementado; sin E2E").

**Independent Test**: Iniciar sesión como `usuario` y ejecutar solo las pruebas de favoritos; cada
prueba deja la lista de favoritos como la encontró.

**Acceptance Scenarios**:

1. **Given** un lector con sesión en el detalle de un libro que no es favorito, **When** pulsa
   "Agregar a favoritos", **Then** el botón cambia a "Quitar de favoritos".
2. **Given** ese libro marcado, **When** abre `/favoritos`, **Then** el libro aparece en la lista.
3. **Given** el libro en `/favoritos`, **When** lo quita, **Then** desaparece de la lista y al
   recargar la página sigue sin aparecer.
4. **Given** un visitante sin sesión en el detalle de un libro, **When** ve la página, **Then** no
   aparece el botón de favoritos.

---

### User Story 4 - Roles y seguridad (Priority: P2)

La suite verifica desde el navegador que cada rol solo accede a lo que le corresponde (constitución,
principio VI: pruebas negativas sin sesión, sin rol y con rol insuficiente).

**Why this priority**: La autorización ya está cubierta por PHPUnit (`RbacTest`,
`SuperAdminControllerTest`); el E2E agrega la verificación de extremo a extremo de lo que ve cada
rol.

**Independent Test**: Con los 3 usuarios del `RolesSeeder`, ejecutar solo las pruebas de roles.

**Acceptance Scenarios**:

1. **Given** un `usuario`, **When** abre `/admin/roles` o `/superadmin`, **Then** recibe 403.
2. **Given** un `admin`, **When** abre `/admin/roles`, **Then** ve el panel de roles; **When** abre
   `/superadmin`, **Then** recibe 403.
3. **Given** un `superadmin`, **When** abre `/superadmin`, **Then** ve el panel de
   superadministrador.
4. **Given** un visitante, **When** abre `/admin/roles` o `/superadmin`, **Then** es redirigido a
   `/login`.
5. **Given** un `usuario`, **When** abre `/usuarios`, **Then** recibe 403. **Hoy falla**: la ruta
   solo exige sesión (defecto conocido; ver Assumptions).

---

### Edge Cases

- **Límite de intentos de login**: 5 por minuto por correo + IP. Si cada prueba inicia sesión con el
  mismo usuario, la suite puede bloquearse; hay que iniciar sesión una vez por rol y reutilizar la
  sesión.
- **Estado compartido**: las pruebas de favoritos modifican datos; si una falla a medias puede dejar
  un favorito que haga fallar a la siguiente.
- **Búsqueda con retardo**: el buscador espera 300 ms después de escribir; las aserciones deben
  esperar el resultado, no un tiempo fijo.
- **Registro y verificación con PIN**: requieren un correo real; quedan fuera del E2E (ya
  documentado en `docs/conclusiones.md`) y están cubiertos por PHPUnit.
- **Base sin datos**: si el seed no se ejecutó, las pruebas de catálogo deben fallar con claridad,
  no pasar vacías.

## Requirements *(mandatory)*

### Functional Requirements

**Suite existente**

- **FR-001**: La suite MUST conservar las pruebas existentes que verifican lo que dicen (login,
  error de credenciales, redirección de invitado, catálogo, buscador visible, menú lateral).
- **FR-002**: La prueba de detalle de libro MUST fallar si no encuentra un libro que abrir; no puede
  haber condicionales que dejen pasar una prueba sin aserciones.
- **FR-003**: Las pruebas cuyo nombre no coincide con lo que verifican MUST renombrarse o corregirse
  para que nombre y verificación coincidan.

**Nuevos flujos**

- **FR-004**: La suite MUST cubrir los escenarios de las historias 1 a 4.
- **FR-005**: Las pruebas con sesión MUST usar los usuarios del `RolesSeeder` (`usuario`, `admin`,
  `superadmin`) y MUST NOT requerir credenciales fuera del repositorio.
- **FR-006**: La suite MUST iniciar sesión como máximo una vez por rol y por ejecución, reutilizando
  la sesión, para no chocar con el límite de 5 intentos por minuto.
- **FR-007**: Las pruebas que modifican datos (favoritos) MUST dejar el estado como lo encontraron.
- **FR-008**: Las pruebas MUST esperar condiciones observables (texto, URL, elemento) y MUST NOT
  usar esperas de tiempo fijo.

**Ejecución y evidencia**

- **FR-009**: La suite MUST seguir ejecutándose en el job `E2E Playwright` del CI (spec 003) sin
  cambios en el workflow.
- **FR-010**: Un defecto de la aplicación que haga fallar un escenario (p. ej. `/usuarios` sin
  restricción de rol) MUST registrarse como Issue antes de agregar su prueba; la prueba se agrega
  cuando exista la corrección, sin desactivarla para pasar el CI (principio VII).
- **FR-011**: La decisión sobre Playwright MCP MUST quedar documentada en research.md.

### Key Entities

- **Prueba E2E**: flujo verificado en el navegador; pertenece a una historia y a un archivo de
  `tests/e2e/`.
- **Sesión por rol**: estado de sesión guardado de `usuario`, `admin` y `superadmin`, reutilizado
  por las pruebas.
- **Datos de prueba**: usuarios del `RolesSeeder`, libros, autores y categorías del
  `BibliotecaSeeder`.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Las 4 historias tienen al menos una prueba E2E por escenario de aceptación (salvo
  US4-5, bloqueada por el defecto de `/usuarios`).
- **SC-002**: 0 pruebas que pasen sin aserciones ejecutadas y 0 pruebas cuyo nombre no coincida con
  lo que verifican.
- **SC-003**: La suite completa pasa en el CI y el job E2E sigue por debajo de 10 minutos.
- **SC-004**: Ejecutar la suite 3 veces seguidas produce el mismo resultado (sin pruebas
  inestables ni bloqueos por límite de intentos).
- **SC-005**: Cada ruta privada (`/favoritos`, `/perfil`, `/dashboard`, `/admin/roles`,
  `/superadmin`) tiene al menos una prueba negativa de acceso.

## Assumptions

- La infraestructura para ejecutar la suite (job de CI, devcontainer) ya existe (spec 003, #138) y no
  se modifica aquí.
- Los usuarios del `RolesSeeder` tienen el correo verificado y contraseñas conocidas en el
  repositorio; son datos de prueba, no secretos.
- **Defecto conocido**: `/usuarios`, `/usuarios/{id}/edit`, `PUT` y `DELETE /usuarios/{id}` solo
  exigen sesión (`routes/web.php`, grupo `auth` sin `role:`). Ya está registrado en
  `docs/sdd/estado-actual/trazabilidad.md` ("sin restricción de rol"). Corregirlo está fuera del
  alcance de esta spec (que es de pruebas); se propone un Issue `fix/`.
- El registro y la verificación con PIN quedan fuera del E2E por depender de un correo real.
- Solo Chromium, igual que el CI.
- Este Issue (#140) entrega la especificación, el plan, las tareas y el análisis; la escritura de
  las pruebas nuevas es trabajo posterior guiado por `tasks.md`.
