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
            // Maderas (id_categoria: 1)
            [
                'nombre' => 'Tabla de Pino 2x4x3m',
                'descripcion' => 'Tabla de pino cepillada, ideal para estructuras',
                'precio_base' => 8500,
                'promocion' => 0,
                'descuento' => 0,
                'precio_final' => 8500,
                'cant_cotizaciones' => 45,
                'id_categoria' => 1,
            ],
            [
                'nombre' => 'Listón de Eucalipto 5x5cm',
                'descripcion' => 'Listón de eucalipto tratado, 3 metros de largo',
                'precio_base' => 6200,
                'promocion' => 1,
                'descuento' => 10,
                'precio_final' => 5580,
                'cant_cotizaciones' => 32,
                'id_categoria' => 1,
            ],
            [
                'nombre' => 'Viga de Cedro 10x10cm',
                'descripcion' => 'Viga de cedro premium para estructuras nobles',
                'precio_base' => 25000,
                'promocion' => 0,
                'descuento' => 0,
                'precio_final' => 25000,
                'cant_cotizaciones' => 18,
                'id_categoria' => 1,
            ],

            // Herrajes (id_categoria: 2)
            [
                'nombre' => 'Cerradura Multipunto Premium',
                'descripcion' => 'Cerradura de seguridad con 3 puntos de anclaje',
                'precio_base' => 45000,
                'promocion' => 0,
                'descuento' => 0,
                'precio_final' => 45000,
                'cant_cotizaciones' => 28,
                'id_categoria' => 2,
            ],
            [
                'nombre' => 'Bisagra Piano 180cm',
                'descripcion' => 'Bisagra continua de acero inoxidable',
                'precio_base' => 12500,
                'promocion' => 1,
                'descuento' => 15,
                'precio_final' => 10625,
                'cant_cotizaciones' => 35,
                'id_categoria' => 2,
            ],
            [
                'nombre' => 'Manija de Bronce Clásica',
                'descripcion' => 'Manija de bronce con acabado patinado',
                'precio_base' => 8900,
                'promocion' => 0,
                'descuento' => 0,
                'precio_final' => 8900,
                'cant_cotizaciones' => 22,
                'id_categoria' => 2,
            ],

            // Aberturas (id_categoria: 3)
            [
                'nombre' => 'Puerta Placa con Marco',
                'descripcion' => 'Puerta placa 80cm con marco y premarco incluido',
                'precio_base' => 95000,
                'promocion' => 1,
                'descuento' => 8,
                'precio_final' => 87400,
                'cant_cotizaciones' => 52,
                'id_categoria' => 3,
            ],
            [
                'nombre' => 'Ventana Aluminio 120x110',
                'descripcion' => 'Ventana de aluminio con vidrio DVH incluido',
                'precio_base' => 78000,
                'promocion' => 0,
                'descuento' => 0,
                'precio_final' => 78000,
                'cant_cotizaciones' => 41,
                'id_categoria' => 3,
            ],
            [
                'nombre' => 'Portón de Garage 2x2m',
                'descripcion' => 'Portón seccional con motor automático',
                'precio_base' => 180000,
                'promocion' => 0,
                'descuento' => 0,
                'precio_final' => 180000,
                'cant_cotizaciones' => 15,
                'id_categoria' => 3,
            ],

            // Sistemas Corredizos (id_categoria: 4)
            [
                'nombre' => 'Riel Corredizo Pesado',
                'descripcion' => 'Sistema de riel para puertas hasta 80kg',
                'precio_base' => 35000,
                'promocion' => 1,
                'descuento' => 12,
                'precio_final' => 30800,
                'cant_cotizaciones' => 26,
                'id_categoria' => 4,
            ],
            [
                'nombre' => 'Guía Inferior Aluminio',
                'descripcion' => 'Guía inferior para ventanas corredizas',
                'precio_base' => 4500,
                'promocion' => 0,
                'descuento' => 0,
                'precio_final' => 4500,
                'cant_cotizaciones' => 38,
                'id_categoria' => 4,
            ],

            // Accesorios (id_categoria: 5)
            [
                'nombre' => 'Tornillo Tirafondo 8x120',
                'descripcion' => 'Caja x100 unidades, tornillos galvanizados',
                'precio_base' => 2800,
                'promocion' => 1,
                'descuento' => 20,
                'precio_final' => 2240,
                'cant_cotizaciones' => 67,
                'id_categoria' => 5,
            ],
            [
                'nombre' => 'Burlete de Goma Negro',
                'descripcion' => 'Rollo de 10 metros, burlete para puertas',
                'precio_base' => 1500,
                'promocion' => 0,
                'descuento' => 0,
                'precio_final' => 1500,
                'cant_cotizaciones' => 43,
                'id_categoria' => 5,
            ],

            // Vidrios (id_categoria: 6)
            [
                'nombre' => 'Vidrio Templado 6mm',
                'descripcion' => 'Metro cuadrado de vidrio templado transparente',
                'precio_base' => 15000,
                'promocion' => 0,
                'descuento' => 0,
                'precio_final' => 15000,
                'cant_cotizaciones' => 29,
                'id_categoria' => 6,
            ]
        ];

        foreach ($productos as $producto) {
            Producto::create($producto);
        }
    }
}