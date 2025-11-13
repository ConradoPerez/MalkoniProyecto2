<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Empresa;

class EmpresaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // La tabla empresas solo tiene nombre, cuit y foto según la migración
        $empresas = [
            [
                'nombre' => 'Constructora del Sur S.A.',
                'cuit' => 30123456789,
                'foto' => null,
            ],
            [
                'nombre' => 'OPM Construcciones',
                'cuit' => 30234567890,
                'foto' => null,
            ],
            [
                'nombre' => 'DIN Propiedades',
                'cuit' => 30345678901,
                'foto' => null,
            ],
            [
                'nombre' => 'CIR Maderas',
                'cuit' => 30456789012,
                'foto' => null,
            ],
            [
                'nombre' => 'MAO Muebles',
                'cuit' => 27567890123,
                'foto' => null,
            ],
            [
                'nombre' => 'RIC Construcciones',
                'cuit' => 30678901234,
                'foto' => null,
            ],
            [
                'nombre' => 'Premium Aberturas',
                'cuit' => 30789012345,
                'foto' => null,
            ],
            [
                'nombre' => 'EcoArq',
                'cuit' => 30890123456,
                'foto' => null,
            ]
        ];

        foreach ($empresas as $empresa) {
            Empresa::create($empresa);
        }
    }
}