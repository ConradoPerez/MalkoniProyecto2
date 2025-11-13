<?php

namespace Database\Seeders;

use App\Models\Rubro;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RubroSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rubros = [
            [
                'nombre' => 'Materiales de Construcción',
                'descripcion' => 'Materiales básicos para construcción y obra',
            ],
            [
                'nombre' => 'Herrajes y Accesorios',
                'descripcion' => 'Herrajes, bisagras, cerraduras y accesorios metálicos',
            ],
            [
                'nombre' => 'Aberturas',
                'descripcion' => 'Puertas, ventanas y sistemas de abertura',
            ]
        ];

        foreach ($rubros as $rubro) {
            Rubro::create($rubro);
        }
    }
}