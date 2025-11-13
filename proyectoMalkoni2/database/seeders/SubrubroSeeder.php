<?php

namespace Database\Seeders;

use App\Models\Subrubro;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubrubroSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subrubros = [
            // Materiales de Construcción (id_rubro: 1)
            [
                'nombre' => 'Maderas',
                'descripcion' => 'Maderas de diferentes tipos para construcción',
                'id_rubro' => 1,
            ],
            [
                'nombre' => 'Adhesivos y Selladores',
                'descripcion' => 'Pegamentos, colas y productos de sellado',
                'id_rubro' => 1,
            ],
            
            // Herrajes y Accesorios (id_rubro: 2)
            [
                'nombre' => 'Bisagras y Pivotes',
                'descripcion' => 'Sistemas de articulación para puertas y ventanas',
                'id_rubro' => 2,
            ],
            [
                'nombre' => 'Cerraduras y Manijas',
                'descripcion' => 'Sistemas de cierre y manipulación',
                'id_rubro' => 2,
            ],
            [
                'nombre' => 'Correderas y Guías',
                'descripcion' => 'Sistemas deslizantes para ventanas y puertas',
                'id_rubro' => 2,
            ],
            
            // Aberturas (id_rubro: 3)
            [
                'nombre' => 'Puertas',
                'descripcion' => 'Puertas de diferentes materiales y diseños',
                'id_rubro' => 3,
            ],
            [
                'nombre' => 'Ventanas',
                'descripcion' => 'Ventanas y marcos para diferentes aplicaciones',
                'id_rubro' => 3,
            ],
            [
                'nombre' => 'Sistemas Corredizos',
                'descripcion' => 'Sistemas completos de abertura corredera',
                'id_rubro' => 3,
            ]
        ];

        foreach ($subrubros as $subrubro) {
            Subrubro::create($subrubro);
        }
    }
}