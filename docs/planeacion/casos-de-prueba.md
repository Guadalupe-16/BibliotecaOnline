# Casos de prueba de BibliotecaOnline

Issue #137 · Fecha: 2026-09-25

Matriz de casos de prueba priorizados por flujo crítico. Cada caso indica cómo se automatiza hoy (con el
nombre real del test) o si está planeado.

**Fuentes verificadas:**
- PHPUnit: `tests/Feature/` y `tests/Unit/` (118 pruebas aprobadas el 2026-09-25).
- Playwright: `tests/e2e/` (14 aprobadas con `npm run build` previo).
- Selenium IDE: `docs/selenium/BibliotecaOnline.side` (7 tests; no se ejecutaron en esta revisión).

**Convenciones:** las credenciales de prueba provienen de `database/seeders/RolesSeeder.php`
(usuarios con rol `superadmin`, `admin` y `usuario`); este documento no repite sus contraseñas.

## 1. Matriz de casos

Prioridad: **Alta** (flujo crítico o seguridad), **Media**, **Baja**.
Automatización: **Existe** (con el test real), **Planeada** (aún no existe) o **Manual**.

| ID | Módulo | Precondición | Pasos | Datos | Resultado esperado | Prioridad | Automatización |
|---|---|---|---|---|---|---|---|
| CP-001 | Login | Usuario verificado y activo | Abrir `/login`; escribir correo y contraseña; enviar | Usuario del seeder | Sesión iniciada y redirección fuera de `/login` | Alta | Existe: Selenium `login-exitoso`; `test_usuario_verificado_puede_loguearse` (PHPUnit). Playwright: Planeada |
| CP-002 | Login | Ninguna | Abrir `/login`; enviar credenciales inexistentes | `noexiste@test.com` / `wrongpassword` | Mensaje de credenciales incorrectas; sin sesión | Alta | Existe: Playwright `login.spec.js` "muestra error con credenciales incorrectas" |
| CP-003 | Login | Sin sesión | Abrir `/favoritos` | — | Redirige a `/login` | Alta | Existe: Playwright `login.spec.js` "redirige al login si no está autenticado" |
| CP-004 | Login | Ninguna | Enviar 6 logins fallidos seguidos | Mismo correo e IP | A partir del sexto intento, mensaje de espera; sin bloqueo a otros usuarios | Alta | Existe: `test_login_se_bloquea_despues_de_cinco_intentos_fallidos`, `test_el_limite_de_login_no_afecta_a_otro_usuario` |
| CP-005 | Login | Usuario no verificado | Intentar iniciar sesión | Cuenta sin `email_verified_at` | Login rechazado | Alta | Existe: `test_usuario_sin_verificar_no_puede_loguearse` |
| CP-006 | Login | Sesión iniciada | Pulsar "Cerrar sesión" | — | Sesión cerrada y redirección a inicio | Media | Planeada (sin prueba de logout identificada) |
| CP-007 | Registro | Ninguna | Abrir `/register`; enviar datos válidos | Nombre, correo nuevo, contraseña | Usuario creado sin verificar y PIN enviado por correo | Alta | Existe: `test_registro_crea_usuario_sin_verificar_y_envia_pin`. E2E: Planeada |
| CP-008 | Registro | Usuario pendiente de verificar | Ingresar el PIN correcto | PIN de 6 dígitos | Cuenta verificada | Alta | Existe: `test_pin_correcto_verifica_la_cuenta` |
| CP-009 | Registro | Usuario pendiente | Ingresar un PIN incorrecto | PIN erróneo | Mensaje de error; cuenta sin verificar | Alta | Existe: `test_pin_incorrecto_retorna_error` |
| CP-010 | Registro | Usuario pendiente | Reenviar el PIN tres veces seguidas | — | Tras dos solicitudes, la tercera se bloquea por límite | Media | Existe: `test_reenvio_de_pin_se_bloquea_despues_de_dos_solicitudes` |
| CP-011 | Recuperación | Correo registrado | Solicitar enlace en `/forgot-password` | Correo existente | Enlace de restablecimiento enviado | Alta | Existe: `test_envia_enlace_a_email_existente` |
| CP-012 | Recuperación | Correo no registrado | Solicitar enlace | Correo inexistente | Mensaje de error | Media | Existe: `test_error_si_email_no_existe` |
| CP-013 | Recuperación | Enlace válido | Abrir el enlace y fijar nueva contraseña | Contraseña nueva | Contraseña cambiada | Alta | Existe: `test_resetea_contrasena_correctamente`; token inválido: `test_error_si_token_invalido` |
| CP-014 | Catálogo | Libros en la base | Abrir `/catalogo` | — | Lista de libros | Alta | Existe: `test_catalogo_muestra_libros`; Playwright `catalogo.spec.js`; Selenium `catalogo-navegacion` |
| CP-015 | Catálogo | Catálogo vacío | Abrir `/catalogo` | — | Mensaje de que no hay libros | Baja | Existe: `test_catalogo_muestra_mensaje_cuando_no_hay_libros` |
| CP-016 | Detalle | Libro existente | Abrir `/libros/{id}` | Id válido | Ficha del libro | Media | Existe: `test_detalle_libro_carga_correctamente`; Playwright "muestra la página de detalle de un libro" |
| CP-017 | Detalle | Libro inexistente | Abrir `/libros/{id}` | Id inexistente | 404 | Baja | Existe: `test_detalle_libro_devuelve_404_si_no_existe` |
| CP-018 | Búsqueda | Libros en la base | En `/buscar` escribir un título | "geor" (Selenium) | Lista filtrada por título o autor | Alta | Existe: `test_buscador_filtra_por_titulo`; Selenium `busqueda-libro` |
| CP-019 | Búsqueda | Ninguna coincidencia | Buscar un término inexistente | Texto sin coincidencias | Mensaje de sin resultados | Media | Existe: `test_buscador_muestra_mensaje_sin_resultados` |
| CP-020 | Favoritos | Sesión iniciada | Marcar un libro como favorito | Libro del catálogo | Favorito creado | Alta | Existe: `test_toggle_agrega_favorito`; Selenium `favoritos` |
| CP-021 | Favoritos | Libro ya favorito | Volver a pulsar el corazón | — | Favorito eliminado | Alta | Existe: `test_toggle_quita_favorito_si_ya_existe` |
| CP-022 | Favoritos | Sin sesión | Abrir `/favoritos` | — | Redirección a login | Alta | Existe: `test_favoritos_requiere_autenticacion` |
| CP-023 | Perfil | Sesión iniciada | Cambiar el nombre y guardar | Nombre nuevo | Nombre actualizado | Media | Existe: `test_actualizar_nombre_correctamente` |
| CP-024 | Perfil | Sesión iniciada | Subir un archivo que no es imagen | Archivo inválido | Rechazo con error | Media | Existe: `test_foto_invalida_es_rechazada` |
| CP-025 | Roles | Usuario con rol `usuario` | Abrir `/admin/roles` | — | Acceso denegado | Alta | Existe: `test_usuario_normal_no_puede_ver_panel_roles` |
| CP-026 | Roles | Admin con sesión | Intentar asignar el rol `superadmin` | Rol `superadmin` | Rechazado | Alta | Existe: `test_admin_no_puede_asignar_el_rol_superadmin` |
| CP-027 | Roles | Admin con sesión | Abrir `/superadmin` | — | Acceso denegado | Alta | Existe: `test_admin_no_puede_acceder` |
| CP-028 | Superadmin | Superadmin con sesión | Desactivar a otro usuario | Usuario distinto | Usuario desactivado | Alta | Existe: `test_superadmin_puede_desactivar_usuario` |
| CP-029 | Superadmin | Superadmin con sesión | Intentar desactivarse a sí mismo | Su propio id | Rechazado | Alta | Existe: `test_superadmin_no_puede_desactivarse_a_si_mismo` |
| CP-030 | Superadmin | Superadmin con sesión | Abrir la gráfica de usuarios | — | Datos y gráfica visibles | Media | Existe: `GraficaUsuariosTest` (5 casos) |
| CP-031 | Actividad | Admin con sesión | Abrir `/admin/logs` y filtrar por "login" | Filtro de texto | Registros filtrados | Media | Existe: Selenium `panel-logs`; PHPUnit: no identificado |
| CP-032 | Open Library | Sin sesión | Abrir `/open-library/buscar` | — | Redirección a login | Alta | Existe: `test_buscar_requiere_autenticacion` |
| CP-033 | Open Library | Sesión con rol `usuario` | Importar un libro | Id de Open Library válido | Acceso denegado | Alta | Existe: `test_importar_requiere_rol_autorizado` |
| CP-034 | Open Library | Admin con sesión | Importar un libro con categoría válida | Id y categoría | Trabajo de importación en cola | Alta | Existe: `test_importar_libro_despacha_job` |
| CP-035 | Open Library | Sesión iniciada | Buscar "clean code" | Término | Resultados de la API (simulada en pruebas) | Media | Existe: `test_buscar_retorna_resultados`; Selenium `buscar-open-library` |
| CP-036 | Seguridad | Usuario con rol `usuario` | Abrir `/usuarios` y editar o eliminar otro usuario | Usuario distinto | **Debe** responder 403 | Alta | Planeada. **Falla hoy**: las rutas solo exigen sesión (brecha S1) |

