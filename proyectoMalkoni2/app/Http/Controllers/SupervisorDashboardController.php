<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Empresa;
use App\Models\Persona;
use App\Models\Cotizacion;
use App\Models\Empleado;
use App\Models\Producto;
use App\Models\Estado;
use Illuminate\Support\Facades\DB;

class SupervisorDashboardController extends Controller
{
    public function index()
    {
        // Métricas principales actualizadas según nueva estructura
        $metrics = [
            // Total de clientes (empresas + personas)
            'clientes_digitalizados' => Empresa::count() + Persona::count(),
            
            // Cotizaciones en proceso según el estado actual
            'cotizaciones_proceso' => $this->getCotizacionesPorEstado('Abierto'),
            
            // Total facturado este mes
            'cotizaciones_este_mes' => Cotizacion::esteMes()->sum('precio_total'),
        ];

        // Datos para el gráfico de cotizaciones por vendedor
        $cotizacionesPorVendedor = Empleado::vendedores()
            ->withCount('cotizaciones')
            ->having('cotizaciones_count', '>', 0)
            ->orderByDesc('cotizaciones_count')
            ->limit(4)
            ->get();

        // Últimas cotizaciones con cliente (empresa o persona)
        $ultimasCotizaciones = Cotizacion::with(['empresa', 'persona', 'empleado'])
            ->orderByDesc('fyh')
            ->limit(6)
            ->get()
            ->map(function ($cotizacion) {
                // Agregar el estado actual y cliente unificado
                $cotizacion->estado_actual = $this->getEstadoActual($cotizacion->id);
                $cotizacion->cliente_nombre = $cotizacion->empresa 
                    ? $cotizacion->empresa->nombre 
                    : ($cotizacion->persona ? 'Cliente Persona' : 'Sin cliente');
                return $cotizacion;
            });

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

    /**
     * Obtener cotizaciones por estado usando la tabla de cambios
     */
    private function getCotizacionesPorEstado($nombreEstado)
    {
        return DB::table('cotizaciones')
            ->join('cambios', function($join) {
                $join->on('cotizaciones.id', '=', 'cambios.id_cotizaciones')
                     ->whereRaw('cambios.fyH = (
                         SELECT MAX(c2.fyH) 
                         FROM cambios c2 
                         WHERE c2.id_cotizaciones = cotizaciones.id
                     )');
            })
            ->join('estados', 'cambios.id_estado', '=', 'estados.id_estado')
            ->where('estados.nombre', $nombreEstado)
            ->count();
    }

    /**
     * Obtener el estado actual de una cotización
     */
    private function getEstadoActual($cotizacionId)
    {
        return DB::table('cambios')
            ->join('estados', 'cambios.id_estado', '=', 'estados.id_estado')
            ->where('cambios.id_cotizaciones', $cotizacionId)
            ->orderByDesc('cambios.fyH')
            ->select('estados.nombre', 'estados.descripcion')
            ->first();
    }

    /**
     * Helper para obtener el color de estado
     */
    public function getEstadoColor($nombreEstado)
    {
        return match(strtolower($nombreEstado)) {
            'nuevo' => '#D88429',
            'abierto' => '#166379',
            'cotizado' => '#22c55e',
            'en entrega' => '#B1B7BB',
            default => '#6B7280'
        };
    }
}