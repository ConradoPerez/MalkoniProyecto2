@extends('layouts.app')

@section('title', 'Mis Cotizaciones')

@section('content')
<div class="min-h-screen text-gray-900" style="background-color: #e1dfd9;">
    <div class="flex">
        @include('cliente.components.sidebar')

        <main class="flex-1 overflow-y-auto md:ml-64 transition-all duration-300">
            
            <div class="p-4 lg:p-8">
                @include('cliente.components.header')

                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 mt-8">
                    <div>
                        <h2 class="text-2xl font-syncopate font-bold text-gray-800">
                            MIS COTIZACIONES
                        </h2>
                        <p class="text-sm text-gray-500 mt-1 font-medium">Gestiona y revisa el historial de tus solicitudes</p>
                    </div>
                    
                    <a href="{{ route('cliente.nueva_cotizacion', ['persona_id' => $personaId]) }}" class="inline-flex items-center justify-center px-5 py-2.5 bg-[#D88429] hover:bg-[#c7731f] text-white text-sm font-medium rounded-lg transition-colors shadow-sm hover:shadow md:w-auto w-full">
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
                                    @php
                                        $estadoColores = [
                                            'Nuevo' => 'bg-blue-100 text-blue-800',
                                            'Abierto' => 'bg-yellow-100 text-yellow-800',
                                            'Cotizado' => 'bg-green-100 text-green-800',
                                            'En entrega' => 'bg-purple-100 text-purple-800',
                                        ];
                                    @endphp
                                    @foreach($cotizaciones as $cotizacion)
                                        <tr class="hover:bg-gray-50/80 transition-colors group">
                                            <td class="px-6 py-4">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $estadoColores[$cotizacion->estado_actual->nombre ?? 'Nuevo'] ?? 'bg-gray-100 text-gray-800' }}">
                                                    {{ $cotizacion->estado_actual->nombre ?? 'Nuevo' }}
                                                </span>
                                            </td>

                                            <td class="px-6 py-4">
                                                <span class="text-sm font-semibold text-gray-900 group-hover:text-[#166379] transition-colors">
                                                    {{ $cotizacion->numero_formateado }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="text-sm text-gray-600">
                                                    {{ $cotizacion->fyh->timezone('America/Argentina/Buenos_Aires')->format('d/m/Y') }}
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
                                                @php
                                                    $estadoActual = $cotizacion->estado_actual->nombre ?? 'Nuevo';
                                                    $sinPrecio = in_array($estadoActual, ['Nuevo', 'Abierto']) || !$cotizacion->precio_total || $cotizacion->precio_total <= 0;
                                                @endphp
                                                @if($sinPrecio)
                                                    <span class="text-sm text-gray-500 italic">Sin Cotizar</span>
                                                @else
                                                    <span class="text-sm font-bold text-gray-900">
                                                        ${{ number_format($cotizacion->precio_total, 0, ',', '.') }}
                                                    </span>
                                                @endif
                                            </td>
                                            
                                            <td class="px-6 py-4 text-center">
                                                <div class="flex items-center justify-center gap-2">
                                                    <a href="{{ route('cliente.cotizacion.ver', ['id' => $cotizacion->id, 'persona_id' => $personaId]) }}" class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-lg text-gray-700 text-sm font-medium bg-white hover:bg-gray-50 transition-colors">
                                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                        </svg>
                                                        Ver detalle
                                                    </a>
                                                </div>
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
                            <a href="{{ route('cliente.nueva_cotizacion', ['persona_id' => $personaId]) }}" class="px-6 py-2 bg-[#D88429] text-white font-semibold rounded-lg shadow hover:bg-[#c7731f] transition-colors">
                                Crear mi primera cotización
                            </a>
                        </div>
                    @endif
                </div>

                <!-- Leyenda de estados -->
                <div class="mt-8">
                    <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4">Referencia de Estados</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="bg-white p-3 rounded-lg border border-gray-200 shadow-sm">
                            <div class="flex items-center gap-2">
                                <span class="inline-block w-3 h-3 rounded-full" style="background-color: #95c4ecff;"></span>
                                <div>
                                    <span class="text-xs font-bold text-gray-900">Nuevo</span>
                                    <p class="text-xs text-gray-500">Recién creado</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white p-3 rounded-lg border border-gray-200 shadow-sm">
                            <div class="flex items-center gap-2">
                                <span class="inline-block w-3 h-3 rounded-full" style="background-color: #ecee80ff;"></span>
                                <div>
                                    <span class="text-xs font-bold text-gray-900">Abierto</span>
                                    <p class="text-xs text-gray-500">En análisis</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white p-3 rounded-lg border border-gray-200 shadow-sm">
                            <div class="flex items-center gap-2">
                                <span class="inline-block w-3 h-3 rounded-full" style="background-color: #72d89bff;"></span>
                                <div>
                                    <span class="text-xs font-bold text-gray-900">Cotizado</span>
                                    <p class="text-xs text-gray-500">Precio definido</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white p-3 rounded-lg border border-gray-200 shadow-sm">
                            <div class="flex items-center gap-2">
                                <span class="inline-block w-3 h-3 rounded-full" style="background-color: #ae74dadc;"></span>
                                <div>
                                    <span class="text-xs font-bold text-gray-900">En entrega</span>
                                    <p class="text-xs text-gray-500">Preparando pedido</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>
</div>
@endsection
