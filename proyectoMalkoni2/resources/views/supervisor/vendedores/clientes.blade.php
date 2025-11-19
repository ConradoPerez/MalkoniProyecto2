@extends('layouts.app')

@section('title', 'Clientes del Vendedor - Malkoni Hnos')
@section('page-title', 'VENDEDORES')

@section('content')
<div class="min-h-screen text-gray-900">
    <!-- Sidebar -->
    @include('supervisor.components.sidebar')

    <!-- Main content -->
    <main class="lg:ml-56">
        <!-- Mobile Header -->
        <div class="lg:hidden bg-white border-b border-gray-200 p-4 sticky top-0 z-10">
            <div class="flex items-center justify-between">
                <button id="mobile-menu-button" class="p-2 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                <div class="flex items-center">
                    <img src="{{ asset('logo/logo negro.png') }}" alt="Malkoni Logo" class="h-8 w-auto">
                </div>
                <div class="flex items-center space-x-2">
                    <span class="text-xs font-medium text-gray-900">
                        {{ isset($supervisor) && $supervisor ? $supervisor->nombre : 'Supervisor' }}
                    </span>
                    <div class="w-8 h-8 rounded-full bg-gray-300 flex items-center justify-center">
                        @if(isset($supervisor) && $supervisor->foto)
                            <img class="w-8 h-8 rounded-full object-cover" 
                                 src="{{ asset('storage/' . $supervisor->foto) }}" 
                                 alt="{{ $supervisor->nombre }}">
                        @else
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="p-4 lg:p-8">
            <!-- Header del Supervisor -->
            @include('supervisor.components.header')
            
            <!-- Contenido -->
            <div class="mb-8">
                <div class="flex items-center gap-4 mb-4">
                    <a href="{{ route('vendedor.index') }}" 
                       class="px-3 py-2 border rounded-lg text-xs sm:text-sm font-medium hover:bg-gray-100 transition-colors"
                       style="border-color: #B1B7BB; color: #B1B7BB;">
                        <span class="sm:hidden">← Volver</span>
                        <span class="hidden sm:inline">← Volver a Vendedores</span>
                    </a>
                </div>
                    <h1 class="text-3xl font-syncopate font-bold text-gray-900">
                        Clientes del Vendedor
                    </h1>
                    <p class="text-gray-600 mt-2">Vendedor: {{ $vendedor->nombre }}</p>
                </div>

                <!-- Lista de clientes -->
                @if($todosClientes->count() > 0)
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
                        <!-- Header de la tabla -->
                        <div class="px-4 py-3 border-b border-gray-200 bg-gray-50 rounded-t-lg">
                            <h3 class="text-sm font-syncopate font-bold text-gray-900">
                                Lista de Clientes ({{ $todosClientes->total() }} encontrados)
                            </h3>
                        </div>

                        <!-- Tabla de clientes -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Cliente
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            CUIT
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Cotizaciones
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Última Cotización
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($todosClientes as $cliente)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-10 w-10">
                                                        @if($cliente->foto)
                                                            <img class="h-10 w-10 rounded-full object-cover" 
                                                                 src="{{ asset('storage/' . $cliente->foto) }}" 
                                                                 alt="{{ $cliente->nombre }}">
                                                        @else
                                                            <div class="h-10 w-10 rounded-full flex items-center justify-center"
                                                                 style="background-color: #FEF2E6;">
                                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" 
                                                                     style="color: #D88429;">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                                          d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                                                </svg>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            {{ $cliente->nombre }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $cliente->cuit_formateado ?? 'Sin CUIT' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                      style="background-color: #E6F4F7; color: #166379;">
                                                    {{ $cliente->total_cotizaciones }} cotizaciones
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                @if($cliente->ultima_cotizacion)
                                                    <div>
                                                        <div class="font-medium">
                                                            {{ $cliente->ultima_cotizacion->created_at->format('d/m/Y') }}
                                                        </div>
                                                        <div class="text-gray-500">
                                                            {{ $cliente->ultima_cotizacion->titulo ?? 'Sin título' }}
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="text-gray-500">Sin cotizaciones</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="{{ route('vendedor.cotizaciones', $vendedor->id_empleado) }}" 
                                                   class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white hover:opacity-90 transition-colors"
                                                   style="background-color: #166379;">
                                                    Ver Cotizaciones
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginación -->
                        @if($todosClientes->hasPages())
                            <div class="px-6 py-3 border-t border-gray-200 bg-gray-50 rounded-b-lg">
                                {{ $todosClientes->links() }}
                            </div>
                        @endif
                    </div>
                @else
                    <!-- Estado vacío -->
                    <div class="bg-white rounded-lg p-4 sm:p-6 lg:p-8 border border-gray-200 shadow-sm text-center">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center" 
                             style="background-color: #FEF2E6;">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" 
                                 style="color: #D88429;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-syncopate font-bold text-gray-900 mb-2">
                            Sin Clientes Asignados
                        </h3>
                        <p class="text-gray-600">
                            Este vendedor aún no tiene clientes asignados o no ha realizado cotizaciones.
                        </p>
                    </div>
                @endif
        </div>
    </main>
</div>
@endsection