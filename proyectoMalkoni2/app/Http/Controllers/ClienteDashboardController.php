<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Empresa;
use App\Models\Cotizacion;
use App\Models\Empleado; // Importado para cargar vendedores
use App\Models\Producto;
use App\Models\Item; // Importado para gestionar items de cotizaciones
use Illuminate\Support\Facades\DB;

class ClienteDashboardController extends Controller
{
    /**
     * Muestra la vista principal del Dashboard del Cliente.
     */
    public function dashboard()
    {
        // Obtener el ID del cliente autenticado.
        $clienteId = auth()->id(); 

        // 1. Obtener las últimas cotizaciones para la tabla del dashboard del cliente.
        $ultimasCotizaciones = Cotizacion::with(['empresa', 'empleado', 'estadoActual'])
            // Filtra solo las cotizaciones del cliente autenticado (usando id_personas)
            ->where('id_personas', $clienteId) 
            ->orderByDesc('fyh')
            ->limit(5)
            ->get();
        
        return view('cliente.dashboard', compact(
            'ultimasCotizaciones'
        ));
    }
    
    // =========================================================================
    // MÉTODOS DE NAVEGACIÓN Y COTIZACIONES
    // =========================================================================

    public function cotizaciones()
    {
        // Obtener todas las cotizaciones del cliente autenticado
        $cotizaciones = Cotizacion::with(['empresa', 'empleado', 'estadoActual'])
            ->where('id_personas', auth()->id())
            ->orderByDesc('fyh')
            ->paginate(10);

        return view('cliente.cotizaciones.index', compact('cotizaciones'));
    }
    
    /**
     * Muestra el formulario para iniciar una nueva cotización.
     * Carga la lista de vendedores disponibles.
     */
    public function createQuotation()
    {
        // 1. Obtener los empleados que tienen rol de VENDEDOR
        // Asumiendo que existe un scope 'vendedores()' en tu modelo Empleado.
        // NOTA: Debes implementar Empleado::vendedores()
        $vendedores = Empleado::vendedores()->get(['id_empleado', 'nombre', 'foto']);
        
        // Asignamos un número de pedido temporal (esto debería ser secuencial en producción)
        $numero_pedido = rand(10000, 99999); 

        return view('cliente.cotizaciones.create', compact('vendedores', 'numero_pedido'));
    }

    /**
     * Muestra la vista para agregar productos a una cotización.
     * Carga la lista de productos disponibles.
     */
    public function addProductsToQuotation($id)
    {
        // Asegura que solo pueda editar sus propias cotizaciones
        $cotizacion = Cotizacion::where('id_personas', auth()->id())->findOrFail($id);
        
        // Obtener todos los productos disponibles
        $productos = Producto::all(['id_producto', 'nombre', 'descripcion', 'precio_base', 'descuento', 'precio_final', 'foto']);
        
        // Obtener items ya agregados a esta cotización
        $itemsAgregados = $cotizacion->items()->with('producto')->get();
        
        return view('cliente.cotizaciones.agregar_productos', compact(
            'cotizacion',
            'productos',
            'itemsAgregados'
        ));
    }

