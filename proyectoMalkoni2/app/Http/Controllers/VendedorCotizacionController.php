<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VendedorCotizacionController extends Controller
{
    /**
     * Mostrar la lista de cotizaciones del vendedor
     */
    public function index(Request $request)
    {
        // Obtener parámetros de búsqueda
        $nroPedido = $request->get('nropedido');
        $cliente = $request->get('cliente');
        $doc = $request->get('doc');
        
        // Aquí puedes agregar lógica para filtrar cotizaciones
        // Ejemplo de consulta (cuando tengas los modelos):
        /*
        $query = Cotizacion::where('vendedor_id', auth()->id())->with(['empresa', 'estado']);
        
        if ($nroPedido) {
            $query->where('numero', 'like', "%{$nroPedido}%");
        }
        
        if ($cliente) {
            $query->whereHas('empresa', function($q) use ($cliente) {
                $q->where('nombre', 'like', "%{$cliente}%");
            });
        }
        
        if ($doc) {
            $query->whereHas('empresa', function($q) use ($doc) {
                $q->where('documento', 'like', "%{$doc}%");
            });
        }
        
        $cotizaciones = $query->latest()->paginate(15);
        $total = $query->count();
        */
        
        // Por ahora retornamos datos de ejemplo
        $cotizaciones = collect([
            ['numero' => '1001', 'cliente' => 'Juan Pérez', 'monto' => 100000, 'estado' => 'Cotizado', 'estado_color' => '#54B66B'],
            ['numero' => '1002', 'cliente' => 'María García', 'monto' => 200000, 'estado' => 'En entrega', 'estado_color' => '#3F5FFF'],
            ['numero' => '1003', 'cliente' => 'Carlos López', 'monto' => 150000, 'estado' => 'Abierto', 'estado_color' => '#F5EA5A'],
        ]);
        
        $total = 181;
        
        return view('vendedor.cotizaciones.index', compact('cotizaciones', 'total'));
    }
}
