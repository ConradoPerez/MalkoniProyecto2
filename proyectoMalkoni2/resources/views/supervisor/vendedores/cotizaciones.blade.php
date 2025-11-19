@extends('layouts.app')

@section('title', 'Cotizaciones del Vendedor - Malkoni Hnos')
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
                        Cotizaciones del Vendedor
                    </h1>
                    <p class="text-gray-600 mt-2">Vendedor: {{ $vendedor->nombre }}</p>
                </div>

                <!-- Estadísticas generales -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center"
                                     style="background-color: #E6F4F7;">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" 
                                         style="color: #166379;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Total Cotizaciones</p>
                                <p class="text-2xl font-bold text-gray-900">{{ number_format($estadisticas['total_cotizaciones']) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center"
                                     style="background-color: #FEF2E6;">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" 
                                         style="color: #D88429;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M8 7V3a4 4 0 118 0v4m-9 4a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2H9z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Este Mes</p>
                                <p class="text-2xl font-bold text-gray-900">{{ number_format($estadisticas['cotizaciones_mes']) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center"
                                     style="background-color: #F3E8FF;">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" 
                                         style="color: #7C3AED;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Monto Mes</p>
                                <p class="text-2xl font-bold text-gray-900">${{ number_format($estadisticas['monto_total_mes']) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center"
                                     style="background-color: #ECFDF5;">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" 
                                         style="color: #10B981;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Clientes Únicos</p>
                                <p class="text-2xl font-bold text-gray-900">{{ number_format($estadisticas['clientes_unicos']) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lista de cotizaciones -->
                @if($cotizaciones->count() > 0)
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
                        <!-- Header de la tabla -->
                        <div class="px-4 py-3 border-b border-gray-200 bg-gray-50 rounded-t-lg">
                            <h3 class="text-sm font-syncopate font-bold text-gray-900">
                                Lista de Cotizaciones ({{ $cotizaciones->total() }} encontradas)
                            </h3>
                        </div>

                        <!-- Tabla de cotizaciones -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Cotización
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Cliente
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Estado
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Items
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Precio Total
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Fecha
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Días
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($cotizaciones as $cotizacion)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900">
                                                        {{ $cotizacion->titulo ?? 'Sin título' }}
                                                    </div>
                                                    <div class="text-sm text-gray-500">
                                                        #{{ str_pad($cotizacion->numero, 7, '0', STR_PAD_LEFT) }}
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $cotizacion->cliente_nombre }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($cotizacion->estado_actual)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium text-white"
                                                          style="{{ $cotizacion->estado_actual->estado_estilo }}">
                                                        {{ $cotizacion->estado_actual->nombre }}
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium text-white"
                                                          style="background-color: #B1B7BB;">
                                                        Pendiente
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $cotizacion->total_items }} items
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                @if($cotizacion->precio_total)
                                                    ${{ number_format($cotizacion->precio_total, 0, ',', '.') }}
                                                @else
                                                    <span class="text-gray-500">Sin precio</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $cotizacion->fyh->format('d/m/Y H:i') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $cotizacion->dias_transcurridos }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginación -->
                        @if($cotizaciones->hasPages())
                            <div class="px-6 py-3 border-t border-gray-200 bg-gray-50 rounded-b-lg">
                                {{ $cotizaciones->links() }}
                            </div>
                        @endif
                    </div>
                @else
                    <!-- Estado vacío -->
                    <div class="bg-white rounded-lg p-4 sm:p-6 lg:p-8 border border-gray-200 shadow-sm text-center">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center" 
                             style="background-color: #E6F4F7;">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" 
                                 style="color: #166379;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-syncopate font-bold text-gray-900 mb-2">
                            Sin Cotizaciones
                        </h3>
                        <p class="text-gray-600">
                            Este vendedor aún no ha realizado cotizaciones.
                        </p>
                    </div>
                @endif
        </div>
    </main>
</div>
@endsection