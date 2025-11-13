<?php

namespace Database\Seeders;

use App\Models\Subdivision;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubdivisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subdivisions = [
            // Maderas (id_subrubro: 1)
            [
                'nombre' => 'Maderas Duras',
                'descripcion' => 'Maderas de alta densidad para estructuras',
                'id_subrubro' => 1,
            ],
            [
                'nombre' => 'Maderas Blandas',
                'descripcion' => 'Maderas ligeras para acabados',
                'id_subrubro' => 1,
            ],
            [
                'nombre' => 'Tableros Manufacturados',
                'descripcion' => 'MDF, aglomerado, contrachapado',
                'id_subrubro' => 1,
            ],
            
            // Adhesivos y Selladores (id_subrubro: 2)
            [
                'nombre' => 'Colas para Madera',
                'descripcion' => 'Adhesivos específicos para unión de maderas',
                'id_subrubro' => 2,
            ],
            [
                'nombre' => 'Selladores',
                'descripcion' => 'Productos para sellado de juntas',
                'id_subrubro' => 2,
            ],
            
            // Bisagras y Pivotes (id_subrubro: 3)
            [
                'nombre' => 'Bisagras de Piano',
                'descripcion' => 'Bisagras continuas para puertas',
                'id_subrubro' => 3,
            ],
            [
                'nombre' => 'Pivotes Centrales',
                'descripcion' => 'Sistemas de pivote para puertas pesadas',
                'id_subrubro' => 3,
            ],
            
            // Cerraduras y Manijas (id_subrubro: 4)
            [
                'nombre' => 'Cerraduras de Embutir',
                'descripcion' => 'Cerraduras integradas en la puerta',
                'id_subrubro' => 4,
            ],
            [
                'nombre' => 'Manijas y Tiradores',
                'descripcion' => 'Elementos de manipulación manual',
                'id_subrubro' => 4,
            ],
            
            // Correderas y Guías (id_subrubro: 5)
            [
                'nombre' => 'Guías Telescópicas',
                'descripcion' => 'Sistemas de extensión completa',
                'id_subrubro' => 5,
            ],
            [
                'nombre' => 'Rieles para Ventana',
                'descripcion' => 'Sistemas de deslizamiento para ventanas',
                'id_subrubro' => 5,
            ],
            
            // Puertas (id_subrubro: 6)
            [
                'nombre' => 'Puertas de Madera',
                'descripcion' => 'Puertas fabricadas en diferentes maderas',
                'id_subrubro' => 6,
            ],
            [
                'nombre' => 'Puertas de Aluminio',
                'descripcion' => 'Puertas metálicas ligeras',
                'id_subrubro' => 6,
            ],
            
            // Ventanas (id_subrubro: 7)
            [
                'nombre' => 'Ventanas de Aluminio',
                'descripcion' => 'Marcos y hojas de aluminio',
                'id_subrubro' => 7,
            ],
            [
                'nombre' => 'Ventanas de Madera',
                'descripcion' => 'Marcos tradicionales de madera',
                'id_subrubro' => 7,
            ],
            
            // Sistemas Corredizos (id_subrubro: 8)
            [
                'nombre' => 'Sistemas de Aluminio',
                'descripcion' => 'Sistemas corredizos completos de aluminio',
                'id_subrubro' => 8,
            ],
            [
                'nombre' => 'Sistemas Mixtos',
                'descripcion' => 'Combinación de materiales para sistemas corredizos',
                'id_subrubro' => 8,
            ]
        ];

        foreach ($subdivisions as $subdivision) {
            Subdivision::create($subdivision);
        }
    }
}