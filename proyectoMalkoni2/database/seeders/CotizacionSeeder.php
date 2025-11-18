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
        // Obtener datos base
        $vendedores = Empleado::vendedores()->get();
        $personas = Persona::with('empresa')->get(); // Cargar empresa para referencia
        $estados = ['Nuevo', 'Abierto', 'Cotizado', 'En entrega'];
        
        // Plantillas de cotizaciones determinísticas
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
            'Canceles de vidrio para oficina'
        ];

        $cotizaciones = [];
        $numero_actual = 9001; // Número base determinístico
        $titulo_index = 0;

        // Crear 1 cotización por estado para cada persona
        foreach ($personas as $persona_index => $persona) {
            // Asignar vendedor de manera cíclica (determinística)
            $vendedor = $vendedores[$persona_index % count($vendedores)];
            
            foreach ($estados as $estado_index => $estado) {
                // Fechas determinísticas basadas en el estado
                $dias_atras = match($estado) {
                    'Nuevo' => 1,      // Cotización muy reciente
                    'Abierto' => 7,    // Cotización de la semana pasada
                    'Cotizado' => 30,  // Cotización del mes pasado
                    'En entrega' => 60 // Cotización de hace 2 meses
                };
                
                // Precios determinísticos según estado y persona
                $precio_total = match($estado) {
                    'Nuevo' => 0,      // Sin precio
                    'Abierto' => 0,    // Sin precio
                    'Cotizado' => ($persona_index + 1) * 100000,     // 100k, 200k, 300k, etc.
                    'En entrega' => ($persona_index + 1) * 150000    // 150k, 300k, 450k, etc.
                };
                
                // Fecha cotizado solo para estados con precio
                $fecha_cotizado = in_array($estado, ['Cotizado', 'En entrega']) 
                    ? Carbon::now()->subDays($dias_atras - 3) 
                    : null;
                
                // Título determinístico
                $titulo = $titulos_base[$titulo_index % count($titulos_base)];
                $titulo_index++;
                
                // ESTRATEGIA HÍBRIDA: Alternar entre persona y empresa
                // Las cotizaciones pares van a la empresa, las impares a la persona
                $usar_empresa = ($persona_index + $estado_index) % 2 === 0;
                
                $cotizaciones[] = [
                    'titulo' => $titulo . " - " . $persona->empresa->nombre,
                    'numero' => $numero_actual++,
                    'fyh' => Carbon::now()->subDays($dias_atras),
                    'fecha_cotizado' => $fecha_cotizado,
                    'precio_total' => $precio_total,
                    'id_empleados' => $vendedor->id_empleado,
                    'id_empresas' => $usar_empresa ? $persona->empresa->id_empresa : null,
                    'id_personas' => $usar_empresa ? null : $persona->id_persona,
                ];
            }
        }

        // Crear todas las cotizaciones
        foreach ($cotizaciones as $cotizacion) {
            Cotizacion::create($cotizacion);
        }
        
        $this->command->info('✅ Cotizaciones determinísticas creadas (estrategia híbrida):');
        $this->command->info("   📊 " . count($personas) . " personas × " . count($estados) . " estados = " . count($cotizaciones) . " cotizaciones");
        $this->command->info("   🔢 Números del " . ($numero_actual - count($cotizaciones)) . " al " . ($numero_actual - 1));
        
        // Contar cuántas van a empresa vs persona
        $cotizaciones_empresa = collect($cotizaciones)->where('id_empresas', '!=', null)->count();
        $cotizaciones_persona = collect($cotizaciones)->where('id_personas', '!=', null)->count();
        $this->command->info("   🏢 Empresas: {$cotizaciones_empresa} | 👤 Personas: {$cotizaciones_persona}");
    }
}