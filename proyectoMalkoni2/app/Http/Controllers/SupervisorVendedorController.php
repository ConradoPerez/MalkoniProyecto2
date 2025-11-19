<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Empleado;
use App\Models\Empresa;
use App\Models\Cotizacion;
use App\Models\Persona;

class SupervisorVendedorController extends Controller
{
    /**
     * Mostrar la lista de vendedores
     */
    public function index(Request $request)
    {
        // Obtener supervisor actual
        $supervisor = $this->getSupervisorActual($request);
        
        // Cargar todos los vendedores con paginación
        $vendedores = Empleado::vendedores()
            ->with('rol')
            ->orderBy('nombre')
            ->paginate(10);

        return view('supervisor.vendedores.index', compact('vendedores', 'supervisor'));
    }

    /**
     * Buscar vendedores
     */
    public function search(Request $request)
    {
        // Obtener supervisor actual
        $supervisor = $this->getSupervisorActual($request);
        
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
        
        return view('supervisor.vendedores.index', compact('vendedores', 'supervisor', 'nombre', 'dni'));
    }

    /**
     * Mostrar clientes de un vendedor específico
     */
    public function clientes($id, Request $request)
    {
        // Obtener supervisor actual
        $supervisor = $this->getSupervisorActual($request);
        
        // Verificar que el empleado existe y es vendedor
        $vendedor = Empleado::vendedores()
            ->where('id_empleado', $id)
            ->firstOrFail();
        
        // Obtener todas las empresas únicas que han tenido cotizaciones con este vendedor
        // Esto incluye empresas directas y empresas a través de personas
        $empresasIds = collect();
        
        // IDs de empresas directas (cotizaciones con id_empresas)
        $empresasDirectas = Cotizacion::where('id_empleados', $id)
            ->whereNotNull('id_empresas')
            ->distinct()
            ->pluck('id_empresas');
        $empresasIds = $empresasIds->merge($empresasDirectas);
        
        // IDs de empresas a través de personas (cotizaciones con id_personas)
        $empresasPersonas = Cotizacion::where('id_empleados', $id)
            ->whereNotNull('id_personas')
            ->join('personas', 'cotizaciones.id_personas', '=', 'personas.id_persona')
            ->whereNotNull('personas.id_empresa')
            ->distinct()
            ->pluck('personas.id_empresa');
        $empresasIds = $empresasIds->merge($empresasPersonas);
        
        // Eliminar duplicados
        $empresasIds = $empresasIds->unique();
        
        // Obtener las empresas con información adicional
        $todosClientes = Empresa::whereIn('id_empresa', $empresasIds)
            ->orderBy('nombre')
            ->paginate(10);
        
        // Para cada cliente, obtener información adicional
        foreach ($todosClientes as $cliente) {
            // Total de cotizaciones (directas + a través de personas)
            $cotizacionesDirectas = Cotizacion::where('id_empleados', $id)
                ->where('id_empresas', $cliente->id_empresa)
                ->count();
            
            $cotizacionesPersonas = Cotizacion::where('id_empleados', $id)
                ->whereHas('persona', function($query) use ($cliente) {
                    $query->where('id_empresa', $cliente->id_empresa);
                })
                ->count();
            
            $cliente->total_cotizaciones = $cotizacionesDirectas + $cotizacionesPersonas;
            
            // Última cotización
            $ultimaCotizacion = Cotizacion::where('id_empleados', $id)
                ->where(function($query) use ($cliente) {
                    $query->where('id_empresas', $cliente->id_empresa)
                          ->orWhereHas('persona', function($subQuery) use ($cliente) {
                              $subQuery->where('id_empresa', $cliente->id_empresa);
                          });
                })
                ->latest('fyh')
                ->first();
            
            $cliente->ultima_cotizacion = $ultimaCotizacion;
        }
        
        return view('supervisor.vendedores.clientes', compact('supervisor', 'vendedor', 'todosClientes'));
    }

    /**
     * Mostrar cotizaciones de un vendedor específico
     */
    public function cotizaciones($id, Request $request)
    {
        // Obtener supervisor actual
        $supervisor = $this->getSupervisorActual($request);
        
        // Verificar que el empleado existe y es vendedor
        $vendedor = Empleado::vendedores()
            ->where('id_empleado', $id)
            ->firstOrFail();
        
        // Cargar todas las cotizaciones del vendedor con sus relaciones
        $cotizaciones = $vendedor->cotizaciones()
            ->with([
                'empresa',
                'persona.empresa',
                'items',
                'cambios' => function($query) {
                    $query->latest('fyH')->limit(1);
                },
                'cambios.estado'
            ])
            ->orderByDesc('fyh')
            ->paginate(15);
        
        // Para cada cotización, calcular información adicional
        foreach ($cotizaciones as $cotizacion) {
            // Obtener el estado actual
            $ultimoCambio = $cotizacion->cambios->first();
            $cotizacion->estado_actual = $ultimoCambio ? $ultimoCambio->estado : null;
            
            // Contar items
            $cotizacion->total_items = $cotizacion->items->count();
            
            // Calcular días transcurridos
            $cotizacion->dias_transcurridos = (int) now()->diffInDays($cotizacion->fyh);
        }
        
        // Estadísticas adicionales
        $estadisticas = [
            'total_cotizaciones' => $vendedor->cotizaciones()->count(),
            'cotizaciones_mes' => $vendedor->cotizaciones()
                ->whereMonth('fyh', now()->month)
                ->whereYear('fyh', now()->year)
                ->count(),
            'monto_total_mes' => $vendedor->cotizaciones()
                ->whereNotNull('fecha_cotizado')
                ->whereMonth('fecha_cotizado', now()->month)
                ->whereYear('fecha_cotizado', now()->year)
                ->sum('precio_total'),
            'clientes_unicos' => $this->contarClientesUnicos($id)
        ];
        
        return view('supervisor.vendedores.cotizaciones', compact('supervisor', 'vendedor', 'cotizaciones', 'estadisticas'));
    }
    
    /**
     * Contar clientes únicos de un vendedor
     */
    private function contarClientesUnicos($vendedorId)
    {
        // Contar empresas directas
        $empresasDirectas = Cotizacion::where('id_empleados', $vendedorId)
            ->whereNotNull('id_empresas')
            ->distinct('id_empresas')
            ->count('id_empresas');
        
        // Contar empresas a través de personas
        $empresasPersonas = Cotizacion::where('id_empleados', $vendedorId)
            ->whereNotNull('id_personas')
            ->join('personas', 'cotizaciones.id_personas', '=', 'personas.id_persona')
            ->whereNotNull('personas.id_empresa')
            ->distinct('personas.id_empresa')
            ->count('personas.id_empresa');
        
        // Total único (puede haber overlap, pero es una aproximación)
        return $empresasDirectas + $empresasPersonas;
    }
    
    /**
     * Obtener supervisor actual
     */
    private function getSupervisorActual($request)
    {
        $supervisorId = $request->get('supervisor_id', 1);
        
        $supervisor = Empleado::with('rol')
            ->whereHas('rol', function($q) {
                $q->where('nombre', 'supervisor');
            })
            ->find($supervisorId);
        
        if (!$supervisor) {
            // Si no encuentra el supervisor específico, toma el primero disponible
            $supervisor = Empleado::with('rol')
                ->whereHas('rol', function($q) {
                    $q->where('nombre', 'supervisor');
                })
                ->first();
        }
        
        return $supervisor;
    }
}