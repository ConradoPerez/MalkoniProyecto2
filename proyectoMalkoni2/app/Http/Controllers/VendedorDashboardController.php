<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cotizacion;
use App\Models\Empleado;
use App\Models\Empresa;
use App\Models\Producto;
use App\Models\Item;
use App\Models\Estado;
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
            'cotizaciones_proceso' => Cotizacion::where('id_empleados', $empleadoId)
                ->whereHas('estadoActual', function($q) {
                    $q->where('nombre', 'En Proceso');
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
            ->join('categorias', 'productos.id_categoria', '=', 'categorias.id_categoria')
            ->where('cotizaciones.id_empleados', $empleadoId)
            ->whereNotNull('items.id_producto')
            ->select(
                'categorias.nombre as categoria',
                'productos.nombre as producto_nombre',
                DB::raw('COUNT(DISTINCT cotizaciones.id) as total_cotizaciones'),
                DB::raw('SUM(items.cantidad) as total_cantidad')
            )
            ->groupBy('categorias.id_categoria', 'categorias.nombre', 'productos.id_producto', 'productos.nombre')
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
                return $query
                    ->where('fyh', '>=', Carbon::now()->subDays(7))
                    ->select(
                        DB::raw('DATE(fyh) as fecha'),
                        DB::raw('COUNT(*) as total')
                    )
                    ->groupBy('fecha')
                    ->orderBy('fecha')
                    ->get();
                    
            case '3meses':
                return $query
                    ->where('fyh', '>=', Carbon::now()->subMonths(3))
                    ->select(
                        DB::raw('DATE_FORMAT(fyh, "%Y-%m") as mes'),
                        DB::raw('COUNT(*) as total')
                    )
                    ->groupBy('mes')
                    ->orderBy('mes')
                    ->get();
                    
            case '6meses':
                return $query
                    ->where('fyh', '>=', Carbon::now()->subMonths(6))
                    ->select(
                        DB::raw('DATE_FORMAT(fyh, "%Y-%m") as mes'),
                        DB::raw('COUNT(*) as total')
                    )
                    ->groupBy('mes')
                    ->orderBy('mes')
                    ->get();
                    
            case '1ano':
                return $query
                    ->where('fyh', '>=', Carbon::now()->subYear())
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
