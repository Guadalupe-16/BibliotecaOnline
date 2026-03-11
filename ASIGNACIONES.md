# Asignacion de Issues - BibliotecaOnline

> **Total de issues open:** 18
> **Equipo:** 4 integrantes — ~4-5 issues por persona
> **Requisito del maestro:** Cada integrante debe tener **minimo 2 tarjetas cerradas (done) con PR aprobado** por otro integrante.

---

## Buenas Practicas Obligatorias

> **Todo lo que se implemente debe seguir estas practicas sin excepcion.**
> El maestro las evaluara directamente en el codigo y en la presentacion.

### PHP / Laravel

- **Skinny controllers, fat models** — Los controladores solo reciben la peticion, llaman un metodo del modelo o servicio, y retornan la respuesta. Toda la logica de negocio va en el Model, Repository o Service.

  ```php
  // MAL — logica en el controlador
  public function store(Request $request) {
      $libro = Libro::where('copias_disponibles', '>', 0)->first();
      $libro->copias_disponibles--;
      $libro->save();
  }

  // BIEN — logica en el modelo
  public function store(Request $request) {
      $libro->registrarPrestamo($request->user_id);
  }
  ```

- **Validacion en Form Requests** — Nunca validar directamente en el controlador con `$request->validate()`. Crear una clase `App\Http\Requests\NombreRequest` para cada formulario.

- **Nombrado en español consistente** — Variables, metodos, rutas y columnas de DB en español (ej: `$libro->estaDisponible()`, no `$book->isAvailable()`). Solo los nombres de clases y namespaces en ingles (convencion de Laravel).

- **Especificar `$table` en modelos con nombres en español** — Laravel pluraliza en ingles. Siempre declarar `protected $table = 'nombre_tabla';` en cada modelo.

- **Usar `$casts`** — Declarar los tipos de columnas especiales (fechas, booleans, enums) en el array `$casts` del modelo para que Laravel los convierta automaticamente.

- **Evitar N+1 queries** — Siempre usar `with()` (eager loading) al consultar relaciones en un loop.

  ```php
  // MAL — hace 1 query por cada libro
  $libros = Libro::all();
  foreach ($libros as $libro) { $libro->autor->nombre; }

  // BIEN — 2 queries en total
  $libros = Libro::with('autor')->get();
  ```

### Frontend (Blade + Alpine.js + Tailwind)

- **Componentes Blade reutilizables** — Extraer elementos repetidos (tarjetas, botones, modales) a `resources/views/components/`. Usar `<x-nombre-componente />` en las vistas.

- **Alpine.js para interactividad** — Todos los eventos de mouse (hover, click, focus) y animaciones de UI se manejan con Alpine.js usando `x-data`, `x-on:click`, `x-show`, `x-transition`. No escribir jQuery ni JS vanilla mezclado en Blade.

- **Transiciones con `x-transition`** — Para mostrar/ocultar elementos con animacion usar las directivas de Alpine en lugar de clases CSS manuales.

  ```html
  <!-- BIEN -->
  <div x-show="abierto" x-transition>Contenido</div>
  ```

- **Tailwind sin CSS personalizado** — Usar solo clases de Tailwind. Agregar CSS propio unicamente si algo es imposible con Tailwind, y hacerlo en `resources/css/app.css`.

### Git

- **Una rama por issue** — Nombre de rama: `feature/nombre-del-issue` (ej: `feature/vista-catalogo`). Nunca trabajar directo en `main` o `develop`.

- **Commits atomicos y descriptivos** — Cada commit debe representar un solo cambio logico. Mensaje en español, con prefijo:
  - `feat:` para nueva funcionalidad
  - `fix:` para correccion de bug
  - `style:` para cambios de UI sin logica
  - `test:` para tests
  - `docs:` para documentacion

  ```
  feat: agregar busqueda dinamica con Livewire en catalogo
  fix: corregir validacion de correo en registro
  ```

- **PR con descripcion completa** — Al abrir un Pull Request incluir: que se hizo, como probarlo, capturas de pantalla si hay cambios visuales, y solicitar revision a un companero.

- **No mergear tu propio PR** — Siempre debe aprobarlo otro integrante del equipo.

