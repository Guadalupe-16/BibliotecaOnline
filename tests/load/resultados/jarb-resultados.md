# Resultados k6 — JARB · `GET /libros/{libro}`

> Issue: #152 · Plan: [`docs/cicd/plan-pruebas-carga-k6.md`](../../../docs/cicd/plan-pruebas-carga-k6.md)
> Ejecutado por: Javier Antonio Romo Bernal (JARB) · Fecha: 2026-09-27, ~19:33 (hora local)

**Resultado: el nivel de servicio se cumple.** p(95) = **33.75 ms**, contra el umbral de 5000 ms,
con 0 % de peticiones fallidas.

---

## 1. Qué se probó

| Campo | Valor |
|---|---|
| Endpoint | `GET /libros/{libro}` → `LibroController@show` (carga el libro con `autor` y `categoria`) |
| ID de libro usado | **1** — "Cien Anos de Soledad" (Gabriel Garcia Marquez, Literatura) |
| Por qué ese ID | Es el primer libro que crea `BibliotecaSeeder`, así que existe en cualquier base sembrada y la prueba es reproducible |
| URL completa | `http://127.0.0.1:8000/libros/1` |
| Script | [`tests/load/jarbprueba.js`](../jarbprueba.js) |
| Comando | `k6 run --summary-export=tests/load/resultados/jarb-resumen.json tests/load/jarbprueba.js` |
| Evidencia | [`jarb-resumen.json`](jarb-resumen.json) · [`jarb-captura.png`](jarb-captura.png) |

### Perfil de carga (sección 6 del plan)

| Etapa | Duración | VUs |
|---|---|---|
| Ramp-up | 30 s | 0 → 10 |
| Carga sostenida | 1 min | 10 |
| Ramp-down | 30 s | 10 → 0 |

Cada usuario virtual hace una petición, valida la respuesta y espera 1 s (`sleep(1)`) antes de la
siguiente, simulando el tiempo de lectura de una persona.

### Validaciones del script

| Check | Qué verifica |
|---|---|
| `status es 200` | La respuesta HTTP es correcta |
| `muestra el titulo del libro` | El HTML contiene "Cien Anos de Soledad", es decir, se renderizó el detalle del libro pedido y no una página de error |

## 2. Entorno de ejecución

El resultado depende del hardware y de la configuración; no se debe comparar en términos absolutos
con la corrida de otra máquina (plan, sección 4).

| Componente | Valor |
|---|---|
| Sistema operativo | macOS 26.6.1 |
| CPU | Apple M4, 10 núcleos |
| RAM | 16 GB |
| k6 | v2.3.0 (darwin/arm64) |
| PHP | 8.3.31 (Homebrew) |
| Servidor web | `php artisan serve` (servidor integrado de PHP, **un solo proceso**: atiende una petición a la vez) |
| Base de datos | MySQL 9.4 local, sembrada con `DatabaseSeeder` (5 libros) |
| Configuración Laravel | `APP_ENV=local`, `APP_DEBUG=true`, `SESSION_DRIVER=database`, `CACHE_STORE=database` |
| Red | Loopback (`127.0.0.1`): k6 y la app en la misma máquina |

## 3. Resultados

### Thresholds

| Threshold | Resultado | ¿Cumple? |
|---|---|---|
| `http_req_duration: p(95)<5000` | p(95) = **33.75 ms** | ✅ Sí |
| `checks: rate>0.99` | rate = **100 %** | ✅ Sí |

> En `jarb-resumen.json` los thresholds aparecen como `"p(95)<5000": false`. En el formato de
> `--summary-export`, `false` significa que el umbral **no se cruzó**, es decir, que se cumplió.

### Tiempo de respuesta (`http_req_duration`)

| avg | min | med | p(90) | **p(95)** | max |
|---|---|---|---|---|---|
| 20.97 ms | 8.92 ms | 20.97 ms | 30.91 ms | **33.75 ms** | 50.08 ms |

Desglose (promedio / p95):

