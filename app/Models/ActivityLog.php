<?php

namespace App\Models;

use App\Jobs\LogActivityJob;
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
        LogActivityJob::dispatch($accion, $descripcion, auth()->id(), request()->ip());
    }
}
