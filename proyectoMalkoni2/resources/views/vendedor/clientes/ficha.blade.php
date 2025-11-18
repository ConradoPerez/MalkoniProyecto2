@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-background text-gray-900">
    <div class="flex">
        {{-- Sidebar --}}
        @include('vendedor.components.sidebar')

        {{-- Contenido principal --}}
        <main class="flex-1 overflow-y-auto ml-56">
            <div class="p-4 lg:p-8">

                {{-- Topbar --}}
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <a href="{{ route('vendedor.app.clientes.index') }}?empleado_id={{ request('empleado_id', 1) }}" 
                           class="text-sm text-gray-500 hover:text-gray-700 mb-2 inline-flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                            Volver a Clientes
                        </a>
                        <h1 class="text-2xl lg:text-3xl font-bold tracking-tight">Ficha de Cliente</h1>
                    </div>

                    <div class="hidden md:flex items-center gap-3 bg-white border border-gray-200 rounded-xl px-3 py-2 shadow-sm">
                        <div class="w-8 h-8 rounded-full bg-gray-200 grid place-items-center">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <div class="text-sm">
                            <div class="font-semibold">{{ $vendedor->nombre ?? 'Vendedor' }}</div>
                            <div class="text-gray-500">Vendedor activo</div>
                        </div>
                    </div>
                </div>

                {{-- Información del cliente --}}
                <section class="bg-white border border-gray-200 rounded-xl p-6 mb-6">
                    <h2 class="text-lg font-semibold mb-4">Información del Cliente</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="text-sm text-gray-600 block mb-1">Nombre de Empresa</label>
                            <p class="text-base font-medium">{{ $empresa->nombre }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-gray-600 block mb-1">CUIT</label>
                            <p class="text-base font-medium">{{ $empresa->cuit_formateado }}</p>
                        </div>
                    </div>
                </section>

                {{-- Estadísticas --}}
                <section class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="bg-white border border-gray-200 rounded-xl p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">Total Cotizaciones</p>
                                <p class="text-2xl font-bold mt-1">{{ $estadisticas['total_cotizaciones'] }}</p>
                            </div>
                            <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white border border-gray-200 rounded-xl p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">Este Mes</p>
                                <p class="text-2xl font-bold mt-1">{{ $estadisticas['cotizaciones_mes'] }}</p>
                            </div>
                            <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white border border-gray-200 rounded-xl p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">Monto Total</p>
                                <p class="text-2xl font-bold mt-1">${{ number_format($estadisticas['monto_total'], 0, ',', '.') }}</p>
                            </div>
                            <div class="w-12 h-12 rounded-full bg-yellow-100 flex items-center justify-center">
                                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- Grupos asociados --}}
                <section class="bg-white border border-gray-200 rounded-xl p-6">
                    <h2 class="text-lg font-semibold mb-4">Grupos Asociados</h2>
                    @if($empresa->grupos->count() > 0)
                        <div class="flex flex-wrap gap-2">
                            @foreach($empresa->grupos as $grupo)
                                <span class="px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-700">
                                    {{ $grupo->nombre }}
                                </span>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-sm">Este cliente no pertenece a ningún grupo</p>
                    @endif
                </section>

                {{-- Acciones rápidas --}}
                <div class="mt-6 flex gap-4">
                    <a href="{{ route('vendedor.app.clientes.cotizaciones', $empresa->id_empresa) }}?empleado_id={{ request('empleado_id', 1) }}"
                       class="inline-flex items-center px-6 py-3 rounded-lg text-white font-semibold transition"
                       style="background-color:#D88429;">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        Ver Cotizaciones
                    </a>
                </div>

            </div>
        </main>
    </div>
</div>
@endsection
