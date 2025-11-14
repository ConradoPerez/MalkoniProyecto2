@extends('layouts.app')

@section('title', 'Detalle del Producto - Malkoni Hnos')

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
                    <div class="w-8 h-8 rounded-full bg-gray-300"></div>
                </div>
            </div>
        </div>

        <div class="p-4 lg:p-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center gap-4 mb-4">
                    <a href="{{ route('productos.index') }}" 
                       class="px-3 py-2 border rounded-lg text-xs sm:text-sm font-medium hover:bg-gray-100 transition-colors"
                       style="border-color: #B1B7BB; color: #B1B7BB;">
                        <span class="sm:hidden">← Volver</span>
                        <span class="hidden sm:inline">← Volver a Productos</span>
                    </a>
                </div>
                    <h1 class="text-3xl font-syncopate font-bold text-gray-900">
                        Detalle del Producto
                    </h1>
                    <p class="text-gray-600 mt-2">Producto ID: {{ $productoId }}</p>
                </div>

                <!-- Content placeholder -->
                <div class="bg-white rounded-lg p-4 sm:p-6 lg:p-8 border border-gray-200 shadow-sm text-center">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center" 
                         style="background-color: #FEF2E6;">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" 
                             style="color: #D88429;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m0 0v10l8 4"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-syncopate font-bold text-gray-900 mb-2">
                        Vista Detallada del Producto
                    </h3>
                    <p class="text-gray-600">
                        Esta sección mostrará información detallada del producto, historial de ventas, estadísticas y más.
                    </p>
                    <div class="mt-6">
                        <a href="{{ route('productos.estadisticas', ['id' => $productoId]) }}" 
                           class="px-4 sm:px-6 py-2 text-white rounded-lg text-sm sm:text-base font-medium hover:opacity-90 transition-opacity inline-block"
                           style="background-color: #166379;">
                            <span class="sm:hidden">Ver Estadísticas</span>
                            <span class="hidden sm:inline">Ver Estadísticas Detalladas</span>
                        </a>
                    </div>
                </div>
        </div>
    </main>
</div>
@endsection