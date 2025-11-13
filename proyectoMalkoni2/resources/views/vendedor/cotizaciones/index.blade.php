@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-background text-gray-900">
    <div class="flex">
        {{-- Sidebar fijo --}}
        @include('vendedor.components.sidebar')

        {{-- Contenido principal --}}
        <main class="flex-1 overflow-y-auto ml-48">
            <div class="p-4 lg:p-8">

                {{-- Header mejorado --}}
                <div class="flex flex-col lg:flex-row lg:items-center justify-between mb-8 gap-4">
                    <div>
                        <h1 class="text-3xl font-syncopate font-bold text-gray-900 tracking-wide">
                            COTIZACIONES
                        </h1>
                        <p class="text-gray-600 mt-1">
                            Gestiona y busca cotizaciones de {{ isset($vendedor) ? $vendedor->nombre : 'Vendedor' }}
                        </p>
                    </div>

                    <div class="flex items-center gap-3 bg-white border border-gray-200 rounded-xl px-4 py-3 shadow-sm">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-r from-amber-500 to-orange-600 grid place-items-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <div class="text-sm">
                            <div class="font-semibold text-gray-900">{{ isset($vendedor) ? $vendedor->nombre : 'Vendedor' }}</div>
                            <div class="text-gray-500">{{ isset($vendedor) ? $vendedor->rol->nombre ?? 'Vendedor' : 'Vendedor activo' }}</div>
                        </div>
                    </div>
                </div>

                {{-- Formulario de búsqueda simple --}}
                <section class="bg-white border border-gray-200 rounded-xl p-6 mb-6 shadow-sm">
                    <h2 class="text-base font-semibold text-gray-900 mb-4">Buscar Cotizaciones</h2>

                    <form method="GET" action="{{ route('vendedor.app.cotizaciones.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        @if(request('empleado_id'))
                            <input type="hidden" name="empleado_id" value="{{ request('empleado_id') }}">
                        @endif
                        
                        <div class="flex flex-col gap-1">
                            <label class="text-sm text-gray-600">Por Número de pedido</label>
                            <input type="text" 
                                   name="nropedido" 
                                   value="{{ request('nropedido') }}"
                                   placeholder="Ej: 1001"
                                   class="h-10 rounded-lg border border-gray-300 px-3 outline-none focus:ring-2 focus:ring-[#D88429]/20 focus:border-[#D88429]" />
                        </div>

                        <div class="flex flex-col gap-1">
                            <label class="text-sm text-gray-600">Por nombre del Cliente</label>
                            <input type="text" 
                                   name="cliente" 
                                   value="{{ request('cliente') }}"
                                   placeholder="Nombre de la empresa"
                                   class="h-10 rounded-lg border border-gray-300 px-3 outline-none focus:ring-2 focus:ring-[#D88429]/20 focus:border-[#D88429]" />
                        </div>

                        <div class="flex flex-col gap-1">
                            <label class="text-sm text-gray-600">Por CUIT</label>
                            <input type="text" 
                                   name="doc" 
                                   value="{{ request('doc') }}"
                                   placeholder="CUIT de la empresa"
                                   class="h-10 rounded-lg border border-gray-300 px-3 outline-none focus:ring-2 focus:ring-[#D88429]/20 focus:border-[#D88429]" />
                        </div>

                        <div class="flex flex-col justify-end">
                            <button type="submit"
                                    class="h-10 w-full inline-flex items-center justify-center rounded-lg text-white font-semibold transition"
                                    style="background-color:#D88429;">
                                Buscar
                            </button>
                        </div>
                    </form>
                </section>

                {{-- Estadísticas y contador --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-4">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl font-bold text-gray-900">{{ number_format($total) }}</span>
                        <span class="text-gray-600">cotizaciones en total</span>
                    </div>
                    
                    @if(request()->hasAny(['nropedido', 'cliente', 'doc']))
                        <div class="text-sm text-gray-600">
                            Mostrando {{ $cotizaciones->count() }} de {{ number_format($cotizaciones->total()) }} resultados filtrados
                        </div>
                    @endif
                </div>

                {{-- Tabla mejorada --}}
                <section class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                    @if($cotizaciones->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50 border-b border-gray-200">
                                    <tr class="text-left">
                                        <th class="px-6 py-4 text-sm font-semibold text-gray-700">Estado</th>
                                        <th class="px-6 py-4 text-sm font-semibold text-gray-700">N° Cotización</th>
                                        <th class="px-6 py-4 text-sm font-semibold text-gray-700">Cliente</th>
                                        <th class="px-6 py-4 text-sm font-semibold text-gray-700">CUIT</th>
                                        <th class="px-6 py-4 text-sm font-semibold text-gray-700">Fecha</th>
                                        <th class="px-6 py-4 text-sm font-semibold text-gray-700 text-right">Monto</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @php
                                        $estadoColores = [
                                            'Nuevo' => 'bg-blue-100 text-blue-800',
                                            'Abierto' => 'bg-yellow-100 text-yellow-800',
                                            'Cotizado' => 'bg-green-100 text-green-800',
                                            'En entrega' => 'bg-purple-100 text-purple-800',
                                        ];
                                    @endphp
                                    
                                    @foreach($cotizaciones as $cotizacion)
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-6 py-4">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $estadoColores[$cotizacion->estado_actual->nombre ?? 'Nuevo'] ?? 'bg-gray-100 text-gray-800' }}">
                                                    {{ $cotizacion->estado_actual->nombre ?? 'Sin estado' }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="text-sm font-bold text-gray-900">#{{ $cotizacion->numero }}</span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="flex flex-col">
                                                    <span class="text-sm font-medium text-gray-900">{{ $cotizacion->empresa->nombre ?? 'Cliente no encontrado' }}</span>
                                                    <span class="text-xs text-gray-500">{{ $cotizacion->titulo ?? 'Sin título' }}</span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="text-sm text-gray-700">{{ $cotizacion->empresa->cuit ?? 'Sin CUIT' }}</span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="text-sm text-gray-700">{{ $cotizacion->fyh ? $cotizacion->fyh->format('d/m/Y H:i') : 'Sin fecha' }}</span>
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <span class="text-sm font-bold text-gray-900">${{ number_format($cotizacion->precio_total, 2, ',', '.') }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Paginación --}}
                        @if($cotizaciones->hasPages())
                            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                                <div class="flex items-center justify-between">
                                    <div class="text-sm text-gray-700">
                                        Mostrando {{ $cotizaciones->firstItem() }} a {{ $cotizaciones->lastItem() }} de {{ $cotizaciones->total() }} resultados
                                    </div>
                                    <div>
                                        {{ $cotizaciones->appends(request()->query())->links('custom.pagination') }}
                                    </div>
                                </div>
                            </div>
                        @endif
                    @else
                        {{-- Estado vacío --}}
                        <div class="text-center py-16">
                            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No se encontraron cotizaciones</h3>
                            <p class="text-gray-500 mb-4">
                                @if(request()->hasAny(['nropedido', 'cliente', 'doc']))
                                    No hay cotizaciones que coincidan con los criterios de búsqueda.
                                @else
                                    Aún no hay cotizaciones registradas para este vendedor.
                                @endif
                            </p>
                            @if(request()->hasAny(['nropedido', 'cliente', 'doc']))
                                <a href="{{ route('vendedor.app.cotizaciones.index', ['empleado_id' => request('empleado_id')]) }}"
                                   class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-amber-500 to-orange-600 text-white font-semibold rounded-lg hover:from-amber-600 hover:to-orange-700 transition-all">
                                    Ver todas las cotizaciones
                                </a>
                            @endif
                        </div>
                    @endif
                </section>

                {{-- Leyenda de estados actualizada --}}
                <div class="mt-8 bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                    <h3 class="text-sm font-semibold text-gray-900 mb-4">Estados de Cotización</h3>
                    <div class="flex flex-wrap justify-between items-center gap-6">
                        <div class="flex items-center gap-3">
                            <span class="inline-block w-4 h-4 rounded-full" style="background-color: #446dddde;"></span>
                            <div>
                                <span class="text-sm font-medium text-gray-900">Nuevo</span>
                                <p class="text-xs text-gray-500">Cotización recién creada</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="inline-block w-4 h-4 rounded-full" style="background-color: #ecee80ff;"></span>
                            <div>
                                <span class="text-sm font-medium text-gray-900">Abierto</span>
                                <p class="text-xs text-gray-500">En proceso de análisis</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="inline-block w-4 h-4 rounded-full" style="background-color: #72d89bff;"></span>
                            <div>
                                <span class="text-sm font-medium text-gray-900">Cotizado</span>
                                <p class="text-xs text-gray-500">Precio definido</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="inline-block w-4 h-4 rounded-full" style="background-color: #ae74dadc;"></span>
                            <div>
                                <span class="text-sm font-medium text-gray-900">En entrega</span>
                                <p class="text-xs text-gray-500">Preparando pedido</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>
</div>
@endsection
