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
        // Obtener todas las cotizaciones agrupadas por empresa
        $cotizaciones = Cotizacion::orderBy('fyh', 'asc')->get();
        $estados = Estado::all();
        
        $cambios = [];
        
        // Agrupar cotizaciones por empresa
        $cotizacionesPorEmpresa = $cotizaciones->groupBy('id_empresas');
        
        foreach ($cotizacionesPorEmpresa as $idEmpresa => $cotizacionesEmpresa) {
            // Ordenar por fecha (más antiguas primero)
            $cotizacionesEmpresa = $cotizacionesEmpresa->sortBy('fyh')->values();
            
            // Asignar estados: 2 de cada uno (excepto Nuevo, solo 1)
            $indice = 0;
            
            // 2 cotizaciones en "En entrega" (las más antiguas)
            for ($i = 0; $i < 2 && $indice < $cotizacionesEmpresa->count(); $i++, $indice++) {
                $cotizacion = $cotizacionesEmpresa[$indice];
                $fechaBase = $cotizacion->fyh;
                
                // Nuevo
                $cambios[] = [
                    'fyH' => $fechaBase,
                    'id_cotizaciones' => $cotizacion->id,
                    'id_estado' => $estados->where('nombre', 'Nuevo')->first()->id_estado,
                ];
                
                // Abierto (1-2 días después)
                $cambios[] = [
                    'fyH' => $fechaBase->copy()->addDays(rand(1, 2)),
                    'id_cotizaciones' => $cotizacion->id,
                    'id_estado' => $estados->where('nombre', 'Abierto')->first()->id_estado,
                ];
                
                // Cotizado (3-7 días después)
                $cambios[] = [
                    'fyH' => $fechaBase->copy()->addDays(rand(3, 7)),
                    'id_cotizaciones' => $cotizacion->id,
                    'id_estado' => $estados->where('nombre', 'Cotizado')->first()->id_estado,
                ];
                
                // En entrega (10-20 días después)
                $cambios[] = [
                    'fyH' => $fechaBase->copy()->addDays(rand(10, 20)),
                    'id_cotizaciones' => $cotizacion->id,
                    'id_estado' => $estados->where('nombre', 'En entrega')->first()->id_estado,
                ];
            }
            
            // 2 cotizaciones en "Cotizado" (siguientes más antiguas)
            for ($i = 0; $i < 2 && $indice < $cotizacionesEmpresa->count(); $i++, $indice++) {
                $cotizacion = $cotizacionesEmpresa[$indice];
                $fechaBase = $cotizacion->fyh;
                
                // Nuevo
                $cambios[] = [
                    'fyH' => $fechaBase,
                    'id_cotizaciones' => $cotizacion->id,
                    'id_estado' => $estados->where('nombre', 'Nuevo')->first()->id_estado,
                ];
                
                // Abierto (1-3 días después)
                $cambios[] = [
                    'fyH' => $fechaBase->copy()->addDays(rand(1, 3)),
                    'id_cotizaciones' => $cotizacion->id,
                    'id_estado' => $estados->where('nombre', 'Abierto')->first()->id_estado,
                ];
                
                // Cotizado (5-10 días después)
                $cambios[] = [
                    'fyH' => $fechaBase->copy()->addDays(rand(5, 10)),
                    'id_cotizaciones' => $cotizacion->id,
                    'id_estado' => $estados->where('nombre', 'Cotizado')->first()->id_estado,
                ];
            }
            
            // 2 cotizaciones en "Abierto" (medianas)
            for ($i = 0; $i < 2 && $indice < $cotizacionesEmpresa->count(); $i++, $indice++) {
                $cotizacion = $cotizacionesEmpresa[$indice];
                $fechaBase = $cotizacion->fyh;
                
                // Nuevo
                $cambios[] = [
                    'fyH' => $fechaBase,
                    'id_cotizaciones' => $cotizacion->id,
                    'id_estado' => $estados->where('nombre', 'Nuevo')->first()->id_estado,
                ];
                
                // Abierto (1-3 días después)
                $cambios[] = [
                    'fyH' => $fechaBase->copy()->addDays(rand(1, 3)),
                    'id_cotizaciones' => $cotizacion->id,
                    'id_estado' => $estados->where('nombre', 'Abierto')->first()->id_estado,
                ];
            }
            
            // Solo 1 cotización en "Nuevo" (la más reciente)
            for ($i = 0; $i < 1 && $indice < $cotizacionesEmpresa->count(); $i++, $indice++) {
                $cotizacion = $cotizacionesEmpresa[$indice];
                $fechaBase = $cotizacion->fyh;
                
                // Nuevo
                $cambios[] = [
                    'fyH' => $fechaBase,
                    'id_cotizaciones' => $cotizacion->id,
                    'id_estado' => $estados->where('nombre', 'Nuevo')->first()->id_estado,
                ];
            }
            
            // El resto de cotizaciones (si hay más) se distribuyen en estados avanzados
            while ($indice < $cotizacionesEmpresa->count()) {
                $cotizacion = $cotizacionesEmpresa[$indice];
                $fechaBase = $cotizacion->fyh;
                $diasDesdeCreacion = now()->diffInDays($fechaBase);
                
                // Nuevo
                $cambios[] = [
                    'fyH' => $fechaBase,
                    'id_cotizaciones' => $cotizacion->id,
                    'id_estado' => $estados->where('nombre', 'Nuevo')->first()->id_estado,
                ];
                
                // Según antigüedad, avanzar más o menos
                if ($diasDesdeCreacion > 60) {
                    // Muy antiguas -> En entrega
                    $cambios[] = [
                        'fyH' => $fechaBase->copy()->addDays(rand(1, 2)),
                        'id_cotizaciones' => $cotizacion->id,
                        'id_estado' => $estados->where('nombre', 'Abierto')->first()->id_estado,
                    ];
                    $cambios[] = [
                        'fyH' => $fechaBase->copy()->addDays(rand(3, 7)),
                        'id_cotizaciones' => $cotizacion->id,
                        'id_estado' => $estados->where('nombre', 'Cotizado')->first()->id_estado,
                    ];
                    $cambios[] = [
                        'fyH' => $fechaBase->copy()->addDays(rand(10, 20)),
                        'id_cotizaciones' => $cotizacion->id,
                        'id_estado' => $estados->where('nombre', 'En entrega')->first()->id_estado,
                    ];
                } elseif ($diasDesdeCreacion > 30) {
                    // Antiguas -> Cotizado
                    $cambios[] = [
                        'fyH' => $fechaBase->copy()->addDays(rand(1, 3)),
                        'id_cotizaciones' => $cotizacion->id,
                        'id_estado' => $estados->where('nombre', 'Abierto')->first()->id_estado,
                    ];
                    $cambios[] = [
                        'fyH' => $fechaBase->copy()->addDays(rand(5, 10)),
                        'id_cotizaciones' => $cotizacion->id,
                        'id_estado' => $estados->where('nombre', 'Cotizado')->first()->id_estado,
                    ];
                } elseif ($diasDesdeCreacion > 10) {
                    // Medianas -> Abierto
                    $cambios[] = [
                        'fyH' => $fechaBase->copy()->addDays(rand(1, 3)),
                        'id_cotizaciones' => $cotizacion->id,
                        'id_estado' => $estados->where('nombre', 'Abierto')->first()->id_estado,
                    ];
                }
                // Recientes se quedan en Nuevo
                
                $indice++;
            }
        }
        
        // Insertar todos los cambios
        foreach ($cambios as $cambio) {
            Cambio::create($cambio);
        }
        
        $this->command->info('✅ Cambios de estado creados: ' . count($cambios) . ' registros');
    }
}