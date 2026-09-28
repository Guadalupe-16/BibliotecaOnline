<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Retención de trazas técnicas
    |--------------------------------------------------------------------------
    |
    | Días que se conserva un registro en `request_traces` antes de que el
    | comando `trazas:podar` lo elimine (specs/005-trazabilidad/research.md §5).
    |
    */

    'retencion_dias' => (int) env('TRAZABILIDAD_RETENCION_DIAS', 30),

];
