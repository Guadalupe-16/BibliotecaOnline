<?php

namespace App\Http\Controllers;

use App\Services\Metricas;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MetricasController extends Controller
{
    public function __invoke(Request $request, Metricas $metricas): Response
    {
        $token = config('metricas.token');

        if ($token) {
            abort_unless(hash_equals($token, (string) $request->bearerToken()), 403);
        } else {
            // Sin token configurado, solo se expone en desarrollo y pruebas
            abort_unless(app()->environment('local', 'testing'), 403);
        }

        return response($metricas->renderizar(), 200)
            ->header('Content-Type', 'text/plain; version=0.0.4; charset=utf-8');
    }
}
