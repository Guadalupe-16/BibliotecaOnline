<?php
namespace App\Http\Controllers;

use App\Http\Requests\BuscarLibroRequest;
use App\Http\Requests\ImportarLibroRequest;
use App\Jobs\ImportarLibroJob;
use App\Models\Categoria;
use App\Services\OpenLibraryService;

class OpenLibraryController extends Controller
{
    public function __construct(private OpenLibraryService $servicio)
    {
    }

    public function buscar(BuscarLibroRequest $request)
    {
        $resultados = $this->servicio->buscarLibros($request->validated('termino'));

        return view('libros.buscar', [
            'resultados' => $resultados,
            'termino' => $request->validated('termino'),
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