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

        // Plantillas de cotizaciones variadas
        $titulos_base = [
            'Reforma integral oficina comercial',
            'Aberturas para vivienda unifamiliar', 
            'Muebles de cocina a medida',
            'Sistema corredizo para showroom',
            'Herrajes premium para portón',
            'Estructura de madera para pérgola',
            'Vidrios temperados para edificio',
            'Revestimiento completo en madera',
            'Carpintería para local gastronómico',
            'Cerramientos para terraza',
            'Mobiliario corporativo completo',
            'Sistemas de seguridad para ventanas',
            'Deck de madera para quincho',
            'Puerta blindada alta seguridad',
            'Ventiluz automatizado para galpón',
            'Escalera de madera maciza',
            'Frente completo de local comercial',
            'Pérgola con techo corredizo',
            'Muebles empotrados dormitorio',
            'Canceles de vidrio para oficina',
            'Estantería industrial personalizada',
            'Mampara de baño premium',
            'Closet completo con cajones',
            'Divisor de ambientes moderno',
            'Mesa de reuniones ejecutiva',
            'Biblioteca empotrada',
            'Cocina integral premium',
            'Ventanas termopanel eficientes',
            'Puerta de entrada luxury',
            'Parrilla con techo de madera'
        ];

        $cotizaciones = [];
        $numero_actual = 9001; // Empezamos con números más bajos para las generadas automáticamente

        // Generar 25 cotizaciones por cada vendedor
        for ($i = 0; $i < count($vendedores); $i++) {
            for ($j = 0; $j < 25; $j++) {
                $dias_atras = rand(1, 365); // Entre 1 día y 1 año atrás
                $precio = rand(50, 1000) * 1000; // Entre $50.000 y $1.000.000
                $empresa_index = rand(0, count($empresas) - 1);
                $persona_index = rand(0, count($personas) - 1);
                $titulo_index = ($i * 25 + $j) % count($titulos_base);

                $cotizaciones[] = [
                    'titulo' => $titulos_base[$titulo_index],
                    'numero' => $numero_actual++,
                    'fyh' => Carbon::now()->subDays($dias_atras),
                    'precio_total' => $precio,
                    'id_empleados' => $vendedores[$i]->id_empleado,
                    'id_empresas' => $empresas[$empresa_index]->id_empresa,
                    'id_personas' => $personas[$persona_index]->id_persona,
                ];
            }
        }

        // Agregar algunas cotizaciones específicas con títulos únicos para testing
        $cotizaciones_especiales = [
            // Cotizaciones del último mes
            [
                'titulo' => 'Proyecto especial - Oficina ejecutiva',
                'numero' => 10001,
                'fyh' => Carbon::now()->subDays(5),
                'precio_total' => 485000,
                'id_empleados' => $vendedores[0]->id_empleado,
                'id_empresas' => $empresas[0]->id_empresa,
                'id_personas' => $personas[0]->id_persona,
            ],
            // Cotizaciones especiales adicionales para variedad
            [
                'titulo' => 'Showroom premium - Muebles exhibit',
                'numero' => 10002,
                'fyh' => Carbon::now()->subDays(12),
                'precio_total' => 720000,
                'id_empleados' => $vendedores[1]->id_empleado,
                'id_empresas' => $empresas[1]->id_empresa,
                'id_personas' => $personas[1]->id_persona,
            ],
            [
                'titulo' => 'Restaurante completo - Carpintería',
                'numero' => 10003,
                'fyh' => Carbon::now()->subDays(8),
                'precio_total' => 850000,
                'id_empleados' => $vendedores[2]->id_empleado,
                'id_empresas' => $empresas[2]->id_empresa,
                'id_personas' => $personas[2]->id_persona,
            ],
            [
                'titulo' => 'Edificio corporativo - Lobby',
                'numero' => 10004,
                'fyh' => Carbon::now()->subDays(3),
                'precio_total' => 1200000,
                'id_empleados' => $vendedores[3]->id_empleado,
                'id_empresas' => $empresas[3]->id_empresa,
                'id_personas' => $personas[3]->id_persona,
            ]
        ];

        // Combinar todas las cotizaciones
        $todas_cotizaciones = array_merge($cotizaciones, $cotizaciones_especiales);

        foreach ($todas_cotizaciones as $cotizacion) {
            Cotizacion::create($cotizacion);
        }
    }
}