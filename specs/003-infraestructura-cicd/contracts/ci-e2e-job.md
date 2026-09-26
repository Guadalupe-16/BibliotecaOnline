# Contrato: job `e2e-playwright`

## Entradas

| Entrada | Origen |
|---|---|
| Código del commit o PR | `actions/checkout@v4` |
| Dependencias PHP | `composer.lock` |
| Dependencias JS | `package-lock.json` (`@playwright/test` fijado por el lockfile) |
| Configuración | `.env.example` copiado a `.env`; `APP_KEY` generada en el run |
| Datos | Migraciones + `DatabaseSeeder` |
| Secretos | Ninguno |

## Pasos obligatorios, en orden

1. Checkout.
2. PHP 8.2 con extensiones `mbstring`, `sqlite3`, `pdo_sqlite` (sin cobertura).
3. Node 20 con caché de npm.
4. `composer install` y `npm ci`.
5. `.env` desde `.env.example` + `php artisan key:generate`.
6. Crear `database/database.sqlite` y ejecutar `php artisan migrate --seed --force`.
7. `npm run build`. **Obligatorio**: sin build fallan 3 pruebas (research.md §1).
8. `php artisan serve --host=127.0.0.1 --port=8000` en segundo plano, con salida a un log.
9. Esperar `GET /up` → 200, máximo 60 s. Si se agota: imprimir el log y fallar.
10. `npx playwright install --with-deps chromium`.
11. `npx playwright test`.
12. Publicar `playwright-report/` (siempre que no se cancele).
13. Publicar el log del servidor (solo si falló).

## Salidas

| Salida | Garantía |
|---|---|
| Check `E2E Playwright` en el PR | Verde si y solo si todas las pruebas de `tests/e2e/` pasan |
| Artefacto `playwright-report` | Disponible durante 14 días |
| Artefacto `laravel-server-log` | Solo en fallos |

## Invariantes

- No modifica los jobs `lint-format` ni `php-tests`.
- Tiempo máximo de 15 minutos (`timeout-minutes`).
- Una prueba marcada con `.only` hace fallar el job (`forbidOnly` con `CI=true`).
- La suite no accede a sitios externos (tras retirar `example.spec.js`).
