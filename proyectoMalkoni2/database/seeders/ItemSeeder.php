<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Item;
use App\Models\Cotizacion;
use App\Models\Producto;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cotizaciones = Cotizacion::all();
        $productos = Producto::all();

        // Items para cada cotización (simulando proyectos realistas)
        $itemsData = [
            // Cotización 1: Reforma integral oficina comercial
            1 => [
                ['producto_id' => 7, 'cantidad' => 3], // Puerta Placa con Marco
                ['producto_id' => 8, 'cantidad' => 5], // Ventana Aluminio
                ['producto_id' => 4, 'cantidad' => 2], // Cerradura Multipunto
                ['producto_id' => 14, 'cantidad' => 15], // Vidrio Templado 6mm
            ],
            
            // Cotización 2: Aberturas para vivienda
            2 => [
                ['producto_id' => 7, 'cantidad' => 4], // Puertas
                ['producto_id' => 8, 'cantidad' => 8], // Ventanas
                ['producto_id' => 5, 'cantidad' => 12], // Bisagras
                ['producto_id' => 6, 'cantidad' => 4], // Manijas
            ],

            // Cotización 3: Muebles de cocina
            3 => [
                ['producto_id' => 1, 'cantidad' => 20], // Tabla de Pino
                ['producto_id' => 12, 'cantidad' => 50], // Tornillos
                ['producto_id' => 5, 'cantidad' => 8], // Bisagras
            ],

            // Cotización 4: Sistema corredizo
            4 => [
                ['producto_id' => 10, 'cantidad' => 2], // Riel Corredizo Pesado
                ['producto_id' => 11, 'cantidad' => 4], // Guía Inferior
                ['producto_id' => 12, 'cantidad' => 30], // Tornillos
            ],

            // Cotización 5: Herrajes premium
            5 => [
                ['producto_id' => 4, 'cantidad' => 1], // Cerradura Multipunto
                ['producto_id' => 5, 'cantidad' => 6], // Bisagras
                ['producto_id' => 6, 'cantidad' => 2], // Manijas
            ],

            // Cotización 6: Estructura de pérgola
            6 => [
                ['producto_id' => 1, 'cantidad' => 15], // Tabla de Pino
                ['producto_id' => 2, 'cantidad' => 8], // Listón Eucalipto
                ['producto_id' => 3, 'cantidad' => 4], // Viga de Cedro
                ['producto_id' => 12, 'cantidad' => 100], // Tornillos
            ],

            // Cotización 7: Vidrios temperados
            7 => [
                ['producto_id' => 14, 'cantidad' => 45], // Vidrio Templado
            ],

            // Cotización 8: Revestimiento en madera
            8 => [
                ['producto_id' => 1, 'cantidad' => 35], // Tabla de Pino
                ['producto_id' => 2, 'cantidad' => 20], // Listón Eucalipto
                ['producto_id' => 12, 'cantidad' => 200], // Tornillos
            ],
        ];

        // Crear items para las primeras 8 cotizaciones
        foreach ($itemsData as $cotizacionIndex => $items) {
            $cotizacion = $cotizaciones[$cotizacionIndex - 1];
            
            // Determinar si esta cotización tiene precio
            $tienePrecio = $cotizacion->precio_total && $cotizacion->precio_total > 0;
            
            foreach ($items as $itemData) {
                $producto = Producto::find($itemData['producto_id']);
                $precioUnitario = $tienePrecio && $producto ? rand(1000, 50000) : null;
                
                Item::create([
                    'cantidad' => $itemData['cantidad'],
                    'precio_unitario' => $precioUnitario,
                    'id_cotizaciones' => $cotizacion->id,
                    'id_Producto' => $itemData['producto_id'],
                    'id_servicio' => null,
                ]);
            }
        }

        // Para las cotizaciones restantes, agregar items aleatorios
        for ($i = 8; $i < $cotizaciones->count(); $i++) {
            $cotizacion = $cotizaciones[$i];
            $numItems = rand(2, 5);
            
            // Determinar si esta cotización tiene precio
            $tienePrecio = $cotizacion->precio_total && $cotizacion->precio_total > 0;
            
            for ($j = 0; $j < $numItems; $j++) {
                $producto = $productos->random();
                $cantidad = rand(1, 10);
                $precioUnitario = $tienePrecio ? rand(1000, 50000) : null;
                
                Item::create([
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precioUnitario,
                    'id_cotizaciones' => $cotizacion->id,
                    'id_Producto' => $producto->id_producto,
                    'id_servicio' => null,
                ]);
            }
        }
    }
}