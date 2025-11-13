<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Subcategoria;

class SubcategoriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subcategorias = [
            // Calidad Premium (id_categoria = 1)
            [
                'nombre' => 'Premium Plus',
                'descripcion' => 'Máxima calidad con garantía extendida',
                'id_categoria' => 1
            ],
            [
                'nombre' => 'Premium Estándar',
                'descripcion' => 'Alta calidad para proyectos exigentes',
                'id_categoria' => 1
            ],
            
            // Calidad Estándar (id_categoria = 2)
            [
                'nombre' => 'Estándar Plus',
                'descripcion' => 'Calidad superior dentro del rango estándar',
                'id_categoria' => 2
            ],
            [
                'nombre' => 'Estándar Básico',
                'descripcion' => 'Calidad confiable para uso general',
                'id_categoria' => 2
            ],
            
            // Calidad Económica (id_categoria = 3)
            [
                'nombre' => 'Económico Superior',
                'descripcion' => 'Mejor relación calidad-precio en la línea económica',
                'id_categoria' => 3
            ],
            [
                'nombre' => 'Económico Básico',
                'descripcion' => 'Opción más accesible para presupuestos limitados',
                'id_categoria' => 3
            ],
            
            // Para Exterior (id_categoria = 4)
            [
                'nombre' => 'Exterior Resistente',
                'descripcion' => 'Máxima resistencia a condiciones climáticas adversas',
                'id_categoria' => 4
            ],
            [
                'nombre' => 'Exterior Estándar',
                'descripcion' => 'Adecuado para uso en exteriores protegidos',
                'id_categoria' => 4
            ],
            
            // Para Interior (id_categoria = 5)
            [
                'nombre' => 'Interior Decorativo',
                'descripcion' => 'Enfocado en acabados estéticos para interiores',
                'id_categoria' => 5
            ],
            [
                'nombre' => 'Interior Funcional',
                'descripcion' => 'Prioriza funcionalidad sobre decoración',
                'id_categoria' => 5
            ],
            
            // Resistente al Agua (id_categoria = 6)
            [
                'nombre' => 'Impermeable Total',
                'descripcion' => 'Resistencia total a la humedad y agua',
                'id_categoria' => 6
            ],
            [
                'nombre' => 'Resistente Humedad',
                'descripcion' => 'Resistencia básica a la humedad',
                'id_categoria' => 6
            ],
            
            // Tratado (id_categoria = 7)
            [
                'nombre' => 'Tratamiento Especial',
                'descripcion' => 'Con tratamientos químicos especializados',
                'id_categoria' => 7
            ],
            [
                'nombre' => 'Tratamiento Básico',
                'descripcion' => 'Tratamiento estándar preventivo',
                'id_categoria' => 7
            ],
            
            // Natural (id_categoria = 8)
            [
                'nombre' => 'Natural Premium',
                'descripcion' => 'Sin tratamiento, máxima calidad natural',
                'id_categoria' => 8
            ],
            [
                'nombre' => 'Natural Estándar',
                'descripcion' => 'Sin tratamiento, calidad natural básica',
                'id_categoria' => 8
            ]
        ];

        foreach ($subcategorias as $subcategoria) {
            Subcategoria::create($subcategoria);
        }
    }
}