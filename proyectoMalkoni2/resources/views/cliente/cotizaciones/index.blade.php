@extends('layouts.app')

@section('title', 'Mis Cotizaciones')

@section('content')
<div class="min-h-screen text-gray-900">
    <!-- Sidebar -->
    @include('cliente.components.sidebar')

    <!-- Main content -->
    <main>
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
                    <div class="w-8 h-8 rounded-full bg-gray-300"></div>
                </div>
            </div>
        </div>

        <!-- Desktop Header with offset -->
        <div class="hidden lg:flex sticky top-0 z-20 bg-white border-b border-gray-200 p-4 lg:p-8 justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Mis Cotizaciones</h1>
            </div>
            <a href="{{ route('cliente.nueva_cotizacion') }}" class="px-4 py-2 bg-[#D88429] text-white font-semibold rounded shadow hover:bg-[#c7731f] transition-colors">
                + Nueva Cotización
            </a>
        </div>

        <div class="p-4 lg:p-8">
                
                <!-- Mobile Header -->
                <div class="lg:hidden flex justify-between items-start border-b pb-4 mb-6">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 mb-2">Mis Cotizaciones</h1>
                        <p class="text-sm text-gray-600">Gestiona tus solicitudes de presupuesto</p>
                    </div>
                    
                    <a href="{{ route('cliente.nueva_cotizacion') }}" class="px-4 py-2 bg-[#D88429] text-white font-semibold rounded shadow hover:bg-[#c7731f] transition-colors">
                        + Nueva Cotización
                    </a>
                </div>

                <!-- Filtros y Búsqueda -->
                <div class="bg-white border border-gray-400 rounded p-4 mb-6">
                    <div class="flex flex-col md:flex-row gap-3 items-center">
                        <input type="text" placeholder="Buscar Cotizaciones" class="flex-1 px-4 py-2 border border-gray-400 rounded focus:ring-2 focus:ring-[#D88429] focus:border-transparent">
                        <select class="px-4 py-2 border border-gray-400 rounded focus:ring-2 focus:ring-[#D88429] focus:border-transparent bg-white">
                            <option>Fecha</option>
                            <option>Más recientes</option>
                            <option>Más antiguos</option>
                        </select>
                        <select class="px-4 py-2 border border-gray-400 rounded focus:ring-2 focus:ring-[#D88429] focus:border-transparent bg-white">
                            <option>Vendedor</option>
                        </select>
                        <select class="px-4 py-2 border border-gray-400 rounded focus:ring-2 focus:ring-[#D88429] focus:border-transparent bg-white">
                            <option>Nº de cotización</option>
                        </select>
                        <button class="px-6 py-2 bg-white border border-gray-400 text-gray-900 font-semibold rounded hover:bg-gray-50 transition-colors">Buscar</button>
                    </div>
                </div>

                <!-- Tabla de Cotizaciones -->
                <div class="bg-white border border-gray-300 rounded-lg overflow-hidden">
                    @if($cotizaciones->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="bg-gray-200 border-b border-gray-300">
                                        <th class="px-6 py-3 text-center text-sm font-semibold text-gray-900">Estado</th>
                                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Nº de Cotización</th>
                                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Fecha de Inicio</th>
                                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Vendedor</th>
                                        <th class="px-6 py-3 text-right text-sm font-semibold text-gray-900">Total</th>
                                        <th class="px-6 py-3 text-center text-sm font-semibold text-gray-900">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($cotizaciones as $cotizacion)
                                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                                            <td class="px-6 py-4 text-center">
                                                @php
                                                    $estado = $cotizacion->estado ?? 'Pendiente';
                                                    $colorMap = [
                                                        'Nuevo' => 'bg-orange-500',
                                                        'Abierto' => 'bg-yellow-400',
                                                        'Cotizado' => 'bg-green-500',
                                                        'En entrega' => 'bg-blue-600',
                                                        'Pendiente' => 'bg-orange-500'
                                                    ];
                                                    $color = $colorMap[$estado] ?? 'bg-gray-400';
                                                @endphp
                                                <span class="inline-block w-4 h-4 rounded-full {{ $color }}"></span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="font-medium">{{ $cotizacion->numero_formateado }}</span>
                                            </td>
                                            <td class="px-6 py-4 text-sm">
                                                {{ $cotizacion->fyh->format('d/m/Y') }}
                                            </td>
                                            <td class="px-6 py-4">
                                                <p class="font-medium">{{ $cotizacion->empleado->nombre ?? 'N/A' }}</p>
                                            </td>
                                            <td class="px-6 py-4 text-right font-medium">
                                                ${{ number_format($cotizacion->precio_total / 100, 2, ',', '.') }}
                                            </td>
                                            <td class="px-6 py-4 text-center">
                                                <a href="{{ route('cliente.cotizacion.ver', ['id' => $cotizacion->id]) }}" class="text-blue-600 font-semibold hover:underline">
                                                    Ver
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-6 py-8 text-center text-gray-600">
                                                <p class="mb-4">No hay cotizaciones aún.</p>
                                                <a href="{{ route('cliente.nueva_cotizacion') }}" class="text-[#D88429] font-semibold hover:underline">
                                                    Crear nueva cotización
                                                </a>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Footer con "Ver más" -->
                        <div class="px-6 py-4 border-t border-gray-300 flex justify-end">
                            @if($cotizaciones->hasPages())
                                <a href="{{ $cotizaciones->nextPageUrl() }}" class="px-6 py-2 text-gray-600 font-semibold hover:text-gray-900 transition-colors">
                                    Ver más
                                </a>
                            @endif
                        </div>
                    @else
                        <div class="px-6 py-16 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No hay cotizaciones</h3>
                            <p class="text-gray-600 mb-4">Comienza creando tu primera cotización</p>
                            <a href="{{ route('cliente.nueva_cotizacion') }}" class="inline-block px-6 py-2 bg-[#D88429] text-white font-semibold rounded shadow hover:bg-[#c7731f] transition-colors">
                                Crear Nueva Cotización
                            </a>
                        </div>
                    @endif
                </div>

                <!-- Leyenda de Estados -->
                <div class="mt-8 flex flex-wrap gap-6 justify-center pb-4">
                    <div class="flex items-center gap-2">
                        <span class="w-4 h-4 rounded-full bg-orange-500"></span>
                        <span class="text-gray-700 text-sm">Nuevo</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-4 h-4 rounded-full bg-yellow-400"></span>
                        <span class="text-gray-700 text-sm">Abierto</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-4 h-4 rounded-full bg-green-500"></span>
                        <span class="text-gray-700 text-sm">Cotizado</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-4 h-4 rounded-full bg-blue-600"></span>
                        <span class="text-gray-700 text-sm">En entrega</span>
                    </div>
                </div>
        </main>
    </div>
</div>

@endsection
