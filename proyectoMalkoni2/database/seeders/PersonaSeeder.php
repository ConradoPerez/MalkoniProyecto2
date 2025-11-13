<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Persona;

class PersonaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // La tabla personas solo tiene foto y token_opt según la migración
        // Los datos de contacto van en la tabla de empresas o empleados
        $personas = [
            ['foto' => null, 'token_opt' => null],
            ['foto' => null, 'token_opt' => null],
            ['foto' => null, 'token_opt' => null],
            ['foto' => null, 'token_opt' => null],
            ['foto' => null, 'token_opt' => null],
            ['foto' => null, 'token_opt' => null],
            ['foto' => null, 'token_opt' => null],
            ['foto' => null, 'token_opt' => null],
        ];

        foreach ($personas as $persona) {
            Persona::create($persona);
        }
    }
}