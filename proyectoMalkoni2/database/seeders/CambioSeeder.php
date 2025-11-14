<?php

namespace Database\Seeders;

use App\Models\Cambio;
use App\Models\Cotizacion;
use App\Models\Estado;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CambioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener todas las cotizaciones y estados
        $cotizaciones = Cotizacion::all();
        $estados = Estado::all();
        
        $cambios = [];
        
        foreach ($cotizaciones as $cotizacion) {
            // Simular progresión de estados para cada cotización
            $fechaBase = $cotizacion->created_at ?? now()->subDays(rand(1, 30));
            
            // Si la cotización NO tiene precio, debe estar en Nuevo o Abierto
            if (!$cotizacion->precio_total || $cotizacion->precio_total <= 0) {
                // Estado Nuevo
                $cambios[] = [
                    'fyH' => $fechaBase,
                    'id_cotizaciones' => $cotizacion->id,
                    'id_estado' => $estados->where('nombre', 'Nuevo')->first()->id_estado,
                ];
                
                // 50% pasan a Abierto (sin cotizar aún)
                if (rand(1, 100) <= 50) {
                    $cambios[] = [
                        'fyH' => $fechaBase->copy()->addHours(rand(2, 12)),
                        'id_cotizaciones' => $cotizacion->id,
                        'id_estado' => $estados->where('nombre', 'Abierto')->first()->id_estado,
                    ];
                }
            } else {
                // Si tiene precio, debe haber pasado por Nuevo -> Abierto -> Cotizado
                
                // Estado Nuevo
                $cambios[] = [
                    'fyH' => $fechaBase,
                    'id_cotizaciones' => $cotizacion->id,
                    'id_estado' => $estados->where('nombre', 'Nuevo')->first()->id_estado,
                ];
                
                // Pasar a Abierto
                $cambios[] = [
                    'fyH' => $fechaBase->copy()->addHours(rand(2, 12)),
                    'id_cotizaciones' => $cotizacion->id,
                    'id_estado' => $estados->where('nombre', 'Abierto')->first()->id_estado,
                ];
                
                // Pasar a Cotizado (ya tiene precio)
                $cambios[] = [
                    'fyH' => $fechaBase->copy()->addDays(rand(1, 5)),
                    'id_cotizaciones' => $cotizacion->id,
                    'id_estado' => $estados->where('nombre', 'Cotizado')->first()->id_estado,
                ];
                
                // 40% pasan a En entrega
                if (rand(1, 100) <= 40) {
                    $cambios[] = [
                        'fyH' => $fechaBase->copy()->addDays(rand(5, 15)),
                        'id_cotizaciones' => $cotizacion->id,
                        'id_estado' => $estados->where('nombre', 'En entrega')->first()->id_estado,
                    ];
                }
            }
        }
        
        // Insertar todos los cambios
        foreach ($cambios as $cambio) {
            Cambio::create($cambio);
        }
        
        $this->command->info('✅ Cambios de estado creados: ' . count($cambios) . ' registros');
    }
}