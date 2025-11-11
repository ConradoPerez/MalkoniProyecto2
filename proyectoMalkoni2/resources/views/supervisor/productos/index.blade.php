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
                                placeholder="Ej: PRD-001, SRV-002..."
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
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                    <div class="bg-white rounded-lg p-4 border border-gray-200 shadow-sm text-center">
                        <div class="text-2xl font-syncopate font-bold" style="color: #D88429;">250</div>
                        <div class="text-sm text-gray-600">Total Productos</div>
                    </div>
                    <div class="bg-white rounded-lg p-4 border border-gray-200 shadow-sm text-center">
                        <div class="text-2xl font-syncopate font-bold" style="color: #166379;">1,895</div>
                        <div class="text-sm text-gray-600">Total Ventas</div>
                    </div>
                    <div class="bg-white rounded-lg p-4 border border-gray-200 shadow-sm text-center">
                        <div class="text-2xl font-syncopate font-bold text-green-600">$389.800</div>
                        <div class="text-sm text-gray-600">Ingresos Totales</div>
                    </div>
                    <div class="bg-white rounded-lg p-4 border border-gray-200 shadow-sm text-center">
                        <div class="text-2xl font-syncopate font-bold" style="color: #B1B7BB;">45</div>
                        <div class="text-sm text-gray-600">Sin Ventas</div>
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
                                    Mostrando 10 de 250 productos registrados
                                </p>
                            </div>
                            <div class="flex items-center gap-2 text-sm text-gray-600">
                                <span>Ordenar por:</span>
                                <select class="border border-gray-300 rounded px-3 py-1 text-sm focus:outline-none focus:border-primary">
                                    <option>Más vendidos</option>
                                    <option>Código</option>
                                    <option>Nombre A-Z</option>
                                    <option>Ingresos</option>
                                </select>
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
                                        Cant. Ventas
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
                                <tr class="border-b border-gray-200 hover:bg-gray-50 transition-colors">
                                    <td class="py-4 px-6 text-gray-900 font-mono font-medium">PRD-001</td>
                                    <td class="py-4 px-6 text-gray-900 font-medium">Software CRM Pro</td>
                                    <td class="py-4 px-6 text-gray-900">250</td>
                                    <td class="py-4 px-6 text-gray-900 font-medium">$75.000</td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full text-white font-bold text-xs" style="background-color: #D88429;">1</span>
                                    </td>
                                </tr>
                                <tr class="border-b border-gray-200 hover:bg-gray-50 transition-colors">
                                    <td class="py-4 px-6 text-gray-900 font-mono font-medium">SRV-002</td>
                                    <td class="py-4 px-6 text-gray-900 font-medium">Servicio Cloud Premium</td>
                                    <td class="py-4 px-6 text-gray-900">180</td>
                                    <td class="py-4 px-6 text-gray-900 font-medium">$90.000</td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full text-white font-bold text-xs" style="background-color: #166379;">2</span>
                                    </td>
                                </tr>
                                <tr class="border-b border-gray-200 hover:bg-gray-50 transition-colors">
                                    <td class="py-4 px-6 text-gray-900 font-mono font-medium">PRD-003</td>
                                    <td class="py-4 px-6 text-gray-900 font-medium">Consultoría TI Integral</td>
                                    <td class="py-4 px-6 text-gray-900">120</td>
                                    <td class="py-4 px-6 text-gray-900 font-medium">$30.000</td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full text-white font-bold text-xs" style="background-color: #B1B7BB;">3</span>
                                    </td>
                                </tr>
                                <tr class="border-b border-gray-200 hover:bg-gray-50 transition-colors">
                                    <td class="py-4 px-6 text-gray-900 font-mono font-medium">HRD-004</td>
                                    <td class="py-4 px-6 text-gray-900 font-medium">Hardware Redes Avanzado</td>
                                    <td class="py-4 px-6 text-gray-900">95</td>
                                    <td class="py-4 px-6 text-gray-900 font-medium">$17.500</td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-200 text-gray-700 font-bold text-xs">4</span>
                                    </td>
                                </tr>
                                <tr class="border-b border-gray-200 hover:bg-gray-50 transition-colors">
                                    <td class="py-4 px-6 text-gray-900 font-mono font-medium">SRV-005</td>
                                    <td class="py-4 px-6 text-gray-900 font-medium">Soporte Técnico 24/7</td>
                                    <td class="py-4 px-6 text-gray-900">70</td>
                                    <td class="py-4 px-6 text-gray-900 font-medium">$40.000</td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-200 text-gray-700 font-bold text-xs">5</span>
                                    </td>
                                </tr>
                                <tr class="border-b border-gray-200 hover:bg-gray-50 transition-colors">
                                    <td class="py-4 px-6 text-gray-900 font-mono font-medium">PRD-006</td>
                                    <td class="py-4 px-6 text-gray-900 font-medium">Desarrollo Web Personalizado</td>
                                    <td class="py-4 px-6 text-gray-900">55</td>
                                    <td class="py-4 px-6 text-gray-900 font-medium">$14.000</td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-200 text-gray-700 font-bold text-xs">6</span>
                                    </td>
                                </tr>
                                <tr class="border-b border-gray-200 hover:bg-gray-50 transition-colors">
                                    <td class="py-4 px-6 text-gray-900 font-mono font-medium">SRV-007</td>
                                    <td class="py-4 px-6 text-gray-900 font-medium">Migración de Datos</td>
                                    <td class="py-4 px-6 text-gray-900">45</td>
                                    <td class="py-4 px-6 text-gray-900 font-medium">$9.300</td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-200 text-gray-700 font-bold text-xs">7</span>
                                    </td>
                                </tr>
                                <tr class="border-b border-gray-200 hover:bg-gray-50 transition-colors">
                                    <td class="py-4 px-6 text-gray-900 font-mono font-medium">PRD-008</td>
                                    <td class="py-4 px-6 text-gray-900 font-medium">Licencias Software Corporativo</td>
                                    <td class="py-4 px-6 text-gray-900">35</td>
                                    <td class="py-4 px-6 text-gray-900 font-medium">$85.000</td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-200 text-gray-700 font-bold text-xs">8</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="p-6 border-t border-gray-200 flex justify-between items-center">
                        <div class="text-sm text-gray-600">
                            Mostrando 1-8 de 250 productos
                        </div>
                        <div class="flex gap-2">
                            <button class="px-3 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50 transition-colors">
                                Anterior
                            </button>
                            <button class="px-3 py-2 text-white rounded-lg text-sm hover:opacity-90 transition-opacity" 
                                    style="background-color: #D88429;">
                                1
                            </button>
                            <button class="px-3 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50 transition-colors">
                                2
                            </button>
                            <button class="px-3 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50 transition-colors">
                                3
                            </button>
                            <button class="px-3 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50 transition-colors">
                                Siguiente
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection