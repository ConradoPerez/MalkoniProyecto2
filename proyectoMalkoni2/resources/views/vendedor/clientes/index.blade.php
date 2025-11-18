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
                            CLIENTES
                        </h1>
                        <p class="text-gray-600 mt-1">
                            Gestiona y busca clientes con cotizaciones asignadas
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
                                <div class="font-semibold text-gray-900">{{ $vendedor->nombre ?? 'Vendedor' }}</div>
                                <div class="text-gray-500">Vendedor activo</div>
                            </div>
                        </div>
                </div>

                {{-- Panel de búsqueda mejorado --}}
                <section class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden mb-6">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Buscar Clientes</h2>
                    </div>
                    <div class="p-6">
                        <form method="GET" action="{{ route('vendedor.app.clientes.index') }}"
                              class="grid grid-cols-1 md:grid-cols-4 gap-4">

                            <input type="hidden" name="empleado_id" value="{{ request('empleado_id', 1) }}" />

                            <div class="flex flex-col gap-1">
                                <label class="text-sm font-medium text-gray-700">Por N° de cotización</label>
                                <input type="text" name="pedido" value="{{ request('pedido') }}"
                                       placeholder="Ej: 1001"
                                       class="h-10 rounded-lg border border-gray-300 px-3 outline-none focus:ring-2 focus:ring-[#D88429]/20 focus:border-[#D88429]" />
                            </div>

                            <div class="flex flex-col gap-1">
                                <label class="text-sm font-medium text-gray-700">Por Nombre</label>
                                <input type="text" name="nombre" value="{{ request('nombre') }}"
                                       placeholder="Nombre de la empresa"
                                       class="h-10 rounded-lg border border-gray-300 px-3 outline-none focus:ring-2 focus:ring-[#D88429]/20 focus:border-[#D88429]" />
                            </div>

                            <div class="flex flex-col gap-1">
                                <label class="text-sm font-medium text-gray-700">Por CUIT</label>
                                <input type="text" name="doc" value="{{ request('doc') }}"
                                       placeholder="CUIT de la empresa"
                                       class="h-10 rounded-lg border border-gray-300 px-3 outline-none focus:ring-2 focus:ring-[#D88429]/20 focus:border-[#D88429]" />
                            </div>

                            <div class="flex flex-col justify-end">
                                <button type="submit"
                                        class="h-10 w-full inline-flex items-center justify-center rounded-lg text-white font-semibold transition hover:opacity-90"
                                        style="background-color:#D88429;">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                    Buscar
                                </button>
                            </div>
                        </form>
                    </div>
                </section>

                {{-- Tabla de clientes mejorada --}}
                <section class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Clientes ({{ $clientes->count() }})</h3>
                    </div>

                    @if($clientes->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50 border-b border-gray-200">
                                    <tr class="text-left">
                                        <th class="px-6 py-4 text-sm font-semibold text-gray-700">Cliente</th>
                                        <th class="px-6 py-4 text-sm font-semibold text-gray-700">CUIT</th>
                                        <th class="px-6 py-4 text-sm font-semibold text-gray-700">Total Cotizaciones</th>
                                        <th class="px-6 py-4 text-sm font-semibold text-gray-700">Estados</th>
                                        <th class="px-6 py-4 text-sm font-semibold text-gray-700 text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @php
                                        $estadoColores = [
                                            'Nuevo' => 'bg-blue-100 text-blue-700',
                                            'Abierto' => 'bg-yellow-100 text-yellow-700',
                                            'Cotizado' => 'bg-green-100 text-green-700',
                                            'En entrega' => 'bg-purple-100 text-purple-700',
                                        ];
                                    @endphp

                                    @foreach($clientes as $cliente)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="flex flex-col">
                                                <span class="text-sm font-medium text-gray-900">{{ $cliente->nombre }}</span>
                                                <span class="text-xs text-gray-500">Cliente empresarial</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="text-sm text-gray-700">{{ $cliente->cuit_formateado }}</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                {{ $cliente->cotizaciones_count }} cotizaciones
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex flex-wrap gap-1">
                                                @foreach($cliente->estadisticas_estados as $estado => $cantidad)
                                                    @if($cantidad > 0)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $estadoColores[$estado] ?? 'bg-gray-100 text-gray-700' }}">
                                                            {{ $cantidad }} {{ $estado }}
                                                        </span>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center justify-center gap-2">
                                                {{-- Acción: Ver cotizaciones del cliente --}}
                                                <a href="{{ route('vendedor.app.clientes.cotizaciones', $cliente->id_empresa) }}?empleado_id={{ request('empleado_id', 1) }}"
                                                   class="inline-flex items-center px-3 py-1.5 rounded-lg text-white text-sm font-semibold transition hover:opacity-90"
                                                   style="background-color:#D88429;">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                                    </svg>
                                                    Cotizaciones
                                                </a>

                                                {{-- Acción: Ficha del cliente --}}
                                                <a href="{{ route('vendedor.app.clientes.ficha', $cliente->id_empresa) }}?empleado_id={{ request('empleado_id', 1) }}"
                                                   class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-lg text-gray-700 text-sm font-medium bg-white hover:bg-gray-50 transition-colors">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                    </svg>
                                                    Ver ficha
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        {{-- Estado vacío --}}
                        <div class="text-center py-16">
                            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No se encontraron clientes</h3>
                            <p class="text-gray-500 mb-4">
                                @if(request()->hasAny(['pedido', 'nombre', 'doc']))
                                    No hay clientes que coincidan con los criterios de búsqueda.
                                @else
                                    Aún no hay clientes con cotizaciones asignadas a este vendedor.
                                @endif
                            </p>
                            @if(request()->hasAny(['pedido', 'nombre', 'doc']))
                                <a href="{{ route('vendedor.app.clientes.index', ['empleado_id' => request('empleado_id', 1)]) }}"
                                   class="inline-flex items-center px-4 py-2 rounded-lg text-white font-semibold transition hover:opacity-90"
                                   style="background-color:#D88429;">
                                    Ver todos los clientes
                                </a>
                            @endif
                        </div>
                    @endif
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
