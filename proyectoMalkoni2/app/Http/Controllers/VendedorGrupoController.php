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
                    $subQuery->where('id_personas', $vendedor->id_personas);
                }]);
            }])
            ->get();

        // Obtener empresas disponibles para agregar a grupos
        // (empresas que tienen cotizaciones del vendedor pero no están en ningún grupo)
        $empresasDisponibles = Empresa::whereHas('cotizaciones', function ($query) use ($vendedor) {
                $query->where('id_personas', $vendedor->id_personas);
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
        $request->validate([
            'nombre_grupo' => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:500',
            'empresas' => 'array',
            'empresas.*' => 'exists:empresas,id_empresa'
        ]);

        $empleadoId = $request->get('empleado_id', 1);
        $vendedor = Empleado::find($empleadoId);

        if (!$vendedor) {
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

        return redirect()->route('vendedor.app.grupos.index')
            ->with('success', 'Grupo creado exitosamente');
    }

    /**
     * Agregar empresa a un grupo
     */
    public function addEmpresa(Request $request, $grupoId)
    {
        $request->validate([
            'id_empresa' => 'required|exists:empresas,id_empresa'
        ]);

        $empleadoId = $request->get('empleado_id', 1);
        $vendedor = Empleado::find($empleadoId);

        $grupo = Grupo::where('id_grupo', $grupoId)
            ->where('id_personas', $vendedor->id_personas)
            ->first();

        if (!$grupo) {
            return response()->json(['error' => 'Grupo no encontrado'], 404);
        }

        // Verificar que la empresa tenga cotizaciones del vendedor
        $empresa = Empresa::whereHas('cotizaciones', function ($query) use ($vendedor) {
            $query->where('id_personas', $vendedor->id_personas);
        })->find($request->id_empresa);

        if (!$empresa) {
            return response()->json(['error' => 'Empresa no válida'], 400);
        }

        // Agregar empresa al grupo si no está ya agregada
        if (!$grupo->empresas()->where('id_empresa', $request->id_empresa)->exists()) {
            $grupo->empresas()->attach($request->id_empresa);
        }

        return response()->json(['success' => 'Empresa agregada al grupo']);
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

        $grupo = Grupo::where('id_grupo', $grupoId)
            ->where('id_personas', $vendedor->id_personas)
            ->first();

        if (!$grupo) {
            return redirect()->back()->with('error', 'Grupo no encontrado');
        }

        $grupo->delete();

        return redirect()->route('vendedor.app.grupos.index')
            ->with('success', 'Grupo eliminado exitosamente');
    }
}