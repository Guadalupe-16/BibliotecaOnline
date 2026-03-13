<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ActivityLog;

new class extends Component
{
    use WithPagination;

    public string $filtro = '';

    public function render()
    {
        $logs = ActivityLog::with('user')
            ->when($this->filtro, fn($q) => $q->where('accion', 'like', "%{$this->filtro}%")
                ->orWhere('descripcion', 'like', "%{$this->filtro}%"))
            ->latest()
            ->paginate(15);

        return view('livewire.activity-logger', compact('logs'));
    }
};
?>
