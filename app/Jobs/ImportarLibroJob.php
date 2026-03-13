<?php

namespace App\Jobs;

use App\Models\Libro;
use App\Services\OpenLibraryService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ImportarLibroJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private string $olid,
        private int $categoriaId,
        private int $copiasDisponibles = 1
    ) {
    }

    public function handle(OpenLibraryService $servicio): void
    {
        $servicio->importarLibro($this->olid, $this->categoriaId, $this->copiasDisponibles);
    }
}