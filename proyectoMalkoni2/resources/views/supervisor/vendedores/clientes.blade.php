@extends('layouts.app')

@section('title', 'Clientes del Vendedor - Malkoni Hnos')

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
                    <a href="{{ route('vendedor.index') }}" 
                       class="px-3 py-2 border rounded-lg text-xs sm:text-sm font-medium hover:bg-gray-100 transition-colors"
                       style="border-color: #B1B7BB; color: #B1B7BB;">
                        <span class="sm:hidden">← Volver</span>
                        <span class="hidden sm:inline">← Volver a Vendedores</span>
                    </a>
                </div>
                    <h1 class="text-3xl font-syncopate font-bold text-gray-900">
                        Clientes del Vendedor
                    </h1>
                    <p class="text-gray-600 mt-2">Vendedor: {{ $vendedor->nombre }} (ID: {{ $vendedor->id_empleado }})</p>
                </div>

                <!-- Content placeholder -->
                <div class="bg-white rounded-lg p-4 sm:p-6 lg:p-8 border border-gray-200 shadow-sm text-center">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center" 
                         style="background-color: #FEF2E6;">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" 
                             style="color: #D88429;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-syncopate font-bold text-gray-900 mb-2">
                        Vista de Clientes
                    </h3>
                    <p class="text-gray-600">
                        Esta sección mostrará todos los clientes asignados al vendedor seleccionado.
                    </p>
                </div>
        </div>
    </main>
</div>
@endsection