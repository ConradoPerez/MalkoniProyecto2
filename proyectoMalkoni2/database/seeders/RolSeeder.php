<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rol;

class RolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'nombre' => 'supervisor',
                'descripcion' => 'Supervisor general de ventas'
            ],
            [
                'nombre' => 'vendedor', 
                'descripcion' => 'Vendedor de campo'
            ],
            [
                'nombre' => 'admin',
                'descripcion' => 'Administrador del sistema'
            ],
            [
                'nombre' => 'cliente',
                'descripcion' => 'Cliente de la empresa'
            ]
        ];

        foreach ($roles as $rol) {
            Rol::create($rol);
        }
    }
}