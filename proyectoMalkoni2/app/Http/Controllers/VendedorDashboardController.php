<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VendedorDashboardController extends Controller
{
    /**
     * Mostrar el dashboard principal del vendedor
     */
    public function index()
    {
        // Aquí puedes agregar lógica para obtener datos del vendedor
        // Por ejemplo: métricas, cotizaciones recientes, etc.
        
        $metrics = [
            'mis_cotizaciones' => 15,
            'mis_clientes' => 8,
            'ventas_del_mes' => 250000
        ];
        
        // También puedes obtener datos de la base de datos:
        // $misCotizaciones = Cotizacion::where('vendedor_id', auth()->id())->latest()->take(5)->get();
        // $cotizacionesPorEstado = Cotizacion::where('vendedor_id', auth()->id())->groupBy('estado_id')->count();
        
        return view('vendedor.dashboard', compact('metrics'));
    }
}
