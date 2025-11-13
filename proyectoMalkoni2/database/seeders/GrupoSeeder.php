<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Grupo;
use App\Models\Empresa;
use App\Models\Empleado;

class GrupoSeeder extends Seeder
{
    public function run()
    {
        // Limpiar grupos existentes para evitar duplicados
        // Primero eliminar relaciones de la tabla pivot
        \DB::table('grupo_empresa')->delete();
        // Luego eliminar grupos
        Grupo::query()->delete();
        
        // Obtener empleados que tienen cotizaciones (usar id_personas que existen en cotizaciones)
        $idPersonasConCotizaciones = \DB::table('cotizaciones')
            ->select('id_personas')
            ->distinct()
            ->take(3)
            ->pluck('id_personas');

        $this->command->info("ID Personas con cotizaciones: " . $idPersonasConCotizaciones->implode(', '));

        foreach ($idPersonasConCotizaciones as $index => $idPersona) {
            $this->command->info("Procesando id_personas: {$idPersona}");
            
            // Verificar empresas con cotizaciones de esta persona
            $empresasConCotizaciones = Empresa::whereHas('cotizaciones', function ($query) use ($idPersona) {
                $query->where('id_personas', $idPersona);
            })->get();

            $this->command->info("Empresas con cotizaciones para id_personas {$idPersona}: " . $empresasConCotizaciones->count());

            if ($empresasConCotizaciones->count() >= 2) {
                // Crear grupos para esta persona
                $grupos = [
                    [
                        'nombre_grupo' => 'Clientes Importantes',
                        'descripcion' => 'Clientes con mayor volumen de cotizaciones',
                    ],
                    [
                        'nombre_grupo' => 'Clientes Nuevos', 
                        'descripcion' => 'Clientes recién incorporados',
                    ]
                ];

                foreach ($grupos as $grupoIndex => $grupoData) {
                    $grupo = Grupo::create([
                        'nombre_grupo' => $grupoData['nombre_grupo'] . ' - Vendedor' . $idPersona,
                        'descripcion' => $grupoData['descripcion'] . ' (ID Persona: ' . $idPersona . ')',
                        'id_personas' => $idPersona
                    ]);

                    // Asignar empresas al grupo (mitad para cada grupo)
                    $empresasParaGrupo = $empresasConCotizaciones->slice($grupoIndex * 2, 2);
                    
                    foreach ($empresasParaGrupo as $empresa) {
                        $grupo->empresas()->attach($empresa->id_empresa);
                        $this->command->info("Empresa {$empresa->nombre_empresa} agregada al grupo {$grupo->nombre_grupo}");
                    }
                    
                    if ($empresasConCotizaciones->count() < 4) break;
                }
            } else {
                $this->command->warn("ID Persona {$idPersona} no tiene suficientes empresas con cotizaciones");
            }
        }

        $this->command->info("✅ Grupos de clientes creados exitosamente");
    }
}