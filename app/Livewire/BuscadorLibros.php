<?php

namespace App\Livewire;

use App\Models\Autor;
use App\Models\Categoria;
use App\Models\Libro;
use Livewire\Component;
use Livewire\WithPagination;

class BuscadorLibros extends Component
{
    use WithPagination;

    public string $termino = '';
    public string $categoriaId = '';
    public string $autorId = '';

    public function updatingTermino(): void
    {
        $this->resetPage();
    }

    public function updatingCategoriaId(): void
    {
        $this->autorId = '';
        $this->resetPage();
    }

    public function updatingAutorId(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $libros = Libro::with(['autor', 'categoria'])
            ->when($this->termino, fn($q) => $q->buscar($this->termino))
            ->when($this->categoriaId, fn($q) => $q->where('categoria_id', $this->categoriaId))
            ->when($this->autorId, fn($q) => $q->where('autor_id', $this->autorId))
            ->orderBy('titulo')
            ->paginate(12);

        $categorias = Categoria::orderBy('nombre')->get();

        $autores = Autor::when(
            $this->categoriaId,
            fn($q) => $q->whereHas('libros', fn($q) => $q->where('categoria_id', $this->categoriaId))
        )->orderBy('nombre')->get();

        return view('livewire.buscador-libros', compact('libros', 'categorias', 'autores'));
    }
}