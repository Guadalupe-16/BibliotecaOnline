# Plan de pruebas de carga con k6 — BibliotecaOnline

Issue #150 · Rama: `docs/150-plan-pruebas-carga-k6` · Fecha: 2026-09-27

**Estado de este documento: PLAN DE K6 DOCUMENTADO, AÚN NO EJECUTADO.**

Este documento es únicamente de planeación. **k6 no está instalado**, **no se ejecutó ninguna prueba
de carga** y **no existen scripts ni resultados**. Todo lo que sigue describe cómo se hará esa
ejecución en un Issue posterior.

## 0. Verificación de los endpoints antes de escribir

Se revisó el repositorio real en `develop` antes de definir este plan:

| Archivo revisado | Qué se confirmó |
|---|---|
| `routes/web.php` | `Route::get('/catalogo', [CatalogoController::class, 'index'])->name('catalogo');` (línea 68) y `Route::get('/libros/{libro}', [LibroController::class, 'show'])->name('libros.show');` (línea 101) |
| `php artisan route:list --except-vendor` | Confirma ambas rutas activas: `GET\|HEAD catalogo › CatalogoController@index` y `GET\|HEAD libros/{libro} › libros.show › LibroController@show` |
| `.github/workflows/ci.yml` | Actualmente existen tres jobs: `lint-format`, `php-tests` y `e2e-playwright`. **Todavía no existe un job de k6** ni de ninguna prueba de carga (no se modifica en este Issue) |
| `composer.json`, `package.json` | Sin dependencia de k6 ni de ninguna herramienta de carga |
| `README.md` | Sin sección de pruebas de carga |
| `tests/`, `docs/` | Sin carpeta `tests/load/` ni `docs/cicd/` previas a este Issue |

**Ambos endpoints existen y son públicos** (ningún middleware `auth` ni `role:` los protege), lo que
los hace aptos para probarse con concurrencia sin necesidad de manejar sesiones o tokens.
**No se encontró ningún impedimento técnico** para usar los dos endpoints asignados; no fue
necesario cambiarlos.

## 1. Objetivo

Medir el comportamiento de BibliotecaOnline bajo concurrencia (múltiples usuarios virtuales
accediendo al mismo tiempo) antes de integrar pruebas de carga al proceso de CI/CD. El objetivo no
es certificar un ambiente de producción — que hoy no existe (ver
[`estrategia-despliegue.md`](../planeacion/estrategia-despliegue.md)) — sino establecer una línea
base de rendimiento local y un proceso repetible para medirlo más adelante.

## 2. Elección de herramienta

| Herramienta | Scripts versionables | Métricas integradas | Thresholds | Integración con CI | Usuarios virtuales (VUs) |
|---|---|---|---|---|---|
| **k6** | Sí, JavaScript (`.js`), se versiona como cualquier archivo del repositorio | Sí, built-in (`http_req_duration`, `http_reqs`, etc.) | Sí, declarativos en `options.thresholds` | Sí, binario único, sin dependencias de runtime pesadas | Sí, con `vus` y `stages` |
| JMeter | Planes `.jmx` en XML, difíciles de revisar en un PR | Sí, pero requiere plugins o reportes aparte para exportar bien | Sí, pero configurados en la UI o en XML | Requiere Java y una instalación más pesada en el runner | Sí |
| Apache Benchmark (`ab`) | No hay script versionable: solo línea de comandos | Muy básicas (tiempo promedio, por segundo) | No tiene mecanismo propio de thresholds | Trivial de instalar, pero sin salida estructurada ni percentiles configurables | Solo concurrencia fija (`-c`), sin rampas |

**Decisión: k6.**

Razones:
- **Scripts en JavaScript, versionables**: se escriben como `.js` y se revisan en PR igual que
  cualquier otro archivo del proyecto, a diferencia de los planes XML de JMeter.
- **Métricas integradas**: k6 reporta automáticamente, para `http_req_duration` (una métrica de tipo
  *Trend*), las sub-estadísticas `avg`, `min`, `med`, `p(90)`, `p(95)` y `max` en el resumen final,
  sin configuración adicional.
