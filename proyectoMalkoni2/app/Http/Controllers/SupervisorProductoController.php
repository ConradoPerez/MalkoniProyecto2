<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;

class SupervisorProductoController extends Controller
{
    /**
     * Mostrar la lista de productos
     */
    public function index(Request $request)
    {
        // Cargar estadísticas básicas
        $estadisticas = [
            'total_productos' => Producto::count(),
            'total_cotizaciones' => Producto::sum('cant_cotizaciones'),
            'ingresos_totales' => Producto::selectRaw('SUM(precio_final * cant_cotizaciones) as total')->value('total') ?? 0,
        ];

        // Obtener parámetro de ordenamiento
        $ordenar = $request->get('ordenar', 'mas_vendidos');
        
        // Construir consulta con ordenamiento
        $query = Producto::with(['subtipo', 'subcategoria']);
        
        switch ($ordenar) {
            case 'codigo':
                $query->orderBy('id_producto');
                break;
            case 'nombre':
                $query->orderBy('nombre');
                break;
            case 'ingresos':
                $query->orderByDesc('precio_final');
                break;
            case 'mas_vendidos':
            default:
                $query->orderByDesc('cant_cotizaciones');
                break;
        }

        // Cargar productos con paginación
        $productos = $query->paginate(10);
        
        return view('supervisor.productos.index', compact('productos', 'estadisticas', 'ordenar'));
    }

    /**
     * Buscar productos
     */
    public function search(Request $request)
    {
        $codigo = $request->get('codigo');
        $nombre = $request->get('nombre');
        $ordenar = $request->get('ordenar', 'mas_vendidos');
        
        // Construir consulta de búsqueda
        $query = Producto::with(['subtipo', 'subcategoria']);
        
        // Aplicar filtros si existen
        if ($codigo) {
            $query->where('id_producto', $codigo);
        }
        
        if ($nombre) {
            $query->where('nombre', 'like', '%' . $nombre . '%');
        }
        
        // Aplicar ordenamiento
        switch ($ordenar) {
            case 'codigo':
                $query->orderBy('id_producto');
                break;
            case 'nombre':
                $query->orderBy('nombre');
                break;
            case 'ingresos':
                $query->orderByDesc('precio_final');
                break;
            case 'mas_vendidos':
            default:
                $query->orderByDesc('cant_cotizaciones');
                break;
        }
        
        // Obtener resultados paginados
        $productos = $query->paginate(10);
        
        // Cargar estadísticas básicas
        $estadisticas = [
            'total_productos' => Producto::count(),
            'total_cotizaciones' => Producto::sum('cant_cotizaciones'),
            'ingresos_totales' => Producto::sum('precio_final'),
        ];
        
        return view('supervisor.productos.index', compact('productos', 'estadisticas', 'codigo', 'nombre', 'ordenar'));
    }

    /**
     * Mostrar detalles de un producto específico
     */
    public function show($id)
    {
        // Cargar el producto con sus relaciones
        $producto = Producto::with(['subtipo', 'subcategoria'])
            ->where('id_producto', $id)
            ->firstOrFail();
        
        return view('supervisor.productos.show', compact('producto'));
    }

    /**
     * Mostrar estadísticas de ventas de un producto
     */
    public function estadisticas($id)
    {
        // Cargar el producto
        $producto = Producto::with(['subtipo', 'subcategoria'])
            ->where('id_producto', $id)
            ->firstOrFail();
        
        // TODO: Implementar estadísticas detalladas cuando tengas modelo de ventas
        
        return view('supervisor.productos.estadisticas', compact('producto'));
    }
}