    /**
     * Guarda la nueva cotización inicial en la base de datos.
     * (Método POST llamado desde el formulario 'Nueva Cotización')
     */
    public function storeQuotation(Request $request)
    {
        // 1. Validación de datos
        $validated = $request->validate([
            'id_empleados' => 'required|exists:empleados,id_empleado',
            'mensaje_inicial' => 'nullable|string|max:1000',
            'numero_pedido' => 'required|integer' 
        ]);
        
        // 2. Creación de la Cotización
        try {
            $cotizacion = DB::transaction(function () use ($validated) {
                // Creamos la cotización
                $cotizacion = Cotizacion::create([
                    'titulo' => 'Cotización #' . $validated['numero_pedido'], 
                    'numero' => $validated['numero_pedido'], 
                    'fyh' => now(),
                    'precio_total' => 0, // Inicia en 0
                    'id_empleados' => $validated['id_empleados'], // Vendedor elegido
                    'id_personas' => auth()->id(), // Cliente autenticado
                    // id_empresas queda en NULL si el usuario es una persona (que asumimos)
                ]);
                
                // Aquí podrías guardar el mensaje inicial en una tabla de mensajes si lo deseas.
                
                return $cotizacion;
            });
            
            // 3. Redirigir a la vista de "Agregar Productos"
            return redirect()->route('cliente.agregar_productos_catalogo', ['cotizacionId' => $cotizacion->id])
                             ->with('success', 'Cotización iniciada con éxito. ¡Añade productos!');
                             
        } catch (\Exception $e) {
            // Manejo de errores de base de datos o lógica
            return back()->with('error', 'Error al iniciar la cotización: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // MÉTODOS DEL SIDEBAR Y ACCIONES DE LA TABLA
    // =========================================================================

    public function messages()
    {
        return view('cliente.mensajes.index');
    }

    public function completedOrders()
    {
        return view('cliente.pedidos.realizados');
    }

    public function unquotedOrders()
    {
        return view('cliente.pedidos.sin_cotizar');
    }

    public function deliveryOrders()
    {
        return view('cliente.pedidos.en_entrega');
    }

    public function viewQuotation($id)
    {
        // Asegura que solo pueda ver sus propias cotizaciones
        $cotizacion = Cotizacion::where('id_personas', auth()->id())
            ->with(['empleado', 'empresa', 'persona', 'items.producto'])
            ->findOrFail($id);
        return view('cliente.cotizaciones.show', compact('cotizacion'));
    }

    public function editQuotation($id)
    {
        // Asegura que solo pueda editar sus propias cotizaciones
        $cotizacion = Cotizacion::where('id_personas', auth()->id())->findOrFail($id);
        return view('cliente.cotizaciones.edit', compact('cotizacion'));
    }

    /**
     * Guarda productos/servicios a una cotización existente.
     * (Método POST llamado desde el formulario 'Agregar Productos')
     */
    public function storeProductsToQuotation(Request $request, $id)
    {
        // Asegura que solo pueda editar sus propias cotizaciones
        $cotizacion = Cotizacion::where('id_personas', auth()->id())->findOrFail($id);

        // Obtener solo los productos con cantidad > 0
        $productosConCantidad = [];
        $productosData = $request->input('productos', []);
        
        foreach ($productosData as $index => $producto) {
            if (isset($producto['cantidad']) && (int)$producto['cantidad'] > 0) {
                $productosConCantidad["productos.$index"] = $producto;
            }
        }

        // Validar que hay al menos 1 producto con cantidad > 0
        if (empty($productosConCantidad)) {
            return back()->with('error', 'Debes seleccionar al menos un producto con cantidad mayor a 0.');
        }

        // Validar cada producto seleccionado
        $request->validate([
            'productos.*.id_producto' => 'required|exists:productos,id_producto',
        ]);

        try {
            $precioTotal = 0;

            DB::transaction(function () use ($productosData, $cotizacion, &$precioTotal) {
                foreach ($productosData as $productoData) {
                    $cantidad = (int)($productoData['cantidad'] ?? 0);
                    
                    // Solo procesar si cantidad > 0
                    if ($cantidad <= 0) {
                        continue;
                    }
                    
                    // Obtener el producto
                    $producto = Producto::findOrFail($productoData['id_producto']);

                    // Crear el item
                    $item = Item::create([
                        'cantidad' => $cantidad,
                        'id_cotizaciones' => $cotizacion->id,
                        'id_producto' => $producto->id_producto,
                    ]);

                    // Sumar al precio total
                    $precioTotal += ($producto->precio_final * $cantidad);
                }

                // Actualizar el precio total de la cotización
                $cotizacion->update([
                    'precio_total' => $precioTotal,
                ]);
            });

            return redirect()->route('cliente.cotizacion.ver', ['id' => $cotizacion->id])
                           ->with('success', 'Productos agregados a la cotización exitosamente.');

        } catch (\Exception $e) {
            return back()->with('error', 'Error al agregar productos: ' . $e->getMessage());
        }
    }

    /**
     * Elimina un item de una cotización.
     */
    public function removeProductFromQuotation($cotizacionId, $itemId)
    {
        // Verificar seguridad: solo el cliente propietario puede eliminar
        $cotizacion = Cotizacion::where('id_personas', auth()->id())->findOrFail($cotizacionId);
        
        // Obtener el item y verificar que pertenece a esta cotización
        $item = Item::where('id_cotizaciones', $cotizacion->id)->findOrFail($itemId);

        try {
            DB::transaction(function () use ($item, $cotizacion) {
                // Guardar el precio del item antes de eliminarlo
                $precioItem = $item->producto ? ($item->producto->precio_final * $item->cantidad) : 0;

                // Eliminar el item
                $item->delete();

                // Recalcular el precio total de la cotización
                $precioTotal = $cotizacion->items()->get()->sum(function ($item) {
                    return ($item->producto ? $item->producto->precio_final : 0) * $item->cantidad;
                });

                $cotizacion->update(['precio_total' => $precioTotal]);
            });

            return back()->with('success', 'Producto eliminado de la cotización.');

        } catch (\Exception $e) {
            return back()->with('error', 'Error al eliminar producto: ' . $e->getMessage());
        }
    }
    
    public function goToOPT()
    {
        // Redirección a un sistema externo
        return redirect()->away('https://tu.sistema.opt/inicio'); 
    }
}