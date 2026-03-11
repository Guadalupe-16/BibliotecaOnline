<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Libro extends Model
{
    use HasFactory;

    protected $fillable = [
        'titulo',
        'isbn',
        'descripcion',
        'portada_url',
        'anio_publicacion',
        'copias_disponibles',
        'autor_id',
        'categoria_id',
    ];

    public function autor(): BelongsTo
    {
        return $this->belongsTo(Autor::class);
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function prestamos(): HasMany
    {
        return $this->hasMany(Prestamo::class);
    }

    public function estaDisponible(): bool
    {
        return $this->copias_disponibles > 0;
    }

    public function scopeBuscar($query, string $termino)
    {
        return $query->where('titulo', 'like', "%{$termino}%")
            ->orWhereHas('autor', fn($q) => $q->where('nombre', 'like', "%{$termino}%"));
    }

    public function scopePorCategoria($query, int $categoriaId)
    {
        return $query->where('categoria_id', $categoriaId);
    }
}