| Fase | avg | p(95) |
|---|---|---|
| `http_req_waiting` (tiempo del servidor, TTFB) | 20.63 ms | 33.38 ms |
| `http_req_connecting` (conexión TCP) | 0.31 ms | 0.45 ms |
| `http_req_blocked` (espera de conexión libre) | 0.41 ms | 0.58 ms |
| `http_req_receiving` (descarga de la respuesta) | 0.31 ms | 0.56 ms |

### Volumen y errores

| Métrica | Valor |
|---|---|
| `http_reqs` | 900 (7.45 req/s en promedio) |
| `http_req_failed` | **0.00 %** (0 de 900) |
| `iterations` | 900 |
| `iteration_duration` | avg 1.02 s (1 s de `sleep` + ~21 ms de petición) |
| `checks` | 1800 de 1800 correctos (900 × 2) |
| `vus` / `vus_max` | máx. 10 / 10 |
| `data_received` | 28 MB (≈ 31 KB por página) |
| `data_sent` | 70 kB |
| Duración total | 2 min 0.7 s, 0 iteraciones interrumpidas |

## 4. Análisis

1. **Holgura amplia frente al nivel de servicio.** El p95 (33.75 ms) está unas **148 veces por
   debajo** del umbral de 5000 ms. Incluso la petición más lenta (50 ms) queda lejos del límite.
2. **Sin errores.** Las 900 respuestas fueron 200 y todas mostraron el libro correcto: el endpoint
   no se degradó ni devolvió páginas de error bajo 10 usuarios concurrentes.
3. **Casi todo el tiempo es trabajo del servidor.** `http_req_waiting` explica ~98 % de la duración
   (20.6 de 21.0 ms). Conexión, espera y descarga suman menos de 1 ms porque todo ocurre en
   loopback. Esos ~21 ms son el costo de Laravel: arranque del framework, sesión en BD, 2–3
   consultas (libro + autor + categoría con `load`) y render de la vista Blade.
4. **El servidor de un solo proceso no fue cuello de botella.** Con 10 VUs y 1 s de espera, la carga
   real fue de ~10 req/s en la etapa sostenida. A ~21 ms por petición, el proceso de PHP estuvo
   ocupado ~21 % del tiempo, así que casi no hubo peticiones haciendo fila: la cola se ve en la
   diferencia entre la mediana (21 ms) y el máximo (50 ms), no en errores.
5. **La tasa de 7.45 req/s la limita el propio script**, no la aplicación: cada VU hace como máximo
   ~1 petición por segundo por el `sleep(1)`, y el promedio incluye las rampas de subida y bajada.

## 5. Limitaciones

- **Entorno local, no productivo.** Sin red real, con `APP_DEBUG=true` y con el servidor de
  desarrollo de PHP. Los tiempos en un hosting (p. ej. Hostinger, issue #156) serán distintos: más
  latencia de red, pero PHP-FPM con varios procesos.
- **Datos mínimos.** 5 libros; la consulta del detalle no crece con el catálogo, pero la sesión y
  la caché en BD sí compiten con otras tablas en un sistema real.
- **Mismo libro en todas las peticiones.** MySQL puede servir la fila desde su caché; con IDs
  variados el resultado podría ser un poco más lento.
- **Carga moderada.** 10 VUs con tiempo de lectura no busca el límite del sistema; es una prueba de
  carga, no de estrés.

## 6. Recomendaciones

| Prioridad | Acción |
|---|---|
| Media | Repetir la misma prueba contra el entorno de despliegue cuando exista (#156), con `BASE_URL=https://...`, sin cambiar el script |
| Media | Agregar una prueba de estrés (p. ej. subir a 50–100 VUs sin `sleep`) para encontrar el punto donde el p95 se acerca al umbral |
| Baja | Variar `LIBRO_ID` en cada iteración (1–5) para evitar que la caché de MySQL favorezca el resultado |
| Baja | Integrar k6 al CI/CD como verificación posterior al despliegue (opcional en #156) |

## 7. Conclusión

`GET /libros/{libro}` **cumple el nivel de servicio** definido en el plan con mucha holgura:
p95 = 33.75 ms (< 5000 ms), 0 % de errores y 100 % de respuestas correctas durante los 2 minutos
de carga con 10 usuarios virtuales. En este entorno local el endpoint no muestra degradación. El
siguiente paso es medir el mismo escenario en un entorno desplegado.
