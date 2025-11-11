<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SupervisorVendedorController extends Controller
{
    /**
     * Mostrar la lista de vendedores
     */
    public function index()
    {
        return view('supervisor.vendedores.index');
    }

    /**
     * Buscar vendedores
     */
    public function search(Request $request)
    {
        $nombre = $request->get('nombre');
        $dni = $request->get('dni');
        
        // Aquí puedes agregar la lógica de búsqueda con la base de datos
        // Ejemplo:
        // $vendedores = Vendedor::query()
        //     ->when($nombre, fn($q) => $q->where('nombre', 'like', '%'.$nombre.'%'))
        //     ->when($dni, fn($q) => $q->where('dni', $dni))
        //     ->paginate(10);
        
        return view('supervisor.vendedores.index', compact('nombre', 'dni'));
    }

    /**
     * Mostrar clientes de un vendedor específico
     */
    public function clientes($id)
    {
        // Aquí puedes cargar los datos del vendedor y sus clientes
        // Ejemplo:
        // $vendedor = Vendedor::findOrFail($id);
        // $clientes = $vendedor->clientes()->paginate(10);
        
        return view('supervisor.vendedores.clientes', ['vendedorId' => $id]);
    }

    /**
     * Mostrar cotizaciones de un vendedor específico
     */
    public function cotizaciones($id)
    {
        // Aquí puedes cargar los datos del vendedor y sus cotizaciones
        // Ejemplo:
        // $vendedor = Vendedor::findOrFail($id);
        // $cotizaciones = $vendedor->cotizaciones()->with(['cliente', 'items'])->paginate(10);
        
        return view('supervisor.vendedores.cotizaciones', ['vendedorId' => $id]);
    }
}