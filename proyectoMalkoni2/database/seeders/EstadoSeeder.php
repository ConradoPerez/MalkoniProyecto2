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
                'descripcion' => 'Cotización recién creada, esperando ser procesada',
                'fecha_hora' => now()
            ],
            [
                'nombre' => 'Abierto',
                'descripcion' => 'Cotización en proceso de elaboración o revisión',
                'fecha_hora' => now()
            ],
            [
                'nombre' => 'Cotizado',
                'descripcion' => 'Cotización completada y enviada al cliente',
                'fecha_hora' => now()
            ],
            [
                'nombre' => 'En entrega',
                'descripcion' => 'Cotización aprobada, productos en proceso de entrega',
                'fecha_hora' => now()
            ]
        ];

        foreach ($estados as $estado) {
            Estado::create($estado);
        }
    }
}