<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Persona;
use App\Models\Empresa;

class PersonaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener las empresas para asociarlas
        $empresas = Empresa::all();
        
        // Crear múltiples personas por empresa (2-3 personas por empresa)
        $personas = [];
        
        foreach ($empresas as $empresa) {
            // Crear 2-3 personas por cada empresa
            $cantidadPersonas = rand(2, 3);
            
            for ($i = 0; $i < $cantidadPersonas; $i++) {
                $personas[] = [
                    'id_empresa' => $empresa->id_empresa,
                    'foto' => null,
                    'token_opt' => null
                ];
            }
        }

        foreach ($personas as $persona) {
            Persona::create($persona);
        }
        
        $this->command->info('✅ Personas creadas: ' . count($personas) . ' usuarios distribuidos en ' . $empresas->count() . ' empresas');
    }
}