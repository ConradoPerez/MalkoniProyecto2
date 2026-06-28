<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Empresa;
use App\Models\Persona;
use App\Models\Cotizacion;
use App\Models\Empleado;
use App\Models\Producto;
use App\Models\Estado;
use Illuminate\Support\Facades\DB;

class SupervisorDashboardController extends Controller
{
    public function index(Request $request)
    {
        $supervisorId = (int) session('user_id', 0);
        abort_if($supervisorId <= 0, 403, 'Sesión de supervisor inválida.');
        
        $supervisor = Empleado::with('rol')
            ->whereHas('rol', function($q) {
                $q->where('nombre', 'supervisor');
            })
            ->find($supervisorId);
        
        // Sincronizar cant_cotizaciones en la tabla productos desde los items registrados
        DB::statement('UPDATE productos SET cant_cotizaciones = (SELECT COUNT(*) FROM items WHERE items.id_Producto = productos.id_producto)');

        if (!$supervisor) {
            abort(403, 'Supervisor no autorizado.');
        }
        
        // Métricas principales actualizadas según nueva estructura
        $metrics = [
            // Total de clientes (empresas + personas)
            'clientes_digitalizados' => Empresa::count() + Persona::count(),
            
            // Cotizaciones en proceso según el estado actual
            'cotizaciones_proceso' => $this->getCotizacionesPorEstado('Abierto'),
            
            // Total facturado este mes
            'cotizaciones_este_mes' => Cotizacion::esteMes()->sum('precio_total'),
        ];

        // Datos para el gráfico de cotizaciones por vendedor
        $cotizacionesPorVendedor = Empleado::vendedores()
            ->withCount('cotizaciones')
            ->having('cotizaciones_count', '>', 0)
            ->orderByDesc('cotizaciones_count')
            ->limit(4)
            ->get();

        // Últimas cotizaciones con cliente (empresa o persona)
        $ultimasCotizaciones = Cotizacion::with(['empresa', 'persona', 'empleado'])
            ->orderByDesc('fyh')
            ->limit(6)
            ->get()
            ->map(function ($cotizacion) {
                // Agregar el estado actual y cliente unificado
                $cotizacion->estado_actual = $this->getEstadoActual($cotizacion->id);
                $cotizacion->cliente_nombre = $cotizacion->empresa 
                    ? $cotizacion->empresa->nombre 
                    : ($cotizacion->persona ? 'Cliente Persona' : 'Sin cliente');
                return $cotizacion;
            });

        // Ranking de productos más cotizados
        $productosRanking = Producto::where('cant_cotizaciones', '>', 0)
            ->orderByDesc('cant_cotizaciones')
            ->limit(6)
            ->get();

        return view('supervisor.dashboard', compact(
            'supervisor',
            'metrics',
            'cotizacionesPorVendedor', 
            'ultimasCotizaciones',
            'productosRanking'
        ));
    }

    /**
     * Obtener cotizaciones por estado usando la tabla de cambios
     */
    private function getCotizacionesPorEstado($nombreEstado)
    {
        return DB::table('cotizaciones')
            ->join('cambios', function($join) {
                $join->on('cotizaciones.id', '=', 'cambios.id_cotizaciones')
                     ->whereRaw('cambios.fyH = (
                         SELECT MAX(c2.fyH) 
                         FROM cambios c2 
                         WHERE c2.id_cotizaciones = cotizaciones.id
                     )');
            })
            ->join('estados', 'cambios.id_estado', '=', 'estados.id_estado')
            ->where('estados.nombre', $nombreEstado)
            ->count();
    }

    /**
     * Obtener el estado actual de una cotización
     */
    private function getEstadoActual($cotizacionId)
    {
        return DB::table('cambios')
            ->join('estados', 'cambios.id_estado', '=', 'estados.id_estado')
            ->where('cambios.id_cotizaciones', $cotizacionId)
            ->orderByDesc('cambios.fyH')
            ->select('estados.nombre', 'estados.descripcion')
            ->first();
    }

    /**
     * Helper para obtener el color de estado
     */
    public function getEstadoColor($nombreEstado)
    {
        return match(strtolower($nombreEstado)) {
            'nuevo' => '#D88429',
            'abierto' => '#166379',
            'cotizado' => '#22c55e',
            'en entrega' => '#B1B7BB',
            default => '#6B7280'
        };
    }

    /**
     * Mostrar formulario para editar perfil del supervisor
     */
    public function editProfile(Request $request)
    {
        $supervisorId = (int) session('user_id', 0);
        abort_if($supervisorId <= 0, 403, 'Sesión de supervisor inválida.');

        $supervisor = Empleado::with('rol')
            ->whereHas('rol', function($q) {
                $q->where('nombre', 'supervisor');
            })
            ->find($supervisorId);

        if (!$supervisor) {
            abort(404, 'Supervisor no encontrado');
        }

        return view('supervisor.perfil.edit', compact('supervisor'));
    }

    /**
     * Actualizar perfil del supervisor
     */
    public function updateProfile(Request $request)
    {
        $supervisorId = (int) session('user_id', 0);
        abort_if($supervisorId <= 0, 403, 'Sesión de supervisor inválida.');

        $supervisor = Empleado::findOrFail($supervisorId);

        $data = $request->validate([
            'nombre' => 'required|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'dni' => 'nullable|string|max:20',
            'foto' => 'nullable|image|max:2048',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        if ($request->has('eliminar_foto') && !$request->hasFile('foto')) {
            if ($supervisor->foto) {
                $oldPath = str_replace('storage/', '', $supervisor->foto);
                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPath);
            }
            $supervisor->foto = null;
        } elseif ($request->hasFile('foto')) {
            if ($supervisor->foto) {
                $oldPath = str_replace('storage/', '', $supervisor->foto);
                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('foto')->store('supervisores', 'public');
            $supervisor->foto = 'storage/' . $path;
        }

        $supervisor->nombre = $data['nombre'];
        $supervisor->telefono = $data['telefono'] ?? null;
        $supervisor->dni = $data['dni'] ?? null;

        if (!empty($data['password'])) {
            $supervisor->password = bcrypt($data['password']);
        }

        $supervisor->save();

        return redirect()->route('dashboard')->with('success', 'Perfil actualizado exitosamente.');
    }
}