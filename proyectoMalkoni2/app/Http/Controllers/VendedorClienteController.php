<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VendedorClienteController extends Controller
{
    /**
     * Mostrar la lista de clientes del vendedor
     */
    public function index(Request $request)
    {
        // Obtener parámetros de búsqueda
        $pedido = $request->get('pedido');
        $nombre = $request->get('nombre');
        $doc = $request->get('doc');
        
        // Aquí puedes agregar lógica para filtrar clientes
        // Ejemplo de consulta (cuando tengas los modelos):
        /*
        $query = Empresa::where('vendedor_id', auth()->id());
        
        if ($pedido) {
            $query->whereHas('cotizaciones', function($q) use ($pedido) {
                $q->where('numero', 'like', "%{$pedido}%");
            });
        }
        
        if ($nombre) {
            $query->where('nombre', 'like', "%{$nombre}%");
        }
        
        if ($doc) {
            $query->where('documento', 'like', "%{$doc}%");
        }
        
        $clientes = $query->withCount('cotizaciones')->paginate(10);
        */
        
        // Por ahora retornamos datos de ejemplo
        $clientes = collect([
            ['nombre' => 'Juan Pérez', 'doc' => '11222333', 'count' => 2],
            ['nombre' => 'María García', 'doc' => '44555666', 'count' => 1],
            ['nombre' => 'Carlos López', 'doc' => '77888999', 'count' => 4],
        ]);
        
        return view('vendedor.clientes.index', compact('clientes'));
    }
}
