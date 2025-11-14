@extends('layouts.app')

@section('title', 'Nueva Cotización')

@section('content')
<div class="min-h-screen text-gray-900">
    <!-- Sidebar -->
    @include('cliente.components.sidebar')

    <!-- Main content -->
    <main>
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

        <!-- Desktop Header with offset -->
        <div class="hidden lg:block sticky top-0 z-20 bg-white border-b border-gray-200 p-8">
            <h1 class="text-2xl font-bold text-gray-900">Nueva Cotización</h1>
        </div>

        <div class="p-4 lg:p-8">
                
                <div class="lg:hidden flex justify-between items-start border-b pb-4 mb-6">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 mb-2">Nueva Cotización</h1>
                        <p class="text-sm text-gray-600">Fecha: {{ now()->format('d/m/Y') }}</p>
                        <p class="text-sm text-gray-600">Número: {{ $numero_pedido ?? 'Generando...' }}</p>
                    </div>
                    
                    <!-- Sección de Usuario -->
                    <div class="flex items-center space-x-2">
                        <span class="text-lg font-medium">Usuario</span>
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                </div>

                <!-- Desktop Header Info -->
                <div class="hidden lg:flex justify-between items-start mb-6 pb-4">
                    <div>
                        <p class="text-sm text-gray-600">Fecha: {{ now()->format('d/m/Y') }}</p>
                        <p class="text-sm text-gray-600">Número: {{ $numero_pedido ?? 'Generando...' }}</p>
                    </div>
                </div>

                <form action="{{ route('cliente.cotizacion.store') }}" method="POST" id="formCotizacion">
                    @csrf

                    <!-- SECCIÓN: ELEGIR UN VENDEDOR -->
                    <div class="mb-12">
                        <h2 class="text-xl font-semibold mb-6 text-center">Elija un vendedor:</h2>
                        
                        <div class="flex justify-center flex-wrap gap-8">
                            @forelse($vendedores as $vendedor)
                                <label class="cursor-pointer group">
                                    <input type="radio" name="id_empleados" value="{{ $vendedor->id_empleado }}" class="hidden peer" required>
                                    
                                    <div class="w-32 h-32 border-2 border-gray-300 rounded-lg p-3 flex flex-col items-center justify-center transition-all peer-checked:border-[#D88429] peer-checked:shadow-lg hover:shadow-md">
                                        <!-- Placeholder de la foto del vendedor -->
                                        <div class="w-16 h-16 rounded-full bg-gray-200 mb-2 border border-gray-400">
                                            @if($vendedor->foto)
                                                <img src="{{ asset($vendedor->foto) }}" alt="{{ $vendedor->nombre }}" class="w-full h-full rounded-full object-cover">
                                            @else
                                                <svg class="w-full h-full text-gray-500 p-2" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" /></svg>
                                            @endif
                                        </div>
                                        <span class="text-sm font-medium text-gray-700 peer-checked:text-[#D88429] text-center">{{ $vendedor->nombre }}</span>
                                    </div>
                                </label>
                            @empty
                                <p class="text-center text-red-500">No hay vendedores disponibles.</p>
                            @endforelse
                        </div>

                        <!-- Mensaje de validación -->
                        @error('id_empleados')
                            <p class="text-red-500 text-sm mt-3 text-center">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- SECCIÓN: MENSAJE Y ACCIONES -->
                    <div class="max-w-xl mx-auto mt-16">
                        <!-- Número de pedido oculto -->
                        <input type="hidden" name="numero_pedido" value="{{ $numero_pedido }}">

                        <!-- Mensaje al vendedor -->
                        <div class="border border-gray-300 p-4 rounded-lg shadow-inner mb-8 h-48">
                            <textarea name="mensaje_inicial" placeholder="Escriba un mensaje al vendedor aquí (opcional)" class="w-full h-full resize-none border-none focus:ring-0 text-gray-700 placeholder-gray-500"></textarea>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="flex justify-center gap-4">
                            <a href="{{ route('cliente.dashboard') }}" class="px-8 py-3 bg-gray-400 text-white font-semibold rounded shadow hover:bg-gray-500 transition-colors">
                                Cancelar
                            </a>
                            <button type="submit" class="px-8 py-3 bg-[#D88429] text-white font-semibold rounded shadow hover:bg-[#c7731f] transition-colors" id="btnContinuar">
                                Continuar (Agregar Productos)
                            </button>
                        </div>
                    </div>
                </form>
                
            </div>
        </main>
    </div>
</div>
@endsection