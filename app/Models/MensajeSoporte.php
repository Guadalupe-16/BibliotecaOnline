<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MensajeSoporte extends Model
{
    protected $fillable = ['nombre', 'email', 'mensaje', 'estado'];
}
