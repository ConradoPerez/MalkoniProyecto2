<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cotizacion;
use App\Models\Empleado;
use App\Models\Empresa;
use App\Models\Estado;
use App\Models\Cambio;
use App\Models\Item;
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

        // Filtro especial: pendientes (Nuevo o Abierto)
        if ($request->get('estado') === 'pendientes') {
            $estadosPendientes = Estado::whereIn('nombre', ['Nuevo', 'Abierto'])->pluck('id_estado');
            $query->whereHas('cambios', function($q) use ($estadosPendientes) {
                $q->whereIn('id_estado', $estadosPendientes)
                  ->whereIn('fyH', function($subQuery) {
                      $subQuery->select(DB::raw('MAX(fyH)'))
                               ->from('cambios as c2')
                               ->whereColumn('c2.id_cotizaciones', 'cambios.id_cotizaciones')
                               ->groupBy('c2.id_cotizaciones');
                  });
            });
        }
        // Filtro por estado
        elseif ($request->filled('estado')) {
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
        $cotizaciones = $query->paginate(10);

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

    /**
     * Mostrar el detalle de una cotización
     */
    public function detalle(Request $request, $id)
    {
        // Obtener el empleado/vendedor actual
        $empleadoId = $request->get('empleado_id', 1);
        
        $vendedor = Empleado::find($empleadoId);
        if (!$vendedor) {
            abort(404, 'Vendedor no encontrado');
        }

        // Obtener la cotización con sus relaciones
        $cotizacion = Cotizacion::with(['empresa', 'items.producto', 'cambios.estado'])
            ->where('id', $id)
            ->where('id_empleados', $empleadoId)
            ->firstOrFail();

        // Obtener el estado actual
        $ultimoCambio = Cambio::where('id_cotizaciones', $cotizacion->id)
            ->with('estado')
            ->latest('fyH')
            ->first();
        
        $cotizacion->estado_actual = $ultimoCambio ? $ultimoCambio->estado : null;

        // Si el estado es "Nuevo", cambiarlo automáticamente a "Abierto"
        if ($cotizacion->estado_actual && $cotizacion->estado_actual->nombre === 'Nuevo') {
            $estadoAbierto = Estado::where('nombre', 'Abierto')->first();
            
            if ($estadoAbierto) {
                Cambio::create([
                    'fyH' => now(),
                    'id_cotizaciones' => $cotizacion->id,
                    'id_estado' => $estadoAbierto->id_estado,
                ]);
                
                // Recargar el estado actual
                $cotizacion->refresh();
                $cotizacion->estado_actual = $estadoAbierto;
            }
        }

        // Colores para los estados
        $estadoColores = [
            'Nuevo' => 'bg-blue-100 text-blue-800',
            'Abierto' => 'bg-yellow-100 text-yellow-800',
            'Cotizado' => 'bg-green-100 text-green-800',
            'En entrega' => 'bg-purple-100 text-purple-800',
        ];

        return view('vendedor.cotizaciones.detalle', compact('cotizacion', 'vendedor', 'estadoColores'));
    }

    /**
     * Guardar los precios de la cotización
     */
    public function guardar(Request $request, $id)
    {
        // Obtener el empleado/vendedor actual
        $empleadoId = $request->get('empleado_id', 1);
        
        $vendedor = Empleado::find($empleadoId);
        if (!$vendedor) {
            abort(404, 'Vendedor no encontrado');
        }

        // Obtener la cotización
        $cotizacion = Cotizacion::where('id', $id)
            ->where('id_empleados', $empleadoId)
            ->firstOrFail();

        // Validar que haya items para actualizar
        $itemsData = $request->input('items', []);
        
        if (empty($itemsData)) {
            return redirect()->back()->with('error', 'No hay items para actualizar');
        }

        // Validar que todos los items tengan precio
        foreach ($itemsData as $itemId => $data) {
            $precioUnitario = floatval($data['precio_unitario'] ?? 0);
            if ($precioUnitario <= 0) {
                return redirect()->back()->with('error', 'Todos los productos deben tener un precio mayor a 0');
            }
        }

        DB::beginTransaction();
        
        try {
            $precioTotal = 0;

            // Actualizar precios de cada item
            foreach ($itemsData as $itemId => $data) {
                $item = Item::where('id_item', $itemId)
                    ->where('id_cotizaciones', $cotizacion->id)
                    ->first();
                
                if ($item) {
                    $precioUnitario = floatval($data['precio_unitario'] ?? 0);
                    $item->precio_unitario = $precioUnitario;
                    $item->save();
                    
                    $precioTotal += $precioUnitario * ($item->cantidad ?? 1);
                }
            }

            // Actualizar el precio total de la cotización
            $cotizacion->precio_total = $precioTotal;
            $cotizacion->fecha_cotizado = now();
            $cotizacion->save();

            // Cambiar el estado a "Cotizado"
            $estadoCotizado = Estado::where('nombre', 'Cotizado')->first();
            
            if ($estadoCotizado) {
                Cambio::create([
                    'fyH' => now(),
                    'id_cotizaciones' => $cotizacion->id,
                    'id_estado' => $estadoCotizado->id_estado,
                ]);
            }

            DB::commit();

            return redirect()->route('vendedor.app.cotizaciones.index', [
                'empleado_id' => $empleadoId
            ])->with('cotizacion_guardada', [
                'numero' => $cotizacion->numero,
                'titulo' => $cotizacion->titulo
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al guardar la cotización: ' . $e->getMessage());
        }
    }
}
