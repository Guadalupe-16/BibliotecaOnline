<?php

namespace App\Jobs;

use App\Models\ActivityLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class LogActivityJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $accion,
        public ?string $descripcion,
        public ?int $userId,
        public ?string $ip,
    ) {}

    public function handle(): void
    {
        ActivityLog::create([
            'accion'      => $this->accion,
            'descripcion' => $this->descripcion,
            'user_id'     => $this->userId,
            'ip'          => $this->ip,
        ]);
    }
}
