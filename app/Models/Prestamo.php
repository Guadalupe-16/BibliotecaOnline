<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Prestamo extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'libro_id',
        'fecha_prestamo',
        'fecha_devolucion_esperada',
        'fecha_devolucion_real',
        'estado',
    ];

    protected $casts = [
        'fecha_prestamo'             => 'date',
        'fecha_devolucion_esperada'  => 'date',
        'fecha_devolucion_real'      => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function libro(): BelongsTo
    {
        return $this->belongsTo(Libro::class);
    }

    public function estaVencido(): bool
    {
        return $this->estado === 'activo'
            && Carbon::today()->isAfter($this->fecha_devolucion_esperada);
    }

    public function diasRestantes(): int
    {
        return Carbon::today()->diffInDays($this->fecha_devolucion_esperada, false);
    }

    public static function crearPrestamo(int $userId, int $libroId, int $dias = 14): self
    {
        return self::create([
            'user_id'                   => $userId,
            'libro_id'                  => $libroId,
            'fecha_prestamo'            => Carbon::today(),
            'fecha_devolucion_esperada' => Carbon::today()->addDays($dias),
            'estado'                    => 'activo',
        ]);
    }
}