### Seguridad

- **Nunca confiar en datos del usuario** — Todo input que venga de un formulario debe pasar por validacion con Form Request antes de usarse.

- **Usar `{{ }}` en Blade, no `{!! !!}`** — Las llaves dobles escapan HTML automaticamente y previenen XSS. Solo usar `{!! !!}` si el contenido es HTML generado por el propio sistema y es de confianza.

- **Proteger rutas con middleware** — Las rutas que requieren autenticacion deben usar `->middleware('auth')`. Las de administrador `->middleware('role:admin')`.

- **Variables de entorno** — Ningun dato sensible (contrasenas, API keys, tokens) en el codigo. Todo en el archivo `.env`, accedido con `config()` o `env()`.

### Tests

- **Todo issue debe tener al menos un test** — Si es logica de modelo, un test unitario en `tests/Unit/`. Si es una vista o flujo completo, un test de feature en `tests/Feature/`.

- **Cobertura minima del 90%** — Requisito del maestro. Ejecutar `php artisan test --coverage` para verificar.

- **Nombrar los tests descriptivamente** — El nombre del metodo debe describir exactamente que se esta probando.

  ```php
  // BIEN
  public function test_libro_no_disponible_cuando_copias_son_cero(): void
  ```

---

## Integrantes y sus issues

---

### Guadalupe Amavizca Quinter

> **Rol en el proyecto:** Base del sistema + Catalogo + API externa
> Ya tiene asignados #2 y #3 en GitHub.

| Issue | Titulo | Que hay que hacer |
|-------|--------|-------------------|
| #2 | **Base de Datos** | Crear las migraciones en Laravel para todas las tablas: `libros`, `autores`, `categorias`, `prestamos`. Tambien los Modelos con sus relaciones (hasMany, belongsTo). Esto es la base de todo el proyecto. |
| #3 | **API de Open Library** | Conectarse a la API publica de Open Library (openlibrary.org) para buscar/importar informacion de libros. Usar `Http::get()` de Laravel de forma **asincrona**. |
| #15 | **Vista del catalogo** | Pagina principal de libros. Mostrar tarjetas de libros con imagen, titulo y autor. Agregar **animaciones CSS** al hacer hover sobre cada tarjeta (Tailwind: `transition`, `hover:scale-105`). |
| #16 | **Vista detalle de libro** | Pagina individual de un libro. Mostrar toda la info: descripcion, autor, categoria, disponibilidad. Agregar **transicion** al cargar la pagina (fade-in con Alpine.js). |
| #25 | **Buscador dinamico** | Busqueda de libros en tiempo real con **Livewire** (sin recargar la pagina). Esto cubre el requisito de **funciones asincronas** del maestro. |

**Issues que cubren requisitos del maestro:**
- Funciones asincronas → #3 (Api Http), #25 (Livewire)
- Animaciones/transiciones → #15, #16

---

### Joel Armando Ibarra Rubalcava

> **Rol en el proyecto:** Autenticacion y control de acceso

| Issue | Titulo | Que hay que hacer |
|-------|--------|-------------------|
| #12 | **Crear vista de login** | Formulario de inicio de sesion. Agregar **animacion de entrada** al formulario (Alpine.js x-transition). Agregar **evento de mouse** para efecto visual al hacer hover en el boton. Validar con las reglas de Laravel. |
| #13 | **Crear vista de Registro** | Formulario de registro de nuevos usuarios. Validacion de contrasena segura (minimo 8 caracteres, mayuscula, numero, simbolo). Esto cubre el requisito de seguridad de **validacion de contraseñas**. |
| #14 | **Recuperacion de contrasena** | Implementar el flujo de "olvide mi contrasena" con Laravel Breeze o manualmente. Envio de email con link temporal. |
| #19 | **Roles del sistema** | Crear roles: `admin`, `usuario`. Proteger rutas con middleware segun el rol. Ejemplo: solo admin puede agregar/editar libros. |
| #23 | **Pagina 404** | Crear una pagina 404 personalizada y bonita para cuando el usuario entre a una ruta que no existe. Agregar una **animacion** (puede ser un Lottiefiles de "pagina no encontrada"). |

