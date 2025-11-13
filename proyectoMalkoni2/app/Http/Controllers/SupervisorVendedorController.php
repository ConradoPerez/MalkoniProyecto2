<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Empleado;

class SupervisorVendedorController extends Controller
{
    /**
     * Mostrar la lista de vendedores
     */
    public function index()
    {
        // Cargar todos los vendedores con paginación
        $vendedores = Empleado::vendedores()
            ->with('rol')
            ->orderBy('nombre')
            ->paginate(10);

        return view('supervisor.vendedores.index', compact('vendedores'));
    }

    /**
     * Buscar vendedores
     */
    public function search(Request $request)
    {
        $nombre = $request->get('nombre');
        $dni = $request->get('dni');
        
        // Construir consulta de búsqueda
        $query = Empleado::vendedores()->with('rol');
        
        // Aplicar filtros si existen
        if ($nombre) {
            $query->buscarPorNombre($nombre);
        }
        
        if ($dni) {
            $query->buscarPorDni($dni);
        }
        
        // Obtener resultados paginados
        $vendedores = $query->orderBy('nombre')->paginate(10);
        
        return view('supervisor.vendedores.index', compact('vendedores', 'nombre', 'dni'));
    }

    /**
     * Mostrar clientes de un vendedor específico
     */
    public function clientes($id)
    {
        // Verificar que el empleado existe y es vendedor
        $vendedor = Empleado::vendedores()
            ->where('id_empleado', $id)
            ->firstOrFail();
        
        // TODO: Implementar lógica para cargar clientes del vendedor
        // $clientes = $vendedor->clientes()->paginate(10);
        
        return view('supervisor.vendedores.clientes', compact('vendedor'));
    }

    /**
     * Mostrar cotizaciones de un vendedor específico
     */
    public function cotizaciones($id)
    {
        // Verificar que el empleado existe y es vendedor
        $vendedor = Empleado::vendedores()
            ->where('id_empleado', $id)
            ->firstOrFail();
        
        // TODO: Implementar lógica para cargar cotizaciones del vendedor
        // $cotizaciones = $vendedor->cotizaciones()->with(['cliente', 'items'])->paginate(10);
        
        return view('supervisor.vendedores.cotizaciones', compact('vendedor'));
    }
}