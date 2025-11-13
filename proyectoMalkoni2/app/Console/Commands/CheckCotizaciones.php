<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Empleado;
use App\Models\Cotizacion;

class CheckCotizaciones extends Command
{
    protected $signature = 'check:cotizaciones';
    protected $description = 'Verificar cuántas cotizaciones tiene cada vendedor';

    public function handle()
    {
        $this->info('=== Cotizaciones por Vendedor ===');
        $this->newLine();

        $vendedores = Empleado::vendedores()->with('cotizaciones')->get();

        foreach ($vendedores as $vendedor) {
            $count = $vendedor->cotizaciones->count();
            $this->line("{$vendedor->nombre}: {$count} cotizaciones");
        }

        $this->newLine();
        $this->info('=== Total de Cotizaciones ===');
        $total = Cotizacion::count();
        $this->line("Total en sistema: {$total} cotizaciones");

        return 0;
    }
}