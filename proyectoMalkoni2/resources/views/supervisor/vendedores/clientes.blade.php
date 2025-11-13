@extends('layouts.app')

@section('title', 'Clientes del Vendedor - Malkoni Hnos')

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
                        <a href="{{ route('vendedor.index') }}" 
                           class="px-4 py-2 border rounded-lg text-sm font-medium hover:bg-gray-100 transition-colors"
                           style="border-color: #B1B7BB; color: #B1B7BB;">
                            ← Volver a Vendedores
                        </a>
                    </div>
                    <h1 class="text-3xl font-syncopate font-bold text-gray-900">
                        Clientes del Vendedor
                    </h1>
                    <p class="text-gray-600 mt-2">Vendedor: {{ $vendedor->nombre }} (ID: {{ $vendedor->id_empleado }})</p>
                </div>

                <!-- Content placeholder -->
                <div class="bg-white rounded-lg p-8 border border-gray-200 shadow-sm text-center">
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
</div>
@endsection