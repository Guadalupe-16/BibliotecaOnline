<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Issue #123: existian dos migraciones para el mismo cambio (agregar el rol
 * superadmin). La duplicada usaba SQL crudo con el workaround de SQLite
 * (ADD COLUMN + DROP COLUMN + RENAME COLUMN), lo que dejaba la columna 'rol'
 * al final de la tabla y con un tipo distinto al declarado tras un rollback.
 * Se conservo unicamente la que usa Schema::table()->change().
 */
class MigracionRolSuperadminTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<int, string>
     */
    private function archivosDeMigracion(): array
    {
        return glob(database_path('migrations/*.php')) ?: [];
    }

    public function test_existe_una_sola_migracion_para_el_rol_superadmin(): void
    {
        $migraciones = array_filter(
            $this->archivosDeMigracion(),
            fn ($ruta) => str_contains(basename($ruta), 'superadmin_a_rol_users')
        );

        $this->assertCount(
            1,
            $migraciones,
            'Debe existir una sola migracion que agregue el rol superadmin. Encontradas: '
                . implode(', ', array_map('basename', $migraciones))
        );
    }

    public function test_ninguna_migracion_usa_rename_column_de_sqlite(): void
    {
        $culpables = [];

        foreach ($this->archivosDeMigracion() as $ruta) {
            $contenido = (string) file_get_contents($ruta);

            if (preg_match('/ALTER\s+TABLE.+RENAME\s+COLUMN/is', $contenido)) {
                $culpables[] = basename($ruta);
            }
        }

        $this->assertSame(
            [],
            $culpables,
            'Las migraciones no deben usar el workaround de SQLite "ALTER TABLE ... RENAME COLUMN". '
                . 'Usa Schema::table()->change(). Archivos: ' . implode(', ', $culpables)
        );
    }

    public function test_la_columna_rol_acepta_los_tres_roles(): void
    {
        foreach (['admin', 'usuario', 'superadmin'] as $rol) {
            $usuario = User::factory()->create(['rol' => $rol]);

            $this->assertSame($rol, $usuario->fresh()->rol);
        }
    }

    public function test_el_rol_por_defecto_es_usuario(): void
    {
        // La factory no define 'rol', asi que aplica el default de la columna.
        $usuario = User::factory()->create();

        $this->assertSame('usuario', $usuario->fresh()->rol);
    }
}
