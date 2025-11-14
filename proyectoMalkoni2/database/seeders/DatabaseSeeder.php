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
            PersonaSeeder::class,         // Mover PersonaSeeder antes de EmpleadoSeeder
            EmpleadoSeeder::class,
            EmpresaSeeder::class,
            TipoSeeder::class,            // Nuevo: tipos de productos (Maderas, Herrajes, etc.)
            SubtipoSeeder::class,         // Nuevo: subtipos de cada tipo
            CategoriaSeeder::class,       // Nuevo: categorías de clasificación (Premium, Estándar, etc.)
            SubcategoriaSeeder::class,    // Nuevo: subcategorías de cada categoría
            ProductoSeeder::class,        // Actualizado: usa id_subtipo e id_subcategoria
            EstadoSeeder::class,
            CotizacionSeeder::class,
            ItemSeeder::class,
            CambioSeeder::class,
            GrupoSeeder::class,           // Nuevo: grupos de clientes para vendedores
        ]);

        $this->command->info('✅ Base de datos poblada exitosamente con datos de Malkoni Hnos.');
        $this->command->info('📊 Dashboard del vendedor listo para usar con datos realistas.');
        $this->command->info('🔄 Estructura actualizada: Tipos->Subtipos y Categorías->Subcategorías');
        $this->command->info('👥 Grupos de clientes creados para gestión de vendedores.');
    }
}
