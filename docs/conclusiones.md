# Conclusiones del Proyecto — Biblioteca Digital

## Sobre el proyecto

Biblioteca Digital es una aplicación web desarrollada como proyecto final de la materia Desarrollo Web Profesional en la Universidad Tecnológica de Honduras (UTH). El objetivo fue construir un sistema funcional de biblioteca en línea usando el stack TALL (Tailwind, Alpine.js, Laravel, Livewire), trabajando en equipo con flujo de trabajo profesional: ramas por feature, pull requests, revisión de código y tablero Kanban en GitHub Projects.

---

## Conclusiones

**Guadalupe**

Al principio me costó bastante entender cómo funcionaban las migraciones en Laravel cuando hay relaciones entre tablas, especialmente con las llaves foráneas. Más de una vez tuve que hacer rollback y replantear el orden de las migraciones. La integración con Open Library también fue más complicada de lo que esperaba porque la API no siempre devuelve los mismos campos y tuve que manejar muchos casos donde los datos venían vacíos o en formatos distintos. Con todo eso, siento que aprendí más de lo que hubiera aprendido siguiendo un tutorial paso a paso.

**Joel**

Lo que más me costó fue el sistema de verificación de correo con PIN. Nunca había trabajado con envío de correos desde Laravel y configurar Mailtrap, generar el PIN, guardarlo hasheado y manejarlo con expiración me tomó varios días. También el middleware de roles fue algo que no entendía del todo al principio, pero una vez que lo vi funcionando en el proyecto entendí por qué es tan importante separar los niveles de acceso desde el backend y no solo desde las vistas.

**Jorge**

Implementar favoritos parecía sencillo al principio pero cuando llegué a la parte del toggle con fetch y Alpine.js sin recargar la página me di cuenta de que había muchas cosas que no sabía sobre cómo funciona el frontend reactivo. El CRUD de usuarios también me enseñó bastante sobre validaciones en Laravel y cómo manejar errores de formulario de forma limpia. Si volviera a empezar, le dedicaría más tiempo al diseño de la base de datos antes de escribir una sola línea de código.

**Javier**

Mi parte favorita fue el panel de logs con Livewire. Ver cómo los filtros actualizan la tabla sin recargar la página y entender cómo Livewire maneja el estado del componente fue algo que me pareció muy poderoso. Lo que más me costó fue aprender a hacer pruebas automatizadas, tanto con Selenium IDE como con Vitest, porque nunca lo había hecho antes. Al final entendí que las pruebas no son opcionales en un proyecto real, son lo que te da confianza para hacer cambios sin romper lo que ya funciona.

---

## Problemas encontrados

Durante el desarrollo nos encontramos con varios problemas que vale la pena documentar:

**Conflictos de merge frecuentes**
Como todos trabajábamos sobre archivos compartidos como `navigation.blade.php` y `routes/web.php`, los conflictos de merge eran casi garantizados cada semana. Tuvimos que aprender a resolverlos manualmente y a coordinar mejor quién tocaba qué archivo. Lo registramos en el proyecto como aprendizaje del equipo.

**Verificación de correo bloqueaba pruebas automatizadas** *(relacionado con Issue #94)*
Al querer automatizar el test de registro con Selenium IDE, nos dimos cuenta de que el flujo requiere un PIN enviado al correo del usuario. Eso hace imposible automatizarlo sin intervención manual o sin acceso a la bandeja de entrada. Decidimos documentarlo como limitación y verificar ese flujo de forma manual.

**Meta tag CSRF faltante en el layout principal**
El botón de favoritos no funcionaba al hacer click porque el layout `app.blade.php` no incluía el meta tag `csrf-token`. Las peticiones fetch fallaban silenciosamente sin ningún mensaje de error visible para el usuario. Se corrigió agregando el meta tag al layout.

**Seeder no actualizaba contraseñas existentes** *(Issue #seeder)*
El método `firstOrCreate` en los seeders no actualiza registros que ya existen. Cuando cambiamos contraseñas en el seeder, los usuarios en la base de datos seguían con la contraseña anterior. Tuvimos que correrlo manualmente desde tinker.

**Botón de favorito no estaba en ninguna vista** *(relacionado con Issue #17)*
Las rutas y el controlador de favoritos estaban implementados pero nunca se agregó el botón en las vistas del catálogo ni del detalle del libro. El feature existía en el backend pero era inaccesible desde la interfaz.

---

## Sugerencias de expansión

Estas son funcionalidades que identificamos como valiosas para una siguiente versión del sistema. Las marcamos como `TODO` para desarrollo futuro:

- **Sistema de préstamos y devoluciones** — Actualmente el sistema muestra disponibilidad pero no permite hacer un préstamo formal con fecha de devolución y recordatorios.

- **Notificaciones en tiempo real** — Avisar al usuario cuando un libro que marcó como favorito vuelve a estar disponible, usando Laravel Echo o Livewire events.

- **Calificaciones y reseñas** — Que los usuarios puedan dejar una puntuación y comentario sobre los libros que leyeron.

- **Exportar lista de favoritos** — Permitir descargar la lista en PDF o CSV.

- **Modo oscuro/claro configurable** — Actualmente el sitio solo tiene modo oscuro, sería bueno que el usuario pudiera cambiarlo desde su perfil.

- **Estadísticas para administradores** — Un dashboard con gráficas de libros más buscados, usuarios más activos y préstamos por categoría.

---

## Recomendaciones

Para equipos que quieran retomar o extender este proyecto:

1. Antes de tocar las migraciones, leer bien las relaciones existentes en los modelos. Hay dependencias que no son obvias a primera vista.

2. Correr `php artisan db:seed` después de `migrate:fresh` o los libros del catálogo no aparecerán.

3. El sistema de roles usa el campo `rol` en la tabla `users` con los valores `superadmin`, `admin` y `usuario`. No confundirlo con paquetes externos como Spatie.

4. Para trabajar con Livewire, siempre verificar que el componente tenga tanto la clase PHP en `app/Livewire` como la vista en `resources/views/livewire`.

5. Cualquier petición fetch desde JavaScript necesita el header `X-CSRF-TOKEN`. El meta tag ya está en el layout principal, solo hay que leerlo con `document.querySelector('meta[name=csrf-token]').content`.

6. Los tests de Selenium están en `docs/selenium/BibliotecaOnline.side` y se abren directamente desde Selenium IDE. Los tests de Vitest se corren con `npm test`.
