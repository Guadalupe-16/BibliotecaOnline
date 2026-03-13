<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = ['accion', 'descripcion', 'user_id', 'ip'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function registrar(string $accion, string $descripcion = null): void
    {
        static::create([
            'accion'      => $accion,
            'descripcion' => $descripcion,
            'user_id'     => auth()->id(),
            'ip'          => request()->ip(),
        ]);
    }
}
