@extends('layouts.app')

@section('title', 'Dashboard Cliente - Malkoni Hnos')

@section('content')
<div class="min-h-screen text-gray-900" style="background-color: #e1dfd9;">
    <div class="flex">
        @include('cliente.components.sidebar')

        <main class="flex-1 overflow-y-auto md:ml-64 transition-all duration-300">
            
            <div class="p-4 lg:p-8">
                @include('cliente.components.header')

                <!-- Botones de acción principales -->
                <div class="flex justify-center gap-6 mt-8 mb-8">
                    <a href="{{ route('cliente.nueva_cotizacion', ['persona_id' => $personaId]) }}" class="inline-flex items-center px-12 py-6 bg-[#D88429] text-white text-xl font-bold rounded-xl shadow-lg hover:bg-[#c7731f] hover:shadow-xl transition-all transform hover:scale-105">
                        <svg class="w-8 h-8 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Nueva Cotización
                    </a>
                    <a href="{{ route('cliente.opt') }}" class="inline-flex items-center px-12 py-6 bg-gray-600 text-white text-xl font-bold rounded-xl shadow-lg hover:bg-gray-700 hover:shadow-xl transition-all transform hover:scale-105">
                        <svg class="w-8 h-8 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                        Ir al OPT
                    </a>
                </div>

                <div class="mt-8">
                    <h2 class="text-xl font-syncopate font-bold text-gray-800 mb-6">
                        ÚLTIMAS COTIZACIONES
                    </h2>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        @include('cliente.components.tables') 
                    </div>
                </div>

                <!-- Leyenda de estados -->
                <div class="mt-8">
                    <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4">Referencia de Estados</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                            <div class="flex items-center gap-3">
                                <span class="inline-block w-4 h-4 rounded-full" style="background-color: #95c4ecff;"></span>
                                <div>
                                    <span class="text-sm font-bold text-gray-900">Nuevo</span>
                                    <p class="text-xs text-gray-500">Recién creado</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                            <div class="flex items-center gap-3">
                                <span class="inline-block w-4 h-4 rounded-full" style="background-color: #ecee80ff;"></span>
                                <div>
                                    <span class="text-sm font-bold text-gray-900">Abierto</span>
                                    <p class="text-xs text-gray-500">En proceso de análisis</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                            <div class="flex items-center gap-3">
                                <span class="inline-block w-4 h-4 rounded-full" style="background-color: #72d89bff;"></span>
                                <div>
                                    <span class="text-sm font-bold text-gray-900">Cotizado</span>
                                    <p class="text-xs text-gray-500">Precio definido</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                            <div class="flex items-center gap-3">
                                <span class="inline-block w-4 h-4 rounded-full" style="background-color: #ae74dadc;"></span>
                                <div>
                                    <span class="text-sm font-bold text-gray-900">En entrega</span>
                                    <p class="text-xs text-gray-500">Preparando pedido</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>
</div>
@endsection
