<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestTrace extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'trace_id',
        'metodo',
        'ruta',
        'user_id',
        'ip',
        'status_http',
        'duracion_ms',
        'resultado',
        'error_referencia',
        'user_agent',
        // `created_at` es asignable en masa a propósito: solo lo escribe
        // LogRequestTraceJob, con el instante real de la petición (no el de
        // procesamiento de la cola), nunca datos de un request HTTP.
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'status_http' => 'integer',
            'duracion_ms' => 'integer',
            'created_at'  => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Categoría derivada de status_http para agrupar en el visor
     * (exitosa / error_cliente / error_servidor), sin duplicar el
     * campo `resultado` (ok/error) definido en el spec.
     */
    public function getCategoriaAttribute(): string
    {
        return match (true) {
            $this->status_http >= 500 => 'error_servidor',
            $this->status_http >= 400 => 'error_cliente',
            default => 'exitosa',
        };
    }
}