**Issues que cubren requisitos del maestro:**
- Animaciones/transiciones → #12 (form transition), #23 (Lottie)
- Eventos del mouse → #12 (hover button)
- Seguridad: validacion de contrasenas → #13
- Seguridad: roles/middleware → #19

---

### Jorge Humberto Martinez Delgado

> **Rol en el proyecto:** Gestion de usuarios + Navegacion + Favoritos

| Issue | Titulo | Que hay que hacer |
|-------|--------|-------------------|
| #17 | **Sistema de favoritos** | Que los usuarios puedan guardar libros en su lista de favoritos. Boton de corazon con **evento de click** y **animacion** al agregar/quitar favorito (Alpine.js toggle + CSS transition). |
| #18 | **CRUD de usuarios** | Panel de administrador para ver, editar y eliminar usuarios. Aplicar el patron **skinny controller / fat model**: la logica va en el Model o en un Repository, el Controller solo llama metodos. |
| #21 | **Menu principal** | Barra de navegacion responsiva. **Eventos del mouse** para mostrar dropdowns al hover. Animacion de apertura del menu movil (Alpine.js x-transition). |
| #22 | **Pagina About** | Pagina "Acerca de" con la informacion del equipo, fotos de los integrantes y descripcion del proyecto. Agregar **animaciones de entrada** (scroll reveal con Alpine.js o CSS). |
| #26 | **Formulario de contacto** | Formulario para que los usuarios envien mensajes. Validacion en frontend (Alpine.js) y backend (Laravel Request). Mostrar mensaje de exito con **transicion** despues de enviar. |

**Issues que cubren requisitos del maestro:**
- Eventos del mouse → #17 (favorito click), #21 (menu hover)
- Animaciones/transiciones → #17, #21, #22, #26
- Buenas practicas: skinny controller / fat model → #18
- Design pattern: Repository → #18

---

### Javier Antonio Romo Bernal

> **Rol en el proyecto:** Features dinamicas + Soporte + Logs

| Issue | Titulo | Que hay que hacer |
|-------|--------|-------------------|
| #20 | **Registro de logs** | Guardar en la base de datos cada accion importante: login, busqueda de libro, prestamo. Usar el sistema de `Log` de Laravel o crear una tabla `activity_logs`. |
| #24 | **Mapa de sitio** | Pagina con el mapa del sitio (sitemap) para SEO. Tambien puede ser un sitemap.xml generado automaticamente. **Este cubre el requisito de Web Services** si se integra con Google Search Console. |
| #27 | **Chat de soporte** | Widget de chat en tiempo real o con respuestas predefinidas. Usar **Livewire** para actualizacion en tiempo real (**funcion asincrona**). Agregar **eventos de mouse** (hover para abrir/cerrar el chat). **Animacion** de apertura del widget. |
| #28* | **Script backup/restauracion DB** | *(Issue nuevo que hay que crear)* Script en PHP o Bash que haga `mysqldump` para respaldar la base de datos y que permita restaurarla. **Requisito obligatorio del maestro (punto 6)**. |

> *#28 es un issue que **deben crear** en GitHub, el maestro lo pide especificamente (punto 6 de la entrega).

**Issues que cubren requisitos del maestro:**
- Funciones asincronas → #27 (Livewire chat)
- Eventos del mouse → #27 (chat toggle)
- Animaciones/transiciones → #27 (chat open animation)
- Backup/restauracion DB → #28

---

## Resumen visual de asignaciones

| Integrante | Issues asignados | Issues para "done" obligatorio |
|---|---|---|
| Guadalupe | #2, #3, #15, #16, #25 | #2 y #3 (ya iniciados) |
| Joel | #12, #13, #14, #19, #23 | #12 y #13 |
| Jorge | #17, #18, #21, #22, #26 | #17 y #21 |
| Javier | #20, #24, #27, #28 | #27 y #28 |

---

## Issues que faltan crear en GitHub

Estos requisitos del maestro **no tienen issue en el tablero** — hay que crearlos:

