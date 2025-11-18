@extends('layouts.app')

@section('title', 'Dashboard Cliente - Malkoni Hnos')

@section('content')
<div class="min-h-screen bg-gray-50 text-gray-900">
    <div class="flex">
        @include('cliente.components.sidebar')

        <main class="flex-1 overflow-y-auto md:ml-64 transition-all duration-300">
            
            <div class="p-4 lg:p-8">
                @include('cliente.components.header')

                <div class="mt-8">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-syncopate font-bold text-gray-800">
                            Últimas Cotizaciones
                        </h2>
                        
                        <a href="{{ route('cliente.nueva_cotizacion') }}" class="bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                            + Nueva Cotización
                        </a>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        @include('cliente.components.tables') 
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-8">
                    <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm flex items-center space-x-3">
                        <span class="h-3 w-3 rounded-full bg-orange-500 shadow-sm shadow-orange-200"></span>
                        <span class="text-sm font-medium text-gray-600">Nuevo</span>
                    </div>

                    <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm flex items-center space-x-3">
                        <span class="h-3 w-3 rounded-full bg-yellow-400 shadow-sm shadow-yellow-200"></span>
                        <span class="text-sm font-medium text-gray-600">Abierto</span>
                    </div>

                    <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm flex items-center space-x-3">
                        <span class="h-3 w-3 rounded-full bg-green-500 shadow-sm shadow-green-200"></span>
                        <span class="text-sm font-medium text-gray-600">Cotizado</span>
                    </div>

                    <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm flex items-center space-x-3">
                        <span class="h-3 w-3 rounded-full bg-blue-600 shadow-sm shadow-blue-200"></span>
                        <span class="text-sm font-medium text-gray-600">En entrega</span>
                    </div>
                </div>

            </div>
        </main>
    </div>
</div>
@endsection