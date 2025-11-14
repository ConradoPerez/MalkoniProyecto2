<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Grupo;
use App\Models\Empresa;
use App\Models\Empleado;
use Illuminate\Http\Request;

class VendedorGrupoController extends Controller
{
    /**
     * Mostrar lista de grupos del vendedor
     */
    public function index(Request $request)
    {
        // Por ahora simulo un empleado/vendedor específico usando parámetro URL
        // En un sistema real esto vendría de la autenticación
        $empleadoId = $request->get('empleado_id', 1);
        
        $vendedor = Empleado::find($empleadoId);
        
        if (!$vendedor) {
            return redirect()->back()->with('error', 'Vendedor no encontrado');
        }

        // Obtener grupos del vendedor con sus empresas y conteo de cotizaciones
        $grupos = Grupo::where('id_personas', $vendedor->id_personas)
            ->with(['empresas' => function ($query) use ($vendedor) {
                $query->withCount(['cotizaciones' => function ($subQuery) use ($vendedor) {
                    $subQuery->where('id_empleados', $vendedor->id_empleado);
                }]);
            }])
            ->get();

        // Obtener empresas disponibles para agregar a grupos
        // (empresas que tienen cotizaciones del vendedor pero no están en ningún grupo del vendedor)
        $empresasDisponibles = Empresa::whereHas('cotizaciones', function ($query) use ($vendedor) {
                $query->where('id_empleados', $vendedor->id_empleado);
            })
            ->whereDoesntHave('grupos', function ($query) use ($vendedor) {
                $query->where('id_personas', $vendedor->id_personas);
            })
            ->get();

        return view('vendedor.grupos.index', compact('grupos', 'empresasDisponibles', 'vendedor'));
    }

