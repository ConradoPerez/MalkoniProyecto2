@extends('layouts.app')

@section('title', 'Cotizaciones del Vendedor - Malkoni Hnos')

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
                        <a href="{{ route('vendedores.index') }}" 
                           class="px-4 py-2 border rounded-lg text-sm font-medium hover:bg-gray-100 transition-colors"
                           style="border-color: #B1B7BB; color: #B1B7BB;">
                            ← Volver a Vendedores
                        </a>
                    </div>
                    <h1 class="text-3xl font-syncopate font-bold text-gray-900">
                        Cotizaciones del Vendedor
                    </h1>
                    <p class="text-gray-600 mt-2">Vendedor ID: {{ $vendedorId }}</p>
                </div>

                <!-- Content placeholder -->
                <div class="bg-white rounded-lg p-8 border border-gray-200 shadow-sm text-center">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center" 
                         style="background-color: #E6F4F7;">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" 
                             style="color: #166379;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-syncopate font-bold text-gray-900 mb-2">
                        Vista de Cotizaciones
                    </h3>
                    <p class="text-gray-600">
                        Esta sección mostrará todas las cotizaciones realizadas por el vendedor seleccionado.
                    </p>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection