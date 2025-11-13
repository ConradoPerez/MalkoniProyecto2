@extends('layouts.app')

@section('title', 'Cotización #' . $cotizacion->numero_formateado)

@section('content')
<div class="min-h-screen text-gray-900">
    <div class="flex">
        <!-- Sidebar del Cliente -->
        <aside class="w-48 bg-gray-100 border-r border-gray-300">
            @include('cliente.components.sidebar') 
        </aside>

        <!-- Main content -->
        <main class="flex-1 overflow-y-auto">
            <div class="p-4 lg:p-8">
                
                <!-- Header -->
                <div class="flex justify-between items-start border-b pb-4 mb-6">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $cotizacion->titulo }}</h1>
                        <p class="text-sm text-gray-600">Cotización: {{ $cotizacion->numero_formateado }}</p>
                        <p class="text-sm text-gray-600">Fecha: {{ $cotizacion->fyh->format('d/m/Y H:i') }}</p>
                    </div>
                    
                    <!-- Estado -->
                    <div class="flex flex-col items-end">
                        <span class="text-lg font-medium">{{ auth()->user()?->name ?? 'Cliente' }}</span>
                        <span class="inline-block mt-2 px-4 py-2 rounded text-white font-semibold" style="{{ $cotizacion->estado_estilo }}">
                            {{ $cotizacion->estado }}
                        </span>
                    </div>
                </div>

                <!-- Información Principal -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <!-- Vendedor -->
                    <div class="bg-white border border-gray-300 rounded-lg p-4">
                        <h3 class="font-semibold text-gray-900 mb-2">Vendedor</h3>
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-gray-200 rounded-full overflow-hidden">
                                @if($cotizacion->empleado?->foto)
                                    <img src="{{ asset($cotizacion->empleado->foto) }}" alt="{{ $cotizacion->empleado->nombre }}" class="w-full h-full object-cover">
                                @else
                                    <svg class="w-full h-full text-gray-400 p-2" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" /></svg>
                                @endif
                            </div>
                            <div>
                                <p class="font-medium">{{ $cotizacion->empleado?->nombre ?? 'Sin vendedor' }}</p>
                                <p class="text-sm text-gray-600">{{ $cotizacion->empleado?->email ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Información de Contacto -->
                    <div class="bg-white border border-gray-300 rounded-lg p-4">
                        <h3 class="font-semibold text-gray-900 mb-2">Cliente</h3>
                        <div>
                            @if($cotizacion->empresa)
                                <p class="font-medium">{{ $cotizacion->empresa->nombre }}</p>
                                <p class="text-sm text-gray-600">CUIT: {{ $cotizacion->empresa->cuit }}</p>
                            @elseif($cotizacion->persona)
                                <p class="font-medium">{{ $cotizacion->persona->nombre }}</p>
                                <p class="text-sm text-gray-600">DNI: {{ $cotizacion->persona->dni }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Tabla de Items -->
                <div class="mb-8 bg-white border border-gray-300 rounded-lg overflow-hidden">
                    <h3 class="bg-gray-100 px-6 py-3 font-semibold text-gray-900 border-b border-gray-300">Productos y Servicios</h3>
                    
                    @if($cotizacion->items->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="bg-gray-50 border-b border-gray-300">
                                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Producto</th>
                                        <th class="px-6 py-3 text-center text-sm font-semibold text-gray-900">Cantidad</th>
                                        <th class="px-6 py-3 text-right text-sm font-semibold text-gray-900">Precio Unitario</th>
                                        <th class="px-6 py-3 text-right text-sm font-semibold text-gray-900">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($cotizacion->items as $item)
                                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                                            <td class="px-6 py-3">
                                                <p class="font-medium text-gray-900">{{ $item->producto?->nombre ?? 'Producto eliminado' }}</p>
                                                <p class="text-sm text-gray-600">{{ $item->producto?->descripcion ?? '' }}</p>
                                            </td>
                                            <td class="px-6 py-3 text-center">{{ $item->cantidad }}</td>
                                            <td class="px-6 py-3 text-right">${{ number_format(($item->producto?->precio_final ?? 0) / 100, 2, ',', '.') }}</td>
                                            <td class="px-6 py-3 text-right font-medium">${{ number_format(($item->producto?->precio_final ?? 0) * $item->cantidad / 100, 2, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-6 py-8 text-center text-gray-600">
                                                No hay productos agregados a esta cotización.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="px-6 py-8 text-center text-gray-600">
                            <p>No hay productos agregados a esta cotización.</p>
                        </div>
                    @endif
                </div>

                <!-- Resumen de Precios -->
                <div class="max-w-md ml-auto bg-gray-50 border-2 border-gray-300 rounded-lg p-6 mb-8">
                    <h3 class="text-lg font-semibold mb-4">Resumen de Precios:</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span>Subtotal:</span>
                            <span class="font-medium">${{ number_format($cotizacion->items->sum(function($item) { return ($item->producto->precio_final ?? 0) * $item->cantidad; }) / 100, 2, ',', '.') }}</span>
                        </div>
                        <div class="border-t pt-2 flex justify-between text-lg font-bold">
                            <span>Total:</span>
                            <span class="text-[#D88429]">${{ number_format($cotizacion->precio_total / 100, 2, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="flex justify-center gap-4">
                    <a href="{{ route('cliente.cotizaciones') }}" class="px-6 py-3 bg-gray-400 text-white font-semibold rounded shadow hover:bg-gray-500 transition-colors">
                        Volver
                    </a>
                    <a href="{{ route('cliente.cotizacion.productos', ['id' => $cotizacion->id]) }}" class="px-6 py-3 bg-blue-500 text-white font-semibold rounded shadow hover:bg-blue-600 transition-colors">
                        Agregar Más Productos
                    </a>
                    <a href="{{ route('cliente.cotizacion.editar', ['id' => $cotizacion->id]) }}" class="px-6 py-3 bg-[#D88429] text-white font-semibold rounded shadow hover:bg-[#c7731f] transition-colors">
                        Editar Cotización
                    </a>
                </div>
                
            </div>
        </main>
    </div>
</div>

@endsection
