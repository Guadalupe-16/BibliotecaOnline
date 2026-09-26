# Quickstart: validar la spec 001

## Preparar

Mismos pasos que `specs/003-infraestructura-cicd/quickstart.md` §2 (base sembrada, `npm run build`,
servidor arriba, Chromium instalado).

## Ejecutar por historia

```bash
npx playwright test login.spec.js acceso.spec.js    # US1 (+ US4)
npx playwright test catalogo.spec.js busqueda.spec.js  # US2
npx playwright test favoritos.spec.js               # US3
npx playwright test --project=chromium              # todo (el setup corre antes automáticamente)
```

## Resultados esperados

| Validación | Esperado |
|---|---|
| Suite completa | Todas pasan; ~30 pruebas + 3 del setup |
| Estabilidad (SC-004) | `npx playwright test --repeat-each=3` sin fallos ni respuestas 429 |
| Sin pruebas vacías (SC-002) | Revisar que ningún `test()` tenga `if` alrededor de sus `expect` |
| Sin esperas fijas | `grep -rn waitForTimeout tests/e2e` → sin resultados |
| CI (SC-003) | Job `E2E Playwright` en verde y < 10 min |

## Validación de la línea base (ya realizada)

12 pruebas pasadas en local y en CI el 2026-09-25 (research.md §1).
