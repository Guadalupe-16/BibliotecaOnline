<?php
namespace App\Http\Controllers;

use App\Http\Requests\BuscarLibroRequest;
use App\Http\Requests\ImportarLibroRequest;
use App\Jobs\ImportarLibroJob;
use App\Models\ActivityLog;
use App\Models\Categoria;
use App\Services\OpenLibraryService;

class OpenLibraryController extends Controller
{
    public function __construct(private OpenLibraryService $servicio)
    {
    }

    public function buscar(BuscarLibroRequest $request)
    {
        $termino = $request->validated('termino');
        $resultados = $this->servicio->buscarLibros($termino);

        ActivityLog::registrar('busqueda', "Búsqueda de libro: \"{$termino}\".");

        return view('libros.buscar', [
            'resultados' => $resultados,
            'termino' => $termino,
            'categorias' => Categoria::all(),
        ]);
    }

    public function importar(ImportarLibroRequest $request)
    {
        $datos = $request->validated();

        ImportarLibroJob::dispatch(
            $datos['olid'],
            $datos['categoria_id'],
            $datos['copias_disponibles'] ?? 1
        );

        return back()->with('exito', 'El libro se está importando en segundo plano.');
    }
}