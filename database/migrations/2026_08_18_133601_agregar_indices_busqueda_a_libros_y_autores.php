<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('autores', function (Blueprint $table) {
            $table->unique('nombre');
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            Schema::table('libros', function (Blueprint $table) {
                $table->fullText('titulo');
            });
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            Schema::table('libros', function (Blueprint $table) {
                $table->dropFullText(['titulo']);
            });
        }

        Schema::table('autores', function (Blueprint $table) {
            $table->dropUnique(['nombre']);
        });
    }
};
