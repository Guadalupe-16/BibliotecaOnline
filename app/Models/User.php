<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'rol',
        'activo',
        'foto',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
        ];
    }

    // Relación: un usuario tiene muchos favoritos
    public function favoritos()
    {
        return $this->hasMany(Favorito::class);
    }

    // Verifica si un libro es favorito del usuario
    public function esFavorito($libro_id)
    {
        return $this->favoritos()->where('libro_id', $libro_id)->exists();
    }

        public function esAdmin(): bool
    {
        return $this->rol === 'admin';
    }

    public function esUsuario(): bool
    {
        return $this->rol === 'usuario';
    }

    public function estaVerificado(): bool
    {
        return $this->email_verified_at !== null;
    }

    public function marcarComoVerificado(): void
    {
        $this->email_verified_at = now();
        $this->save();
    }

    public static function crear(array $datos): self
    {
        return self::create($datos);
    }

    public function esSuperadmin(): bool
    {
        return $this->rol === 'superadmin';
    }

    public function tieneRol(string ...$roles): bool
    {
        return in_array($this->rol, $roles, true);
    }

    public static function rolesDisponibles(): array
    {
        return ['usuario', 'admin', 'superadmin'];
    }

    /**
     * Roles que el usuario indicado puede asignar a otros.
     * Solo un superadmin puede otorgar el rol superadmin.
     */
    public static function rolesAsignablesPor(self $actor): array
    {
        return $actor->esSuperadmin()
            ? self::rolesDisponibles()
            : ['usuario', 'admin'];
    }

    /**
     * Indica si este usuario puede modificar el rol del usuario objetivo.
     * Nadie cambia su propio rol y un admin no puede tocar a un superadmin.
     */
    public function puedeGestionarRolDe(self $objetivo): bool
    {
        if ($this->id === $objetivo->id) {
            return false;
        }

        return $this->esSuperadmin() || ! $objetivo->esSuperadmin();
    }

    /**
     * Indica si este usuario puede asignar el rol indicado al usuario objetivo.
     */
    public function puedeAsignarRol(string $rol, self $objetivo): bool
    {
        return $this->puedeGestionarRolDe($objetivo)
            && in_array($rol, self::rolesAsignablesPor($this), true);
    }

    public function cambiarRol(string $rol): void
    {
        $this->rol = $rol;
        $this->save();
    }
}
