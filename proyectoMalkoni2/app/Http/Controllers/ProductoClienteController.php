<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Cotizacion;

class ProductoClienteController extends Controller
{
    /**
     * Muestra el formulario para agregar productos a una cotización.
     * Usado por el cliente al crear/editar una cotización.
     */
    public function agregarProducto(Request $request, $cotizacionId)
    {
        // Obtener el ID del cliente desde la request
        $clienteId = $request->get('cliente_id', 1);
        
        // Verificar que la cotización existe y pertenece al cliente
        $cotizacion = Cotizacion::where('id_personas', $clienteId)
            ->findOrFail($cotizacionId);

        // Obtener todas las categorías con sus subcategorías y productos
        $categorias = Categoria::with('subcategorias')->get();

        // Obtener todos los productos con sus relaciones
        $productos = Producto::with(['subcategoria' => function($query) {
            $query->with('categoria');
        }])->get();

        return view('cliente.productos.agregar', compact(
            'cotizacionId',
            'categorias',
            'productos',
            'cotizacion',
            'clienteId'
        ));
    }

    /**
     * Obtener productos por subcategoría (para AJAX)
     */
    public function obtenerPorCategoria($categoriaId)
    {
        $productos = Producto::whereHas('subcategoria', function ($query) use ($categoriaId) {
            $query->where('id_categoria', $categoriaId);
        })->get();

        return response()->json($productos);
    }

    /**
     * Buscar productos (para búsqueda en tiempo real)
     */
    public function buscar(Request $request)
    {
        $busqueda = $request->get('q', '');

        $productos = Producto::where(function ($query) use ($busqueda) {
            $query->where('nombre', 'like', "%$busqueda%")
                  ->orWhere('descripcion', 'like', "%$busqueda%")
                  ->orWhere('id_producto', 'like', "%$busqueda%");
        })->limit(20)->get();

        return response()->json($productos);
    }
}
