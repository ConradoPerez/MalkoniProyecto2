<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Categoria;

class CategoriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categorias = [
            // Maderas Duras (id_subdivision: 1)
            [
                'nombre' => 'Eucalipto',
                'descripcion' => 'Madera de eucalipto para estructuras',
                'id_subdivision' => 1,
            ],
            [
                'nombre' => 'Quebracho',
                'descripcion' => 'Madera dura resistente a la intemperie',
                'id_subdivision' => 1,
            ],
            
            // Maderas Blandas (id_subdivision: 2)
            [
                'nombre' => 'Pino',
                'descripcion' => 'Madera de pino para construcción liviana',
                'id_subdivision' => 2,
            ],
            [
                'nombre' => 'Álamo',
                'descripcion' => 'Madera blanda para acabados',
                'id_subdivision' => 2,
            ],
            
            // Tableros Manufacturados (id_subdivision: 3)
            [
                'nombre' => 'MDF',
                'descripcion' => 'Tableros de fibra de densidad media',
                'id_subdivision' => 3,
            ],
            [
                'nombre' => 'Melamina',
                'descripcion' => 'Tableros con recubrimiento melamínico',
                'id_subdivision' => 3,
            ],
            
            // Colas para Madera (id_subdivision: 4)
            [
                'nombre' => 'Cola Vinílica',
                'descripcion' => 'Adhesivo PVA para madera',
                'id_subdivision' => 4,
            ],
            
            // Selladores (id_subdivision: 5)
            [
                'nombre' => 'Silicona',
                'descripcion' => 'Sellador de silicona transparente',
                'id_subdivision' => 5,
            ],
            
            // Bisagras de Piano (id_subdivision: 6)
            [
                'nombre' => 'Bisagra Piano 32mm',
                'descripcion' => 'Bisagra continua de 32mm',
                'id_subdivision' => 6,
            ],
            
            // Pivotes Centrales (id_subdivision: 7)
            [
                'nombre' => 'Pivote Superior',
                'descripcion' => 'Pivote de carga superior',
                'id_subdivision' => 7,
            ],
            
            // Cerraduras de Embutir (id_subdivision: 8)
            [
                'nombre' => 'Cerradura Cilindro',
                'descripcion' => 'Cerradura de cilindro europeo',
                'id_subdivision' => 8,
            ],
            
            // Manijas y Tiradores (id_subdivision: 9)
            [
                'nombre' => 'Manija Cromada',
                'descripcion' => 'Manija con acabado cromado',
                'id_subdivision' => 9,
            ],
            
            // Guías Telescópicas (id_subdivision: 10)
            [
                'nombre' => 'Guía 45cm',
                'descripcion' => 'Guía telescópica de 45cm',
                'id_subdivision' => 10,
            ],
            
            // Rieles para Ventana (id_subdivision: 11)
            [
                'nombre' => 'Riel Aluminio',
                'descripcion' => 'Riel de aluminio para ventana corredera',
                'id_subdivision' => 11,
            ],
            
            // Puertas de Madera (id_subdivision: 12)
            [
                'nombre' => 'Puerta Placa',
                'descripcion' => 'Puerta placa de madera',
                'id_subdivision' => 12,
            ],
            
            // Puertas de Aluminio (id_subdivision: 13)
            [
                'nombre' => 'Puerta Vidriada',
                'descripcion' => 'Puerta de aluminio con vidrio',
                'id_subdivision' => 13,
            ],
            
            // Ventanas de Aluminio (id_subdivision: 14)
            [
                'nombre' => 'Ventana Corrediza',
                'descripcion' => 'Ventana corredera de aluminio',
                'id_subdivision' => 14,
            ],
            
            // Ventanas de Madera (id_subdivision: 15)
            [
                'nombre' => 'Ventana Batiente',
                'descripcion' => 'Ventana de madera con apertura batiente',
                'id_subdivision' => 15,
            ],
            
            // Sistemas de Aluminio (id_subdivision: 16)
            [
                'nombre' => 'Sistema Modena',
                'descripcion' => 'Sistema corredizo línea Modena',
                'id_subdivision' => 16,
            ],
            
            // Sistemas Mixtos (id_subdivision: 17)
            [
                'nombre' => 'Sistema Madera-Aluminio',
                'descripcion' => 'Sistema híbrido madera y aluminio',
                'id_subdivision' => 17,
            ]
        ];

        foreach ($categorias as $categoria) {
            Categoria::create($categoria);
        }
    }
}