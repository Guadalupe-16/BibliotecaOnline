# Quickstart: validar la spec 003

## 1. Job E2E en GitHub Actions (historia 1)

1. Abrir un PR desde `ci/138-e2e-devcontainer-spec-003` hacia `develop`.
2. En *Checks*, confirmar que aparecen **tres** checks: `ESLint + Prettier`, `PHPUnit Tests` y
   `E2E Playwright`.
3. **Esperado**: los tres en verde. En el resumen del run, el artefacto `playwright-report`
   descargable.
4. Registrar el enlace del run en `analysis.md` y en la descripción del PR (SC-001).

Prueba negativa (opcional, en una rama temporal): cambiar una aserción de `tests/e2e/login.spec.js`
para que falle. **Esperado**: solo `E2E Playwright` en rojo; el reporte muestra la prueba y el paso.

## 2. Reproducir el job en local

Requisitos: PHP 8.2+, Composer, Node 20+, puerto libre (se usa 8000 por defecto).

```bash
cp .env.example .env            # solo en una copia limpia; no sobrescribas tu .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed --force
npm ci
npm run build
php artisan serve --port=8000 &
curl -fsS http://127.0.0.1:8000/up
npx playwright install chromium
npx playwright test
```

Si el puerto 8000 está ocupado: levantar en otro puerto y ejecutar
`PLAYWRIGHT_BASE_URL=http://127.0.0.1:8100 npx playwright test`.

**Esperado**: 12 pruebas pasadas. Sin `npm run build` fallan 3 (research.md §1).

## 3. Devcontainer (historia 2)

1. En GitHub: *Code → Codespaces → Create codespace* sobre la rama (o en VS Code:
   *Dev Containers: Reopen in Container*).
2. Esperar a que termine `post-create.sh`.
3. `php artisan serve --host=0.0.0.0 --port=8000` y abrir el puerto 8000 reenviado.
4. `php artisan test`, `npm test` y `npx playwright test` (con el servidor levantado y
   `npm run build`).

**Esperado**: todo funciona sin instalar herramientas a mano. Si no se pudo probar, se deja
constancia en `analysis.md` (FR-015).

## 4. Propuesta de Terraform (historia 3)

```bash
git ls-files | grep -E '\.tf$|\.tfvars$|\.tfstate' || echo "sin archivos de Terraform"
```

**Esperado**: `sin archivos de Terraform`; `docs/planeacion/propuesta-terraform.md` existe y todo
está marcado como PLANEADO.
