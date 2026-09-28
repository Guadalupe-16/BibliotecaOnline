// Prueba de carga de Javier Antonio Romo Bernal (JARB) — GET /libros/{libro}
// Issue #152 · Plan: docs/cicd/plan-pruebas-carga-k6.md
//
//   k6 run --summary-export=tests/load/resultados/jarb-resumen.json tests/load/jarbprueba.js
//
// Variables opcionales: BASE_URL (default http://127.0.0.1:8000), LIBRO_ID (default 1) y
// TITULO (default "Cien Anos de Soledad", el libro 1 que crea BibliotecaSeeder).
import http from 'k6/http'
import { check, sleep } from 'k6'

const BASE_URL = __ENV.BASE_URL || 'http://127.0.0.1:8000'
const LIBRO_ID = __ENV.LIBRO_ID || '1'
const TITULO = __ENV.TITULO || 'Cien Anos de Soledad'

export const options = {
  // Perfil de carga del plan (seccion 6)
  stages: [
    { duration: '30s', target: 10 }, // ramp-up: 0 -> 10 VUs
    { duration: '1m', target: 10 }, // carga sostenida: 10 VUs
    { duration: '30s', target: 0 }, // ramp-down: 10 -> 0 VUs
  ],
  thresholds: {
    http_req_duration: ['p(95)<5000'], // nivel de servicio del plan (seccion 7)
    checks: ['rate>0.99'], // practicamente todas las respuestas deben ser correctas
  },
}

export default function () {
  const res = http.get(`${BASE_URL}/libros/${LIBRO_ID}`, {
    tags: { name: 'GET /libros/{libro}' },
  })

  check(res, {
    'status es 200': (r) => r.status === 200,
    'muestra el titulo del libro': (r) => r.body.includes(TITULO),
  })

  // Tiempo de "lectura" entre peticiones de cada usuario virtual
  sleep(1)
}
