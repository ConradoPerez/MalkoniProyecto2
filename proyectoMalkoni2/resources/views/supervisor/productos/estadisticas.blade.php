@extends('layouts.app')

@section('title', 'Estadísticas del Producto - Malkoni Hnos')

@section('content')
<div class="min-h-screen text-gray-900">
    <!-- Sidebar -->
    @include('supervisor.components.sidebar')

    <!-- Main content -->
    <main class="lg:ml-48">
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
                        Estadísticas del Producto
                    </h1>
                    <p class="text-gray-600 mt-2">Producto ID: {{ $productoId }}</p>
                </div>

                <!-- Content placeholder -->
                <div class="bg-white rounded-lg p-4 sm:p-6 lg:p-8 border border-gray-200 shadow-sm text-center">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center" 
                         style="background-color: #E6F4F7;">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" 
                             style="color: #166379;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-syncopate font-bold text-gray-900 mb-2">
                        Estadísticas Avanzadas
                    </h3>
                    <p class="text-gray-600">
                        Esta sección mostrará gráficos detallados de ventas por mes, tendencias, comparativas y análisis completos del producto.
                    </p>
                </div>
        </div>
    </main>
</div>
@endsection