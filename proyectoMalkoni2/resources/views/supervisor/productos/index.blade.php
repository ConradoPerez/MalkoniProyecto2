@extends('layouts.app')

@section('title', 'Productos - Malkoni Hnos')
@section('page-title', 'PRODUCTOS')

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
            <div class="mb-6">
                <p class="text-gray-600 mt-2">Consulta el catálogo completo de productos y estadísticas de ventas</p>
            </div>

            <!-- Alertas de éxito o error -->
            @if(session('success'))
                <div class="mb-6 rounded-xl border border-green-200 bg-green-50 p-4 flex items-start gap-3 text-sm text-green-700 shadow-sm">
                    <svg class="w-5 h-5 text-green-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="font-semibold">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 flex items-start gap-3 text-sm text-red-700 shadow-sm">
                    <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span class="font-semibold">{{ session('error') }}</span>
                </div>
            @endif

            <!-- Search Section -->
                <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm mb-8">
                    <h2 class="text-lg font-syncopate font-bold text-gray-900 mb-4 text-center sm:text-left">
                        BUSCAR PRODUCTOS
                    </h2>
                    <form action="{{ route('productos.search') }}" method="GET" class="flex flex-col lg:flex-row gap-4 items-center lg:items-end">
                        <div class="w-full max-w-sm mx-auto lg:max-w-none lg:mx-0 lg:flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-2 text-center sm:text-left">
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
                        <div class="w-full max-w-sm mx-auto lg:max-w-none lg:mx-0 lg:flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-2 text-center sm:text-left">
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
                        <div class="w-full max-w-sm mx-auto lg:max-w-none lg:mx-0 lg:w-auto flex flex-col sm:flex-row gap-2 lg:gap-4">
                            <button 
                                type="submit"
                                class="w-full sm:w-auto px-6 py-2 text-white rounded-lg font-medium hover:opacity-90 transition-opacity whitespace-nowrap"
                                style="background-color: #D88429;"
                            >
                                Buscar
                            </button>
                            <button 
                                type="button"
                                onclick="document.querySelector('input[name=codigo]').value=''; document.querySelector('input[name=nombre]').value=''; window.location.href='{{ route('productos.index') }}'"
                                class="w-full sm:w-auto px-6 py-2 border rounded-lg font-medium hover:bg-gray-100 transition-colors whitespace-nowrap"
                                style="border-color: #B1B7BB; color: #B1B7BB;"
                            >
                                Limpiar
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Products Statistics -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
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
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                            <div>
                                <div class="flex items-center gap-3">
                                    <h2 class="text-lg font-syncopate font-bold text-gray-900">
                                        PRODUCTOS ENCONTRADOS
                                    </h2>
                                    <a href="{{ route('productos.create') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-white bg-[#D88429] hover:bg-[#c7731f] rounded-lg transition-all shadow-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        Crear Producto
                                    </a>
                                </div>
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
                                    <th class="text-left py-3 px-6 font-semibold text-gray-700 hidden sm:table-cell">
                                        Código
                                    </th>
                                    <th class="text-left py-3 px-6 font-semibold text-gray-700">
                                        Nombre del Producto
                                    </th>
                                    <th class="text-left py-3 px-6 font-semibold text-gray-700 hidden md:table-cell">
                                        Cant. Cotizaciones
                                    </th>
                                    <th class="text-left py-3 px-6 font-semibold text-gray-700 hidden lg:table-cell">
                                        Ingresos Totales
                                    </th>
                                    <th class="text-left py-3 px-6 font-semibold text-gray-700">
                                        Ranking
                                    </th>
                                    <th class="text-center py-3 px-6 font-semibold text-gray-700">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($productos) && $productos->count() > 0)
                                    @foreach($productos as $index => $producto)
                                        <tr class="border-b border-gray-200 hover:bg-gray-50 transition-colors">
                                            <td class="py-4 px-6 text-gray-900 font-mono font-medium hidden sm:table-cell">{{ $producto->id_producto }}</td>
                                            <td class="py-4 px-6 text-gray-900 font-medium">
                                                <div class="max-w-[180px] sm:max-w-none truncate">{{ $producto->nombre }}</div>
                                                <div class="text-sm text-gray-500 sm:hidden mt-1">ID: {{ $producto->id_producto }}</div>
                                                <div class="text-xs text-gray-400 md:hidden mt-1">{{ number_format($producto->cant_cotizaciones ?? 0) }} cotizaciones</div>
                                                <div class="text-xs text-gray-400 lg:hidden mt-1">${{ number_format(($producto->precio_final ?? 0) * ($producto->cant_cotizaciones ?? 0)) }}</div>
                                            </td>
                                            <td class="py-4 px-6 text-gray-900 hidden md:table-cell">{{ number_format($producto->cant_cotizaciones ?? 0) }}</td>
                                            <td class="py-4 px-6 text-gray-900 font-medium hidden lg:table-cell">${{ number_format(($producto->precio_final ?? 0) * ($producto->cant_cotizaciones ?? 0)) }}</td>
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
                                            <td class="py-4 px-6 text-center flex items-center justify-center gap-2">
                                                <a href="{{ route('productos.edit', ['id' => $producto->id_producto]) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-[#166379] hover:opacity-90 text-white rounded-lg transition-all shadow-sm active:scale-95" title="Editar Producto">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                    Editar
                                                </a>
                                                <form action="{{ route('productos.destroy', ['id' => $producto->id_producto]) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este producto? Esta acción no se puede deshacer.');" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="p-1.5 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition-colors inline-flex items-center justify-center" title="Eliminar Producto">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="6" class="py-8 px-6 text-center text-gray-500">
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
@endsection