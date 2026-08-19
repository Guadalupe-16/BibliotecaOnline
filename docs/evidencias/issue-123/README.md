# Evidencia — Issue #123

**chore: eliminar migración duplicada con sintaxis SQLite incompatible con MySQL**
Tipo: **Refactorización**

Evidencia de **comportamiento**: se ejecutan las mismas operaciones (`migrate:fresh`
y `migrate:rollback`) contra **MySQL 9.4**, antes y después de eliminar la migración
duplicada, y se compara el esquema que queda en la tabla `users`.

Base de datos usada para la prueba: `biblioteca_evid_123` (temporal, creada solo para
esto; nunca se tocó `biblioteca_online`).

---

## El problema

Existían dos migraciones para el mismo cambio (agregar el rol `superadmin`):

| Migración | Cómo lo hace |
|---|---|
| `2026_03_14_000001_agregar_superadmin_a_rol_users.php` | SQL crudo: `ADD COLUMN` + `UPDATE` + `DROP COLUMN` + `RENAME COLUMN` (workaround de SQLite) |
| `2026_03_14_161703_agregar_superadmin_a_rol_users.php` | `Schema::table()->change()` (portable) |

Se eliminó la primera y se conservó la segunda.

---

## Aclaración sobre la premisa del issue

El issue afirma que la migración con sintaxis SQLite *"fallará al correr contra
MySQL 8.x en producción"*. **Eso no se cumple**: `ALTER TABLE ... RENAME COLUMN`
existe en MySQL desde la versión **8.0**, así que la migración corre sin error.
Se comprobó contra MySQL 9.4 — las 18 migraciones aplican `DONE`.

El daño es real, pero es **otro**, y es silencioso: deja el esquema distinto al
declarado. Eso es lo que documenta esta evidencia.

*(La migración sí rompería en MySQL 5.7, donde `RENAME COLUMN` no existe.)*

---

## ANTES — con la migración duplicada

```
1) Base recreada desde cero:
   18 migraciones OK (no truena en MySQL 9.4)

2) Orden de columnas en users (la migracion base pedia rol DESPUES de email):
   3. email
   7. created_at
   9. rol                <-- rol termina al final, detras de los timestamps

3) Se revierten las migraciones hasta deshacer el cambio de superadmin (step=5):
   2026_08_18_133601_agregar_indices_busqueda_a_libros_y_autores  DONE
   2026_03_15_144009_agregar_foto_a_users                         DONE
   2026_03_14_162208_agregar_activo_a_users                       DONE
   2026_03_14_161703_agregar_superadmin_a_rol_users               DONE
   2026_03_14_000001_agregar_superadmin_a_rol_users               DONE

4) Tipo de 'rol' tras revertir  (lo correcto seria: enum('admin','usuario')):
    columna rol -> tipo: varchar(20)          <-- el rollback NO restaura el tipo
```

Dos defectos concretos:

1. **La columna cambia de lugar.** `2026_03_13_061257` la declara `->after('email')`,
   pero el `DROP COLUMN` + `RENAME COLUMN` la reconstruye al final de la tabla.
2. **El rollback corrompe el esquema.** Revertir deja `rol` como `varchar(20)` en vez
   del `enum('admin','usuario')` original. El `down()` de la migración duplicada crea
   la columna de respaldo como `VARCHAR(20)` y nunca la devuelve a `enum`.

---

## DESPUÉS — migración duplicada eliminada

```
1) Base recreada desde cero:
   17 migraciones OK

2) Orden de columnas en users:
   3. email
   4. rol                <-- en su lugar, justo despues de email
   10. created_at

3) Se revierten las migraciones hasta deshacer el cambio de superadmin (step=4):
   2026_08_18_133601_agregar_indices_busqueda_a_libros_y_autores  DONE
   2026_03_15_144009_agregar_foto_a_users                         DONE
   2026_03_14_162208_agregar_activo_a_users                       DONE
   2026_03_14_161703_agregar_superadmin_a_rol_users               DONE

4) Tipo de 'rol' tras revertir:
    columna rol -> tipo: enum('admin','usuario')      <-- restaurado correctamente
```

Esquema final de `users`:

```
   1. id                   bigint unsigned
   2. name                 varchar(191)
   3. email                varchar(191)
   4. rol                  enum('admin','usuario','superadmin')
   5. activo               tinyint(1)
   6. foto                 varchar(191)
   7. email_verified_at    timestamp
   8. password             varchar(191)
   9. remember_token       varchar(100)
  10. created_at           timestamp
  11. updated_at           timestamp
```

---

## Comparación

| Operación (idéntica en ambos casos) | ANTES | DESPUÉS |
|---|---|---|
| `migrate:fresh` en MySQL 9.4 | 18 OK | 17 OK |
| Posición de `rol` en `users` | 9 (tras los timestamps) | **4 (tras `email`)** |
| Tipo de `rol` tras el rollback | **`varchar(20)`** | **`enum('admin','usuario')`** |
| Migraciones para el mismo cambio | 2 | 1 |
| Pruebas de `MigracionRolSuperadminTest` | 2 fallidas | 4 en verde |

---

## Prueba automatizada

`tests/Feature/MigracionRolSuperadminTest.php` blinda la regresión:

```
PASS  Tests\Feature\MigracionRolSuperadminTest
  ✓ existe una sola migracion para el rol superadmin
  ✓ ninguna migracion usa rename column de sqlite
  ✓ la columna rol acepta los tres roles
  ✓ el rol por defecto es usuario
```

Se comprobó que **detecta el problema**: devolviendo la migración duplicada al
proyecto, las dos primeras pruebas fallan.

Suite completa: **101 pasan, 2 fallan** (`RecuperacionContrasenaTest > resetea
contrasena correctamente` y `VerificacionEmailPinTest > pin correcto verifica la
cuenta`). Se verificó que fallan **igual en `develop` sin este cambio**: son
preexistentes y ajenas a este issue.

---

## Impacto en bases de datos ya existentes

En una base que ya aplicó la migración duplicada, el registro de
`2026_03_14_000001` queda en la tabla `migrations` apuntando a un archivo que ya no
existe. Es inofensivo para `php artisan migrate`, pero `migrate:rollback` avisará que
no encuentra esa migración.

Para dejar una base local al día basta con:

```bash
php artisan migrate          # aplica 2026_03_14_161703 y corrige el tipo de 'rol'
```

O, si no hay datos que conservar:

```bash
php artisan migrate:fresh --seed
```

---

## Cómo reproducirlo

```bash
# Base temporal, para no tocar la de desarrollo
php artisan tinker --execute="DB::statement('CREATE DATABASE biblioteca_evid_123');"

DB_DATABASE=biblioteca_evid_123 php artisan migrate:fresh --force
DB_DATABASE=biblioteca_evid_123 php artisan migrate:rollback --step=4 --force
DB_DATABASE=biblioteca_evid_123 php artisan tinker --execute="print_r(DB::select('DESCRIBE users'));"

php artisan test --filter=MigracionRolSuperadminTest
```
