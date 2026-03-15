<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $usuarios = [
            [
                'name'              => 'Superadmin Sistema',
                'email'             => 'superadmin@biblioteca.com',
                'password'          => Hash::make('Superadmin123!'),
                'rol'               => 'superadmin',
                'email_verified_at' => now(),
            ],
            [
                'name'              => 'Admin Biblioteca',
                'email'             => 'admin@biblioteca.com',
                'password'          => Hash::make('Admin123!'),
                'rol'               => 'admin',
                'email_verified_at' => now(),
            ],
            [
                'name'              => 'Usuario Prueba',
                'email'             => 'usuario@biblioteca.com',
                'password'          => Hash::make('Usuario123!'),
                'rol'               => 'usuario',
                'email_verified_at' => now(),
            ],
        ];

        foreach ($usuarios as $datos) {
            User::firstOrCreate(
                ['email' => $datos['email']],
                $datos
            );
        }
    }
}
