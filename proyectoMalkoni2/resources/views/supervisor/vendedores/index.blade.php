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
                            Mostrando 10 de 60 vendedores registrados
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
                                        DNI/CUIT
                                    </th>
                                    <th class="text-left py-3 px-6 font-semibold text-gray-700">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="border-b border-gray-200 hover:bg-gray-50 transition-colors">
                                    <td class="py-4 px-6 text-gray-900 font-medium">Juan Pérez</td>
                                    <td class="py-4 px-6 text-gray-600">20-11222333-4</td>
                                    <td class="py-4 px-6">
                                        <div class="flex gap-3">
                                            <a href="{{ route('vendedor.clientes', ['id' => 1]) }}" 
                                               class="px-4 py-2 border rounded-lg text-sm font-medium hover:bg-gray-100 transition-colors"
                                               style="border-color: #166379; color: #166379;">
                                                Ver Clientes
                                            </a>
                                            <a href="{{ route('vendedor.cotizaciones', ['id' => 1]) }}" 
                                               class="px-4 py-2 border rounded-lg text-sm font-medium hover:opacity-90 transition-opacity text-white"
                                               style="background-color: #D88429; border-color: #D88429;">
                                                Ver Cotizaciones
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="border-b border-gray-200 hover:bg-gray-50 transition-colors">
                                    <td class="py-4 px-6 text-gray-900 font-medium">María García</td>
                                    <td class="py-4 px-6 text-gray-600">27-22333444-5</td>
                                    <td class="py-4 px-6">
                                        <div class="flex gap-3">
                                            <a href="{{ route('vendedor.clientes', ['id' => 2]) }}" 
                                               class="px-4 py-2 border rounded-lg text-sm font-medium hover:bg-gray-100 transition-colors"
                                               style="border-color: #166379; color: #166379;">
                                                Ver Clientes
                                            </a>
                                            <a href="{{ route('vendedor.cotizaciones', ['id' => 2]) }}" 
                                               class="px-4 py-2 border rounded-lg text-sm font-medium hover:opacity-90 transition-opacity text-white"
                                               style="background-color: #D88429; border-color: #D88429;">
                                                Ver Cotizaciones
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="border-b border-gray-200 hover:bg-gray-50 transition-colors">
                                    <td class="py-4 px-6 text-gray-900 font-medium">Carlos Ruiz</td>
                                    <td class="py-4 px-6 text-gray-600">20-33444555-6</td>
                                    <td class="py-4 px-6">
                                        <div class="flex gap-3">
                                            <a href="{{ route('vendedor.clientes', ['id' => 3]) }}" 
                                               class="px-4 py-2 border rounded-lg text-sm font-medium hover:bg-gray-100 transition-colors"
                                               style="border-color: #166379; color: #166379;">
                                                Ver Clientes
                                            </a>
                                            <a href="{{ route('vendedor.cotizaciones', ['id' => 3]) }}" 
                                               class="px-4 py-2 border rounded-lg text-sm font-medium hover:opacity-90 transition-opacity text-white"
                                               style="background-color: #D88429; border-color: #D88429;">
                                                Ver Cotizaciones
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="border-b border-gray-200 hover:bg-gray-50 transition-colors">
                                    <td class="py-4 px-6 text-gray-900 font-medium">Ana López</td>
                                    <td class="py-4 px-6 text-gray-600">27-44555666-7</td>
                                    <td class="py-4 px-6">
                                        <div class="flex gap-3">
                                            <a href="{{ route('vendedor.clientes', ['id' => 4]) }}" 
                                               class="px-4 py-2 border rounded-lg text-sm font-medium hover:bg-gray-100 transition-colors"
                                               style="border-color: #166379; color: #166379;">
                                                Ver Clientes
                                            </a>
                                            <a href="{{ route('vendedor.cotizaciones', ['id' => 4]) }}" 
                                               class="px-4 py-2 border rounded-lg text-sm font-medium hover:opacity-90 transition-opacity text-white"
                                               style="background-color: #D88429; border-color: #D88429;">
                                                Ver Cotizaciones
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="border-b border-gray-200 hover:bg-gray-50 transition-colors">
                                    <td class="py-4 px-6 text-gray-900 font-medium">Roberto Martínez</td>
                                    <td class="py-4 px-6 text-gray-600">20-55666777-8</td>
                                    <td class="py-4 px-6">
                                        <div class="flex gap-3">
                                            <a href="{{ route('vendedor.clientes', ['id' => 5]) }}" 
                                               class="px-4 py-2 border rounded-lg text-sm font-medium hover:bg-gray-100 transition-colors"
                                               style="border-color: #166379; color: #166379;">
                                                Ver Clientes
                                            </a>
                                            <a href="{{ route('vendedor.cotizaciones', ['id' => 5]) }}" 
                                               class="px-4 py-2 border rounded-lg text-sm font-medium hover:opacity-90 transition-opacity text-white"
                                               style="background-color: #D88429; border-color: #D88429;">
                                                Ver Cotizaciones
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="border-b border-gray-200 hover:bg-gray-50 transition-colors">
                                    <td class="py-4 px-6 text-gray-900 font-medium">Sofía Fernández</td>
                                    <td class="py-4 px-6 text-gray-600">27-66777888-9</td>
                                    <td class="py-4 px-6">
                                        <div class="flex gap-3">
                                            <a href="{{ route('vendedor.clientes', ['id' => 6]) }}" 
                                               class="px-4 py-2 border rounded-lg text-sm font-medium hover:bg-gray-100 transition-colors"
                                               style="border-color: #166379; color: #166379;">
                                                Ver Clientes
                                            </a>
                                            <a href="{{ route('vendedor.cotizaciones', ['id' => 6]) }}" 
                                               class="px-4 py-2 border rounded-lg text-sm font-medium hover:opacity-90 transition-opacity text-white"
                                               style="background-color: #D88429; border-color: #D88429;">
                                                Ver Cotizaciones
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="p-6 border-t border-gray-200 flex justify-between items-center">
                        <div class="text-sm text-gray-600">
                            Mostrando 1-6 de 60 vendedores
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