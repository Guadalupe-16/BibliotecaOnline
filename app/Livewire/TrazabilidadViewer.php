<?php

namespace App\Livewire;

use App\Models\RequestTrace;
use Livewire\Component;
use Livewire\WithPagination;

class TrazabilidadViewer extends Component
{
    use WithPagination;

    public string $filtroTraceId = '';
    public string $filtroMetodo = '';
    public string $filtroStatus = '';
    public string $filtroRuta = '';
    public string $filtroUsuario = '';
    public string $fechaDesde = '';
    public string $fechaHasta = '';

    public ?int $trazaSeleccionada = null;

    public function updatingFiltroTraceId(): void { $this->resetPage(); }
    public function updatingFiltroMetodo(): void { $this->resetPage(); }
    public function updatingFiltroStatus(): void { $this->resetPage(); }
    public function updatingFiltroRuta(): void { $this->resetPage(); }
    public function updatingFiltroUsuario(): void { $this->resetPage(); }
    public function updatingFechaDesde(): void { $this->resetPage(); }
    public function updatingFechaHasta(): void { $this->resetPage(); }

    public function limpiarFiltros(): void
    {
        $this->filtroTraceId = '';
        $this->filtroMetodo  = '';
        $this->filtroStatus  = '';
        $this->filtroRuta    = '';
        $this->filtroUsuario = '';
        $this->fechaDesde    = '';
        $this->fechaHasta    = '';
        $this->resetPage();
    }

    public function verDetalle(int $id): void
    {
        $this->trazaSeleccionada = $this->trazaSeleccionada === $id ? null : $id;
    }

    public function render()
    {
        $trazas = RequestTrace::with('user')
            ->when($this->filtroTraceId, fn ($q) => $q->where('trace_id', 'like', "%{$this->filtroTraceId}%"))
            ->when($this->filtroMetodo, fn ($q) => $q->where('metodo', $this->filtroMetodo))
            ->when($this->filtroStatus, function ($q) {
                if (str_ends_with($this->filtroStatus, 'xx')) {
                    $rango = (int) substr($this->filtroStatus, 0, 1) * 100;
                    $q->whereBetween('status_http', [$rango, $rango + 99]);
                } else {
                    $q->where('status_http', (int) $this->filtroStatus);
                }
            })
            ->when($this->filtroRuta, fn ($q) => $q->where('ruta', 'like', "%{$this->filtroRuta}%"))
            ->when($this->filtroUsuario, function ($q) {
                $termino = $this->filtroUsuario;
                $q->where(function ($sub) use ($termino) {
                    $sub->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$termino}%"))
                        ->orWhere(function ($orQ) use ($termino) {
                            if (str_contains(mb_strtolower('Anónimo'), mb_strtolower($termino))) {
                                $orQ->whereNull('user_id');
                            }
                        });
                });
            })
            ->when($this->fechaDesde, fn ($q) => $q->whereDate('created_at', '>=', $this->fechaDesde))
            ->when($this->fechaHasta, fn ($q) => $q->whereDate('created_at', '<=', $this->fechaHasta))
            ->latest('created_at')
            ->paginate(20);

        return view('livewire.trazabilidad-viewer', compact('trazas'));
    }
}
