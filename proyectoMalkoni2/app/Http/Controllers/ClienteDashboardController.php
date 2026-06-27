<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Empresa;
use App\Models\Cotizacion;
use App\Models\Empleado; 
use App\Models\Producto;
use App\Models\Item; 
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class ClienteDashboardController extends Controller
{
    /**
     * Muestra la vista principal del Dashboard del Cliente con cotizaciones paginadas.
     */
    public function dashboard(Request $request)
    {
        // Obtener el ID de la persona desde la sesión autenticada
        $personaId = (int) session('user_id', 0);
        abort_if($personaId <= 0, 403, 'Sesión de cliente inválida.');
        
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

        // Asegurar que la vista reciba también el plano OPT cuando exista
        $todasCotizaciones->each(function (Cotizacion $cotizacion) {
            $cotizacion->setVisible(array_unique(array_merge($cotizacion->getVisible() ?: [], ['pedido_opt_id', 'pdf_url'])));
        });
        
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
    
    /**
     * Muestra el formulario para iniciar una nueva cotización.
     */
    public function createQuotation(Request $request)
    {
        $cotizacion = null;
        $personaId = (int) session('user_id', 0);
        abort_if($personaId <= 0, 403, 'Sesión de cliente inválida.');
        $cotizacionId = $request->get('cotizacion_id');

        if ($cotizacionId) {
            $cotizacion = Cotizacion::with(['empresa', 'persona', 'empleado'])->findOrFail($cotizacionId);
            $personaId = (int) ($cotizacion->id_personas ?? $personaId);
        }

        $vendedores = Empleado::vendedores()->get(['id_empleado', 'nombre', 'foto']);

        $numero_pedido = $cotizacion
            ? ($cotizacion->pedido_opt_id ?? $cotizacion->numero)
            : rand(10000, 99999);

        return view('cliente.cotizaciones.create', compact('vendedores', 'numero_pedido', 'personaId', 'cotizacion'));
    }

    /**
     * Muestra la vista para agregar productos a una cotización existente.
     */
    public function addProductsToQuotation(Request $request, $id)
    {
        $personaId = (int) session('user_id', 0);
        abort_if($personaId <= 0, 403, 'Sesión de cliente inválida.');
        
        $cotizacion = Cotizacion::with(['empresa', 'persona.empresa'])->where('id_personas', $personaId)->findOrFail($id);
        
        $categorias = \App\Models\Categoria::with([
            'subcategorias.productos.subtipo.tipo'
        ])->get();
        
        $itemsAgregados = $cotizacion->items()->with('producto')->get();
        
        return view('cliente.cotizaciones.agregar_productos', compact(
            'cotizacion',
            'categorias',
            'itemsAgregados',
            'personaId'
        ));
    }

    /**
     * Procesa la selección de vendedor y datos iniciales.
     */
    public function prepareQuotation(Request $request)
    {
        $personaId = (int) session('user_id', 0);
        abort_if($personaId <= 0, 403, 'Sesión de cliente inválida.');

        $validated = $request->validate([
            'id_empleados' => 'required|exists:empleados,id_empleado',
            'mensaje_inicial' => 'nullable|string|max:1000',
            'numero_pedido' => 'required|integer',
            'cotizacion_id' => 'nullable|integer|exists:cotizaciones,id',
        ]);

        if (!empty($validated['cotizacion_id'])) {
            $cotizacion = Cotizacion::where('id', $validated['cotizacion_id'])
                ->where('id_personas', $personaId)
                ->findOrFail($validated['cotizacion_id']);

            $cotizacion->update([
                'id_empleados' => $validated['id_empleados'],
            ]);

            return redirect()->route('cliente.cotizacion.agregar_productos', [
                'id' => $cotizacion->id,
            ])->with('success', 'Vendedor asignado correctamente. Ya podés agregar productos a la cotización importada.');
        }
        
        session([
            'nueva_cotizacion' => [
                'id_empleados' => $validated['id_empleados'],
                'mensaje_inicial' => $validated['mensaje_inicial'],
                'numero_pedido' => $validated['numero_pedido'],
                'persona_id' => $personaId,
                'fecha' => now()
            ]
        ]);
        
        return redirect()->route('cliente.cotizacion.productos')
                         ->with('success', 'Datos guardados. ¡Podés seleccionar productos opcionales o guardarla directamente!');
    }

    /**
     * Muestra la vista para seleccionar productos para una nueva cotización.
     */
    public function selectProducts(Request $request)
    {
        $personaId = (int) session('user_id', 0);
        abort_if($personaId <= 0, 403, 'Sesión de cliente inválida.');
        
        $datosCotizacion = session('nueva_cotizacion');
        if (!$datosCotizacion || $datosCotizacion['persona_id'] != $personaId) {
            return redirect()->route('cliente.nueva_cotizacion')
                           ->with('error', 'Sesión expirada. Completa nuevamente el formulario.');
        }
        
        $categorias = \App\Models\Categoria::with([
            'subcategorias.productos.subtipo.tipo'
        ])->get();
        
        $cotizacion = (object) [
            'id' => null, 
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
     * Crea la cotización (los productos adicionales ahora son OPCIONALES).
     */
    public function createQuotationWithProducts(Request $request)
    {
        $datosCotizacion = session('nueva_cotizacion');
        $personaId = (int) session('user_id', 0);
        abort_if($personaId <= 0, 403, 'Sesión de cliente inválida.');
        
        if (!$datosCotizacion || $datosCotizacion['persona_id'] != $personaId) {
            return back()->with('error', 'Sesión expirada. Inicia una nueva cotización.');
        }
        
        // Filtrar productos con cantidad > 0 (si es que mandó alguno)
        $productosArray = $request->input('productos', []);
        $productosFiltrados = array_filter($productosArray, function($producto) {
            return isset($producto['cantidad']) && intval($producto['cantidad']) > 0;
        });
        
        $productosFiltrados = array_values($productosFiltrados);
        
        try {
            $cotizacion = DB::transaction(function () use ($datosCotizacion, $productosFiltrados, $personaId) {
                // 1. Crear la cotización básica
                $cotizacion = Cotizacion::create([
                    'titulo' => 'Cotización #' . $datosCotizacion['numero_pedido'],
                    'numero' => $datosCotizacion['numero_pedido'],
                    'fyh' => now(),
                    'precio_total' => 0,
                    'id_empleados' => $datosCotizacion['id_empleados'],
                    'id_personas' => $personaId,
                ]);
                
                // 2. Agregar productos adicionales solo si el array no está vacío
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
                    }
                }
                
                $cotizacion->update(['precio_total' => $precioTotal]);
                return $cotizacion;
            });
            
            session()->forget('nueva_cotizacion');
            
            $mensajeSuccess = count($productosFiltrados) > 0 
                ? 'Cotización creada exitosamente con ' . count($productosFiltrados) . ' productos adicionales.'
                : 'Cotización creada exitosamente basada exclusivamente en el plano del OPT.';

            return redirect()->route('cliente.cotizacion.ver', ['id' => $cotizacion->id])
                           ->with('success', $mensajeSuccess);
                           
        } catch (\Exception $e) {
            \Log::error('Error al crear cotización: ' . $e->getMessage());
            return back()->with('error', 'Error al crear la cotización: ' . $e->getMessage())->withInput();
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
        $personaId = (int) session('user_id', 0);
        abort_if($personaId <= 0, 403, 'Sesión de cliente inválida.');
        
        $cotizacion = Cotizacion::where('id_personas', $personaId)
            ->with(['empleado', 'empresa', 'persona', 'items.producto'])
            ->findOrFail($id);
        return view('cliente.cotizaciones.show', compact('cotizacion', 'personaId'));
    }

    /**
     * Descarga el plano OPT de una cotización del cliente como attachment.
     */
    public function downloadOptPlano(Request $request, $id)
    {
        $personaId = (int) session('user_id', 0);
        abort_if($personaId <= 0, 403, 'Sesión de cliente inválida.');

        $cotizacion = Cotizacion::query()
            ->where('id', $id)
            ->where('id_personas', $personaId)
            ->firstOrFail();

        abort_unless(!empty($cotizacion->pedido_opt_id) && !empty($cotizacion->pdf_url), 404);

        $response = Http::timeout(30)->retry(2, 200)->get($cotizacion->pdf_url);

        abort_unless($response->successful(), 404);

        $filename = 'plano-opt-' . $cotizacion->pedido_opt_id . '.pdf';
        $contentType = $response->header('Content-Type', 'application/pdf');

        return response()->streamDownload(function () use ($response) {
            echo $response->body();
        }, $filename, [
            'Content-Type' => $contentType,
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Guarda productos/servicios a una cotización existente.
     */
    public function storeProductsToQuotation(Request $request, $id)
    {
        $personaId = (int) session('user_id', 0);
        abort_if($personaId <= 0, 403, 'Sesión de cliente inválida.');
        
        $cotizacion = Cotizacion::where('id_personas', $personaId)->findOrFail($id);

        $productosData = $request->input('productos', []);
        $hasProducts = false;
        
        foreach ($productosData as $producto) {
            if (isset($producto['cantidad']) && (int)$producto['cantidad'] > 0) {
                $hasProducts = true;
                break;
            }
        }

        if (!$hasProducts) {
            return redirect()->route('cliente.cotizacion.ver', ['id' => $cotizacion->id])
                             ->with('success', 'No se agregaron productos adicionales.');
        }

        $request->validate([
            'productos.*.id_producto' => 'required|exists:productos,id_producto',
        ]);

        try {
            DB::transaction(function () use ($productosData, $cotizacion) {
                foreach ($productosData as $productoData) {
                    $cantidad = (int)($productoData['cantidad'] ?? 0);
                    
                    if ($cantidad <= 0) {
                        continue;
                    }
                    
                    $producto = Producto::findOrFail($productoData['id_producto']);

                    // Buscar si ya existe el producto en esta cotización
                    $itemExistente = Item::where('id_cotizaciones', $cotizacion->id)
                        ->where('id_Producto', $producto->id_producto)
                        ->first();

                    if ($itemExistente) {
                        $itemExistente->update([
                            'cantidad' => $itemExistente->cantidad + $cantidad,
                        ]);
                    } else {
                        Item::create([
                            'cantidad' => $cantidad,
                            'id_cotizaciones' => $cotizacion->id,
                            'id_Producto' => $producto->id_producto,
                        ]);
                    }
                }

                // Recalcular el precio total de toda la cotización sumando todos sus ítems
                $precioTotal = 0;
                $items = Item::where('id_cotizaciones', $cotizacion->id)->with('producto')->get();
                foreach ($items as $item) {
                    if ($item->producto) {
                        $precioTotal += ($item->producto->precio_final ?? 0) * $item->cantidad;
                    }
                }

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
    public function removeProductFromQuotation(Request $request, $cotizacionId, $itemId)
    {
        $personaId = (int) session('user_id', 0);
        abort_if($personaId <= 0, 403, 'Sesión de cliente inválida.');
        
        $cotizacion = Cotizacion::where('id_personas', $personaId)->findOrFail($cotizacionId);
        $item = Item::where('id_cotizaciones', $cotizacion->id)->findOrFail($itemId);

        try {
            DB::transaction(function () use ($item, $cotizacion) {
                $item->delete();

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

    /**
     * Incrementa o decrementa la cantidad de un item de la cotización.
     */
    public function updateItemQuantity(Request $request, $cotizacionId, $itemId)
    {
        $personaId = (int) session('user_id', 0);
        abort_if($personaId <= 0, 403, 'Sesión de cliente inválida.');
        
        $cotizacion = Cotizacion::where('id_personas', $personaId)->findOrFail($cotizacionId);
        $item = Item::where('id_cotizaciones', $cotizacion->id)->findOrFail($itemId);

        $cambio = (int) $request->input('change', 0);
        $nuevaCantidad = $item->cantidad + $cambio;

        if ($nuevaCantidad <= 0) {
            return $this->removeProductFromQuotation($request, $cotizacionId, $itemId);
        }

        try {
            DB::transaction(function () use ($item, $cotizacion, $nuevaCantidad) {
                $item->update(['cantidad' => $nuevaCantidad]);

                // Recalcular precio total
                $precioTotal = $cotizacion->items()->get()->sum(function ($item) {
                    return ($item->producto ? $item->producto->precio_final : 0) * $item->cantidad;
                });

                $cotizacion->update(['precio_total' => $precioTotal]);
            });

            return back()->with('success', 'Cantidad actualizada correctamente.');

        } catch (\Exception $e) {
            return back()->with('error', 'Error al actualizar cantidad: ' . $e->getMessage());
        }
    }

    
    /**
     * Redirige dinámicamente al cliente de vuelta al optimizador de producción en Localhost o Web Real.
     */
    public function goToOPT()
    {
        // Obtener el ID del cliente logueado desde la sesión
        $personaId = (int) session('user_id', 0);
        abort_if($personaId <= 0, 403, 'Sesión de cliente inválida.');

        // Buscar el cliente real para extraer su token_opt sincronizado
        $persona = \App\Models\Persona::findOrFail($personaId);
        
        // Leer la URL base del entorno (lee http://localhost:8080 en local o el dominio real en cPanel)
        $baseUrl = env('MALKONI_ONLINE_URL', 'https://online.malkoni.com.ar');

        // Redirección directa aplicando el sistema de Auto-Login por Token en producción
        return redirect()->away($baseUrl . '/public/Dashboard/opt.php?token=' . $persona->token_opt); 
    }
}