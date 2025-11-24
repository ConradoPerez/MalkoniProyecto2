@extends('layouts.app')

@section('title', 'Cotizaciones - Vendedor - Malkoni Hnos')

@section('content')
<div class="min-h-screen text-gray-900">
    <!-- Sidebar -->
    @include('vendedor.components.sidebar')

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
                    <span class="text-xs font-medium text-gray-900">Cotizaciones</span>
                    <div class="w-8 h-8 rounded-full bg-gray-300 flex items-center justify-center">
                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

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
                            <div class="text-gray-500">Vendedor activo</div>
                        </div>
                    </div>
                </div>

                {{-- Header con botón de filtros --}}
                <div class="flex flex-col lg:flex-row lg:items-center justify-between mb-6 gap-4">
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-2">
                            <span class="text-2xl font-bold text-gray-900">{{ number_format($total) }}</span>
                            <span class="text-gray-600">cotizaciones en total</span>
                        </div>
                        
                        @if(request()->hasAny(['nropedido', 'cliente', 'doc', 'estado']))
                            <div class="flex flex-wrap gap-2">
                                <div class="text-sm text-gray-600">
                                    {{ $cotizaciones->count() }} de {{ number_format($cotizaciones->total()) }} resultados
                                </div>
                                @if(request('estado'))
                                    @php
                                        $estadoFiltrado = $estados->where('id_estado', request('estado'))->first();
                                    @endphp
                                    @if($estadoFiltrado)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            Estado: {{ $estadoFiltrado->nombre }}
                                        </span>
                                    @endif
                                @endif
                                @if(request('orderby') && request('orderby') != 'fecha')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Ordenado por: {{ ucfirst(request('orderby')) }} ({{ request('direction', 'desc') == 'desc' ? 'Descendente' : 'Ascendente' }})
                                    </span>
                                @endif
                            </div>
                        @endif
                    </div>

                    <div class="flex gap-3">
                        @if(request()->hasAny(['nropedido', 'cliente', 'doc', 'estado', 'orderby']))
                            <a href="{{ route('vendedor.app.cotizaciones.index', ['empleado_id' => request('empleado_id')]) }}"
                               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                Limpiar filtros
                            </a>
                        @endif
                        
                        <button onclick="toggleFiltros()" 
                                class="inline-flex items-center px-4 py-2 rounded-lg text-white font-semibold transition hover:opacity-90"
                                style="background-color:#D88429;">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z"/>
                            </svg>
                            Filtros
                            <svg id="filtros-icon" class="w-4 h-4 ml-2 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Panel de filtros desplegable --}}
                <div id="filtros-panel" class="mb-6 bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                    <div class="p-6">
                        <form method="GET" action="{{ route('vendedor.app.cotizaciones.index') }}" class="space-y-4">
                            @if(request('empleado_id'))
                                <input type="hidden" name="empleado_id" value="{{ request('empleado_id') }}">
                            @endif
                            
                            {{-- Filtros de búsqueda --}}
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div class="flex flex-col gap-1">
                                    <label class="text-sm font-medium text-gray-700">Número de pedido</label>
                                    <input type="text" 
                                           name="nropedido" 
                                           value="{{ request('nropedido') }}"
                                           placeholder="Ej: 1001"
                                           class="h-10 rounded-lg border border-gray-300 px-3 outline-none focus:ring-2 focus:ring-[#D88429]/20 focus:border-[#D88429]" />
                                </div>

                                <div class="flex flex-col gap-1">
                                    <label class="text-sm font-medium text-gray-700">Nombre del Cliente</label>
                                    <input type="text" 
                                           name="cliente" 
                                           value="{{ request('cliente') }}"
                                           placeholder="Nombre de la empresa"
                                           class="h-10 rounded-lg border border-gray-300 px-3 outline-none focus:ring-2 focus:ring-[#D88429]/20 focus:border-[#D88429]" />
                                </div>

                                <div class="flex flex-col gap-1">
                                    <label class="text-sm font-medium text-gray-700">CUIT</label>
                                    <input type="text" 
                                           name="doc" 
                                           value="{{ request('doc') }}"
                                           placeholder="CUIT de la empresa"
                                           class="h-10 rounded-lg border border-gray-300 px-3 outline-none focus:ring-2 focus:ring-[#D88429]/20 focus:border-[#D88429]" />
                                </div>

                                <div class="flex flex-col gap-1">
                                    <label class="text-sm font-medium text-gray-700">Estado</label>
                                    <select name="estado" 
                                            class="h-10 rounded-lg border border-gray-300 px-3 outline-none focus:ring-2 focus:ring-[#D88429]/20 focus:border-[#D88429]">
                                        <option value="">Todos los estados</option>
                                        @foreach($estados as $estado)
                                            <option value="{{ $estado->id_estado }}" {{ request('estado') == $estado->id_estado ? 'selected' : '' }}>
                                                {{ $estado->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Ordenamiento --}}
                            <div class="border-t border-gray-200 pt-4">
                                <h3 class="text-sm font-medium text-gray-700 mb-3">Ordenamiento</h3>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="flex flex-col gap-1">
                                        <label class="text-sm text-gray-600">Ordenar por</label>
                                        <select name="orderby" 
                                                class="h-10 rounded-lg border border-gray-300 px-3 outline-none focus:ring-2 focus:ring-[#D88429]/20 focus:border-[#D88429]">
                                            <option value="fecha" {{ request('orderby', 'fecha') == 'fecha' ? 'selected' : '' }}>Fecha Creación</option>
                                            <option value="modificacion" {{ request('orderby') == 'modificacion' ? 'selected' : '' }}>Última Modificación</option>
                                            <option value="estado" {{ request('orderby') == 'estado' ? 'selected' : '' }}>Estado</option>
                                            <option value="numero" {{ request('orderby') == 'numero' ? 'selected' : '' }}>N° Cotización</option>
                                            <option value="monto" {{ request('orderby') == 'monto' ? 'selected' : '' }}>Monto</option>
                                        </select>
                                    </div>

                                    <div class="flex flex-col gap-1">
                                        <label class="text-sm text-gray-600">Dirección</label>
                                        <select name="direction" 
                                                class="h-10 rounded-lg border border-gray-300 px-3 outline-none focus:ring-2 focus:ring-[#D88429]/20 focus:border-[#D88429]">
                                            <option value="desc" {{ request('direction', 'desc') == 'desc' ? 'selected' : '' }}>Descendente</option>
                                            <option value="asc" {{ request('direction') == 'asc' ? 'selected' : '' }}>Ascendente</option>
                                        </select>
                                    </div>

                                    <div class="flex flex-col justify-end">
                                        <button type="submit"
                                                class="h-10 w-full inline-flex items-center justify-center rounded-lg text-white font-semibold transition hover:opacity-90"
                                                style="background-color:#D88429;">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                            </svg>
                                            Aplicar filtros
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Tabla mejorada --}}
                <section class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                    @if($cotizaciones->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50 border-b border-gray-200">
                                    <tr class="text-left">
                                        <th class="px-6 py-4 text-sm font-semibold text-gray-700">
                                            <a href="{{ request()->fullUrlWithQuery(['orderby' => 'estado', 'direction' => request('orderby') == 'estado' && request('direction', 'desc') == 'desc' ? 'asc' : 'desc']) }}" 
                                               class="flex items-center hover:text-[#D88429] transition-colors">
                                                Estado
                                                <svg class="w-4 h-4 ml-1 {{ request('orderby') == 'estado' ? 'text-[#D88429]' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    @if(request('orderby') == 'estado' && request('direction', 'desc') == 'desc')
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                    @else
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                                    @endif
                                                </svg>
                                            </a>
                                        </th>
                                        <th class="px-6 py-4 text-sm font-semibold text-gray-700">
                                            <a href="{{ request()->fullUrlWithQuery(['orderby' => 'numero', 'direction' => request('orderby') == 'numero' && request('direction', 'desc') == 'desc' ? 'asc' : 'desc']) }}" 
                                               class="flex items-center hover:text-[#D88429] transition-colors">
                                                N° Cotización
                                                <svg class="w-4 h-4 ml-1 {{ request('orderby') == 'numero' ? 'text-[#D88429]' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    @if(request('orderby') == 'numero' && request('direction', 'desc') == 'desc')
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                    @else
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                                    @endif
                                                </svg>
                                            </a>
                                        </th>
                                        <th class="px-6 py-4 text-sm font-semibold text-gray-700">Cliente</th>
                                        <th class="px-6 py-4 text-sm font-semibold text-gray-700">CUIT</th>
                                        <th class="px-6 py-4 text-sm font-semibold text-gray-700">
                                            <a href="{{ request()->fullUrlWithQuery(['orderby' => 'fecha', 'direction' => request('orderby') == 'fecha' && request('direction', 'desc') == 'desc' ? 'asc' : 'desc']) }}" 
                                               class="flex items-center hover:text-[#D88429] transition-colors">
                                                Fecha Creación
                                                <svg class="w-4 h-4 ml-1 {{ request('orderby', 'fecha') == 'fecha' ? 'text-[#D88429]' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    @if((request('orderby', 'fecha') == 'fecha') && request('direction', 'desc') == 'desc')
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                    @else
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                                    @endif
                                                </svg>
                                            </a>
                                        </th>
                                        <th class="px-6 py-4 text-sm font-semibold text-gray-700">
                                            <a href="{{ request()->fullUrlWithQuery(['orderby' => 'modificacion', 'direction' => request('orderby') == 'modificacion' && request('direction', 'desc') == 'desc' ? 'asc' : 'desc']) }}" 
                                               class="flex items-center hover:text-[#D88429] transition-colors">
                                                Última Modificación
                                                <svg class="w-4 h-4 ml-1 {{ request('orderby') == 'modificacion' ? 'text-[#D88429]' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    @if(request('orderby') == 'modificacion' && request('direction', 'desc') == 'desc')
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                    @else
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                                    @endif
                                                </svg>
                                            </a>
                                        </th>
                                        <th class="px-6 py-4 text-sm font-semibold text-gray-700 text-right">
                                            <a href="{{ request()->fullUrlWithQuery(['orderby' => 'monto', 'direction' => request('orderby') == 'monto' && request('direction', 'desc') == 'desc' ? 'asc' : 'desc']) }}" 
                                               class="flex items-center justify-end hover:text-[#D88429] transition-colors">
                                                Monto
                                                <svg class="w-4 h-4 ml-1 {{ request('orderby') == 'monto' ? 'text-[#D88429]' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    @if(request('orderby') == 'monto' && request('direction', 'desc') == 'desc')
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                    @else
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                                    @endif
                                                </svg>
                                            </a>
                                        </th>
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
                                                    <span class="text-sm font-medium text-gray-900">{{ $cotizacion->cliente_nombre }}</span>
                                                    <span class="text-xs text-gray-500">{{ $cotizacion->titulo ?? 'Sin título' }}</span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                @php
                                                    $cuit = null;
                                                    if ($cotizacion->empresa) {
                                                        // Cotización directa a empresa
                                                        $cuit = $cotizacion->empresa->cuit_formateado;
                                                    } elseif ($cotizacion->persona && $cotizacion->persona->empresa) {
                                                        // Cotización a persona de empresa
                                                        $cuit = $cotizacion->persona->empresa->cuit_formateado;
                                                    }
                                                @endphp
                                                
                                                @if($cuit)
                                                    <span class="text-sm text-gray-700">{{ $cuit }}</span>
                                                @else
                                                    <span class="text-sm text-gray-400 italic">Sin CUIT</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="text-sm text-gray-700">{{ $cotizacion->fyh ? $cotizacion->fyh->timezone('America/Argentina/Buenos_Aires')->format('d/m/Y H:i') : 'Sin fecha' }}</span>
                                            </td>
                                            <td class="px-6 py-4">
                                                @if($cotizacion->updated_at && $cotizacion->updated_at != $cotizacion->created_at)
                                                    <span class="text-sm text-gray-700">{{ $cotizacion->updated_at->timezone('America/Argentina/Buenos_Aires')->format('d/m/Y H:i') }}</span>
                                                @else
                                                    <span class="text-sm text-gray-400 italic">Sin modificar</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                @php
                                                    $estadoNombre = $cotizacion->estado_actual->nombre ?? 'Sin estado';
                                                    $esCotizable = in_array($estadoNombre, ['Nuevo', 'Abierto']);
                                                    $tienePrecio = !$esCotizable && $cotizacion->precio_total && $cotizacion->precio_total > 0;
                                                @endphp
                                                
                                                @if($tienePrecio)
                                                    <span class="text-sm font-bold text-gray-900">${{ number_format($cotizacion->precio_total, 0, ',', '.') }}</span>
                                                @else
                                                    <span class="text-sm text-gray-500 italic">Sin cotizar</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="flex items-center justify-center gap-2">
                                                    @php
                                                        $esEnEntrega = $estadoNombre == 'En entrega';
                                                    @endphp
                                                    
                                                    @if(!$tienePrecio && $esCotizable)
                                                        {{-- Botón Cotizar (solo cuando no tiene precio y es cotizable - estados Nuevo/Abierto) --}}
                                                        <a href="{{ route('vendedor.app.cotizaciones.detalle', ['id' => $cotizacion->id, 'empleado_id' => request('empleado_id')]) }}" 
                                                           class="inline-flex items-center px-3 py-1.5 rounded-lg text-white text-sm font-semibold transition hover:opacity-90"
                                                           style="background-color:#D88429;">
                                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                                            </svg>
                                                            Cotizar
                                                        </a>
                                                    @elseif($tienePrecio)
                                                        {{-- Botón Editar (solo cuando ya tiene precio - estados Cotizado/En entrega) --}}
                                                        @if($esEnEntrega)
                                                            <button disabled
                                                                    class="inline-flex items-center px-3 py-1.5 rounded-lg text-gray-400 text-sm font-semibold cursor-not-allowed bg-gray-200"
                                                                    title="No se puede editar una cotización en entrega">
                                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                                </svg>
                                                                Editar
                                                            </button>
                                                        @else
                                                            <a href="{{ route('vendedor.app.cotizaciones.detalle', ['id' => $cotizacion->id, 'empleado_id' => request('empleado_id')]) }}" 
                                                               class="inline-flex items-center px-3 py-1.5 rounded-lg text-white text-sm font-semibold transition hover:opacity-90"
                                                               style="background-color:#172A32;">
                                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                                </svg>
                                                                Editar
                                                            </a>
                                                        @endif
                                                    @endif
                                                    
                                                    {{-- Botón Ver detalle (siempre visible) --}}
                                                    <a href="{{ route('vendedor.app.cotizaciones.detalle', ['id' => $cotizacion->id, 'empleado_id' => request('empleado_id')]) }}" 
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
                                @if(request()->hasAny(['nropedido', 'cliente', 'doc', 'estado']))
                                    No hay cotizaciones que coincidan con los criterios de búsqueda y filtros aplicados.
                                @else
                                    Aún no hay cotizaciones registradas para este vendedor.
                                @endif
                            </p>
                            @if(request()->hasAny(['nropedido', 'cliente', 'doc', 'estado']))
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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Mostrar SweetAlert si la cotización fue guardada
@if(session('cotizacion_guardada'))
    Swal.fire({
        icon: 'success',
        title: '{{ session('cotizacion_guardada')['modificada'] ?? false ? '¡Cotización actualizada!' : '¡Cotización guardada!' }}',
        html: `La cotización N° <strong>{{ session('cotizacion_guardada')['numero'] }}</strong> ha sido {{ session('cotizacion_guardada')['modificada'] ?? false ? 'actualizada' : 'cotizada' }} con éxito.{{ session('cotizacion_guardada')['modificada'] ?? false ? '' : " Se le notificará al cliente." }}`,
        confirmButtonText: 'Entendido',
        confirmButtonColor: '#D88429',
        timer: 5000,
        timerProgressBar: true
    });
@endif

function toggleFiltros() {
    const panel = document.getElementById('filtros-panel');
    const icon = document.getElementById('filtros-icon');
    
    panel.classList.toggle('hidden');
    icon.classList.toggle('rotate-180');
    
    // Animación suave
    if (!panel.classList.contains('hidden')) {
        panel.style.maxHeight = '0px';
        panel.style.overflow = 'hidden';
        panel.style.transition = 'max-height 0.3s ease-out';
        
        setTimeout(() => {
            panel.style.maxHeight = panel.scrollHeight + 'px';
        }, 10);
        
        setTimeout(() => {
            panel.style.maxHeight = '';
            panel.style.overflow = '';
        }, 300);
    }
}

// El panel está abierto por defecto, solo necesitamos rotar el icono
document.addEventListener('DOMContentLoaded', function() {
    const icon = document.getElementById('filtros-icon');
    
    if (icon) {
        icon.classList.add('rotate-180');
    }
});
</script>

@endsection
