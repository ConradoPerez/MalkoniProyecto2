<?php

require 'c:/Users/Mati/Downloads/MalkoniProyecto2/proyectoMalkoni2/vendor/autoload.php';
$app = require_once 'c:/Users/Mati/Downloads/MalkoniProyecto2/proyectoMalkoni2/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\MensajeCotizacion;
use App\Models\Empleado;

$vendedores = Empleado::whereHas('rol', function($q) {
    $q->where('nombre', 'vendedor');
})->get();

foreach ($vendedores as $vendedor) {
    $empleadoId = $vendedor->id_empleado;
    $count = MensajeCotizacion::whereHas('cotizacion', fn($q) => $q->where('id_empleados', $empleadoId))
        ->where('sender_type', 'cliente')
        ->where('leido', false)
        ->count();
        
    echo "Vendedor ID: {$empleadoId}, Nombre: {$vendedor->nombre}, Mensajes sin leer: {$count}" . PHP_EOL;
}
