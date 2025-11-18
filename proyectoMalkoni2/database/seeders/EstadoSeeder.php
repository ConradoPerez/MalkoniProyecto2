<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Estado;

class EstadoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $estados = [
            [
                'nombre' => 'Nuevo',
                'descripcion' => 'Cotización recién creada por el cliente, esperando ser procesada por el vendedor',
                'fecha_hora' => now()
            ],
            [
                'nombre' => 'Abierto',
                'descripcion' => 'Cotización abierta por el vendedor, en proceso de elaboración o revisión',
                'fecha_hora' => now()
            ],
            [
                'nombre' => 'Cotizado',
                'descripcion' => 'Cotización completada con precios por el vendedor y lista para el cliente',
                'fecha_hora' => now()
            ],
            [
                'nombre' => 'En entrega',
                'descripcion' => 'Cotización aprobada por el cliente, productos en proceso de entrega',
                'fecha_hora' => now()
            ]
        ];

        foreach ($estados as $estado) {
            Estado::create($estado);
        }
    }
}