- **Thresholds**: se declaran en el propio script (`options.thresholds`), por ejemplo
  `http_req_duration: ['p(95)<5000']`, y k6 termina con código de salida distinto de 0 si no se
  cumplen — útil para integrarlo a un pipeline en el futuro.
- **Fácil integración con CI**: es un binario único sin dependencia de una JVM (a diferencia de
  JMeter), lo que simplifica agregarlo después a `.github/workflows/ci.yml` (no se hace en este Issue).
- **Soporte de escenarios con usuarios virtuales**: la opción `stages` permite definir rampas de
  subida, sostenimiento y bajada de VUs, que es justo lo que pide el perfil de carga (sección 6).

## 3. Instalación (documentada, NO ejecutada)

Comandos para Windows, según la documentación oficial de Grafana k6
(https://grafana.com/docs/k6/latest/set-up/install-k6/):

```powershell
# Opción A: winget (paquetes oficiales de los manifiestos de k6)
winget install k6 --source winget

# Opción B: Chocolatey (paquete no oficial de la comunidad)
choco install k6

# Opción C: instalador MSI
# Descargar y ejecutar: https://dl.k6.io/msi/k6-latest-amd64.msi
```

**Verificación de la instalación** (a ejecutar en el Issue de implementación, no ahora):

```powershell
k6 version
```

Ninguno de estos comandos se ejecutó en esta revisión: se comprobó que el binario `k6` **no está
instalado** en esta máquina (`k6 version` y `which k6` no encuentran el comando).

## 4. Entorno de ejecución

La prueba se ejecutará contra BibliotecaOnline **levantado localmente**, con base:

```text
http://127.0.0.1:8000
```

**Por qué en local y no contra producción**: BibliotecaOnline no tiene hoy un ambiente de producción
ni de staging desplegado (`estrategia-despliegue.md`, sección 1: "no existe hosting"). Medir contra
un servidor que no existe no es posible. Además, antes de exponer cualquier servicio a una prueba de
carga real conviene:

1. Conocer el comportamiento base en un entorno controlado, sin el ruido de una red compartida ni de
   otros procesos ajenos al proyecto.
2. No arriesgar un ambiente compartido (ni de producción ni de staging) con tráfico artificial antes
   de tener gates y aprobaciones definidos para ese tipo de prueba.
3. Poder repetir la medición de forma idéntica en la máquina de cada integrante, documentando sus
   características (criterio de aceptación, sección 11).

Requisitos para levantar el entorno (ya documentados en `plan-de-pruebas.md` y
`parametros-configuracion.md`, no exclusivos de esta prueba): `.env` con `APP_KEY`,
`php artisan migrate --seed`, `npm run build` y `php artisan serve --host=127.0.0.1 --port=8000`.

**Cada integrante ejecutará su script en su propio equipo**, no en una máquina compartida. Cada
quien registrará el sistema operativo, el procesador (CPU) y la memoria RAM de su equipo junto con
su resultado (`gaq-resultados.md` y `jarb-resultados.md`, sección 9). **Los resultados obtenidos en
máquinas distintas no deben tratarse como una comparación absoluta entre sí**: una diferencia en los
tiempos entre la corrida de Guadalupe y la de Javier puede deberse al hardware de cada equipo y no
necesariamente a una diferencia real entre `/catalogo` y `/libros/{libro}`. Cada resultado se evalúa
contra el umbral de la sección 7, no uno contra el otro.

## 5. Endpoints seleccionados

| Integrante | Endpoint | Método | Justificación |
|---|---|---|---|
| Guadalupe Amavizca Quinter | `/catalogo` | `GET` | Ruta pública, de lectura frecuente; consulta el listado de libros con sus relaciones de autor y categoría (`CatalogoController@index`) |
| Javier Antonio Romo Bernal | `/libros/{libro}` | `GET` | Ruta pública, consulta individual de un libro y sus relaciones asociadas (`LibroController@show`) |

Ambos endpoints se confirmaron existentes en la sección 0 y no requieren autenticación, lo que
simplifica el script de carga (sin manejo de cookies de sesión ni tokens CSRF).

**Sobre `/libros/{libro}`**: la ruta recibe un `{libro}` variable, así que **antes de ejecutar el
script**, Javier deberá seleccionar un ID de libro que exista realmente en su base de datos local
(sembrada con `php artisan migrate --seed`) y **documentar en `jarb-resultados.md` cuál ID utilizó**,
para que el resultado sea reproducible por cualquier otra persona que consulte ese mismo ID. Ese ID
no se define en este documento de planeación porque depende de los datos que existan en la base de
datos de cada máquina en el momento de la ejecución.

## 6. Perfil de carga

Igual para ambos endpoints, expresado como `stages` de k6:

```javascript
export const options = {
  stages: [
    { duration: '30s', target: 10 }, // ramp-up: sube gradualmente hasta 10 VUs
    { duration: '1m', target: 10 },  // carga sostenida: se mantiene en 10 VUs
    { duration: '30s', target: 0 },  // ramp-down: baja gradualmente a 0 VUs
  ],
};
```

| Etapa | Duración | VUs objetivo |
|---|---|---|
| Ramp-up | 30 s | 0 → 10 |
| Carga sostenida | 1 min | 10 |
| Ramp-down | 30 s | 10 → 0 |
| **Total** | **≈ 2 min** | — |

La actividad exige más de 5 usuarios virtuales; se usan **10** para cumplir ese mínimo con margen.

## 7. Nivel de servicio

**Umbral definido**:

```text
p95 < 5000 ms
```

Es decir, el percentil 95 de `http_req_duration` debe ser menor a 5000 milisegundos.

**Qué es el percentil 95 y por qué se usa en vez del promedio**: p95 es el valor por debajo del cual
cae el 95 % de las peticiones — dicho de otro modo, solo el 5 % de las peticiones más lentas quedan
por encima de ese valor. El **promedio** puede ocultar problemas reales: si 95 peticiones responden
en 50 ms y 5 responden en 8 segundos, el promedio puede parecer aceptable mientras 1 de cada 20
personas usuarias sufre una espera muy larga. El percentil 95 expone justamente ese comportamiento de
cola (las peticiones más lentas), que es lo que una persona real percibe como "la aplicación a veces
se congela", y por eso es el criterio de nivel de servicio más común en pruebas de carga, en vez de
depender solo del promedio.

En el script de k6 esto se declara como un threshold:

```javascript
export const options = {
  thresholds: {
    http_req_duration: ['p(95)<5000'],
  },
};
```

## 8. Métricas

Se documentan como mínimo, con su descripción según la documentación oficial de k6
(https://grafana.com/docs/k6/latest/using-k6/metrics/reference/):

| Métrica | Tipo | Descripción |
|---|---|---|
| `http_req_duration` | Trend | Tiempo total de la petición (envío + espera + recepción de la respuesta) |
| `http_req_failed` | Rate | Proporción de peticiones fallidas |
| `http_reqs` | Counter | Total de peticiones HTTP generadas por k6 |
| `iterations` | Counter | Número de veces que los VUs ejecutaron la función principal del script |
| `vus` | Gauge | Número de usuarios virtuales activos en un momento dado |
| `vus_max` | Gauge | Máximo de usuarios virtuales reservados durante la prueba |
| `http_req_waiting` | Trend | Tiempo esperando la respuesta del servidor ("time to first byte") |
| `http_req_connecting` | Trend | Tiempo estableciendo la conexión TCP |
| `data_received` | Counter | Cantidad de datos recibidos |
| `data_sent` | Counter | Cantidad de datos enviados |

**Aclaración importante**: `avg`, `min`, `med`, `p(90)`, `p(95)` y `max` **no son métricas aparte**:
son las seis sub-estadísticas con las que k6 resume, en su reporte de fin de prueba, una métrica de
tipo *Trend* como `http_req_duration`. Aparecen todas en la misma línea del resumen, por ejemplo:

```text
http_req_duration..: avg=140.36ms min=119.08ms med=140.96ms max=154.63ms p(90)=146.88ms p(95)=148.21ms
```

## 9. Convención de archivos

Se documenta únicamente la estructura prevista. **Ninguno de estos archivos existe todavía**; no se
crean scripts en este Issue:

```text
tests/
  load/
    gaqprueba.js               # script de Guadalupe Amavizca Quinter (GAQ) — GET /catalogo
    jarbprueba.js               # script de Javier Antonio Romo Bernal (JARB) — GET /libros/{libro}
    resultados/
      gaq-resumen.json          # --summary-export de la corrida de GAQ
      gaq-resultados.md         # análisis en Markdown de la corrida de GAQ
      gaq-captura.png           # captura de terminal de la corrida de GAQ
      jarb-resumen.json         # --summary-export de la corrida de JARB
      jarb-resultados.md        # análisis en Markdown de la corrida de JARB
      jarb-captura.png          # captura de terminal de la corrida de JARB
```

`GAQ` = Guadalupe Amavizca Quinter · `JARB` = Javier Antonio Romo Bernal. El prefijo permite
identificar, en la misma carpeta, qué script y qué resultado corresponden a cada integrante sin
necesidad de subcarpetas por persona.

## 10. Comandos futuros de ejecución (documentados, NO ejecutados)

```bash
# Guadalupe — GET /catalogo
k6 run tests/load/gaqprueba.js
k6 run --summary-export=tests/load/resultados/gaq-resumen.json tests/load/gaqprueba.js

# Javier — GET /libros/{libro}
k6 run tests/load/jarbprueba.js
k6 run --summary-export=tests/load/resultados/jarb-resumen.json tests/load/jarbprueba.js
```

Ninguno de estos comandos se ejecutó como parte de este Issue.

## 11. Criterios de aceptación

Para el Issue de implementación (no para este de planeación):

- [ ] La prueba usa al menos 10 VUs.
- [ ] Las tres etapas (ramp-up, sostenida, ramp-down) se ejecutan completas, sin interrupciones.
- [ ] El umbral `p(95) < 5000 ms` se evalúa; si no se cumple, **se documenta como hallazgo, nunca se
      oculta ni se falsifica**.
- [ ] Las respuestas HTTP de `/catalogo` y `/libros/{libro}` son las esperadas (código 200) durante la
      prueba.
- [ ] Los resultados se exportan a JSON con `--summary-export` (uno por integrante).
- [ ] Se guarda una captura de la terminal de cada corrida.
- [ ] Se escribe un análisis en Markdown de cada corrida (`gaq-resultados.md`, `jarb-resultados.md`).
- [ ] Se registran las características de la máquina donde se ejecutó (SO, CPU, RAM), porque el
      resultado de una prueba de carga depende del hardware donde corre y no es comparable sin ese
      dato.

## 12. Relación con la actividad

Este documento cubre la parte de la actividad que pide un "PR de implementación / plan markdown con
comandos de instalación de k6 y ejecución de endpoints frecuentes":

- **Plan markdown**: este mismo archivo, `docs/cicd/plan-pruebas-carga-k6.md`.
- **Comandos de instalación de k6**: sección 3 (documentados, no ejecutados).
- **Endpoints frecuentes**: sección 5, dos rutas públicas y de lectura (`GET /catalogo` y
  `GET /libros/{libro}`), justificadas por ser consultas habituales del catálogo.
- El PR de este Issue entrega el plan; la ejecución real, los scripts, los resultados y el análisis
  quedan para un Issue de implementación posterior, una vez aprobado este plan.

## Reglas seguidas en este documento

No se instaló k6, no se ejecutó ninguna prueba de carga, no se crearon scripts ni resultados, no se
modificó código de Laravel ni el CI/CD, y ninguna métrica o resultado de esta sección fue inventado:
las cifras de ejemplo de la sección 8 (`avg=140.36ms...`) son el formato ilustrativo tal como lo
muestra la documentación oficial de k6, no una ejecución real de BibliotecaOnline.
