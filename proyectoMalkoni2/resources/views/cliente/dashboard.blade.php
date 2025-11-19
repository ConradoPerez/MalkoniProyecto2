@extends('layouts.app')

@section('title', 'Dashboard Cliente - Malkoni Hnos')

@section('content')
<div class="min-h-screen text-gray-900" style="background-color: #e1dfd9;">
    <div class="flex">
        @include('cliente.components.sidebar')

        <main class="flex-1 overflow-y-auto md:ml-64 transition-all duration-300">
            
            <div class="p-4 lg:p-8">
                @include('cliente.components.header')

                <!-- Botones de acción principales -->
                <div class="flex justify-center gap-6 mt-8 mb-8">
                    <a href="{{ route('cliente.nueva_cotizacion', ['persona_id' => $personaId]) }}" class="inline-flex items-center px-12 py-6 bg-[#D88429] text-white text-xl font-bold rounded-xl shadow-lg hover:bg-[#c7731f] hover:shadow-xl transition-all transform hover:scale-105">
                        <svg class="w-8 h-8 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Nueva Cotización
                    </a>
                    <a href="{{ route('cliente.opt') }}" class="inline-flex items-center px-12 py-6 bg-gray-600 text-white text-xl font-bold rounded-xl shadow-lg hover:bg-gray-700 hover:shadow-xl transition-all transform hover:scale-105">
                        <svg class="w-8 h-8 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                        Ir al OPT
                    </a>
                </div>

                <!-- Panel de filtros desplegable -->
                <div class="mb-6">
                    <button type="button" id="toggle-filters" class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 transition-colors">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z"/>
                        </svg>
                        <span class="font-medium text-gray-700">Buscar y Filtrar</span>
                        <svg id="filter-arrow" class="w-4 h-4 text-gray-600 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                        @if(request()->hasAny(['search', 'estado', 'fecha_desde', 'fecha_hasta']))
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-[#D88429] text-white ml-2">
                                Activo
                            </span>
                        @endif
                    </button>
                    
                    <div id="filters-panel" class="hidden mt-3 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="p-6">
                            <form method="GET" action="{{ route('cliente.dashboard') }}" class="space-y-4">
                                <input type="hidden" name="persona_id" value="{{ $personaId }}">
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                    <div>
                                        <label class="flex items-center gap-1 text-sm font-medium text-gray-700 mb-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                            </svg>
                                            Buscar
                                        </label>
                                        <input type="text" name="search" value="{{ request('search') }}" placeholder="N° cotización o título..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#D88429] focus:border-[#D88429] outline-none">
                                    </div>
                                    
                                    <div>
                                        <label class="flex items-center gap-1 text-sm font-medium text-gray-700 mb-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            Estado
                                        </label>
                                        <select name="estado" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#D88429] focus:border-[#D88429] outline-none">
                                            <option value="">Todos los estados</option>
                                            <option value="Nuevo" {{ request('estado') == 'Nuevo' ? 'selected' : '' }} data-color="#95c4ecff">🔵 Nuevo</option>
                                            <option value="Abierto" {{ request('estado') == 'Abierto' ? 'selected' : '' }} data-color="#ecee80ff">🟡 Abierto</option>
                                            <option value="Cotizado" {{ request('estado') == 'Cotizado' ? 'selected' : '' }} data-color="#72d89bff">🟢 Cotizado</option>
                                            <option value="En entrega" {{ request('estado') == 'En entrega' ? 'selected' : '' }} data-color="#ae74dadc">🟣 En entrega</option>
                                        </select>
                                    </div>
                                    
                                    <div>
                                        <label class="flex items-center gap-1 text-sm font-medium text-gray-700 mb-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            Desde
                                        </label>
                                        <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#D88429] focus:border-[#D88429] outline-none">
                                    </div>
                                    
                                    <div>
                                        <label class="flex items-center gap-1 text-sm font-medium text-gray-700 mb-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            Hasta
                                        </label>
                                        <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#D88429] focus:border-[#D88429] outline-none">
                                    </div>
                                </div>
                                
                                <div class="flex gap-3 pt-4 border-t border-gray-200">
                                    <button type="submit" class="flex items-center gap-2 px-6 py-2 bg-[#D88429] text-white font-semibold rounded-lg hover:bg-[#c7731f] transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                        </svg>
                                        Aplicar Filtros
                                    </button>
                                    
                                    @if(request()->hasAny(['search', 'estado', 'fecha_desde', 'fecha_hasta']))
                                        <a href="{{ route('cliente.dashboard', ['persona_id' => $personaId]) }}" class="flex items-center gap-2 px-6 py-2 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                            Limpiar
                                        </a>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Tabla de cotizaciones -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                                <h2 class="text-xl font-syncopate font-bold text-gray-800">
                                    MIS COTIZACIONES
                                </h2>
                                
                                <!-- Estadísticas compactas al lado del título -->
                                <div class="flex flex-wrap gap-2">
                                    <div class="flex items-center gap-2 bg-gray-50 px-3 py-1 rounded-full text-sm">
                                        <span class="text-gray-600">Total:</span>
                                        <span class="font-semibold text-gray-900">{{ $estadisticas['total'] }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 bg-blue-50 px-3 py-1 rounded-full text-sm">
                                        <span class="inline-block w-2 h-2 rounded-full" style="background-color: #95c4ecff;"></span>
                                        <span class="font-semibold text-blue-600">{{ $estadisticas['nuevo'] }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 bg-yellow-50 px-3 py-1 rounded-full text-sm">
                                        <span class="inline-block w-2 h-2 rounded-full" style="background-color: #ecee80ff;"></span>
                                        <span class="font-semibold text-yellow-600">{{ $estadisticas['abierto'] }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 bg-green-50 px-3 py-1 rounded-full text-sm">
                                        <span class="inline-block w-2 h-2 rounded-full" style="background-color: #72d89bff;"></span>
                                        <span class="font-semibold text-green-600">{{ $estadisticas['cotizado'] }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 bg-purple-50 px-3 py-1 rounded-full text-sm">
                                        <span class="inline-block w-2 h-2 rounded-full" style="background-color: #ae74dadc;"></span>
                                        <span class="font-semibold text-purple-600">{{ $estadisticas['en_entrega'] }}</span>
                                    </div>
                                </div>
                                
                                <div class="text-sm text-gray-600">
                                    Mostrando {{ $cotizaciones->firstItem() ?? 0 }} - {{ $cotizaciones->lastItem() ?? 0 }} de {{ $cotizaciones->total() }} cotizaciones
                                </div>
                            </div>
                        </div>
                    
                    @if($cotizaciones->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50">
                                    <tr class="border-b border-gray-200">
                                        <th class="text-left py-4 px-6 font-semibold text-gray-700">Estado</th>
                                        <th class="text-left py-4 px-6 font-semibold text-gray-700">N° Cotización</th>
                                        <th class="text-left py-4 px-6 font-semibold text-gray-700">Título</th>
                                        <th class="text-left py-4 px-6 font-semibold text-gray-700">Fecha Creación</th>
                                        <th class="text-left py-4 px-6 font-semibold text-gray-700">Vendedor</th>
                                        <th class="text-right py-4 px-6 font-semibold text-gray-700">Total</th>
                                        <th class="text-center py-4 px-6 font-semibold text-gray-700">Acciones</th>
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
                                            <td class="py-4 px-6">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $estadoColores[$cotizacion->estado_actual->nombre ?? 'Nuevo'] ?? 'bg-gray-100 text-gray-800' }}">
                                                    {{ $cotizacion->estado_actual->nombre ?? 'Nuevo' }}
                                                </span>
                                            </td>
                                            <td class="py-4 px-6">
                                                <span class="text-gray-900 font-semibold">#{{ $cotizacion->numero }}</span>
                                            </td>
                                            <td class="py-4 px-6">
                                                <div class="text-gray-900 font-medium">{{ $cotizacion->titulo ?? 'Sin título' }}</div>
                                            </td>
                                            <td class="py-4 px-6 text-gray-700">
                                                {{ $cotizacion->fyh ? $cotizacion->fyh->timezone('America/Argentina/Buenos_Aires')->format('d/m/Y H:i') : '-' }}
                                            </td>
                                            <td class="py-4 px-6 text-gray-700">
                                                {{ $cotizacion->empleado->nombre ?? 'Sin vendedor' }}
                                            </td>
                                            <td class="py-4 px-6 text-right">
                                                @php
                                                    $estadoActual = $cotizacion->estado_actual->nombre ?? 'Nuevo';
                                                    $sinPrecio = in_array($estadoActual, ['Nuevo', 'Abierto']) || !$cotizacion->precio_total || $cotizacion->precio_total <= 0;
                                                @endphp
                                                @if($sinPrecio)
                                                    <span class="text-gray-500 text-sm italic">Sin Cotizar</span>
                                                @else
                                                    <span class="text-gray-900 font-semibold">${{ number_format($cotizacion->precio_total, 0, ',', '.') }}</span>
                                                @endif
                                            </td>
                                            <td class="py-4 px-6">
                                                <div class="flex items-center justify-center">
                                                    <a href="{{ route('cliente.cotizacion.ver', ['id' => $cotizacion->id, 'persona_id' => $personaId]) }}" class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
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
                        
                        <!-- Paginación -->
                        @if($cotizaciones->hasPages())
                            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                                <div class="flex items-center justify-between">
                                    <div class="text-sm text-gray-700">
                                        Mostrando {{ $cotizaciones->firstItem() }} a {{ $cotizaciones->lastItem() }} de {{ $cotizaciones->total() }} resultados
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        {{-- Botón Anterior --}}
                                        @if($cotizaciones->onFirstPage())
                                            <span class="px-3 py-2 text-sm text-gray-400 bg-gray-100 border border-gray-300 rounded-lg cursor-not-allowed">
                                                Anterior
                                            </span>
                                        @else
                                            <a href="{{ $cotizaciones->appends(request()->query())->previousPageUrl() }}" class="px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                                Anterior
                                            </a>
                                        @endif

                                        {{-- Números de página --}}
                                        @foreach(range(1, $cotizaciones->lastPage()) as $page)
                                            @if($page == $cotizaciones->currentPage())
                                                <span class="px-3 py-2 text-sm text-white bg-[#D88429] border border-[#D88429] rounded-lg font-semibold">
                                                    {{ $page }}
                                                </span>
                                            @else
                                                <a href="{{ $cotizaciones->appends(request()->query())->url($page) }}" class="px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                                    {{ $page }}
                                                </a>
                                            @endif
                                        @endforeach

                                        {{-- Botón Siguiente --}}
                                        @if($cotizaciones->hasMorePages())
                                            <a href="{{ $cotizaciones->appends(request()->query())->nextPageUrl() }}" class="px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                                Siguiente
                                            </a>
                                        @else
                                            <span class="px-3 py-2 text-sm text-gray-400 bg-gray-100 border border-gray-300 rounded-lg cursor-not-allowed">
                                                Siguiente
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-12">
                            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No hay cotizaciones</h3>
                            <p class="text-gray-500 mb-4">
                                @if(request()->hasAny(['search', 'estado', 'fecha_desde', 'fecha_hasta']))
                                    No hay cotizaciones que coincidan con los filtros aplicados.
                                @else
                                    Aún no tienes cotizaciones registradas.
                                @endif
                            </p>
                            @if(request()->hasAny(['search', 'estado', 'fecha_desde', 'fecha_hasta']))
                                <a href="{{ route('cliente.dashboard', ['persona_id' => $personaId]) }}" class="inline-flex items-center px-4 py-2 bg-[#D88429] text-white font-semibold rounded-lg hover:bg-[#c7731f] transition-colors">
                                    Ver todas las cotizaciones
                                </a>
                            @else
                                <a href="{{ route('cliente.nueva_cotizacion', ['persona_id' => $personaId]) }}" class="inline-flex items-center px-4 py-2 bg-[#D88429] text-white font-semibold rounded-lg hover:bg-[#c7731f] transition-colors">
                                    Crear primera cotización
                                </a>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Estados de Cotización -->
                <div class="mt-6 bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                    <h3 class="text-sm font-semibold text-gray-900 mb-4">Estados de Cotización</h3>
                    <div class="flex flex-wrap justify-between items-center gap-6">
                        <div class="flex items-center gap-3">
                            <span class="inline-block w-4 h-4 rounded-full" style="background-color: #95c4ecff;"></span>
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
        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleButton = document.getElementById('toggle-filters');
    const filtersPanel = document.getElementById('filters-panel');
    const filterArrow = document.getElementById('filter-arrow');
    
    toggleButton.addEventListener('click', function() {
        const isHidden = filtersPanel.classList.contains('hidden');
        
        if (isHidden) {
            filtersPanel.classList.remove('hidden');
            filterArrow.style.transform = 'rotate(180deg)';
        } else {
            filtersPanel.classList.add('hidden');
            filterArrow.style.transform = 'rotate(0deg)';
        }
    });

    // Auto-abrir si hay filtros aplicados
    @if(request()->hasAny(['search', 'estado', 'fecha_desde', 'fecha_hasta']))
        filtersPanel.classList.remove('hidden');
        filterArrow.style.transform = 'rotate(180deg)';
    @endif
});
</script>
@endsection
