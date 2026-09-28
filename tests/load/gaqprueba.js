// Prueba de carga — GET /catalogo
// Issue #151 · Responsable: Guadalupe Amavizca Quinter (GAQ)
// Plan de referencia: docs/cicd/plan-pruebas-carga-k6.md

import http from 'k6/http';
import { check } from 'k6';

// BASE_URL configurable por variable de entorno, con el mismo patrón que playwright.config.js
const BASE_URL = __ENV.BASE_URL || 'http://127.0.0.1:8000';

export const options = {
  stages: [
    { duration: '30s', target: 10 }, // ramp-up: sube gradualmente hasta 10 VUs
    { duration: '1m', target: 10 }, // carga sostenida: se mantiene en 10 VUs
    { duration: '30s', target: 0 }, // ramp-down: baja gradualmente a 0 VUs
  ],
  thresholds: {
    http_req_duration: ['p(95)<5000'],
    http_req_failed: ['rate<0.01'],
  },
};

export default function () {
  const respuesta = http.get(`${BASE_URL}/catalogo`);

  check(respuesta, {
    'status es 200': (r) => r.status === 200,
    'la respuesta no esta vacia': (r) => r.body && r.body.length > 0,
  });
}
