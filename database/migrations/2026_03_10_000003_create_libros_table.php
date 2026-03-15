<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('libros', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->string('isbn')->unique()->nullable();
            $table->text('descripcion')->nullable();
            $table->string('portada_url')->nullable();
            $table->integer('anio_publicacion')->nullable();
            $table->integer('copias_disponibles')->default(1);
            $table->foreignId('autor_id')->nullable()->constrained('autores')->nullOnDelete();
            $table->foreignId('categoria_id')->constrained('categorias')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('libros');
    }
};
