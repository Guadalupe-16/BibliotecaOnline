<?php

namespace App\Livewire;

use App\Models\Libro;
use Livewire\Component;
use Livewire\WithPagination;

class BuscadorLibros extends Component
{
    use WithPagination;

    public string $termino = '';

    public function updatingTermino(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $libros = Libro::with(['autor', 'categoria'])
            ->when($this->termino, fn($q) => $q->buscar($this->termino))
            ->orderBy('titulo')
            ->paginate(12);

        return view('livewire.buscador-libros', compact('libros'));
    }
}