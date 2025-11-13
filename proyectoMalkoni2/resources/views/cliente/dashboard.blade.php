@extends('layouts.app')

@section('title', 'Dashboard Cliente - Malkoni Hnos')

@section('content')
<div class="min-h-screen text-gray-900">
    <!-- Sidebar -->
    @include('cliente.components.sidebar')

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
                
                @include('cliente.components.header')

                <h2 class="text-xl font-semibold mb-4">Últimas Cotizaciones</h2>
                
                @include('cliente.components.tables') 

                <div class="flex justify-center space-x-8 mt-8 p-4 rounded">
                    <div class="flex items-center space-x-2">
                        <span class="h-4 w-4 rounded-full bg-orange-500"></span>
                        <span>Nuevo</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="h-4 w-4 rounded-full bg-yellow-400"></span>
                        <span>Abierto</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="h-4 w-4 rounded-full bg-green-500"></span>
                        <span>Cotizado</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="h-4 w-4 rounded-full bg-blue-600"></span>
                        <span>En entrega</span>
                    </div>
                </div>

            </div>
        </main>
    </div>
@endsection