<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cotizacion;
use App\Models\Empleado;
use App\Models\Empresa;
use App\Models\Estado;
use App\Models\Cambio;
use Illuminate\Support\Facades\DB;

class VendedorCotizacionController extends Controller
{
    /**
     * Mostrar la lista de cotizaciones del vendedor
     */
    public function index(Request $request)
    {
        // Obtener el empleado/vendedor actual (por ahora desde query param)
        $empleadoId = $request->get('empleado_id', 1);
        
        $vendedor = Empleado::find($empleadoId);
        if (!$vendedor) {
            abort(404, 'Vendedor no encontrado');
        }

        // Obtener todos los estados para el filtro
        $estados = Estado::orderBy('nombre')->get();

        // Construir query base para las cotizaciones del vendedor
        $query = Cotizacion::with(['empresa', 'empleado'])
            ->where('id_empleados', $empleadoId);

        // Aplicar filtros de búsqueda
        if ($request->filled('nropedido')) {
            $query->where('numero', 'like', '%' . $request->nropedido . '%');
        }

        if ($request->filled('cliente')) {
            $query->whereHas('empresa', function($q) use ($request) {
                $q->where('nombre', 'like', '%' . $request->cliente . '%');
            });
        }

        if ($request->filled('doc')) {
            $query->whereHas('empresa', function($q) use ($request) {
                $q->where('cuit', 'like', '%' . $request->doc . '%');
            });
        }

        // Filtro por estado
        if ($request->filled('estado')) {
            $query->whereHas('cambios', function($q) use ($request) {
                $q->where('id_estado', $request->estado)
                  ->whereIn('fyH', function($subQuery) {
                      $subQuery->select(DB::raw('MAX(fyH)'))
                               ->from('cambios as c2')
                               ->whereColumn('c2.id_cotizaciones', 'cambios.id_cotizaciones')
                               ->groupBy('c2.id_cotizaciones');
                  });
            });
        }

        // Ordenamiento
        $orderBy = $request->get('orderby', 'fecha');
        $orderDirection = $request->get('direction', 'desc');

        switch ($orderBy) {
            case 'estado':
                // Para ordenar por estado con orden personalizado: Nuevo -> Abierto -> Cotizado -> En entrega
                $query->leftJoin('cambios as ultimo_cambio', function($join) {
                    $join->on('cotizaciones.id', '=', 'ultimo_cambio.id_cotizaciones')
                         ->whereIn('ultimo_cambio.fyH', function($subQuery) {
                             $subQuery->select(DB::raw('MAX(fyH)'))
                                      ->from('cambios as c3')
                                      ->whereColumn('c3.id_cotizaciones', 'cotizaciones.id')
                                      ->groupBy('c3.id_cotizaciones');
                         });
                })
                ->leftJoin('estados', 'ultimo_cambio.id_estado', '=', 'estados.id_estado')
                ->orderByRaw("
                    CASE estados.nombre 
                        WHEN 'Nuevo' THEN 1 
                        WHEN 'Abierto' THEN 2 
                        WHEN 'Cotizado' THEN 3 
                        WHEN 'En entrega' THEN 4 
                        ELSE 5 
                    END " . ($orderDirection == 'desc' ? 'DESC' : 'ASC'))
                ->select('cotizaciones.*');
                break;
            case 'numero':
                $query->orderBy('numero', $orderDirection);
                break;
            case 'monto':
                $query->orderBy('precio_total', $orderDirection);
                break;
            default: // fecha
                $query->orderBy('fyh', $orderDirection);
                break;
        }

        // Obtener cotizaciones con paginación
        $cotizaciones = $query->paginate(15);

        // Para cada cotización, obtener su estado actual
        foreach ($cotizaciones as $cotizacion) {
            // Obtener el último cambio de estado
            $ultimoCambio = Cambio::where('id_cotizaciones', $cotizacion->id)
                ->with('estado')
                ->latest('fyH')
                ->first();
            
            $cotizacion->estado_actual = $ultimoCambio ? $ultimoCambio->estado : null;
        }

        // Obtener total para mostrar
        $total = Cotizacion::where('id_empleados', $empleadoId)->count();

        return view('vendedor.cotizaciones.index', compact(
            'cotizaciones', 
            'total', 
            'vendedor',
            'estados'
        ));
    }

    /**
     * Obtener color del estado para la vista
     */
    private function getEstadoColor($estadoNombre)
    {
        $colores = [
            'Nuevo' => '#C56C39',
            'Abierto' => '#F5EA5A', 
            'Cotizado' => '#54B66B',
            'En entrega' => '#3F5FFF'
        ];

        return $colores[$estadoNombre] ?? '#6B7280';
    }
}
