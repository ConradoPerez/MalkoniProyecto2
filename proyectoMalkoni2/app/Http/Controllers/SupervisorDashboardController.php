<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Empresa;
use App\Models\Cotizacion;
use App\Models\Empleado;
use App\Models\Producto;
use Illuminate\Support\Facades\DB;

class SupervisorDashboardController extends Controller
{
    public function index()
    {
        // Métricas principales
        $metrics = [
            'clientes_digitalizados' => Empresa::count(),
            'cotizaciones_proceso' => Cotizacion::porEstado('En Proceso')->count(),
            'cotizaciones_este_mes' => Cotizacion::esteMes()->sum('precio_total'),
        ];

        // Datos para el gráfico de cotizaciones por vendedor
        $cotizacionesPorVendedor = Empleado::vendedores()
            ->withCount('cotizaciones')
            ->having('cotizaciones_count', '>', 0)
            ->orderByDesc('cotizaciones_count')
            ->limit(4)
            ->get();

        // Últimas cotizaciones
        $ultimasCotizaciones = Cotizacion::with(['empresa', 'empleado', 'estadoActual'])
            ->orderByDesc('fyh')
            ->limit(6)
            ->get();

        // Ranking de productos más cotizados
        $productosRanking = Producto::where('cant_cotizaciones', '>', 0)
            ->orderByDesc('cant_cotizaciones')
            ->limit(6)
            ->get();

        return view('supervisor.dashboard', compact(
            'metrics',
            'cotizacionesPorVendedor', 
            'ultimasCotizaciones',
            'productosRanking'
        ));
    }
}