<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Favorito extends Model
{
    protected $table = 'favoritos';

    protected $fillable = [
        'user_id',
        'libro_id',
    ];

    // Relación: un favorito pertenece a un usuario
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relación: un favorito pertenece a un libro
    public function libro()
    {
        return $this->belongsTo(Libro::class, 'libro_id');
    }
}