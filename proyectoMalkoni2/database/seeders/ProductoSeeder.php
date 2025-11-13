<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Producto;

class ProductoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $productos = [
            // Madera Maciza (id_subtipo: 1) - Premium Plus (id_subcategoria: 1)
            [
                'nombre' => 'Tabla de Pino 2x4x3m Premium',
                'descripcion' => 'Tabla de pino cepillada premium, ideal para estructuras',
                'precio_base' => 8500,
                'promocion' => 0,
                'descuento' => 0,
                'precio_final' => 8500,
                'id_subtipo' => 1,
                'id_subcategoria' => 1,
            ],
            [
                'nombre' => 'Listón de Eucalipto 5x5cm',
                'descripcion' => 'Listón de eucalipto tratado, 3 metros de largo',
                'precio_base' => 6200,
                'promocion' => 1,
                'descuento' => 10,
                'precio_final' => 5580,
                'id_subtipo' => 1,
                'id_subcategoria' => 2,
            ],
            [
                'nombre' => 'Viga de Cedro 10x10cm',
                'descripcion' => 'Viga de cedro premium para estructuras nobles',
                'precio_base' => 25000,
                'promocion' => 0,
                'descuento' => 0,
                'precio_final' => 25000,
                'id_subtipo' => 1,
                'id_subcategoria' => 1,
            ],

            // Tableros (id_subtipo: 2) - Estándar Plus (id_subcategoria: 3)
            [
                'nombre' => 'Tablero MDF 18mm',
                'descripcion' => 'Tablero MDF de 18mm, 2.60x1.83m',
                'precio_base' => 12000,
                'promocion' => 0,
                'descuento' => 0,
                'precio_final' => 12000,
                'id_subtipo' => 2,
                'id_subcategoria' => 3,
            ],
            [
                'nombre' => 'Melamina Blanca 15mm',
                'descripcion' => 'Tablero melamínico blanco, 2.75x1.83m',
                'precio_base' => 15500,
                'promocion' => 1,
                'descuento' => 5,
                'precio_final' => 14725,
                'id_subtipo' => 2,
                'id_subcategoria' => 3,
            ],

            // Molduras (id_subtipo: 3) - Interior Decorativo (id_subcategoria: 9)
            [
                'nombre' => 'Moldura Colonial 5cm',
                'descripcion' => 'Moldura decorativa estilo colonial, pino finger',
                'precio_base' => 2500,
                'promocion' => 0,
                'descuento' => 0,
                'precio_final' => 2500,
                'id_subtipo' => 3,
                'id_subcategoria' => 9,
            ],

            // Bisagras (id_subtipo: 4) - Premium Estándar (id_subcategoria: 2)
            [
                'nombre' => 'Bisagra Piano 180cm',
                'descripcion' => 'Bisagra continua de acero inoxidable',
                'precio_base' => 12500,
                'promocion' => 1,
                'descuento' => 15,
                'precio_final' => 10625,
                'id_subtipo' => 4,
                'id_subcategoria' => 2,
            ],

            // Cerraduras (id_subtipo: 5) - Premium Plus (id_subcategoria: 1)
            [
                'nombre' => 'Cerradura Multipunto Premium',
                'descripcion' => 'Cerradura de seguridad con 3 puntos de anclaje',
                'precio_base' => 45000,
                'promocion' => 0,
                'descuento' => 0,
                'precio_final' => 45000,
                'id_subtipo' => 5,
                'id_subcategoria' => 1,
            ],

            // Tornillería (id_subtipo: 6) - Estándar Básico (id_subcategoria: 4)
            [
                'nombre' => 'Tornillo Tirafondo 8x120',
                'descripcion' => 'Caja x100 unidades, tornillos galvanizados',
                'precio_base' => 2800,
                'promocion' => 1,
                'descuento' => 20,
                'precio_final' => 2240,
                'id_subtipo' => 6,
                'id_subcategoria' => 4,
            ],

            // Manijas (id_subtipo: 7) - Estándar Plus (id_subcategoria: 3)
            [
                'nombre' => 'Manija de Bronce Clásica',
                'descripcion' => 'Manija de bronce con acabado patinado',
                'precio_base' => 8900,
                'promocion' => 0,
                'descuento' => 0,
                'precio_final' => 8900,
                'id_subtipo' => 7,
                'id_subcategoria' => 3,
            ],

            // Puertas (id_subtipo: 8) - Interior Funcional (id_subcategoria: 10)
            [
                'nombre' => 'Puerta Placa con Marco',
                'descripcion' => 'Puerta placa 80cm con marco y premarco incluido',
                'precio_base' => 95000,
                'promocion' => 1,
                'descuento' => 8,
                'precio_final' => 87400,
                'id_subtipo' => 8,
                'id_subcategoria' => 10,
            ],

            // Ventanas (id_subtipo: 9) - Exterior Estándar (id_subcategoria: 8)
            [
                'nombre' => 'Ventana Aluminio 120x110',
                'descripcion' => 'Ventana de aluminio con vidrio DVH incluido',
                'precio_base' => 78000,
                'promocion' => 0,
                'descuento' => 0,
                'precio_final' => 78000,
                'id_subtipo' => 9,
                'id_subcategoria' => 8,
            ],

            // Marcos (id_subtipo: 10) - Estándar Plus (id_subcategoria: 3)
            [
                'nombre' => 'Marco de Pino para Puerta',
                'descripcion' => 'Marco de pino finger joint, medida estándar',
                'precio_base' => 18500,
                'promocion' => 0,
                'descuento' => 0,
                'precio_final' => 18500,
                'id_subtipo' => 10,
                'id_subcategoria' => 3,
            ],

            // Rieles (id_subtipo: 11) - Exterior Resistente (id_subcategoria: 7)
            [
                'nombre' => 'Riel Corredizo Pesado',
                'descripcion' => 'Sistema de riel para puertas hasta 80kg',
                'precio_base' => 35000,
                'promocion' => 1,
                'descuento' => 12,
                'precio_final' => 30800,
                'id_subtipo' => 11,
                'id_subcategoria' => 7,
            ],

            // Rodamientos (id_subtipo: 12) - Estándar Básico (id_subcategoria: 4)
            [
                'nombre' => 'Rodamiento para Corredizo',
                'descripcion' => 'Rodamiento de acero inoxidable con ruleman',
                'precio_base' => 4500,
                'promocion' => 0,
                'descuento' => 0,
                'precio_final' => 4500,
                'id_subtipo' => 12,
                'id_subcategoria' => 4,
            ],

            // Guías (id_subtipo: 13) - Estándar Plus (id_subcategoria: 3)
            [
                'nombre' => 'Guía Inferior Aluminio',
                'descripcion' => 'Guía inferior para ventanas corredizas',
                'precio_base' => 4500,
                'promocion' => 0,
                'descuento' => 0,
                'precio_final' => 4500,
                'id_subtipo' => 13,
                'id_subcategoria' => 3,
            ],

            // Adhesivos (id_subtipo: 14) - Estándar Básico (id_subcategoria: 4)
            [
                'nombre' => 'Cola Vinílica 1kg',
                'descripcion' => 'Adhesivo PVA para madera, secado rápido',
                'precio_base' => 1800,
                'promocion' => 1,
                'descuento' => 10,
                'precio_final' => 1620,
                'id_subtipo' => 14,
                'id_subcategoria' => 4,
            ],

            // Acabados (id_subtipo: 15) - Premium Estándar (id_subcategoria: 2)
            [
                'nombre' => 'Barniz Marino Transparente',
                'descripcion' => 'Barniz poliuretánico para exteriores, 1 litro',
                'precio_base' => 8500,
                'promocion' => 0,
                'descuento' => 0,
                'precio_final' => 8500,
                'id_subtipo' => 15,
                'id_subcategoria' => 2,
            ],

            // Herramientas (id_subtipo: 16) - Estándar Plus (id_subcategoria: 3)
            [
                'nombre' => 'Formón 25mm Profesional',
                'descripcion' => 'Formón de acero templado con mango de madera',
                'precio_base' => 5200,
                'promocion' => 0,
                'descuento' => 0,
                'precio_final' => 5200,
                'id_subtipo' => 16,
                'id_subcategoria' => 3,
            ]
        ];

        foreach ($productos as $producto) {
            Producto::create($producto);
        }
    }
}