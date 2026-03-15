<?php

namespace App\Livewire;

use App\Models\ActivityLog;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class ActivityLogger extends Component
{
    use WithPagination;

    public string $filtroUsuario = '';
    public string $filtroAccion = '';
    public string $fechaDesde = '';
    public string $fechaHasta = '';

    public function updatingFiltroUsuario(): void { $this->resetPage(); }
    public function updatingFiltroAccion(): void { $this->resetPage(); }
    public function updatingFechaDesde(): void { $this->resetPage(); }
    public function updatingFechaHasta(): void { $this->resetPage(); }

    public function limpiarFiltros(): void
    {
        $this->filtroUsuario = '';
        $this->filtroAccion  = '';
        $this->fechaDesde    = '';
        $this->fechaHasta    = '';
        $this->resetPage();
    }

    public function render()
    {
        $logs = ActivityLog::with('user')
            ->when($this->filtroUsuario, function ($q) {
                $q->whereHas('user', fn($u) => $u->where('name', 'like', "%{$this->filtroUsuario}%"));
            })
            ->when($this->filtroAccion, fn($q) => $q->where('accion', 'like', "%{$this->filtroAccion}%"))
            ->when($this->fechaDesde, fn($q) => $q->whereDate('created_at', '>=', $this->fechaDesde))
            ->when($this->fechaHasta, fn($q) => $q->whereDate('created_at', '<=', $this->fechaHasta))
            ->latest()
            ->paginate(20);

        $usuarios = User::orderBy('name')->pluck('name', 'id');

        return view('livewire.activity-logger', compact('logs', 'usuarios'));
    }
}
