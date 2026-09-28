# Resultados — Prueba de carga GET /catalogo (k6)

## Identificación

- **Responsable:** Guadalupe Amavizca Quinter
- **Issue:** #151
- **Endpoint:** `GET /catalogo`
- **Script:** `tests/load/gaqprueba.js`
- **Fecha de ejecución:** 2026-09-27
- **Resumen exportado:** `tests/load/resultados/gaq-resumen.json`

## Entorno

| Dato | Valor |
|---|---|
| Sistema operativo | Microsoft Windows 11 Pro for Workstations, 64 bits (build 26200) |
| CPU | AMD Ryzen 7 7730U with Radeon Graphics |
| RAM | 15.40 GB |
| PHP | 8.3.14 (cli) |
| Node.js | v22.13.1 |
| k6 | v2.2.0 (commit 00a9a1b7f5, windows/amd64) |
| Base URL | `http://127.0.0.1:8000` (pasada explícitamente con `-e BASE_URL`) |
| Servidor | `php artisan serve --host=127.0.0.1 --port=8000`, con assets compilados (`npm run build`) |
| Base de datos | MySQL local, con 5 libros existentes de una siembra previa (no se volvió a sembrar en esta ejecución; el seeder falló solo por un usuario de prueba duplicado, sin afectar el catálogo) |

Estos datos corresponden **únicamente a esta máquina**. Según el plan de pruebas
(`docs/cicd/plan-pruebas-carga-k6.md`, sección 4), no deben compararse de forma absoluta con la
corrida que haga Javier en su propio equipo.

## Perfil de carga

- 10 VUs máximo (alcanzados y confirmados: `vus_max = 10`)
- Ramp-up: 30 s
- Carga sostenida: 1 min con 10 VUs
- Ramp-down: 30 s
- Duración total real: 2m00.0s

## Nivel de servicio

Umbral definido: `p(95) < 5000 ms`

## Resultados

Extraídos directamente de `gaq-resumen.json` (ejecución real, sin editar):

| Métrica | Valor |
|---|---|
| `http_req_duration` avg | 593.49 ms |
| `http_req_duration` min | 59.64 ms |
| `http_req_duration` med | 699.36 ms |
| `http_req_duration` p(90) | 828.28 ms |
| `http_req_duration` p(95) | **854.85 ms** (≈ 0.85 s) |
| `http_req_duration` max | 953.09 ms |
| `http_req_failed` | 0.00 % (0 de 1543) |
| `http_reqs` | 1543 peticiones |
| Requests por segundo | 12.85 req/s |
| `iterations` | 1543 |
| `vus` | mín. 1, máx. 10 |
| `vus_max` | 10 |
| `http_req_waiting` avg / p(95) | 591.50 ms / 852.84 ms |
| `http_req_connecting` avg / p(95) | 0.58 ms / 1.33 ms |
| `data_received` | 59,558,257 bytes (≈ 59.56 MB), ≈ 496.07 kB/s |
| `data_sent` | 120,354 bytes (≈ 120.35 kB), ≈ 1002.45 B/s |
| `checks` | 3086 de 3086 (100 % aprobados; 0 fallidos) |
| Duración total de la corrida | 2m00.0s |

**p95 obtenido: 854.85 ms — vs umbral: 5000 ms**

## Evaluación

**CUMPLE.**

El p95 real (854.85 ms) está muy por debajo del umbral de 5000 ms — a menos de una sexta parte del
límite definido. El threshold `http_req_failed: ['rate<0.01']` también se cumplió, con 0 % de
peticiones fallidas. k6 reportó ambos thresholds como superados en el resumen de la corrida.

## Análisis

- **Comportamiento observado:** el tiempo de respuesta subió de forma consistente conforme
  aumentaron los VUs durante el ramp-up (de ~140 ms con 1-2 VUs en la prueba de humo a un promedio de
  593.49 ms con hasta 10 VUs sostenidos), lo cual es el comportamiento esperado: más concurrencia,
  más tiempo de respuesta por petición, sin que eso implique una falla.
- **Tasa de errores:** 0.00 % en las 1543 peticiones; los 3086 checks (`status es 200` y
  `la respuesta no esta vacia`) pasaron en su totalidad.
- **Dispersión entre mediana, p95 y máximo:** la mediana (699.36 ms) y el p95 (854.85 ms) están
  relativamente cerca del máximo (953.09 ms), sin un salto extremo entre ellos. Esto sugiere una
  distribución de tiempos de respuesta razonablemente uniforme bajo esta carga, sin una cola larga de
  peticiones anormalmente lentas.
- **Solicitudes por segundo:** 12.85 req/s sostenidas durante la prueba, con 10 VUs concurrentes.
- **Indicios de degradación:** no se observaron. No hubo peticiones fallidas ni interrumpidas
  (`0 interrupted iterations`), y el tiempo de respuesta se mantuvo estable durante todo el tramo de
  carga sostenida (1 minuto con 10 VUs), sin una tendencia de crecimiento continuo.
- **Hallazgos:** ninguno que reportar. El endpoint `GET /catalogo` respondió de forma estable y
  consistente durante toda la prueba.

Esta es una prueba con 10 usuarios virtuales concurrentes, no una prueba de estrés extrema; los
resultados no deben extrapolarse a cargas significativamente mayores sin una nueva medición.

## Evidencia

La captura del bloque final de resultados de k6 (mostrando `http_req_duration`, `p(95)`, el
threshold `p(95)<5000`, `checks`, `http_req_failed` y los VUs alcanzados) se guardará como:

```text
tests/load/resultados/gaq-captura.png
```
