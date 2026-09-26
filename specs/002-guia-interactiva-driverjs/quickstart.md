# Quickstart de validación: guía interactiva

Guía para comprobar que la funcionalidad cumple la spec **cuando se implemente**. Hoy el estado es
PLANEADO: ningún comando de esta guía puede pasar todavía porque no hay código.

## Prerrequisitos

```bash
composer install
npm ci
cp .env.example .env && php artisan key:generate
npm run build            # los E2E requieren los assets de Vite compilados
php artisan migrate --seed
php artisan serve --host=127.0.0.1 --port=8000
```

Playwright usa `http://127.0.0.1:8000` (`playwright.config.js`).

## Escenarios manuales

1. **Primera visita (US1)**: abrir `http://127.0.0.1:8000/catalogo` en una ventana de incógnito. Debe
   aparecer el paso "Bienvenido a BibliotecaOnline" con progreso "1 de N".
2. **Avanzar y retroceder**: "Siguiente" resalta Catálogo, Buscador y Favoritos; "Anterior" regresa.
3. **Finalizar**: en el último paso el botón dice "Finalizar" y cierra el recorrido.
4. **No repetir (US2)**: recargar la página; el recorrido no aparece.
5. **Relanzar**: pulsar "Ver guía" en el menú; el recorrido empieza en el paso 1.
6. **Visitante (US3)**: sin sesión, el paso "Mi perfil" no aparece y el progreso cuenta solo los pasos
   mostrados. Con sesión aparecen los cinco.
7. **Estado por usuario**: completar el recorrido con la cuenta A, cerrar sesión, entrar con la cuenta B en
   el mismo navegador; el recorrido debe volver a aparecer.
8. **Teclado (US4)**: recorrer el tour con Tab y las flechas y cerrarlo con Escape; el foco vuelve a la página.
9. **Móvil**: ancho menor a 1024 px; el menú lateral se abre solo durante el recorrido y se cierra al terminar.
10. **Sin almacenamiento**: desactivar `localStorage` en las herramientas del navegador; el recorrido
    funciona sin errores en consola.

## Comandos automáticos

```bash
php artisan test --filter=TourAnclajesTest   # anclajes en el HTML
npm run test                                  # Vitest, incluye tour.test.js
npx playwright test tests/e2e/tour.spec.js    # comportamiento en navegador
npm run lint && npm run format:check
```

Resultado esperado: todas las pruebas pasan y las suites existentes (PHPUnit 118, Playwright 14, Vitest 18
al 2026-09-25) siguen pasando (SC-005).

## Trazabilidad requisito → prueba

| Comportamiento requerido | Requisito | Prueba planeada |
|---|---|---|
| Inicia cuando corresponde | FR-006 | Vitest `debeIniciar` + Playwright "inicia en la primera visita" |
| Avanza | FR-004 | Playwright "avanza y retrocede" |
| Puede cerrarse | FR-004, FR-005 | Playwright "cierra con botón y con Escape" |
| No se repite si se completó | FR-007 | Vitest `marcarVisto` + Playwright "no se repite" |
| Puede relanzarse | FR-008 | Playwright "Ver guía relanza" |
| No rompe si falta un selector | FR-009, FR-010 | Vitest `construirPasos` + Playwright "omite pasos sin ancla" |
| Anclajes presentes | FR-002 | PHPUnit `TourAnclajesTest` |
| Accesibilidad por teclado | FR-011, FR-012 | Playwright "operable con teclado" |
