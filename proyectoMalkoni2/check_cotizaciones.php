<?php

require_once 'vendor/autoload.php';
require_once 'bootstrap/app.php';

use App\Models\Empleado;

echo "=== Cotizaciones por Vendedor ===\n\n";

$vendedores = Empleado::vendedores()->with('cotizaciones')->get();

foreach ($vendedores as $vendedor) {
    $count = $vendedor->cotizaciones->count();
    echo "{$vendedor->nombre}: {$count} cotizaciones\n";
}

echo "\n=== Total de Cotizaciones ===\n";
$total = \App\Models\Cotizacion::count();
echo "Total en sistema: {$total} cotizaciones\n";