@extends('layouts.app')

@section('title', 'Vendedores - Malkoni Hnos')
@section('page-title', 'VENDEDORES')

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
            <div class="mb-8">
                    <p class="text-gray-600 mt-2">Gestiona y consulta información de vendedores</p>
                </div>

                <!-- Search Section -->
                <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm mb-8">
                    <h2 class="text-lg font-syncopate font-bold text-gray-900 mb-4 text-center sm:text-left">
                        BUSCAR VENDEDORES
                    </h2>
                    <form action="{{ route('vendedor.search') }}" method="GET" class="flex flex-col lg:flex-row gap-4 items-center lg:items-end">
                        <div class="w-full max-w-sm mx-auto lg:max-w-none lg:mx-0 lg:flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-2 text-center sm:text-left">
                                Por Nombre
                            </label>
                            <input 
                                type="text" 
                                name="nombre" 
                                value="{{ request('nombre') }}"
                                placeholder="Buscar por nombre..."
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-opacity-50 transition-all"
                                style="focus:border-color: #D88429; focus:ring-color: #D88429;"
                            >
                        </div>
                        <div class="w-full max-w-sm mx-auto lg:max-w-none lg:mx-0 lg:flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-2 text-center sm:text-left">
                                Por DNI/CUIT
                            </label>
                            <input 
                                type="text" 
                                name="dni" 
                                value="{{ request('dni') }}"
                                placeholder="Buscar por DNI/CUIT..."
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-opacity-50 transition-all"
                                style="focus:border-color: #D88429; focus:ring-color: #D88429;"
                            >
                        </div>
                        <div class="w-full max-w-sm mx-auto lg:max-w-none lg:mx-0 lg:w-auto">
                            <button 
                                type="submit"
                                class="w-full lg:w-auto px-6 py-2 text-white rounded-lg font-medium hover:opacity-90 transition-opacity whitespace-nowrap"
                                style="background-color: #D88429;"
                            >
                                Buscar
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Vendedores Table -->
                <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-syncopate font-bold text-gray-900">
                            Lista de Vendedores
                        </h2>
                        <p class="text-sm text-gray-600 mt-1">
                            @if(isset($vendedores))
                                Mostrando {{ $vendedores->firstItem() }} al {{ $vendedores->lastItem() }} de {{ $vendedores->total() }} vendedores registrados
                            @else
                                No se han cargado vendedores
                            @endif
                        </p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-gray-200" style="background-color: #F9FAFB;">
                                    <th class="text-left py-3 px-6 font-semibold text-gray-700">
                                        Vendedor
                                    </th>
                                    <th class="text-left py-3 px-6 font-semibold text-gray-700 hidden md:table-cell">
                                        Email
                                    </th>
                                    <th class="text-left py-3 px-6 font-semibold text-gray-700 hidden lg:table-cell">
                                        DNI
                                    </th>
                                    <th class="text-left py-3 px-6 font-semibold text-gray-700">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($vendedores) && $vendedores->count() > 0)
                                    @foreach($vendedores as $vendedor)
                                        <tr class="border-b border-gray-200 hover:bg-gray-50 transition-colors">
                                            <td class="py-4 px-6 text-gray-900 font-medium">
                                                <div class="max-w-[150px] sm:max-w-none truncate">{{ $vendedor->nombre }}</div>
                                                <div class="text-sm text-gray-500 md:hidden mt-1">{{ $vendedor->email }}</div>
                                                <div class="text-xs text-gray-400 lg:hidden mt-1">DNI: {{ $vendedor->dni }}</div>
                                            </td>
                                            <td class="py-4 px-6 text-gray-600 hidden md:table-cell">
                                                <div class="max-w-[200px] truncate">{{ $vendedor->email }}</div>
                                            </td>
                                            <td class="py-4 px-6 text-gray-600 hidden lg:table-cell">{{ $vendedor->dni }}</td>
                                            <td class="py-4 px-6">
                                                <div class="flex flex-col sm:flex-row gap-2 sm:gap-3">
                                                    <a href="{{ route('vendedor.clientes', ['id' => $vendedor->id_empleado]) }}" 
                                                       class="px-3 py-2 border rounded-lg text-xs sm:text-sm font-medium hover:bg-gray-100 transition-colors text-center"
                                                       style="border-color: #166379; color: #166379;">
                                                        <span class="sm:hidden">Clientes</span>
                                                        <span class="hidden sm:inline">Ver Clientes</span>
                                                    </a>
                                                    <a href="{{ route('vendedor.cotizaciones', ['id' => $vendedor->id_empleado]) }}" 
                                                       class="px-3 py-2 border rounded-lg text-xs sm:text-sm font-medium hover:opacity-90 transition-opacity text-white text-center"
                                                       style="background-color: #D88429; border-color: #D88429;">
                                                        <span class="sm:hidden">Cotizaciones</span>
                                                        <span class="hidden sm:inline">Ver Cotizaciones</span>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="4" class="py-8 px-6 text-center text-gray-500">
                                            @if(request('nombre') || request('dni'))
                                                No se encontraron vendedores que coincidan con los criterios de búsqueda.
                                            @else
                                                No hay vendedores registrados en el sistema.
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if(isset($vendedores) && $vendedores->hasPages())
                        @include('components.custom-pagination', ['paginator' => $vendedores->appends(request()->query())])
                    @endif
                </div>
        </div>
    </main>
</div>
@endsection