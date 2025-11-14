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
        
        // Obtener empleados vendedores
        $vendedores = Empleado::whereHas('rol', function ($query) {
            $query->where('nombre', 'vendedor');
        })->get();

        $this->command->info("Vendedores encontrados: " . $vendedores->count());

        foreach ($vendedores as $vendedor) {
            $this->command->info("Procesando vendedor: {$vendedor->nombre} (ID empleado: {$vendedor->id_empleado})");
            
            // Verificar empresas con cotizaciones del vendedor
            $empresasConCotizaciones = Empresa::whereHas('cotizaciones', function ($query) use ($vendedor) {
                $query->where('id_personas', $vendedor->id_personas);
            })->get();

            $this->command->info("Empresas con cotizaciones del vendedor {$vendedor->nombre}: " . $empresasConCotizaciones->count());

            if ($empresasConCotizaciones->count() >= 2) {
                // Crear grupos para este vendedor
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
                        'nombre_grupo' => $grupoData['nombre_grupo'] . ' - ' . $vendedor->nombre,
                        'descripcion' => $grupoData['descripcion'] . ' del vendedor ' . $vendedor->nombre,
                        'id_empleado' => $vendedor->id_empleado
                    ]);

                    // Asignar empresas al grupo (dividir empresas entre grupos)
                    $empresasParaGrupo = $empresasConCotizaciones->slice($grupoIndex * 2, 2);
                    
                    foreach ($empresasParaGrupo as $empresa) {
                        $grupo->empresas()->attach($empresa->id_empresa);
                        $this->command->info("Empresa {$empresa->nombre} agregada al grupo {$grupo->nombre_grupo}");
                    }
                    
                    if ($empresasConCotizaciones->count() < 4) break;
                }
            } else {
                $this->command->warn("Vendedor {$vendedor->nombre} no tiene suficientes empresas con cotizaciones");
            }
        }

        $this->command->info("✅ Grupos de clientes creados exitosamente");
    }
}