| Issue nuevo | Descripcion | Responsable sugerido |
|---|---|---|
| Script Backup/Restauracion DB | Script mysqldump + restore | Javier |
| Configuracion CORS y Headers de seguridad | Middleware de seguridad, Content-Security-Policy | Joel |
| Animacion Lottiefiles | Integrar lottiefiles en pagina 404 o splash screen | Joel |
| Tests unitarios PHPUnit | Tests de modelos y controladores (coverage >=90%) | Guadalupe |
| Tests e2e Playwright | Pruebas automaticas de flujo login/busqueda/catalogo | Jorge |

---

## Requisitos tecnicos del maestro — donde se cubren

| Requisito | Quien lo hace | Issue |
|---|---|---|
| Funciones asincronas (Livewire/Http) | Guadalupe, Javier | #25, #3, #27 |
| Eventos del mouse | Jorge, Joel, Javier | #17, #21, #12, #27 |
| Animaciones CSS / Tailwind | Guadalupe, Joel, Jorge | #15, #16, #12, #22 |
| Lottiefiles | Joel | #23 |
| CORS | Joel | Issue nuevo |
| XSS (Laravel default + headers) | Joel | Issue nuevo |
| Validacion de contrasenas | Joel | #13 |
| Backup/restauracion DB | Javier | #28 |
| API de terceros | Guadalupe | #3 (Open Library) |
| Skinny controller / fat model | Jorge | #18 |
| Design pattern (Repository) | Jorge | #18 |
| Tests unitarios | Guadalupe | Issue nuevo |
| Tests e2e | Jorge | Issue nuevo |

---

## Flujo de trabajo para cada issue (importante para el maestro)

1. **Crear rama** con el nombre `feature/nombre-del-issue` desde `develop`
2. **Hacer commits** con mensajes descriptivos en español
3. **Abrir Pull Request** hacia `develop` con descripcion de que se hizo
4. **Pedir revision** a otro integrante del equipo (el maestro pide code review)
5. **Aprobar y mergear** despues de la revision
6. **Cerrar el issue** en GitHub

> El maestro pide que cada PR done tenga aprobacion de otro integrante. Asignense mutuamente como revisores en GitHub.

---

## Orden de implementacion — 4 dias

```
DIA 1 (base del proyecto — todos dependen de esto):
  Guadalupe → #2  Base de datos: migraciones + modelos con relaciones
  Joel      → #12 Vista de login
  Joel      → #13 Vista de registro + validacion de contrasena
  Jorge     → #21 Menu principal
  Javier    → #28 Script backup/restauracion DB  ← crear issue primero

DIA 2 (funcionalidades principales):
  Guadalupe → #15 Vista del catalogo (con animaciones CSS)
  Guadalupe → #3  API de Open Library
  Joel      → #19 Roles del sistema (admin / usuario)
  Jorge     → #17 Sistema de favoritos (con eventos de mouse)
  Jorge     → #18 CRUD de usuarios (skinny controller / fat model)
  Javier    → #27 Chat de soporte (Livewire — funcion asincrona)

DIA 3 (features secundarios + seguridad):
  Guadalupe → #25 Buscador dinamico (Livewire)
  Guadalupe → #16 Vista detalle de libro
  Joel      → #14 Recuperacion de contrasena
  Joel      → #23 Pagina 404 con Lottiefiles
  Joel      → Issue CORS + Headers de seguridad  ← crear issue
  Jorge     → #22 Pagina About
  Jorge     → #26 Formulario de contacto
  Javier    → #20 Registro de logs
  Javier    → #24 Mapa de sitio

DIA 4 (cierre, tests y entrega):
  Guadalupe → Tests unitarios PHPUnit (coverage >=90%)  ← crear issue
  Jorge     → Tests e2e Playwright  ← crear issue
  Joel      → Evidencia de seguridad (MDN Observatory antes/despues)
  Javier    → Verificar que backup/restore funciona correctamente
  Todos     → Revisar y aprobar PRs pendientes
  Todos     → Crear tag de release v1.0.0 en GitHub
```

> **Prioridad absoluta el Dia 1:** Guadalupe debe terminar #2 (Base de datos)
> antes de que los demas puedan conectar sus vistas con datos reales.
