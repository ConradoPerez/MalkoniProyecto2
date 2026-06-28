<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\Empleado;
use Illuminate\Support\Facades\DB;

class SupervisorProductoController extends Controller
{
    /**
     * Mostrar la lista de productos
     */
    public function index(Request $request)
    {
        // Sincronizar cant_cotizaciones en la tabla productos
        DB::statement('UPDATE productos SET cant_cotizaciones = (SELECT COUNT(*) FROM items WHERE items.id_Producto = productos.id_producto)');

        // Obtener supervisor actual
        $supervisor = $this->getSupervisorActual($request);
        
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
        
        return view('supervisor.productos.index', compact('supervisor', 'productos', 'estadisticas', 'ordenar'));
    }

    /**
     * Buscar productos
     */
    public function search(Request $request)
    {
        // Sincronizar cant_cotizaciones en la tabla productos
        DB::statement('UPDATE productos SET cant_cotizaciones = (SELECT COUNT(*) FROM items WHERE items.id_Producto = productos.id_producto)');

        // Obtener supervisor actual
        $supervisor = $this->getSupervisorActual($request);
        
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
        
        return view('supervisor.productos.index', compact('supervisor', 'productos', 'estadisticas', 'codigo', 'nombre', 'ordenar'));
    }

    /**
     * Mostrar detalles de un producto específico
     */
    public function show($id, Request $request)
    {
        // Obtener supervisor actual
        $supervisor = $this->getSupervisorActual($request);
        
        // Cargar el producto con sus relaciones
        $producto = Producto::with(['subtipo', 'subcategoria'])
            ->where('id_producto', $id)
            ->firstOrFail();
        
        return view('supervisor.productos.show', compact('supervisor', 'producto'));
    }

    /**
     * Mostrar estadísticas de ventas de un producto
     */
    public function estadisticas($id, Request $request)
    {
        // Obtener supervisor actual
        $supervisor = $this->getSupervisorActual($request);
        
        // Cargar el producto
        $producto = Producto::with(['subtipo', 'subcategoria'])
            ->where('id_producto', $id)
            ->firstOrFail();
        
        // TODO: Implementar estadísticas detalladas cuando tengas modelo de ventas
        
        return view('supervisor.productos.estadisticas', compact('supervisor', 'producto'));
    }

    /**
     * Mostrar formulario de creación de producto
     */
    public function create(Request $request)
    {
        $supervisor = $this->getSupervisorActual($request);
        abort_if(!$supervisor, 403, 'Supervisor no autorizado.');

        $subtipos = \App\Models\Subtipo::all();
        $subcategorias = \App\Models\Subcategoria::all();

        return view('supervisor.productos.create', compact('supervisor', 'subtipos', 'subcategorias'));
    }

    /**
     * Guardar nuevo producto
     */
    public function store(Request $request)
    {
        $supervisor = $this->getSupervisorActual($request);
        abort_if(!$supervisor, 403, 'Supervisor no autorizado.');

        $data = $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'precio_base' => 'required|integer|min:0',
            'descuento' => 'required|integer|min:0|max:100',
            'promocion' => 'nullable|boolean',
            'id_subtipo' => 'nullable|exists:subtipos,id_subtipo',
            'id_subcategoria' => 'nullable|exists:subcategorias,id_subcategoria',
            'foto' => 'nullable|image|max:2048',
        ]);

        // Calcular precio_final
        $descuento = (int) ($data['descuento'] ?? 0);
        $precioBase = (int) $data['precio_base'];
        $data['precio_final'] = $precioBase - ($precioBase * $descuento / 100);
        $data['promocion'] = $request->has('promocion') ? 1 : 0;

        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('productos', 'public');
            $data['foto'] = 'storage/' . $path;
        }

        Producto::create($data);

        return redirect()->route('productos.index')->with('success', 'Producto creado exitosamente.');
    }

    /**
     * Mostrar formulario de edición de producto
     */
    public function edit($id, Request $request)
    {
        $supervisor = $this->getSupervisorActual($request);
        abort_if(!$supervisor, 403, 'Supervisor no autorizado.');

        $producto = Producto::where('id_producto', $id)->firstOrFail();
        $subtipos = \App\Models\Subtipo::all();
        $subcategorias = \App\Models\Subcategoria::all();

        return view('supervisor.productos.edit', compact('supervisor', 'producto', 'subtipos', 'subcategorias'));
    }

    /**
     * Actualizar producto existente
     */
    public function update(Request $request, $id)
    {
        $supervisor = $this->getSupervisorActual($request);
        abort_if(!$supervisor, 403, 'Supervisor no autorizado.');

        $producto = Producto::where('id_producto', $id)->firstOrFail();

        $data = $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'precio_base' => 'required|integer|min:0',
            'descuento' => 'required|integer|min:0|max:100',
            'promocion' => 'nullable|boolean',
            'id_subtipo' => 'nullable|exists:subtipos,id_subtipo',
            'id_subcategoria' => 'nullable|exists:subcategorias,id_subcategoria',
            'foto' => 'nullable|image|max:2048',
        ]);

        // Calcular precio_final
        $descuento = (int) ($data['descuento'] ?? 0);
        $precioBase = (int) $data['precio_base'];
        $data['precio_final'] = $precioBase - ($precioBase * $descuento / 100);
        $data['promocion'] = $request->has('promocion') ? 1 : 0;

        if ($request->has('eliminar_foto') && !$request->hasFile('foto')) {
            if ($producto->foto) {
                $oldPath = str_replace('storage/', '', $producto->foto);
                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPath);
            }
            $data['foto'] = null;
        } elseif ($request->hasFile('foto')) {
            if ($producto->foto) {
                $oldPath = str_replace('storage/', '', $producto->foto);
                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('foto')->store('productos', 'public');
            $data['foto'] = 'storage/' . $path;
        }

        $producto->update($data);

        return redirect()->route('productos.index')->with('success', 'Producto actualizado exitosamente.');
    }

    /**
     * Eliminar producto existente
     */
    public function destroy($id, Request $request)
    {
        $supervisor = $this->getSupervisorActual($request);
        abort_if(!$supervisor, 403, 'Supervisor no autorizado.');

        $producto = Producto::where('id_producto', $id)->firstOrFail();

        try {
            // Eliminar foto física si existe
            if ($producto->foto) {
                $filePath = str_replace('storage/', '', $producto->foto);
                \Illuminate\Support\Facades\Storage::disk('public')->delete($filePath);
            }

            $producto->delete();
            return redirect()->route('productos.index')->with('success', 'Producto eliminado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->route('productos.index')->with('error', 'No se puede eliminar el producto porque está asociado a cotizaciones existentes.');
        }
    }

    
    /**
     * Obtener supervisor actual
     */
    private function getSupervisorActual($request)
    {
        $supervisorId = (int) session('user_id', 0);
        if ($supervisorId <= 0) {
            return null;
        }
        
        $supervisor = Empleado::with('rol')
            ->whereHas('rol', function($q) {
                $q->where('nombre', 'supervisor');
            })
            ->find($supervisorId);
        
        if (!$supervisor) {
            return null;
        }
        
        return $supervisor;
    }
}