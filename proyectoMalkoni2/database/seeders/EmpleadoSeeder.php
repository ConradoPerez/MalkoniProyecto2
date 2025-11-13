<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Empleado;
use App\Models\Rol;

class EmpleadoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener IDs de roles
        $supervisorRol = Rol::where('nombre', 'supervisor')->first()->id_rol;
        $vendedorRol = Rol::where('nombre', 'vendedor')->first()->id_rol;
        $adminRol = Rol::where('nombre', 'admin')->first()->id_rol;

        $empleados = [
            // Supervisores
            [
                'nombre' => 'Carlos Alberto Malkoni',
                'email' => 'carlos.malkoni@malkonihnos.com',
                'telefono' => 1145678901,
                'dni' => 25789456,
                'id_rol' => $supervisorRol,
            ],
            [
                'nombre' => 'María Elena Rodriguez',
                'email' => 'maria.rodriguez@malkonihnos.com', 
                'telefono' => 1145678902,
                'dni' => 28456789,
                'id_rol' => $supervisorRol,
            ],

            // Vendedores
            [
                'nombre' => 'Juan Carlos Pérez',
                'email' => 'juan.perez@malkonihnos.com',
                'telefono' => 1145678903,
                'dni' => 32456789,
                'id_rol' => $vendedorRol,
            ],
            [
                'nombre' => 'Ana Sofía González',
                'email' => 'ana.gonzalez@malkonihnos.com',
                'telefono' => 1145678904,
                'dni' => 30789456,
                'id_rol' => $vendedorRol,
            ],
            [
                'nombre' => 'Roberto Daniel López',
                'email' => 'roberto.lopez@malkonihnos.com',
                'telefono' => 1145678905,
                'dni' => 29456789,
                'id_rol' => $vendedorRol,
            ],
            [
                'nombre' => 'Carmen Isabel Torres',
                'email' => 'carmen.torres@malkonihnos.com',
                'telefono' => 1145678906,
                'dni' => 31789456,
                'id_rol' => $vendedorRol,
            ],

            // Admin
            [
                'nombre' => 'Luis Eduardo Malkoni',
                'email' => 'admin@malkonihnos.com',
                'telefono' => 1145678900,
                'dni' => 24123456,
                'id_rol' => $adminRol,
            ]
        ];

        foreach ($empleados as $empleado) {
            Empleado::create($empleado);
        }
    }
}