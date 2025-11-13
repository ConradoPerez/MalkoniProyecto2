<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Empresa;
use App\Models\Cotizacion;
use App\Models\Empleado;
use App\Models\Producto;
use Illuminate\Support\Facades\DB;

class ClienteDashboardController extends Controller
{
    /**
     * Muestra la vista principal del Dashboard del Cliente.
     * La lógica se centra en obtener las cotizaciones relacionadas con el usuario autenticado.
     */
    public function dashboard()
    {
        // Obtener el ID del cliente autenticado.
        $clienteId = auth()->id(); 

        // 1. Obtener las últimas cotizaciones para la tabla del dashboard del cliente.
        // Se asume que el modelo Cotizacion tiene una columna 'cliente_id' o una relación.
        // Se incluye el empleado/vendedor para mostrar el nombre en la tabla.
        $ultimasCotizaciones = Cotizacion::with(['empresa', 'empleado', 'estadoActual'])
            // CORRECCIÓN FINAL: Se usa 'id_personas' basándose en la estructura del modelo.
            ->where('id_personas', $clienteId) // Filtra solo las cotizaciones del cliente
            ->orderByDesc('fyh')
            ->limit(5) // La imagen sugiere mostrar solo unas pocas
            ->get();
        
        // El dashboard del cliente no necesita métricas, ranking de productos, ni gráficos de vendedores, 
        // pero se mantiene la estructura de retorno simple.
        
        return view('cliente.dashboard', compact(
            'ultimasCotizaciones'
            // Puedes añadir 'mensajesCount', 'pedidosCount', etc. aquí si los calculas.
        ));
    }
    
    // =========================================================================
    // MÉTODOS ADICIONALES PARA EL SIDEBAR Y ACCIONES DE LA TABLA
    // Se han mantenido simples, asumiendo que solo devuelven una vista.
    // =========================================================================

    public function cotizaciones()
    {
        return view('cliente.cotizaciones.index'); // Asumiendo que esta vista existe
    }
    
    public function createQuotation()
    {
        return view('cliente.cotizaciones.create');
    }

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
        // CORRECCIÓN FINAL: Se usa 'id_personas'
        $cotizacion = Cotizacion::where('id_personas', auth()->id())->findOrFail($id);
        return view('cliente.cotizaciones.show', compact('cotizacion'));
    }

    public function editQuotation($id)
    {
        // CORRECCIÓN FINAL: Se usa 'id_personas'
        $cotizacion = Cotizacion::where('id_personas', auth()->id())->findOrFail($id);
        return view('cliente.cotizaciones.edit', compact('cotizacion'));
    }
    
    public function goToOPT()
    {
        // En una aplicación real, esto sería una redirección a un sistema externo
        // o una página de enlace.
        return redirect()->away('https://tu.sistema.opt/inicio'); 
    }
}