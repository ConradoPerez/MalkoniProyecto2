<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Ejecutar seeders en orden de dependencias
        $this->call([
            RolSeeder::class,
            EmpleadoSeeder::class,
            PersonaSeeder::class, 
            EmpresaSeeder::class,
            RubroSeeder::class,           // Nuevo: tabla base para jerarquía
            SubrubroSeeder::class,        // Nuevo: depende de rubros
            SubdivisionSeeder::class,     // Nuevo: depende de subrubros
            CategoriaSeeder::class,       // Depende de subdivisions
            ProductoSeeder::class,        // Depende de categorias
            EstadoSeeder::class,          // Estados actualizados
            CotizacionSeeder::class,
            ItemSeeder::class,
            CambioSeeder::class,          // Nuevo: registra cambios de estado
        ]);

        $this->command->info('✅ Base de datos poblada exitosamente con datos de Malkoni Hnos.');
        $this->command->info('📊 Dashboard del vendedor listo para usar con datos realistas.');
    }
}
