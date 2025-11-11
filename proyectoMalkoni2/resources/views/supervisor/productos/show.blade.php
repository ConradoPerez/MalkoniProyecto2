@extends('layouts.app')

@section('title', 'Detalle del Producto - Malkoni Hnos')

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
                    <div class="flex items-center gap-4 mb-4">
                        <a href="{{ route('productos.index') }}" 
                           class="px-4 py-2 border rounded-lg text-sm font-medium hover:bg-gray-100 transition-colors"
                           style="border-color: #B1B7BB; color: #B1B7BB;">
                            ← Volver a Productos
                        </a>
                    </div>
                    <h1 class="text-3xl font-syncopate font-bold text-gray-900">
                        Detalle del Producto
                    </h1>
                    <p class="text-gray-600 mt-2">Producto ID: {{ $productoId }}</p>
                </div>

                <!-- Content placeholder -->
                <div class="bg-white rounded-lg p-8 border border-gray-200 shadow-sm text-center">
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
                           class="px-6 py-2 text-white rounded-lg font-medium hover:opacity-90 transition-opacity"
                           style="background-color: #166379;">
                            Ver Estadísticas Detalladas
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection