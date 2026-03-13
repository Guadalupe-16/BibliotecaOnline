<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('favoritos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('libro_id')->constrained('libros')->onDelete('cascade');
            $table->timestamps();

            // Un usuario no puede tener el mismo libro dos veces
            $table->unique(['user_id', 'libro_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('favoritos');
    }
};