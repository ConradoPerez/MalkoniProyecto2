<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Tipo;

class TipoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tipos = [
            [
                'nombre' => 'Maderas',
                'descripcion' => 'Productos de madera para construcción y carpintería'
            ],
            [
                'nombre' => 'Herrajes',
                'descripcion' => 'Herrajes y elementos de ferretería para carpintería'
            ],
            [
                'nombre' => 'Aberturas',
                'descripcion' => 'Puertas, ventanas y elementos de abertura'
            ],
            [
                'nombre' => 'Sistemas Corredizos',
                'descripcion' => 'Sistemas de rieles y mecanismos para puertas y ventanas corredizas'
            ],
            [
                'nombre' => 'Accesorios',
                'descripcion' => 'Accesorios complementarios para carpintería y construcción'
            ]
        ];

        foreach ($tipos as $tipo) {
            Tipo::create($tipo);
        }
    }
}