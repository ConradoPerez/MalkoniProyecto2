@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-background text-gray-900">
    <div class="flex">
        {{-- Sidebar --}}
        @include('vendedor.components.sidebar')

        {{-- Contenido principal --}}
        <main class="flex-1 overflow-y-auto ml-56">
            <div class="p-4 lg:p-8">

                {{-- Header mejorado --}}
                <div class="flex flex-col lg:flex-row lg:items-center justify-between mb-8 gap-4">
                    <div>
                        <h1 class="text-3xl font-syncopate font-bold text-gray-900 tracking-wide">
                            COTIZACIONES DE {{ strtoupper($empresa->nombre) }}
                        </h1>
                        <p class="text-gray-600 mt-1">
                            CUIT: {{ $empresa->cuit_formateado }}
                        </p>
                    </div>

                    <div class="flex items-center gap-4">
                        {{-- Botón volver --}}
                        <a href="{{ route('vendedor.app.clientes.index', ['empleado_id' => request('empleado_id', 1)]) }}" 
                           class="inline-flex items-center px-6 py-3 rounded-lg text-white font-semibold transition hover:opacity-90 shadow-md"
                           style="background-color:#D88429;">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Volver a Clientes
                        </a>

                        {{-- Tarjeta del vendedor --}}
                        <div class="flex items-center gap-3 bg-white border border-gray-200 rounded-xl px-4 py-3 shadow-sm">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-r from-amber-500 to-orange-600 grid place-items-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <div class="text-sm">
                                <div class="font-semibold text-gray-900">{{ $vendedor->nombre ?? 'Vendedor' }}</div>
                                <div class="text-gray-500">Vendedor activo</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Caja de tabla de cotizaciones --}}
                <section class="bg-white border border-gray-200 rounded-xl shadow-sm">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Cotizaciones ({{ $cotizaciones->count() }})</h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr class="text-left">
                                    <th class="px-6 py-4 text-sm font-semibold text-gray-700">Estado</th>
                                    <th class="px-6 py-4 text-sm font-semibold text-gray-700">N° Cotización</th>
                                    <th class="px-6 py-4 text-sm font-semibold text-gray-700">Título</th>
                                    <th class="px-6 py-4 text-sm font-semibold text-gray-700">Fecha</th>
                                    <th class="px-6 py-4 text-sm font-semibold text-gray-700 text-right">Precio Total</th>
                                    <th class="px-6 py-4 text-sm font-semibold text-gray-700 text-center">Acciones</th>
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

                                @forelse($cotizaciones as $cotizacion)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $estadoColores[$cotizacion->estado] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ $cotizacion->estado }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm font-bold text-gray-900">#{{ $cotizacion->numero }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm text-gray-900">{{ $cotizacion->titulo }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm text-gray-700">{{ $cotizacion->fyh->timezone('America/Argentina/Buenos_Aires')->format('d/m/Y H:i') }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        @if($cotizacion->precio_total && $cotizacion->precio_total > 0)
                                            <span class="text-sm font-bold text-gray-900">${{ number_format($cotizacion->precio_total, 2, ',', '.') }}</span>
                                        @else
                                            <span class="text-sm text-gray-500 italic">Sin cotizar</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-center gap-2">
                                            @php
                                                $tienePrecio = $cotizacion->precio_total && $cotizacion->precio_total > 0;
                                                $esCotizable = in_array($cotizacion->estado, ['Nuevo', 'Abierto']);
                                            @endphp
                                            
                                            @if($esCotizable || ($cotizacion->estado == 'Cotizado' && $tienePrecio))
                                                <a href="{{ route('vendedor.app.cotizaciones.detalle', ['id' => $cotizacion->id, 'empleado_id' => request('empleado_id', 1), 'from_cliente' => $empresa->id_empresa]) }}"
                                                   class="inline-flex items-center px-3 py-1.5 rounded-lg text-white text-sm font-semibold transition hover:opacity-90"
                                                   style="background-color:#D88429;">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        @if($tienePrecio)
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                        @else
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                                        @endif
                                                    </svg>
                                                    {{ $tienePrecio ? 'Editar' : 'Cotizar' }}
                                                </a>
                                            @endif
                                            
                                            <a href="{{ route('vendedor.app.cotizaciones.detalle', ['id' => $cotizacion->id, 'empleado_id' => request('empleado_id', 1), 'from_cliente' => $empresa->id_empresa]) }}"
                                               class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-lg text-gray-700 text-sm font-medium bg-white hover:bg-gray-50 transition-colors">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                                Ver detalle
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center">
                                        <div class="text-gray-500">
                                            <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            <p class="text-sm">No hay cotizaciones para este cliente</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                {{-- Leyenda de estados --}}
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
