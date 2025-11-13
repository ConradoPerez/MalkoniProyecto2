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
            [
                'nombre' => 'Calidad Premium',
                'descripcion' => 'Productos de alta calidad y acabado premium'
            ],
            [
                'nombre' => 'Calidad Estándar',
                'descripcion' => 'Productos de calidad estándar para uso general'
            ],
            [
                'nombre' => 'Calidad Económica',
                'descripcion' => 'Productos económicos para presupuestos ajustados'
            ],
            [
                'nombre' => 'Para Exterior',
                'descripcion' => 'Productos especialmente diseñados para uso en exteriores'
            ],
            [
                'nombre' => 'Para Interior',
                'descripcion' => 'Productos para uso exclusivo en interiores'
            ],
            [
                'nombre' => 'Resistente al Agua',
                'descripcion' => 'Productos con tratamiento resistente a la humedad'
            ],
            [
                'nombre' => 'Tratado',
                'descripcion' => 'Productos con tratamientos especiales'
            ],
            [
                'nombre' => 'Natural',
                'descripcion' => 'Productos sin tratamiento, en estado natural'
            ]
        ];

        foreach ($categorias as $categoria) {
            Categoria::create($categoria);
        }
    }
}