@extends('layouts.app')

@section('title', 'Mis Cotizaciones')

@section('content')
<div class="min-h-screen bg-gray-50 text-gray-900">
    <div class="flex">
        @include('cliente.components.sidebar')

        <main class="flex-1 overflow-y-auto md:ml-64 transition-all duration-300">
            
            <div class="p-4 lg:p-8">
                @include('cliente.components.header')

                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 mt-8">
                    <div>
                        <h2 class="text-xl font-syncopate font-bold text-gray-800">
                            Mis Cotizaciones
                        </h2>
                        <p class="text-sm text-gray-500 mt-1">Gestiona y revisa el historial de tus solicitudes</p>
                    </div>
                    
                    <a href="{{ route('cliente.nueva_cotizacion') }}" class="inline-flex items-center justify-center px-5 py-2.5 bg-[#D88429] hover:bg-[#c7731f] text-white text-sm font-medium rounded-lg transition-colors shadow-sm hover:shadow md:w-auto w-full">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Nueva Cotización
                    </a>
                </div>

                <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6">
                    <div class="flex flex-col md:flex-row gap-3 items-center">
                        <div class="relative flex-1 w-full">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input type="text" placeholder="Buscar por número..." class="block w-full pl-10 px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-[#D88429] focus:border-[#D88429] bg-gray-50 focus:bg-white transition-colors">
                        </div>

                        <div class="grid grid-cols-2 md:flex gap-3 w-full md:w-auto">
                            <select class="px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-[#D88429] focus:border-[#D88429] bg-white text-gray-600 cursor-pointer hover:border-gray-300">
                                <option>Fecha</option>
                                <option>Más recientes</option>
                                <option>Más antiguos</option>
                            </select>
                            <select class="px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-[#D88429] focus:border-[#D88429] bg-white text-gray-600 cursor-pointer hover:border-gray-300">
                                <option>Vendedor</option>
                                </select>
                        </div>
                        
                        <button class="w-full md:w-auto px-6 py-2 bg-gray-900 text-white text-sm font-medium rounded-lg hover:bg-gray-800 transition-colors">
                            Filtrar
                        </button>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    @if($cotizaciones->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full whitespace-nowrap">
                                <thead>
                                    <tr class="bg-gray-50/50 border-b border-gray-100 text-left">
                                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">Estado</th>
                                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Nº Cotización</th>
                                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Fecha</th>
                                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Vendedor</th>
                                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Total</th>
                                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    @foreach($cotizaciones as $cotizacion)
                                        <tr class="hover:bg-gray-50/80 transition-colors group">
                                            <td class="px-6 py-4 text-center">
                                                @php
                                                    $estado = $cotizacion->estado ?? 'Pendiente';
                                                    $colorMap = [
                                                        'Nuevo' => 'bg-orange-500 shadow-orange-200',
                                                        'Abierto' => 'bg-yellow-400 shadow-yellow-200',
                                                        'Cotizado' => 'bg-green-500 shadow-green-200',
                                                        'En entrega' => 'bg-blue-600 shadow-blue-200',
                                                        'Pendiente' => 'bg-gray-400 shadow-gray-200'
                                                    ];
                                                    $colorClass = $colorMap[$estado] ?? 'bg-gray-400 shadow-gray-200';
                                                @endphp
                                                <div class="flex justify-center">
                                                    <span class="h-3 w-3 rounded-full {{ $colorClass }} shadow-sm ring-2 ring-white" title="{{ $estado }}"></span>
                                                </div>
                                            </td>

                                            <td class="px-6 py-4">
                                                <span class="text-sm font-semibold text-gray-900 group-hover:text-[#166379] transition-colors">
                                                    {{ $cotizacion->numero_formateado }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="text-sm text-gray-600">
                                                    {{ $cotizacion->fyh->format('d M, Y') }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="flex items-center">
                                                    <div class="h-6 w-6 rounded-full bg-gray-100 flex items-center justify-center text-xs font-bold text-gray-500 mr-2">
                                                        {{ substr($cotizacion->empleado->nombre ?? 'A', 0, 1) }}
                                                    </div>
                                                    <span class="text-sm text-gray-700">{{ $cotizacion->empleado->nombre ?? 'Sin asignar' }}</span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <span class="text-sm font-bold text-gray-900">
                                                    ${{ number_format($cotizacion->precio_total / 100, 2, ',', '.') }}
                                                </span>
                                            </td>
                                            
                                            <td class="px-6 py-4 text-center">
                                                <a href="{{ route('cliente.cotizacion.ver', ['id' => $cotizacion->id]) }}" class="inline-flex items-center px-3 py-1.5 border border-gray-200 rounded-lg text-xs font-medium text-gray-600 bg-white hover:bg-gray-50 hover:text-[#166379] hover:border-[#166379] transition-all">
                                                    Ver Detalle
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if($cotizaciones->hasPages())
                        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex justify-end">
                            <a href="{{ $cotizaciones->nextPageUrl() }}" class="text-sm font-medium text-[#166379] hover:text-[#0e4555] hover:underline transition-colors">
                                Ver más cotizaciones &rarr;
                            </a>
                        </div>
                        @endif
                    @else
                        <div class="flex flex-col items-center justify-center py-16 px-4 text-center">
                            <div class="bg-gray-50 rounded-full p-4 mb-4">
                                <svg class="h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900">Aún no tienes cotizaciones</h3>
                            <p class="text-gray-500 mt-1 mb-6 max-w-sm">Comienza solicitando un presupuesto para tus productos de interés.</p>
                            <a href="{{ route('cliente.nueva_cotizacion') }}" class="px-6 py-2 bg-[#D88429] text-white font-semibold rounded-lg shadow hover:bg-[#c7731f] transition-colors">
                                Crear mi primera cotización
                            </a>
                        </div>
                    @endif
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-8">
                    <div class="bg-white p-3 rounded-lg border border-gray-200 shadow-sm flex items-center space-x-3">
                        <span class="h-2.5 w-2.5 rounded-full bg-orange-500 shadow-sm shadow-orange-200"></span>
                        <span class="text-xs font-medium text-gray-600">Nuevo</span>
                    </div>
                    <div class="bg-white p-3 rounded-lg border border-gray-200 shadow-sm flex items-center space-x-3">
                        <span class="h-2.5 w-2.5 rounded-full bg-yellow-400 shadow-sm shadow-yellow-200"></span>
                        <span class="text-xs font-medium text-gray-600">Abierto</span>
                    </div>
                    <div class="bg-white p-3 rounded-lg border border-gray-200 shadow-sm flex items-center space-x-3">
                        <span class="h-2.5 w-2.5 rounded-full bg-green-500 shadow-sm shadow-green-200"></span>
                        <span class="text-xs font-medium text-gray-600">Cotizado</span>
                    </div>
                    <div class="bg-white p-3 rounded-lg border border-gray-200 shadow-sm flex items-center space-x-3">
                        <span class="h-2.5 w-2.5 rounded-full bg-blue-600 shadow-sm shadow-blue-200"></span>
                        <span class="text-xs font-medium text-gray-600">En entrega</span>
                    </div>
                </div>

            </div>
        </main>
    </div>
</div>
@endsection