Los casos de seguridad de rol y de sesión están sobre todo en PHPUnit; los flujos de navegador (Playwright
y Selenium) cubren login, catálogo y navegación. Los casos marcados "E2E: Planeada" son los candidatos de
la spec `specs/001-pruebas-e2e-playwright` (Issue #140).

## 2. Resumen de automatización

| Estado | Casos |
|---|---:|
| Con prueba existente (al menos un nivel) | 34 |
| Planeada sin prueba | 2 (CP-006, CP-036) |
| Con prueba de navegador (Playwright o Selenium) | 9 (CP-001, 002, 003, 014, 016, 018, 020, 031, 035) |

Los conteos de la tabla de arriba se pueden reconstruir contando las filas de la sección 1: 36 casos,
de los cuales 2 no tienen prueba hoy.

## 3. Caso detallado: CP-001 Login exitoso

### 3.1 En lenguaje neutral

| Elemento | Detalle |
|---|---|
| Objetivo | Un usuario verificado inicia sesión con sus credenciales |
| Precondiciones | Base de datos con los usuarios del seeder; usuario con `email_verified_at` y `activo` verdadero |
| Pasos | 1. Abrir la página de inicio. 2. Elegir "Iniciar sesión". 3. Escribir el correo. 4. Escribir la contraseña. 5. Pulsar el botón de enviar |
| Resultado esperado | El usuario queda autenticado y sale de `/login` (el menú muestra su sesión) |
| Resultado ante error | Con credenciales incorrectas: mensaje de credenciales inválidas y sin sesión (CP-002) |
| Prioridad | Alta |

### 3.2 Equivalente en Selenium IDE (`docs/selenium/BibliotecaOnline.side`, test `login-exitoso`)

Comandos grabados, tal como están en el archivo (URL base `http://127.0.0.1:8001`):

| # | Comando | Target | Value |
|---|---|---|---|
| 1 | `open` | `/` | |
| 2 | `setWindowSize` | `1710x1073` | |
| 3 | `click` | `linkText=Iniciar sesión` | |
| 4 | `click` | `id=email` | |
| 5 | `type` | `id=email` | correo del usuario del seeder |
| 6 | `click` | `id=password` | |
| 7 | `type` | `id=password` | contraseña del usuario del seeder |
| 8 | `click` | `css=.transform` | |

Limitaciones observadas: el test no contiene ninguna aserción (no verifica que el login haya funcionado),
la URL base usa el puerto 8001 (Playwright usa el 8000) y el último `click` depende de la clase CSS
`.transform`, que es frágil. Los datos de acceso del test grabado están guardados en el archivo del
proyecto.

### 3.3 Equivalente en Playwright (propuesto)

No existe aún en `tests/e2e/`; es la propuesta para la spec 001. Está sin ejecutar.

```js
import { test, expect } from '@playwright/test';

test('login exitoso con usuario del seeder', async ({ page }) => {
  await page.goto('/');
  await page.getByRole('link', { name: 'Iniciar sesión' }).click();
  await page.fill('input[type="email"]', process.env.E2E_USER_EMAIL);
  await page.fill('input[type="password"]', process.env.E2E_USER_PASSWORD);
  await page.click('button[type="submit"]');
  await expect(page).not.toHaveURL(/login/);
});
```

Las credenciales se pasan por variables de entorno para no guardarlas en el código.

### 3.4 Prueba existente que sí corre hoy

PHPUnit `VerificacionEmailPinTest::test_usuario_verificado_puede_loguearse` cubre la lógica de login con
un usuario verificado; el flujo de navegador completo con credenciales correctas todavía no está
automatizado en Playwright.

## 4. Relación con Selenium IDE y Playwright

| Herramienta | Qué aporta | Estado |
|---|---|---|
| Selenium IDE | 7 flujos grabados: login, búsqueda, catálogo, favoritos, panel de logs, gestión de usuarios y Open Library | Documentados; sin aserciones y con selectores frágiles; no corren en CI |
| Playwright | 14 pruebas reales: login, catálogo, navegación y ejemplos | Corren localmente; el CI aún no las ejecuta (Issue #138) |
| PHPUnit | 118 pruebas de reglas, rutas, autorización y base de datos | Corren en CI |

Recomendaciones (a decidir en `specs/001`): convertir los flujos de Selenium a Playwright con aserciones,
quitar `example.spec.js` y renombrar `contacto.spec.js`, que no prueba contacto.

## 5. Trazabilidad

| Módulo | Casos |
|---|---|
| Login | CP-001 a CP-006 |
| Registro | CP-007 a CP-010 |
| Recuperación | CP-011 a CP-013 |
| Catálogo y detalle | CP-014 a CP-017 |
| Búsqueda | CP-018, CP-019 |
| Favoritos | CP-020 a CP-022 |
| Perfil | CP-023, CP-024 |
| Roles y superadmin | CP-025 a CP-030 |
| Actividad | CP-031 |
| Open Library | CP-032 a CP-035 |
| Seguridad de rutas | CP-036 |

Los casos de la guía interactiva (spec 002) están en `specs/002-guia-interactiva-driverjs/quickstart.md`
y sus pruebas en `tasks.md` (estado PLANEADO).
