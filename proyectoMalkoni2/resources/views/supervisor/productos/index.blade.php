@extends('layouts.app')

@section('title', 'Productos - Malkoni Hnos')

@section('content')
<div class="min-h-screen text-gray-900">
    <div class="flex">
        <!-- Sidebar -->
        @include('supervisor.components.sidebar')

        <!-- Main content -->
        <main class="flex-1 overflow-y-auto ml-48">
            <div class="p-4 lg:p-8">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-syncopate font-bold text-gray-900">
                        PRODUCTOS
                    </h1>
                    <p class="text-gray-600 mt-2">Consulta el catálogo completo de productos y estadísticas de ventas</p>
                </div>

                <!-- Search Section -->
                <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm mb-8">
                    <h2 class="text-lg font-syncopate font-bold text-gray-900 mb-4">
                        BUSCAR PRODUCTOS
                    </h2>
                    <form action="{{ route('productos.search') }}" method="GET" class="flex flex-col lg:flex-row gap-4 items-end">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Por Código del producto
                            </label>
                            <input 
                                type="text" 
                                name="codigo" 
                                value="{{ request('codigo') }}"
                                placeholder="Ej: 1, 23, 45..."
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-opacity-50 transition-all"
                                style="focus:border-color: #D88429; focus:ring-color: #D88429;"
                            >
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Por nombre del producto
                            </label>
                            <input 
                                type="text" 
                                name="nombre" 
                                value="{{ request('nombre') }}"
                                placeholder="Buscar por nombre del producto..."
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-opacity-50 transition-all"
                                style="focus:border-color: #D88429; focus:ring-color: #D88429;"
                            >
                        </div>
                        <input type="hidden" name="ordenar" value="{{ request('ordenar', 'mas_vendidos') }}">
                        <button 
                            type="submit"
                            class="px-6 py-2 text-white rounded-lg font-medium hover:opacity-90 transition-opacity whitespace-nowrap"
                            style="background-color: #D88429;"
                        >
                            Buscar
                        </button>
                        <button 
                            type="button"
                            onclick="document.querySelector('input[name=codigo]').value=''; document.querySelector('input[name=nombre]').value=''; window.location.href='{{ route('productos.index') }}'"
                            class="px-6 py-2 border rounded-lg font-medium hover:bg-gray-100 transition-colors whitespace-nowrap"
                            style="border-color: #B1B7BB; color: #B1B7BB;"
                        >
                            Limpiar
                        </button>
                    </form>
                </div>

                <!-- Products Statistics -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                    <div class="bg-white rounded-lg p-4 border border-gray-200 shadow-sm text-center">
                        <div class="text-2xl font-syncopate font-bold" style="color: #D88429;">{{ $estadisticas['total_productos'] ?? 0 }}</div>
                        <div class="text-sm text-gray-600">Total Productos</div>
                    </div>
                    <div class="bg-white rounded-lg p-4 border border-gray-200 shadow-sm text-center">
                        <div class="text-2xl font-syncopate font-bold" style="color: #166379;">{{ number_format($estadisticas['total_cotizaciones'] ?? 0) }}</div>
                        <div class="text-sm text-gray-600">Total Cotizaciones</div>
                    </div>
                    <div class="bg-white rounded-lg p-4 border border-gray-200 shadow-sm text-center">
                        <div class="text-2xl font-syncopate font-bold text-green-600">${{ number_format($estadisticas['ingresos_totales'] ?? 0) }}</div>
                        <div class="text-sm text-gray-600">Ingresos Totales</div>
                    </div>
                </div>

                <!-- Productos Table -->
                <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex justify-between items-center">
                            <div>
                                <h2 class="text-lg font-syncopate font-bold text-gray-900">
                                    PRODUCTOS ENCONTRADOS
                                </h2>
                                <p class="text-sm text-gray-600 mt-1">
                                    @if(isset($productos))
                                        Mostrando {{ $productos->firstItem() }} al {{ $productos->lastItem() }} de {{ $productos->total() }} productos registrados
                                    @else
                                        No se han cargado productos
                                    @endif
                                </p>
                            </div>
                            <div class="flex items-center gap-2 text-sm text-gray-600">
                                <span>Ordenar por:</span>
                                <form method="GET" action="{{ request('codigo') || request('nombre') ? route('productos.search') : route('productos.index') }}" class="inline">
                                    <input type="hidden" name="codigo" value="{{ request('codigo') }}">
                                    <input type="hidden" name="nombre" value="{{ request('nombre') }}">
                                    <select name="ordenar" onchange="this.form.submit()" class="border border-gray-300 rounded px-3 py-1 text-sm focus:outline-none focus:border-primary">
                                        <option value="mas_vendidos" {{ request('ordenar', 'mas_vendidos') == 'mas_vendidos' ? 'selected' : '' }}>Más cotizados</option>
                                        <option value="codigo" {{ request('ordenar') == 'codigo' ? 'selected' : '' }}>Código</option>
                                        <option value="nombre" {{ request('ordenar') == 'nombre' ? 'selected' : '' }}>Nombre A-Z</option>
                                        <option value="ingresos" {{ request('ordenar') == 'ingresos' ? 'selected' : '' }}>Ingresos</option>
                                    </select>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-gray-200" style="background-color: #F9FAFB;">
                                    <th class="text-left py-3 px-6 font-semibold text-gray-700">
                                        Código
                                    </th>
                                    <th class="text-left py-3 px-6 font-semibold text-gray-700">
                                        Nombre del Producto
                                    </th>
                                    <th class="text-left py-3 px-6 font-semibold text-gray-700">
                                        Cant. Cotizaciones
                                    </th>
                                    <th class="text-left py-3 px-6 font-semibold text-gray-700">
                                        Ingresos Totales
                                    </th>
                                    <th class="text-left py-3 px-6 font-semibold text-gray-700">
                                        Ranking
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($productos) && $productos->count() > 0)
                                    @foreach($productos as $index => $producto)
                                        <tr class="border-b border-gray-200 hover:bg-gray-50 transition-colors">
                                            <td class="py-4 px-6 text-gray-900 font-mono font-medium">{{ $producto->id_producto }}</td>
                                            <td class="py-4 px-6 text-gray-900 font-medium">{{ $producto->nombre }}</td>
                                            <td class="py-4 px-6 text-gray-900">{{ number_format($producto->cant_cotizaciones ?? 0) }}</td>
                                            <td class="py-4 px-6 text-gray-900 font-medium">${{ number_format(($producto->precio_final ?? 0) * ($producto->cant_cotizaciones ?? 0)) }}</td>
                                            <td class="py-4 px-6">
                                                @php
                                                    $rankingIndex = ($productos->currentPage() - 1) * $productos->perPage() + $index + 1;
                                                    $bgColor = match(true) {
                                                        $rankingIndex === 1 => '#D88429',
                                                        $rankingIndex === 2 => '#166379', 
                                                        $rankingIndex === 3 => '#B1B7BB',
                                                        default => '#6B7280'
                                                    };
                                                @endphp
                                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full text-white font-bold text-xs" 
                                                      style="background-color: {{ $bgColor }};">
                                                    {{ $rankingIndex }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="5" class="py-8 px-6 text-center text-gray-500">
                                            @if(request('codigo') || request('nombre'))
                                                No se encontraron productos que coincidan con los criterios de búsqueda.
                                            @else
                                                No hay productos registrados en el sistema.
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if(isset($productos) && $productos->hasPages())
                        @include('components.custom-pagination', ['paginator' => $productos->appends(request()->query())])
                    @endif
                </div>
            </div>
        </main>
    </div>
</div>
@endsection