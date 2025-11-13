<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Subtipo;

class SubtipoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subtipos = [
            // Maderas (id_tipo = 1)
            [
                'nombre' => 'Madera Maciza',
                'descripcion' => 'Maderas sólidas de diferentes especies',
                'id_tipo' => 1
            ],
            [
                'nombre' => 'Tableros',
                'descripcion' => 'Tableros de MDF, aglomerado, melamina',
                'id_tipo' => 1
            ],
            [
                'nombre' => 'Molduras',
                'descripcion' => 'Molduras y perfiles decorativos',
                'id_tipo' => 1
            ],
            
            // Herrajes (id_tipo = 2)
            [
                'nombre' => 'Bisagras',
                'descripcion' => 'Bisagras para puertas y ventanas',
                'id_tipo' => 2
            ],
            [
                'nombre' => 'Cerraduras',
                'descripcion' => 'Cerraduras y sistemas de seguridad',
                'id_tipo' => 2
            ],
            [
                'nombre' => 'Tornillería',
                'descripcion' => 'Tornillos, tuercas y elementos de fijación',
                'id_tipo' => 2
            ],
            [
                'nombre' => 'Manijas',
                'descripcion' => 'Manijas y tiradores para muebles',
                'id_tipo' => 2
            ],
            
            // Aberturas (id_tipo = 3)
            [
                'nombre' => 'Puertas',
                'descripcion' => 'Puertas de interior y exterior',
                'id_tipo' => 3
            ],
            [
                'nombre' => 'Ventanas',
                'descripcion' => 'Ventanas de diferentes materiales',
                'id_tipo' => 3
            ],
            [
                'nombre' => 'Marcos',
                'descripcion' => 'Marcos para puertas y ventanas',
                'id_tipo' => 3
            ],
            
            // Sistemas Corredizos (id_tipo = 4)
            [
                'nombre' => 'Rieles',
                'descripcion' => 'Rieles para sistemas corredizos',
                'id_tipo' => 4
            ],
            [
                'nombre' => 'Rodamientos',
                'descripcion' => 'Rodamientos y ruedas para sistemas corredizos',
                'id_tipo' => 4
            ],
            [
                'nombre' => 'Guías',
                'descripcion' => 'Guías y sistemas de deslizamiento',
                'id_tipo' => 4
            ],
            
            // Accesorios (id_tipo = 5)
            [
                'nombre' => 'Adhesivos',
                'descripcion' => 'Colas y adhesivos para carpintería',
                'id_tipo' => 5
            ],
            [
                'nombre' => 'Acabados',
                'descripcion' => 'Barnices, lacas y productos de acabado',
                'id_tipo' => 5
            ],
            [
                'nombre' => 'Herramientas',
                'descripcion' => 'Herramientas básicas de carpintería',
                'id_tipo' => 5
            ]
        ];

        foreach ($subtipos as $subtipo) {
            Subtipo::create($subtipo);
        }
    }
}