<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cotizacion;
use App\Models\Empleado;
use App\Models\Empresa;
use App\Models\Persona;
use Carbon\Carbon;

class CotizacionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener IDs de vendedores
        $vendedores = Empleado::vendedores()->get();
        $empresas = Empresa::all();
        $personas = Persona::all();

        $cotizaciones = [
            // Cotizaciones del último mes
            [
                'titulo' => 'Reforma integral oficina comercial',
                'numero' => 10001,
                'fyh' => Carbon::now()->subDays(5),
                'precio_total' => 485000,
                'id_empleados' => $vendedores[0]->id_empleado,
                'id_empresas' => $empresas[0]->id_empresa,
                'id_personas' => $personas[0]->id_persona,
            ],
            [
                'titulo' => 'Aberturas para vivienda unifamiliar',
                'numero' => 10002,
                'fyh' => Carbon::now()->subDays(12),
                'precio_total' => 320000,
                'id_empleados' => $vendedores[1]->id_empleado,
                'id_empresas' => $empresas[1]->id_empresa,
                'id_personas' => $personas[1]->id_persona,
            ],
            [
                'titulo' => 'Muebles de cocina a medida',
                'numero' => 10003,
                'fyh' => Carbon::now()->subDays(8),
                'precio_total' => 156000,
                'id_empleados' => $vendedores[2]->id_empleado,
                'id_empresas' => $empresas[2]->id_empresa,
                'id_personas' => $personas[2]->id_persona,
            ],
            [
                'titulo' => 'Sistema corredizo para showroom',
                'numero' => 10004,
                'fyh' => Carbon::now()->subDays(3),
                'precio_total' => 98000,
                'id_empleados' => $vendedores[0]->id_empleado,
                'id_empresas' => $empresas[3]->id_empresa,
                'id_personas' => $personas[3]->id_persona,
            ],
            [
                'titulo' => 'Herrajes premium para portón',
                'numero' => 10005,
                'fyh' => Carbon::now()->subDays(15),
                'precio_total' => 75000,
                'id_empleados' => $vendedores[1]->id_empleado,
                'id_empresas' => $empresas[4]->id_empresa,
                'id_personas' => $personas[4]->id_persona,
            ],
            [
                'titulo' => 'Estructura de madera para pérgola',
                'numero' => 10006,
                'fyh' => Carbon::now()->subDays(7),
                'precio_total' => 145000,
                'id_empleados' => $vendedores[2]->id_empleado,
                'id_empresas' => $empresas[5]->id_empresa,
                'id_personas' => $personas[5]->id_persona,
            ],
            [
                'titulo' => 'Vidrios temperados para edificio',
                'numero' => 10007,
                'fyh' => Carbon::now()->subDays(20),
                'precio_total' => 890000,
                'id_empleados' => $vendedores[0]->id_empleado,
                'id_empresas' => $empresas[0]->id_empresa,
                'id_personas' => $personas[6]->id_persona,
            ],
            [
                'titulo' => 'Revestimiento completo en madera',
                'numero' => 10008,
                'fyh' => Carbon::now()->subDays(25),
                'precio_total' => 267000,
                'id_empleados' => $vendedores[1]->id_empleado,
                'id_empresas' => $empresas[6]->id_empresa,
                'id_personas' => $personas[7]->id_persona,
            ],

            // Cotizaciones de meses anteriores
            [
                'titulo' => 'Carpintería para local gastronómico',
                'numero' => 9998,
                'fyh' => Carbon::now()->subMonths(1)->subDays(10),
                'precio_total' => 345000,
                'id_empleados' => $vendedores[2]->id_empleado,
                'id_empresas' => $empresas[7]->id_empresa,
                'id_personas' => $personas[0]->id_persona,
            ],
            [
                'titulo' => 'Cerramientos para terraza',
                'numero' => 9999,
                'fyh' => Carbon::now()->subMonths(1)->subDays(5),
                'precio_total' => 198000,
                'id_empleados' => $vendedores[0]->id_empleado,
                'id_empresas' => $empresas[1]->id_empresa,
                'id_personas' => $personas[1]->id_persona,
            ],
            [
                'titulo' => 'Mobiliario corporativo completo',
                'numero' => 9997,
                'fyh' => Carbon::now()->subMonths(2)->subDays(8),
                'precio_total' => 567000,
                'id_empleados' => $vendedores[1]->id_empleado,
                'id_empresas' => $empresas[2]->id_empresa,
                'id_personas' => $personas[2]->id_persona,
            ],
            [
                'titulo' => 'Sistemas de seguridad para ventanas',
                'numero' => 9996,
                'fyh' => Carbon::now()->subMonths(2)->subDays(15),
                'precio_total' => 123000,
                'id_empleados' => $vendedores[2]->id_empleado,
                'id_empresas' => $empresas[3]->id_empresa,
                'id_personas' => $personas[3]->id_persona,
            ],

            // Cotizaciones más antiguas
            [
                'titulo' => 'Deck de madera para quincho',
                'numero' => 9995,
                'fyh' => Carbon::now()->subMonths(3)->subDays(12),
                'precio_total' => 234000,
                'id_empleados' => $vendedores[0]->id_empleado,
                'id_empresas' => $empresas[4]->id_empresa,
                'id_personas' => $personas[4]->id_persona,
            ],
            [
                'titulo' => 'Puerta blindada alta seguridad',
                'numero' => 9994,
                'fyh' => Carbon::now()->subMonths(3)->subDays(20),
                'precio_total' => 189000,
                'id_empleados' => $vendedores[1]->id_empleado,
                'id_empresas' => $empresas[5]->id_empresa,
                'id_personas' => $personas[5]->id_persona,
            ],
            [
                'titulo' => 'Ventiluz automatizado para galpón',
                'numero' => 9993,
                'fyh' => Carbon::now()->subMonths(4)->subDays(5),
                'precio_total' => 678000,
                'id_empleados' => $vendedores[2]->id_empleado,
                'id_empresas' => $empresas[6]->id_empresa,
                'id_personas' => $personas[6]->id_persona,
            ],

            // Cotizaciones de hace 6 meses
            [
                'titulo' => 'Escalera de madera maciza',
                'numero' => 9992,
                'fyh' => Carbon::now()->subMonths(5)->subDays(18),
                'precio_total' => 145000,
                'id_empleados' => $vendedores[0]->id_empleado,
                'id_empresas' => $empresas[7]->id_empresa,
                'id_personas' => $personas[7]->id_persona,
            ],
            [
                'titulo' => 'Frente completo de local comercial',
                'numero' => 9991,
                'fyh' => Carbon::now()->subMonths(6)->subDays(10),
                'precio_total' => 890000,
                'id_empleados' => $vendedores[1]->id_empleado,
                'id_empresas' => $empresas[0]->id_empresa,
                'id_personas' => $personas[0]->id_persona,
            ],
            [
                'titulo' => 'Pérgola con techo corredizo',
                'numero' => 9990,
                'fyh' => Carbon::now()->subMonths(6)->subDays(25),
                'precio_total' => 456000,
                'id_empleados' => $vendedores[2]->id_empleado,
                'id_empresas' => $empresas[1]->id_empresa,
                'id_personas' => $personas[1]->id_persona,
            ],
            [
                'titulo' => 'Muebles empotrados dormitorio',
                'numero' => 9989,
                'fyh' => Carbon::now()->subMonths(7)->subDays(8),
                'precio_total' => 234000,
                'id_empleados' => $vendedores[0]->id_empleado,
                'id_empresas' => $empresas[2]->id_empresa,
                'id_personas' => $personas[2]->id_persona,
            ],
            [
                'titulo' => 'Canceles de vidrio para oficina',
                'numero' => 9988,
                'fyh' => Carbon::now()->subMonths(8)->subDays(15),
                'precio_total' => 167000,
                'id_empleados' => $vendedores[1]->id_empleado,
                'id_empresas' => $empresas[3]->id_empresa,
                'id_personas' => $personas[3]->id_persona,
            ]
        ];

        foreach ($cotizaciones as $cotizacion) {
            Cotizacion::create($cotizacion);
        }
    }
}