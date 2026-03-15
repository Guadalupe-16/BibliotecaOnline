<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite no soporta modificar ENUM directamente, se usa un workaround
        DB::statement("ALTER TABLE users ADD COLUMN rol_nuevo VARCHAR(20) NOT NULL DEFAULT 'usuario'");
        DB::statement("UPDATE users SET rol_nuevo = rol");
        DB::statement("ALTER TABLE users DROP COLUMN rol");
        DB::statement("ALTER TABLE users RENAME COLUMN rol_nuevo TO rol");
    }

    public function down(): void
    {
        // Revertir a enum con solo admin/usuario (los superadmin quedan como usuario)
        DB::statement("UPDATE users SET rol = 'usuario' WHERE rol = 'superadmin'");
        DB::statement("ALTER TABLE users ADD COLUMN rol_enum VARCHAR(20) NOT NULL DEFAULT 'usuario'");
        DB::statement("UPDATE users SET rol_enum = rol");
        DB::statement("ALTER TABLE users DROP COLUMN rol");
        DB::statement("ALTER TABLE users RENAME COLUMN rol_enum TO rol");
    }
};
