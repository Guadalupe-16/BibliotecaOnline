# Contrato de interfaz: guía interactiva

Contrato entre las vistas Blade y el módulo JavaScript del recorrido. Estado: PLANEADO (ninguno de estos
atributos existe todavía en `components/navigation.blade.php`).

## Anclajes en el menú

Cada atributo se agrega a **ambas** copias del menú (barra lateral de escritorio y drawer móvil).

| Atributo | Elemento | Presente para |
|---|---|---|
| `data-tour="catalogo"` | Enlace "Catálogo" (`route('catalogo')`) | Todos |
| `data-tour="buscar"` | Enlace "Buscar" (`route('buscar')`) | Todos |
| `data-tour="favoritos"` | Enlace "Favoritos" (`route('favoritos')`) | Todos (el destino exige sesión) |
| `data-tour="perfil"` | Enlace "Mi perfil" (`route('perfil')`) | Solo con sesión (`@auth`) |
| `data-tour-relanzar` | Enlace o botón "Ver guía" | Todos |
| `data-tour-usuario="<id o guest>"` | Elemento raíz del `<nav>` | Todos: `auth()->id()` o `guest` |

Reglas:
- Un `data-tour` puede repetirse (escritorio y móvil); el módulo elige el primer elemento visible.
- Un ancla ausente o invisible provoca que el paso se omita, nunca un error.
- El valor de `data-tour-usuario` es solo el id de la persona que ya tiene sesión; no incluye correo,
  nombre ni rol.

## Evento para abrir el menú móvil

| Evento | Emisor | Receptor | Efecto |
|---|---|---|---|
| `tour:abrir-menu` | `tour.js` antes de iniciar en pantallas menores a `lg` | `<nav x-data>` con `x-on:tour:abrir-menu.window="menuAbierto = true"` | Abre el drawer |
| `tour:cerrar-menu` | `tour.js` al terminar el recorrido | `<nav x-data>` con `x-on:tour:cerrar-menu.window="menuAbierto = false"` | Cierra el drawer |

## API del módulo `tour.js` (planeada)

| Función | Efecto |
|---|---|
| `iniciarSiCorresponde()` | Se llama al cargar la página; inicia solo si no hay estado guardado y hay pasos disponibles |
| `iniciar({ forzar })` | Inicia el recorrido; con `forzar: true` ignora el estado guardado |
| `relanzar()` | Borra el estado y llama a `iniciar({ forzar: true })` |

Funciones puras de `tour-logic.js` (probables con Vitest sin DOM de navegador):

| Función | Entrada → salida |
|---|---|
| `claveEstado(idUsuario)` | `"guest"` o id → `bo_tour_visto:guest` / `bo_tour_visto:user:<id>` |
| `debeIniciar(almacen, clave)` | Almacén y clave → `true` si no hay estado válido; `false` si hay error de lectura tratado como ausente |
| `marcarVisto(almacen, clave, resultado)` | Guarda `completado` o `cerrado`; no lanza si el almacén falla |
| `limpiarEstado(almacen, clave)` | Elimina la clave; no lanza si falla |
| `construirPasos(pasos, resolverAncla)` | Devuelve los pasos con ancla resuelta y visible, más la bienvenida; vacío si ninguno anclado |

## Textos (español)

`nextBtnText`: "Siguiente" · `prevBtnText`: "Anterior" · `doneBtnText`: "Finalizar" ·
`progressText`: "{{current}} de {{total}}" · enlace del menú: "Ver guía".
