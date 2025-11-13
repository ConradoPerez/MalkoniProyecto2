@extends('layouts.app')

@section('title', 'Vendedores - Malkoni Hnos')

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
                        Vendedores
                    </h1>
                    <p class="text-gray-600 mt-2">Gestiona y consulta información de vendedores</p>
                </div>

                <!-- Search Section -->
                <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm mb-8">
                    <h2 class="text-lg font-syncopate font-bold text-gray-900 mb-4">
                        Buscar Vendedores
                    </h2>
                    <form action="{{ route('vendedor.search') }}" method="GET" class="flex flex-col lg:flex-row gap-4 items-end">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
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
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
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
                        <button 
                            type="submit"
                            class="px-6 py-2 text-white rounded-lg font-medium hover:opacity-90 transition-opacity whitespace-nowrap"
                            style="background-color: #D88429;"
                        >
                            Buscar
                        </button>
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
                                    <th class="text-left py-3 px-6 font-semibold text-gray-700">
                                        Email
                                    </th>
                                    <th class="text-left py-3 px-6 font-semibold text-gray-700">
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
                                            <td class="py-4 px-6 text-gray-900 font-medium">{{ $vendedor->nombre }}</td>
                                            <td class="py-4 px-6 text-gray-600">{{ $vendedor->email }}</td>
                                            <td class="py-4 px-6 text-gray-600">{{ $vendedor->dni }}</td>
                                            <td class="py-4 px-6">
                                                <div class="flex gap-3">
                                                    <a href="{{ route('vendedor.clientes', ['id' => $vendedor->id_empleado]) }}" 
                                                       class="px-4 py-2 border rounded-lg text-sm font-medium hover:bg-gray-100 transition-colors"
                                                       style="border-color: #166379; color: #166379;">
                                                        Ver Clientes
                                                    </a>
                                                    <a href="{{ route('vendedor.cotizaciones', ['id' => $vendedor->id_empleado]) }}" 
                                                       class="px-4 py-2 border rounded-lg text-sm font-medium hover:opacity-90 transition-opacity text-white"
                                                       style="background-color: #D88429; border-color: #D88429;">
                                                        Ver Cotizaciones
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
</div>
@endsection