<?php

// Monitoreo con Prometheus (Issue #165, spec 004). Guia: docs/monitoreo/monitoreo.md

return [

    // Prefijo de todas las metricas expuestas en /metrics
    'namespace' => 'bibliotecaonline',

    // Version de la liberacion (la imagen Docker define APP_VERSION al construirse)
    'version' => env('APP_VERSION', 'dev'),

    // apcu: memoria compartida entre procesos de Apache (imagen de liberacion).
    // memory: solo dura la peticion; se usa en pruebas y cuando APCu no esta instalado.
    'storage' => env('METRICS_STORAGE', 'memory'),

    // Token que Prometheus envia como "Authorization: Bearer <token>".
    // Vacio: /metrics solo responde en entornos local y testing (403 en production).
    'token' => env('METRICS_TOKEN'),

    // Rutas que no se miden (el propio scraping y el health check)
    'excluir' => ['metrics', 'up'],

    // Cubetas del histograma de duracion, en segundos (el umbral NS-1 es 5 s)
    'buckets' => [0.01, 0.025, 0.05, 0.1, 0.25, 0.5, 1, 2.5, 5, 10],
];
