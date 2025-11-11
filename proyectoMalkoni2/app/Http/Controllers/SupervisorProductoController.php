<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SupervisorProductoController extends Controller
{
    /**
     * Mostrar la lista de productos
     */
    public function index()
    {
        // Aquí puedes cargar estadísticas y productos
        // Ejemplo:
        // $estadisticas = [
        //     'total_productos' => Producto::count(),
        //     'total_ventas' => Venta::count(),
        //     'ingresos_totales' => Venta::sum('total'),
        //     'sin_ventas' => Producto::whereDoesntHave('ventas')->count(),
        // ];
        // $productos = Producto::withCount('ventas')
        //     ->withSum('ventas', 'total')
        //     ->orderByDesc('ventas_count')
        //     ->paginate(10);
        
        return view('supervisor.productos.index');
    }

    /**
     * Buscar productos
     */
    public function search(Request $request)
    {
        $codigo = $request->get('codigo');
        $nombre = $request->get('nombre');
        
        // Aquí puedes agregar la lógica de búsqueda con la base de datos
        // Ejemplo:
        // $productos = Producto::query()
        //     ->when($codigo, fn($q) => $q->where('codigo', 'like', '%'.$codigo.'%'))
        //     ->when($nombre, fn($q) => $q->where('nombre', 'like', '%'.$nombre.'%'))
        //     ->withCount('ventas')
        //     ->withSum('ventas', 'total')
        //     ->orderByDesc('ventas_count')
        //     ->paginate(10);
        
        return view('supervisor.productos.index', compact('codigo', 'nombre'));
    }

    /**
     * Mostrar detalles de un producto específico
     */
    public function show($id)
    {
        // Aquí puedes cargar los detalles completos del producto
        // Ejemplo:
        // $producto = Producto::with(['ventas.cliente', 'categoria'])
        //     ->withCount('ventas')
        //     ->withSum('ventas', 'total')
        //     ->findOrFail($id);
        
        return view('supervisor.productos.show', ['productoId' => $id]);
    }

    /**
     * Mostrar estadísticas de ventas de un producto
     */
    public function estadisticas($id)
    {
        // Aquí puedes cargar estadísticas detalladas del producto
        // Ejemplo:
        // $producto = Producto::findOrFail($id);
        // $ventasPorMes = $producto->ventas()
        //     ->selectRaw('MONTH(created_at) as mes, COUNT(*) as cantidad, SUM(total) as ingresos')
        //     ->groupBy('mes')
        //     ->get();
        
        return view('supervisor.productos.estadisticas', ['productoId' => $id]);
    }
}