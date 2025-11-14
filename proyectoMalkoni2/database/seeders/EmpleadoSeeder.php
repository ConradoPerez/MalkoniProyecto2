<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Empleado;
use App\Models\Rol;
use App\Models\Persona;

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

        // Obtener personas disponibles (las primeras 7 para los empleados)
        $personas = Persona::limit(7)->get();

        $empleados = [
            // Supervisores
            [
                'nombre' => 'Carlos Alberto Malkoni',
                'email' => 'carlos.malkoni@malkonihnos.com',
                'telefono' => 1145678901,
                'dni' => 25789456,
                'id_rol' => $supervisorRol,
                'id_personas' => $personas[0]->id_persona ?? null,
            ],
            [
                'nombre' => 'María Elena Rodriguez',
                'email' => 'maria.rodriguez@malkonihnos.com', 
                'telefono' => 1145678902,
                'dni' => 28456789,
                'id_rol' => $supervisorRol,
                'id_personas' => $personas[1]->id_persona ?? null,
            ],

            // Vendedores
            [
                'nombre' => 'Juan Carlos Pérez',
                'email' => 'juan.perez@malkonihnos.com',
                'telefono' => 1145678903,
                'dni' => 32456789,
                'id_rol' => $vendedorRol,
                'id_personas' => $personas[2]->id_persona ?? null,
            ],
            [
                'nombre' => 'Ana Sofía González',
                'email' => 'ana.gonzalez@malkonihnos.com',
                'telefono' => 1145678904,
                'dni' => 30789456,
                'id_rol' => $vendedorRol,
                'id_personas' => $personas[3]->id_persona ?? null,
            ],
            [
                'nombre' => 'Roberto Daniel López',
                'email' => 'roberto.lopez@malkonihnos.com',
                'telefono' => 1145678905,
                'dni' => 29456789,
                'id_rol' => $vendedorRol,
                'id_personas' => $personas[4]->id_persona ?? null,
            ],
            [
                'nombre' => 'Carmen Isabel Torres',
                'email' => 'carmen.torres@malkonihnos.com',
                'telefono' => 1145678906,
                'dni' => 31789456,
                'id_rol' => $vendedorRol,
                'id_personas' => $personas[5]->id_persona ?? null,
            ],

            // Admin
            [
                'nombre' => 'Luis Eduardo Malkoni',
                'email' => 'admin@malkonihnos.com',
                'telefono' => 1145678900,
                'dni' => 24123456,
                'id_rol' => $adminRol,
                'id_personas' => $personas[6]->id_persona ?? null,
            ]
        ];

        foreach ($empleados as $empleado) {
            Empleado::create($empleado);
        }
    }
}