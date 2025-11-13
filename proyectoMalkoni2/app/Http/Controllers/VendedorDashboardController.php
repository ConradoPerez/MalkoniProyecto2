<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cotizacion;
use App\Models\Empleado;
use App\Models\Empresa;
use App\Models\Producto;
use App\Models\Item;
use App\Models\Estado;
use App\Models\Tipo;
use App\Models\Subtipo;
use App\Models\Categoria;
use App\Models\Subcategoria;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VendedorDashboardController extends Controller
{
    /**
     * Mostrar el dashboard principal del vendedor
     */
    public function index(Request $request)
    {
        // Por ahora simulo un empleado/vendedor específico (ID 1)
        // En un sistema real esto vendría de la autenticación
        $empleadoId = $request->get('empleado_id', 1);
        
        $vendedor = Empleado::with('rol')->find($empleadoId);
        
        if (!$vendedor) {
            abort(404, 'Vendedor no encontrado');
        }

        // Métricas principales del vendedor
        $metrics = [
            'clientes_digitalizados' => Empresa::whereHas('cotizaciones', function($q) use ($empleadoId) {
                $q->where('id_empleados', $empleadoId);
            })->count(),
            'cotizaciones_pendientes' => Cotizacion::where('id_empleados', $empleadoId)
                ->whereHas('cambios', function($q) {
                    $q->whereHas('estado', function($subQ) {
                        $subQ->where('nombre', 'Nuevo');
                    })
                    ->whereRaw('cambios.fyH = (
                        SELECT MAX(fyH) 
                        FROM cambios AS c2 
                        WHERE c2.id_cotizaciones = cambios.id_cotizaciones
                    )');
                })
                ->count(),
            'comisiones_mes' => Cotizacion::where('id_empleados', $empleadoId)
                ->esteMes()
                ->sum('precio_total'),
        ];

        // Cotizaciones por producto del vendedor (para gráfico de tortas)
        $cotizacionesPorProducto = DB::table('items')
            ->join('cotizaciones', 'items.id_cotizaciones', '=', 'cotizaciones.id')
            ->join('productos', 'items.id_producto', '=', 'productos.id_producto')
            ->join('subtipos', 'productos.id_subtipo', '=', 'subtipos.id_subtipo')
            ->join('tipos', 'subtipos.id_tipo', '=', 'tipos.id_tipo')
            ->where('cotizaciones.id_empleados', $empleadoId)
            ->whereNotNull('items.id_producto')
            ->select(
                'tipos.nombre as tipo',
                'subtipos.nombre as subtipo',
                'productos.nombre as producto_nombre',
                DB::raw('COUNT(DISTINCT cotizaciones.id) as total_cotizaciones'),
                DB::raw('SUM(items.cantidad) as total_cantidad')
            )
            ->groupBy('tipos.id_tipo', 'tipos.nombre', 'subtipos.id_subtipo', 'subtipos.nombre', 'productos.id_producto', 'productos.nombre')
            ->orderBy('total_cotizaciones', 'desc')
            ->limit(4)
            ->get();

        // Cotizaciones por tiempo (para gráfico de barras)
        $intervalo = $request->get('intervalo', '7dias');
        $cotizacionesPorTiempo = $this->getCotizacionesPorTiempo($empleadoId, $intervalo);

        // Últimas cotizaciones del vendedor
        $ultimasCotizaciones = Cotizacion::with(['empresa', 'estadoActual'])
            ->where('id_empleados', $empleadoId)
            ->orderByDesc('fyh')
            ->limit(6)
            ->get();

        // Ranking de productos más cotizados por este vendedor
        $productosRanking = DB::table('items')
            ->join('cotizaciones', 'items.id_cotizaciones', '=', 'cotizaciones.id')
            ->join('productos', 'items.id_producto', '=', 'productos.id_producto')
            ->where('cotizaciones.id_empleados', $empleadoId)
            ->whereNotNull('items.id_producto')
            ->select(
                'productos.id_producto',
                'productos.nombre',
                DB::raw('COUNT(DISTINCT cotizaciones.id) as total_cotizaciones')
            )
            ->groupBy('productos.id_producto', 'productos.nombre')
            ->orderBy('total_cotizaciones', 'desc')
            ->limit(5)
            ->get();
        
        return view('vendedor.dashboard', compact(
            'vendedor',
            'metrics',
            'cotizacionesPorProducto', 
            'cotizacionesPorTiempo',
            'ultimasCotizaciones',
            'productosRanking'
        ));
    }

    /**
     * Obtener cotizaciones por tiempo para gráfico de barras
     */
    private function getCotizacionesPorTiempo($empleadoId, $intervalo)
    {
        $query = Cotizacion::where('id_empleados', $empleadoId);
        
        switch($intervalo) {
            case '7dias':
                $fechaInicio = Carbon::now()->subDays(6)->startOfDay();
                return $query
                    ->where('fyh', '>=', $fechaInicio)
                    ->select(
                        DB::raw('DATE(fyh) as fecha'),
                        DB::raw('COUNT(*) as total')
                    )
                    ->groupBy('fecha')
                    ->orderBy('fecha')
                    ->get();
                    
            case '3meses':
                $fechaInicio = Carbon::now()->subMonths(3)->startOfMonth();
                return $query
                    ->where('fyh', '>=', $fechaInicio)
                    ->select(
                        DB::raw('DATE_FORMAT(fyh, "%Y-%m") as mes'),
                        DB::raw('COUNT(*) as total')
                    )
                    ->groupBy('mes')
                    ->orderBy('mes')
                    ->get();
                    
            case '6meses':
                $fechaInicio = Carbon::now()->subMonths(6)->startOfMonth();
                return $query
                    ->where('fyh', '>=', $fechaInicio)
                    ->select(
                        DB::raw('DATE_FORMAT(fyh, "%Y-%m") as mes'),
                        DB::raw('COUNT(*) as total')
                    )
                    ->groupBy('mes')
                    ->orderBy('mes')
                    ->get();
                    
            case '1ano':
                $fechaInicio = Carbon::now()->subYear()->startOfMonth();
                return $query
                    ->where('fyh', '>=', $fechaInicio)
                    ->select(
                        DB::raw('DATE_FORMAT(fyh, "%Y-%m") as mes'),
                        DB::raw('COUNT(*) as total')
                    )
                    ->groupBy('mes')
                    ->orderBy('mes')
                    ->get();
                    
            default:
                return collect();
        }
    }

    /**
     * Endpoint AJAX para obtener datos del gráfico de barras
     */
    public function getCotizacionesBarChart(Request $request)
    {
        $empleadoId = $request->get('empleado_id', 1);
        $intervalo = $request->get('intervalo', '7dias');
        
        $data = $this->getCotizacionesPorTiempo($empleadoId, $intervalo);
        
        // Formatear la respuesta para asegurar que siempre tenga la estructura correcta
        $formattedData = $data->map(function($item) use ($intervalo) {
            if ($intervalo === '7dias') {
                return [
                    'fecha' => $item->fecha,
                    'total' => (int)$item->total
                ];
            } else {
                return [
                    'mes' => $item->mes,
                    'total' => (int)$item->total
                ];
            }
        });
        
        return response()->json($formattedData);
    }
}
