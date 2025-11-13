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

        // Obtener cotizaciones con paginación
        $cotizaciones = $query->latest('fyh')->paginate(15);

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
            'vendedor'
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
