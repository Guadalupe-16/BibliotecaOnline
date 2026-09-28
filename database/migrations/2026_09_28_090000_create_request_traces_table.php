<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('request_traces', function (Blueprint $table) {
            $table->id();
            $table->uuid('trace_id')->unique();
            $table->string('metodo', 10);
            $table->string('ruta');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ip', 45)->nullable();
            $table->unsignedSmallInteger('status_http');
            $table->unsignedInteger('duracion_ms');
            $table->string('resultado', 10)->default('ok');
            $table->string('error_referencia', 500)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index('created_at');
            $table->index(['ruta', 'status_http']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_traces');
    }
};
