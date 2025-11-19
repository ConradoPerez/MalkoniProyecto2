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
     * Muestra la vista principal del Dashboard del Cliente con cotizaciones paginadas.
     */
    public function dashboard(Request $request)
    {
        // Obtener el ID de la persona desde la request
        $personaId = $request->get('persona_id', 1);
        
        // Obtener información del cliente (persona)
        $cliente = \App\Models\Persona::with('empresa')->find($personaId);
        
        if (!$cliente) {
            abort(404, 'Persona no encontrada');
        }

        // Obtener el nombre de la empresa asociada a la persona
        $nombreEmpresa = $cliente->empresa ? $cliente->empresa->nombre : 'Sin empresa';

        // Obtener parámetros de búsqueda y filtrado
        $search = $request->get('search');
        $estado = $request->get('estado');
        $fechaDesde = $request->get('fecha_desde');
        $fechaHasta = $request->get('fecha_hasta');

        // Query base para las cotizaciones del cliente
        $query = Cotizacion::with(['empresa', 'empleado'])
            ->where('id_personas', $personaId);

        // Aplicar filtros de búsqueda
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('numero', 'like', "%{$search}%")
                  ->orWhere('titulo', 'like', "%{$search}%");
            });
        }

        // Filtro por rango de fechas
        if ($fechaDesde) {
            $query->whereDate('fyh', '>=', $fechaDesde);
        }
        if ($fechaHasta) {
            $query->whereDate('fyh', '<=', $fechaHasta);
        }

        // Obtener todas las cotizaciones que cumplen los filtros
        $todasCotizaciones = $query->orderByDesc('fyh')->get();
        
        // Para cada cotización, obtener su estado actual
        foreach ($todasCotizaciones as $cotizacion) {
            $ultimoCambio = \App\Models\Cambio::where('id_cotizaciones', $cotizacion->id)
                ->with('estado')
                ->latest('fyH')
                ->first();
            
            $cotizacion->estado_actual = $ultimoCambio ? $ultimoCambio->estado : null;
        }

        // Aplicar filtro por estado después de cargar el estado actual
        if ($estado) {
            $todasCotizaciones = $todasCotizaciones->filter(function($cotizacion) use ($estado) {
                return ($cotizacion->estado_actual->nombre ?? 'Nuevo') === $estado;
            });
        }
        
        // Ordenar por prioridad de estado: Nuevo > Abierto > Cotizado > En entrega
        // Dentro de cada estado, ordenar por fecha descendente (más reciente primero)
        $ordenEstados = ['Nuevo' => 1, 'Abierto' => 2, 'Cotizado' => 3, 'En entrega' => 4];
        $cotizacionesOrdenadas = $todasCotizaciones->sortBy([
            fn($a, $b) => ($ordenEstados[$a->estado_actual->nombre ?? 'Nuevo'] ?? 5) <=> ($ordenEstados[$b->estado_actual->nombre ?? 'Nuevo'] ?? 5),
            fn($a, $b) => $b->fyh <=> $a->fyh
        ]);

        // Paginar manualmente
        $page = $request->get('page', 1);
        $perPage = 10;
        $cotizaciones = new \Illuminate\Pagination\LengthAwarePaginator(
            $cotizacionesOrdenadas->forPage($page, $perPage),
            $cotizacionesOrdenadas->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query()
            ]
        );

        // Estadísticas para mostrar
        $estadisticas = [
            'total' => $todasCotizaciones->count(),
            'nuevo' => $todasCotizaciones->filter(fn($c) => ($c->estado_actual->nombre ?? 'Nuevo') === 'Nuevo')->count(),
            'abierto' => $todasCotizaciones->filter(fn($c) => ($c->estado_actual->nombre ?? 'Nuevo') === 'Abierto')->count(),
            'cotizado' => $todasCotizaciones->filter(fn($c) => ($c->estado_actual->nombre ?? 'Nuevo') === 'Cotizado')->count(),
            'en_entrega' => $todasCotizaciones->filter(fn($c) => ($c->estado_actual->nombre ?? 'Nuevo') === 'En entrega')->count(),
        ];

        // Estadísticas para el sidebar (cotizaciones activas = Nuevo + Abierto)
        $cotizacionesActivas = $estadisticas['nuevo'] + $estadisticas['abierto'];
        
        return view('cliente.dashboard', compact(
            'cotizaciones',
            'estadisticas',
            'cotizacionesActivas',
            'personaId',
            'cliente',
            'nombreEmpresa'
        ));
    }
    
    // =========================================================================
    // MÉTODOS DE NAVEGACIÓN Y COTIZACIONES
    // =========================================================================
    // MÉTODOS DE NAVEGACIÓN Y COTIZACIONES
    // =========================================================================
    
    /**
     * Muestra el formulario para iniciar una nueva cotización.
     * Carga la lista de vendedores disponibles.
     */
    public function createQuotation(Request $request)
    {
        // Obtener el ID de la persona desde la request
        $personaId = $request->get('persona_id', 1);
        
        // 1. Obtener los empleados que tienen rol de VENDEDOR
        $vendedores = Empleado::vendedores()->get(['id_empleado', 'nombre', 'foto']);
        
        // Asignamos un número de pedido temporal (esto debería ser secuencial en producción)
        $numero_pedido = rand(10000, 99999); 

        return view('cliente.cotizaciones.create', compact('vendedores', 'numero_pedido', 'personaId'));
    }

    /**
     * Muestra la vista para agregar productos a una cotización.
     * Carga la lista de productos disponibles organizados por jerarquía.
     */
    public function addProductsToQuotation(Request $request, $id)
    {
        // Obtener el ID del cliente desde la request
        $personaId = $request->get('persona_id', 1);
        
        // Asegura que solo pueda editar sus propias cotizaciones
        $cotizacion = Cotizacion::with(['empresa', 'persona.empresa'])->where('id_personas', $personaId)->findOrFail($id);
        
        // Obtener productos organizados por categoría > subcategoría
        $categorias = \App\Models\Categoria::with([
            'subcategorias.productos.subtipo.tipo'
        ])->get();
        
        // Obtener items ya agregados a esta cotización
        $itemsAgregados = $cotizacion->items()->with('producto')->get();
        
        return view('cliente.cotizaciones.agregar_productos', compact(
            'cotizacion',
            'categorias',
            'itemsAgregados',
            'personaId'
        ));
    }

    /**
     * Procesa la selección de vendedor y datos iniciales, redirige a selección de productos.
     * (Método POST llamado desde el formulario 'Nueva Cotización')
     */
    public function prepareQuotation(Request $request)
    {
        // Obtener el ID del cliente desde la request
        $personaId = $request->get('persona_id', 1);
        
        // 1. Validación de datos
        $validated = $request->validate([
            'id_empleados' => 'required|exists:empleados,id_empleado',
            'mensaje_inicial' => 'nullable|string|max:1000',
            'numero_pedido' => 'required|integer' 
        ]);
        
        // 2. Guardar datos en sesión para crear la cotización después
        session([
            'nueva_cotizacion' => [
                'id_empleados' => $validated['id_empleados'],
                'mensaje_inicial' => $validated['mensaje_inicial'],
                'numero_pedido' => $validated['numero_pedido'],
                'persona_id' => $personaId,
                'fecha' => now()
            ]
        ]);
        
        // 3. Redirigir a la vista de selección de productos
        return redirect()->route('cliente.cotizacion.productos', ['persona_id' => $personaId])
                         ->with('success', 'Datos guardados. ¡Selecciona productos para tu cotización!');
    }

    /**
     * Muestra la vista para seleccionar productos para una nueva cotización.
     * (No hay cotización creada aún)
     */
    public function selectProducts(Request $request)
    {
        // Obtener el ID del cliente desde la request
        $personaId = $request->get('persona_id', 1);
        
        // Verificar que existan datos de cotización en sesión
        $datosCotizacion = session('nueva_cotizacion');
        if (!$datosCotizacion || $datosCotizacion['persona_id'] != $personaId) {
            return redirect()->route('cliente.nueva_cotizacion', ['persona_id' => $personaId])
                           ->with('error', 'Sesión expirada. Completa nuevamente el formulario.');
        }
        
        // Obtener productos organizados por categoría > subcategoría
        $categorias = \App\Models\Categoria::with([
            'subcategorias.productos.subtipo.tipo'
        ])->get();
        
        // Datos de cotización temporal
        $cotizacion = (object) [
            'id' => null, // Temporal, no existe en BD aún
            'numero_cotizacion' => $datosCotizacion['numero_pedido'],
            'estado_actual' => 'Preparando',
            'cliente_nombre' => 'Cotización en preparación'
        ];
        
        return view('cliente.cotizaciones.agregar_productos', compact(
            'cotizacion',
            'categorias',
            'personaId'
        ))->with([
            'itemsAgregados' => collect([]),
            'esNuevaCotizacion' => true
        ]);
    }

    /**
     * Crea la cotización junto con los productos seleccionados.
     */
    public function createQuotationWithProducts(Request $request)
    {
        // Obtener datos de sesión
        $datosCotizacion = session('nueva_cotizacion');
        $personaId = $request->get('persona_id', 1);
        
        \Log::info('=== CREAR COTIZACIÓN ===');
        \Log::info('Request completo:', $request->all());
        \Log::info('Sesión:', $datosCotizacion);
        
        if (!$datosCotizacion || $datosCotizacion['persona_id'] != $personaId) {
            \Log::error('Sesión inválida');
            return back()->with('error', 'Sesión expirada. Inicia una nueva cotización.');
        }
        
        // PRIMERO: Filtrar productos con cantidad > 0 ANTES de validar
        $productosArray = $request->input('productos', []);
        $productosFiltrados = array_filter($productosArray, function($producto) {
            return isset($producto['cantidad']) && intval($producto['cantidad']) > 0;
        });
        
        \Log::info('Productos después del filtro:', ['total' => count($productosFiltrados), 'productos' => $productosFiltrados]);
        
        if (empty($productosFiltrados)) {
            return back()->withErrors(['productos' => 'Debe seleccionar al menos un producto con cantidad mayor a 0.'])->withInput();
        }
        
        // Reindexar el array para que tenga índices consecutivos
        $productosFiltrados = array_values($productosFiltrados);
        
        try {
            $cotizacion = DB::transaction(function () use ($datosCotizacion, $productosFiltrados, $personaId) {
                // 1. Crear la cotización
                $cotizacion = Cotizacion::create([
                    'titulo' => 'Cotización #' . $datosCotizacion['numero_pedido'],
                    'numero' => $datosCotizacion['numero_pedido'],
                    'fyh' => now(),
                    'precio_total' => 0,
                    'id_empleados' => $datosCotizacion['id_empleados'],
                    'id_personas' => $personaId,
                ]);
                
                \Log::info('Cotización creada:', ['id' => $cotizacion->id]);
                
                // 2. Agregar productos seleccionados
                $precioTotal = 0;
                foreach ($productosFiltrados as $productoData) {
                    $producto = \App\Models\Producto::find($productoData['id_producto']);
                    
                    if ($producto) {
                        Item::create([
                            'id_Producto' => $productoData['id_producto'],
                            'cantidad' => $productoData['cantidad'],
                            'id_cotizaciones' => $cotizacion->id,
                        ]);
                        
                        $precioTotal += ($producto->precio_final ?? 0) * $productoData['cantidad'];
                        \Log::info('Item agregado:', ['producto' => $productoData['id_producto'], 'cantidad' => $productoData['cantidad']]);
                    }
                }
                
                // Actualizar precio total
                $cotizacion->update(['precio_total' => $precioTotal]);
                
                return $cotizacion;
            });
            
            \Log::info('✓ Cotización creada exitosamente:', ['id' => $cotizacion->id, 'items' => count($productosFiltrados)]);
            
            // Limpiar sesión
            session()->forget('nueva_cotizacion');
            
            // Redirigir al detalle de la cotización
            return redirect()->route('cliente.cotizacion.ver', ['id' => $cotizacion->id, 'persona_id' => $personaId])
                           ->with('success', 'Cotización creada exitosamente con ' . count($productosFiltrados) . ' productos.');
                           
        } catch (\Exception $e) {
            \Log::error('Error al crear cotización: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return back()->with('error', 'Error al crear la cotización: ' . $e->getMessage())
                        ->withInput();
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

    public function viewQuotation(Request $request, $id)
    {
        // Obtener el ID del cliente desde la request
        $personaId = $request->get('persona_id', 1);
        
        // Asegura que solo pueda ver sus propias cotizaciones
        $cotizacion = Cotizacion::where('id_personas', $personaId)
            ->with(['empleado', 'empresa', 'persona', 'items.producto'])
            ->findOrFail($id);
        return view('cliente.cotizaciones.show', compact('cotizacion', 'personaId'));
    }

    /**
     * Guarda productos/servicios a una cotización existente.
     * (Método POST llamado desde el formulario 'Agregar Productos')
     */
    public function storeProductsToQuotation(Request $request, $id)
    {
        // Obtener el ID del cliente desde la request
        $personaId = $request->get('persona_id', 1);
        
        // Asegura que solo pueda editar sus propias cotizaciones
        $cotizacion = Cotizacion::where('id_personas', $personaId)->findOrFail($id);

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

            return redirect()->route('cliente.cotizacion.ver', ['id' => $cotizacion->id, 'persona_id' => $personaId])
                           ->with('success', 'Productos agregados a la cotización exitosamente.');

        } catch (\Exception $e) {
            return back()->with('error', 'Error al agregar productos: ' . $e->getMessage());
        }
    }

    /**
     * Elimina un item de una cotización.
     */
    public function removeProductFromQuotation(Request $request, $cotizacionId, $itemId)
    {
        // Obtener el ID del cliente desde la request
        $personaId = $request->get('persona_id', 1);
        
        // Verificar seguridad: solo el cliente propietario puede eliminar
        $cotizacion = Cotizacion::where('id_personas', $personaId)->findOrFail($cotizacionId);
        
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
