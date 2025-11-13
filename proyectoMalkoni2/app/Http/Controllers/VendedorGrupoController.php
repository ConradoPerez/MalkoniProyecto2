<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VendedorGrupoController extends Controller
{
    /**
     * Mostrar los grupos de clientes del vendedor
     */
    public function index()
    {
        // Aquí puedes agregar lógica para obtener grupos y clientes
        // Ejemplo de consulta (cuando tengas los modelos):
        /*
        $grupos = Grupo::where('vendedor_id', auth()->id())
                      ->with(['empresas' => function($query) {
                          $query->select('id', 'cuit', 'nombre', 'grupo_id');
                      }])
                      ->get();
        */
        
        // Por ahora retornamos datos de ejemplo
        $grupos = [
            [
                'nombre' => 'Interior',
                'clientes' => [
                    ['cuit' => '20-44896243-4', 'razon_social' => 'Empresa A', 'usuario' => 'usuario1'],
                    ['cuit' => '20-12345678-9', 'razon_social' => 'Empresa B', 'usuario' => 'usuario2'],
                ]
            ],
            [
                'nombre' => 'Capital',
                'clientes' => [
                    ['cuit' => '20-98765432-1', 'razon_social' => 'Empresa C', 'usuario' => 'usuario3'],
                ]
            ]
        ];
        
        return view('vendedor.grupos.index', compact('grupos'));
    }
}
