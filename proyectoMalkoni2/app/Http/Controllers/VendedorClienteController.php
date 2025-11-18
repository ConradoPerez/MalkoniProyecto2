<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Empresa;
use App\Models\Cotizacion;
use App\Models\Empleado;
use Illuminate\Support\Facades\DB;

class VendedorClienteController extends Controller
{
    /**
     * Mostrar la lista de clientes del vendedor
     */
    public function index(Request $request)
    {
        // Obtener el ID del empleado desde la request (vendedor actual)
        $empleadoId = $request->get('empleado_id', 1);
        
        // Obtener información del vendedor
        $vendedor = Empleado::with('rol')->find($empleadoId);
        
        // Obtener parámetros de búsqueda
        $pedido = $request->get('pedido');
        $nombre = $request->get('nombre');
        $doc = $request->get('doc');
        
        // Consulta base: empresas que tienen cotizaciones del vendedor
        $query = Empresa::whereHas('cotizaciones', function($q) use ($empleadoId) {
            $q->where('id_empleados', $empleadoId);
        });
        
        // Filtro por número de pedido/cotización
        if ($pedido) {
            $query->whereHas('cotizaciones', function($q) use ($pedido, $empleadoId) {
                $q->where('id_empleados', $empleadoId)
                  ->where('numero', 'like', "%{$pedido}%");
            });
        }
        
        // Filtro por nombre de empresa
        if ($nombre) {
            $query->where('nombre', 'like', "%{$nombre}%");
        }
        
        // Filtro por CUIT
        if ($doc) {
            $query->where('cuit', 'like', "%{$doc}%");
        }
        
        // Obtener clientes con el conteo de cotizaciones del vendedor y estadísticas por estado
        $clientes = $query->withCount(['cotizaciones' => function($q) use ($empleadoId) {
            $q->where('id_empleados', $empleadoId);
        }])
        ->with(['cotizaciones' => function($q) use ($empleadoId) {
            $q->where('id_empleados', $empleadoId);
        }])
        ->orderBy('nombre')
        ->get();

        // Calcular estadísticas por estado para cada cliente
        foreach ($clientes as $cliente) {
            $estadisticas = [
                'Nuevo' => 0,
                'Abierto' => 0,
                'Cotizado' => 0,
                'En entrega' => 0
            ];
            
            foreach ($cliente->cotizaciones as $cotizacion) {
                $estadoActual = $cotizacion->getEstadoActualDirecto();
                $nombreEstado = $estadoActual ? $estadoActual->nombre : 'Nuevo';
                
                if (isset($estadisticas[$nombreEstado])) {
                    $estadisticas[$nombreEstado]++;
                }
            }
            
            $cliente->estadisticas_estados = $estadisticas;
        }
        
        return view('vendedor.clientes.index', compact('clientes', 'empleadoId', 'vendedor'));
    }

    /**
     * Mostrar las cotizaciones de un cliente específico
     */
    public function cotizaciones(Request $request, $empresaId)
    {
        // Obtener el ID del empleado desde la request (vendedor actual)
        $empleadoId = $request->get('empleado_id', 1);
        
        // Obtener información del vendedor
        $vendedor = Empleado::with('rol')->find($empleadoId);
        
        // Obtener la empresa
        $empresa = Empresa::findOrFail($empresaId);
        
        // Obtener cotizaciones del cliente para este vendedor
        $cotizaciones = Cotizacion::where('id_empresas', $empresaId)
            ->where('id_empleados', $empleadoId)
            ->orderBy('fyh', 'desc')
            ->get();
        
        return view('vendedor.clientes.cotizaciones', compact('empresa', 'cotizaciones', 'empleadoId', 'vendedor'));
    }

    /**
     * Mostrar la ficha detallada de un cliente
     */
    public function ficha(Request $request, $empresaId)
    {
        // Obtener el ID del empleado desde la request (vendedor actual)
        $empleadoId = $request->get('empleado_id', 1);
        
        // Obtener información del vendedor
        $vendedor = Empleado::with('rol')->find($empleadoId);
        
        // Obtener la empresa con sus grupos
        $empresa = Empresa::with('grupos')->findOrFail($empresaId);
        
        // Verificar que el vendedor tiene cotizaciones con este cliente
        $tieneCotizaciones = Cotizacion::where('id_empresas', $empresaId)
            ->where('id_empleados', $empleadoId)
            ->exists();
        
        if (!$tieneCotizaciones) {
            abort(403, 'No tienes acceso a este cliente');
        }
        
        // Obtener estadísticas del cliente para este vendedor
        $estadisticas = [
            'total_cotizaciones' => Cotizacion::where('id_empresas', $empresaId)
                ->where('id_empleados', $empleadoId)
                ->count(),
            'cotizaciones_mes' => Cotizacion::where('id_empresas', $empresaId)
                ->where('id_empleados', $empleadoId)
                ->esteMes()
                ->count(),
            'monto_total' => Cotizacion::where('id_empresas', $empresaId)
                ->where('id_empleados', $empleadoId)
                ->sum('precio_total'),
        ];
        
        return view('vendedor.clientes.ficha', compact('empresa', 'estadisticas', 'empleadoId', 'vendedor'));
    }
}
