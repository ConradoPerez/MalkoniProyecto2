@extends('layouts.app')

@section('title', 'Detalle de cotización')

@section('content')
<div class="min-h-screen bg-gray-100">
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
            <div class="max-w-5xl mx-auto">
                
                <!-- Header principal -->
                <div class="flex justify-between items-start mb-8">
                    <div>
                        <h1 class="text-4xl font-bold text-gray-900 mb-4">Detalle de cotización</h1>
                        <div class="space-y-2 text-gray-700">
                            <p><strong>Número del pedido:</strong> {{ $cotizacion->numero_formateado }}</p>
                            <p><strong>Fecha de inicio:</strong> {{ $cotizacion->fyh->format('d/m/Y') }}</p>
                        </div>
                    </div>
                    
                    <!-- Usuario/Estado -->
                    <div class="flex flex-col items-center gap-2">
                        <div class="w-16 h-16 bg-gray-300 rounded-full flex items-center justify-center">
                            <svg class="w-8 h-8 text-gray-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
                            </svg>
                        </div>
                        <p class="font-medium text-gray-900">{{ auth()->user()?->name ?? 'Cliente' }}</p>
                    </div>
                </div>

                <!-- Contenido principal en 2 columnas -->
                <div class="grid grid-cols-3 gap-6">
                    
                    <!-- Columna izquierda - Tabla de productos -->
                    <div class="col-span-2">
                        <div class="bg-white rounded-lg shadow-md border border-gray-300 overflow-hidden">
                            <div class="bg-gray-200 px-6 py-4 border-b border-gray-300">
                                <h2 class="font-bold text-gray-900">Código Producto Cantidad</h2>
                            </div>
                            
                            @if($cotizacion->items->count() > 0)
                                <table class="w-full">
                                    <tbody>
                                        @forelse($cotizacion->items as $item)
                                            <tr class="border-b border-gray-200 hover:bg-gray-50">
                                                <td class="px-6 py-4 text-gray-700">{{ $item->producto?->codigo ?? 'N/A' }}</td>
                                                <td class="px-6 py-4">
                                                    <p class="font-medium text-gray-900">{{ $item->producto?->nombre ?? 'Producto eliminado' }}</p>
                                                </td>
                                                <td class="px-6 py-4 text-gray-700">{{ $item->cantidad }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="px-6 py-8 text-center text-gray-600">
                                                    No hay productos agregados a esta cotización.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            @else
                                <div class="px-6 py-8 text-center text-gray-600">
                                    <p>No hay productos agregados a esta cotización.</p>
                                </div>
                            @endif

                            <div class="bg-gray-50 px-6 py-4 border-t border-gray-300">
                                <a href="{{ route('cliente.cotizacion.productos', ['id' => $cotizacion->id]) }}" class="text-blue-600 font-medium hover:underline">
                                    + Agregar productos
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Columna derecha - Información y resumen -->
                    <div class="space-y-6">
                        
                        <!-- Sección Mensajes -->
                        <div class="bg-white rounded-lg shadow-md border border-gray-300 p-4">
                            <button class="w-full px-4 py-2 bg-gray-200 border border-gray-400 rounded text-gray-700 font-medium hover:bg-gray-300 transition">
                                Mensajes
                            </button>
                        </div>

                        <!-- Información de Cliente -->
                        <div class="bg-white rounded-lg shadow-md border border-gray-300 p-4">
                            <h3 class="font-bold text-gray-900 mb-3">Cliente</h3>
                            <div class="bg-blue-50 border border-blue-300 rounded p-3 text-sm text-gray-700">
                                @if($cotizacion->empresa)
                                    <p><strong>{{ $cotizacion->empresa->nombre }}</strong></p>
                                    <p>CUIT: {{ $cotizacion->empresa->cuit }}</p>
                                @elseif($cotizacion->persona)
                                    <p><strong>{{ $cotizacion->persona->nombre }}</strong></p>
                                    <p>DNI: {{ $cotizacion->persona->dni }}</p>
                                @else
                                    <p>Sin información de cliente</p>
                                @endif
                            </div>
                        </div>

                        <!-- Información de Vendedor -->
                        <div class="bg-white rounded-lg shadow-md border border-gray-300 p-4">
                            <h3 class="font-bold text-gray-900 mb-3">Vendedor</h3>
                            <div class="bg-green-50 border border-green-300 rounded p-3 text-sm text-gray-700">
                                <p><strong>{{ $cotizacion->empleado?->nombre ?? 'Sin vendedor' }}</strong></p>
                                <p>{{ $cotizacion->empleado?->email ?? 'N/A' }}</p>
                            </div>
                        </div>

                        <!-- Resumen de Precios -->
                        <div class="bg-white rounded-lg shadow-md border border-gray-300 p-4">
                            <h3 class="font-bold text-gray-900 mb-4">Resumen</h3>
                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-700">Subtotal:</span>
                                    <span class="font-medium">${{ number_format($cotizacion->items->sum(function($item) { return ($item->producto?->precio_final ?? 0) * $item->cantidad; }) / 100, 2, ',', '.') }}</span>
                                </div>
                                <div class="border-t border-gray-300 pt-3 flex justify-between text-lg">
                                    <span class="font-bold text-gray-900">Total:</span>
                                    <span class="font-bold text-[#D88429]">${{ number_format($cotizacion->precio_total / 100, 2, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Botones de acción al final -->
                <div class="flex justify-center gap-4 mt-8">
                    <a href="{{ route('cliente.cotizaciones') }}" class="px-6 py-2 bg-gray-400 text-white font-medium rounded hover:bg-gray-500 transition">
                        Volver
                    </a>
                    <a href="{{ route('cliente.cotizacion.editar', ['id' => $cotizacion->id]) }}" class="px-6 py-2 bg-[#D88429] text-white font-medium rounded hover:bg-[#c7731f] transition">
                        Editar
                    </a>
                </div>
                
            </div>
        </div>
    </main>
</div>

@endsection
