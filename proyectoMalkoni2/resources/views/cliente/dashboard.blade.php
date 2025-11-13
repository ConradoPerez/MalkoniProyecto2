@extends('layouts.app')

@section('title', 'Dashboard Cliente - Malkoni Hnos')

@section('content')
<div class="min-h-screen text-gray-900">
    <div class="flex">
        @include('cliente.components.sidebar') 

        <main class="flex-1 overflow-y-auto lg:ml-48">
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
</div>
@endsection