    /**
     * Crear un nuevo grupo
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'nombre_grupo' => 'required|string|max:100',
                'descripcion' => 'nullable|string|max:500',
                'empresas' => 'array',
                'empresas.*' => 'exists:empresas,id_empresa',
                'empleado_id' => 'required|exists:empleados,id_empleado'
            ]);

            $empleadoId = $request->get('empleado_id', 1);
            $vendedor = Empleado::find($empleadoId);

            if (!$vendedor) {
                if ($request->expectsJson()) {
                    return response()->json(['error' => 'Vendedor no encontrado'], 404);
                }
                return redirect()->back()->with('error', 'Vendedor no encontrado');
            }

            // Crear el grupo
            $grupo = Grupo::create([
                'nombre_grupo' => $request->nombre_grupo,
                'descripcion' => $request->descripcion,
                'id_personas' => $vendedor->id_personas
            ]);

            // Agregar empresas al grupo si se proporcionaron
            if ($request->has('empresas') && is_array($request->empresas)) {
                $grupo->empresas()->attach($request->empresas);
            }

            // Si es una petición AJAX, devolver JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => 'Grupo creado exitosamente',
                    'grupo_id' => $grupo->id_grupo
                ]);
            }

            return redirect()->route('vendedor.app.grupos.index', ['empleado_id' => $empleadoId])
                ->with('success', 'Grupo creado exitosamente');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Error al crear el grupo: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Error al crear el grupo');
        }
    }

    /**
     * Agregar empresa(s) a un grupo
     */
    public function addEmpresa(Request $request, $grupoId)
    {
        try {
            // Debug: registrar los datos que llegan
            \Log::info('addEmpresa called', [
                'grupoId' => $grupoId,
                'request_data' => $request->all(),
                'empleado_id' => $request->get('empleado_id')
            ]);

            $request->validate([
                'empresas' => 'required|array|min:1',
                'empresas.*' => 'required|integer'
            ]);

            $empleadoId = $request->get('empleado_id', 1);
            $vendedor = Empleado::find($empleadoId);

            if (!$vendedor) {
                \Log::error('Vendedor no encontrado', ['empleado_id' => $empleadoId]);
                return response()->json(['error' => 'Vendedor no encontrado'], 404);
            }

            $grupo = Grupo::where('id_grupo', $grupoId)
                ->where('id_personas', $vendedor->id_personas)
                ->first();

            if (!$grupo) {
                \Log::error('Grupo no encontrado', [
                    'grupo_id' => $grupoId,
                    'empleado_id' => $vendedor->id_empleado,
                    'id_personas' => $vendedor->id_personas
                ]);
                return response()->json(['error' => 'Grupo no encontrado o no pertenece al vendedor'], 404);
            }

            $empresasAgregadas = 0;
            $empresasYaExistentes = [];

            foreach ($request->empresas as $empresaId) {
                // Verificar si la empresa existe
                $empresa = Empresa::find($empresaId);

                if (!$empresa) {
                    \Log::warning('Empresa no encontrada', ['empresa_id' => $empresaId]);
                    continue;
                }

                // Verificar si la empresa ya está en el grupo
                if ($grupo->empresas()->where('empresas.id_empresa', $empresaId)->exists()) {
                    $empresasYaExistentes[] = $empresa->nombre_empresa ?? $empresa->nombre;
                } else {
                    $grupo->empresas()->attach($empresaId);
                    $empresasAgregadas++;
                    \Log::info('Empresa agregada', [
                        'grupo_id' => $grupoId,
                        'empresa_id' => $empresaId,
                        'empresa_name' => $empresa->nombre_empresa ?? $empresa->nombre
                    ]);
                }
            }

            // Construir mensaje de respuesta
            if ($empresasAgregadas > 0) {
                $mensaje = $empresasAgregadas === 1 
                    ? '1 empresa agregada exitosamente'
                    : "{$empresasAgregadas} empresas agregadas exitosamente";

                if (!empty($empresasYaExistentes)) {
                    $mensaje .= '. Algunas empresas ya estaban en el grupo.';
                }

                \Log::info('Empresas agregadas exitosamente', [
                    'grupo_id' => $grupoId,
                    'empresas_agregadas' => $empresasAgregadas,
                    'mensaje' => $mensaje
                ]);

                return response()->json(['success' => $mensaje]);
            } else {
                if (!empty($empresasYaExistentes)) {
                    return response()->json(['error' => 'Todas las empresas seleccionadas ya están en el grupo'], 400);
                } else {
                    return response()->json(['error' => 'No se pudieron agregar las empresas'], 400);
                }
            }

        } catch (\Exception $e) {
            \Log::error('Error en addEmpresa', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Error al agregar empresas: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remover empresa de un grupo
     */
    public function removeEmpresa(Request $request, $grupoId, $empresaId)
    {
        $empleadoId = $request->get('empleado_id', 1);
        $vendedor = Empleado::find($empleadoId);

        $grupo = Grupo::where('id_grupo', $grupoId)
            ->where('id_personas', $vendedor->id_personas)
            ->first();

        if (!$grupo) {
            return response()->json(['error' => 'Grupo no encontrado'], 404);
        }

        $grupo->empresas()->detach($empresaId);

        return response()->json(['success' => 'Empresa removida del grupo']);
    }

    /**
     * Eliminar grupo
     */
    public function destroy(Request $request, $grupoId)
    {
        $empleadoId = $request->get('empleado_id', 1);
        $vendedor = Empleado::find($empleadoId);

        if (!$vendedor) {
            return response()->json(['error' => 'Vendedor no encontrado'], 404);
        }

        $grupo = Grupo::where('id_grupo', $grupoId)
            ->where('id_personas', $vendedor->id_personas)
            ->first();

        if (!$grupo) {
            return response()->json(['error' => 'Grupo no encontrado o no pertenece al vendedor'], 404);
        }

        try {
            $grupo->delete();
            
            if ($request->expectsJson()) {
                return response()->json(['success' => 'Grupo eliminado exitosamente']);
            }
            
            return redirect()->route('vendedor.app.grupos.index', ['empleado_id' => $empleadoId])
                ->with('success', 'Grupo eliminado exitosamente');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Error al eliminar el grupo'], 500);
            }
            
            return redirect()->back()->with('error', 'Error al eliminar el grupo');
        }
    }
}