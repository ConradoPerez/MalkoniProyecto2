@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-background text-gray-900">
    <div class="flex">
        {{-- Sidebar fijo --}}
        @include('vendedor.components.sidebar')

        {{-- Contenido principal --}}
        <main class="flex-1 overflow-y-auto ml-56">
            <div class="p-4 lg:p-8">

                {{-- Header --}}
                <div class="flex flex-col lg:flex-row lg:items-center justify-between mb-8 gap-4">
                    <div>
                        <div class="flex items-center gap-4 mb-2">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $estadoColores[$cotizacion->estado_actual->nombre ?? 'Nuevo'] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ $cotizacion->estado_actual->nombre ?? 'Sin estado' }}
                            </span>
                        </div>
                        <h1 class="text-3xl font-syncopate font-bold text-gray-900 tracking-wide">
                            COTIZACIÓN #{{ $cotizacion->numero }}
                        </h1>
                        <p class="text-gray-600 mt-1">
                            {{ $cotizacion->titulo ?? 'Sin título' }}
                        </p>
                    </div>

                    <div class="flex items-center gap-4">
                        {{-- Botón volver --}}
                        <a href="{{ route('vendedor.app.cotizaciones.index', ['empleado_id' => request('empleado_id')]) }}" 
                           class="inline-flex items-center px-6 py-3 rounded-lg text-white font-semibold transition hover:opacity-90 shadow-md"
                           style="background-color:#D88429;">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Volver a Cotizaciones
                        </a>

                        {{-- Tarjeta del cliente --}}
                        <div class="flex items-center gap-3 bg-white border border-gray-200 rounded-xl px-4 py-3 shadow-sm">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-r from-amber-500 to-orange-600 grid place-items-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </div>
                            <div class="text-sm">
                                <div class="font-semibold text-gray-900">{{ $cotizacion->empresa->nombre ?? 'Sin cliente' }}</div>
                                <div class="text-gray-500">CUIT: {{ $cotizacion->empresa->cuit ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Mensajes de éxito o error --}}
                @if(session('success'))
                    <div class="mb-6 bg-green-50 border border-green-200 rounded-xl p-4">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-green-800 font-medium">{{ session('success') }}</p>
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-xl">
                        <div class="flex items-start">
                            <svg class="w-6 h-6 text-red-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-red-800">Error al guardar la cotización:</p>
                                <p class="text-sm text-red-700 mt-1">{{ session('error') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Información de la cotización --}}
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                    <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600 mb-1">Fecha de creación</p>
                                <p class="text-xl font-bold text-gray-900">{{ $cotizacion->fyh ? $cotizacion->fyh->format('d/m/Y') : 'N/A' }}</p>
                            </div>
                            <div class="w-12 h-12 rounded-full bg-blue-100 grid place-items-center">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600 mb-1">Total de items</p>
                                <p class="text-xl font-bold text-gray-900">{{ $cotizacion->items->count() }}</p>
                            </div>
                            <div class="w-12 h-12 rounded-full bg-purple-100 grid place-items-center">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600 mb-1">Monto total</p>
                                @if($cotizacion->precio_total && $cotizacion->precio_total > 0)
                                    <p id="monto-total" class="text-xl font-bold text-green-600">${{ number_format($cotizacion->precio_total, 2, ',', '.') }}</p>
                                @else
                                    <p id="monto-total" class="text-xl font-bold text-gray-400">$0,00</p>
                                @endif
                            </div>
                            <div class="w-12 h-12 rounded-full bg-green-100 grid place-items-center">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Formulario de cotización --}}
                @php
                    $estadoNombre = $cotizacion->estado_actual->nombre ?? 'Sin estado';
                    $puedeEditar = in_array($estadoNombre, ['Nuevo', 'Abierto']) || !$cotizacion->precio_total || $cotizacion->precio_total <= 0;
                @endphp

                <form method="POST" action="{{ route('vendedor.app.cotizaciones.guardar', ['id' => $cotizacion->id]) }}" class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden mb-6">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="empleado_id" value="{{ request('empleado_id') }}">
                    
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Items de la cotización</h2>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr class="text-left">
                                    <th class="px-6 py-4 text-sm font-semibold text-gray-700">Producto</th>
                                    <th class="px-6 py-4 text-sm font-semibold text-gray-700 text-center">Cantidad</th>
                                    <th class="px-6 py-4 text-sm font-semibold text-gray-700 text-right">Precio Unitario</th>
                                    <th class="px-6 py-4 text-sm font-semibold text-gray-700 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($cotizacion->items as $index => $item)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="flex flex-col">
                                                <span class="text-sm font-medium text-gray-900">
                                                    {{ $item->producto->nombre ?? 'Producto personalizado' }}
                                                </span>
                                                @if($item->descripcion)
                                                    <span class="text-xs text-gray-500">{{ $item->descripcion }}</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="text-sm text-gray-700">{{ $item->cantidad ?? 1 }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            @if($puedeEditar)
                                                <input type="number" 
                                                       name="items[{{ $item->id_item }}][precio_unitario]" 
                                                       value="{{ $item->precio_unitario ?? 0 }}"
                                                       step="0.01"
                                                       min="0"
                                                       required
                                                       class="w-32 px-3 py-2 border border-gray-300 rounded-lg text-right focus:ring-2 focus:ring-[#D88429]/20 focus:border-[#D88429]"
                                                       placeholder="0.00">
                                            @else
                                                <span class="text-sm font-medium text-gray-900">${{ number_format($item->precio_unitario ?? 0, 2, ',', '.') }}</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <span class="text-sm font-bold text-gray-900">
                                                ${{ number_format(($item->precio_unitario ?? 0) * ($item->cantidad ?? 1), 2, ',', '.') }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-12 text-center">
                                            <div class="text-gray-500">
                                                <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                                </svg>
                                                <p class="text-sm">Esta cotización no tiene items asociados</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($cotizacion->items->count() > 0 && $puedeEditar)
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-between items-center">
                            <div class="text-sm text-gray-600">
                                <p>Complete los precios unitarios y haga clic en "Guardar cotización"</p>
                                <p class="text-xs mt-1">El estado cambiará automáticamente a "Cotizado" al guardar</p>
                            </div>
                            <button type="submit" 
                                    class="inline-flex items-center px-6 py-3 rounded-lg text-white font-semibold transition hover:opacity-90"
                                    style="background-color:#D88429;">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Guardar cotización
                            </button>
                        </div>
                    @endif
                </form>

                {{-- Historial de cambios de estado --}}
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Historial de estados</h2>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            @forelse($cotizacion->cambios()->with('estado')->orderByDesc('fyH')->get() as $cambio)
                                <div class="flex items-start gap-4">
                                    <div class="w-10 h-10 rounded-full {{ $estadoColores[$cambio->estado->nombre ?? ''] ?? 'bg-gray-100' }} grid place-items-center flex-shrink-0">
                                        <svg class="w-5 h-5 {{ in_array($cambio->estado->nombre ?? '', ['Nuevo', 'Abierto']) ? 'text-blue-800' : 'text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm font-semibold text-gray-900">{{ $cambio->estado->nombre ?? 'Estado desconocido' }}</span>
                                            <span class="text-xs text-gray-500">{{ $cambio->fyH ? $cambio->fyH->format('d/m/Y H:i') : 'N/A' }}</span>
                                        </div>
                                        @if($cambio->estado->nombre == 'Cotizado')
                                            <p class="text-xs text-gray-600 mt-1">Cotización completada por el vendedor</p>
                                        @elseif($cambio->estado->nombre == 'Abierto')
                                            <p class="text-xs text-gray-600 mt-1">Cotización abierta para revisión</p>
                                        @elseif($cambio->estado->nombre == 'Nuevo')
                                            <p class="text-xs text-gray-600 mt-1">Cotización creada por el cliente</p>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500 text-center py-4">No hay historial de cambios</p>
                            @endforelse
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>
</div>

<script>
// Calcular subtotales y total dinámicamente si se editan los precios
document.addEventListener('DOMContentLoaded', function() {
    const inputs = document.querySelectorAll('input[name*="precio_unitario"]');
    const montoTotalElement = document.getElementById('monto-total');
    
    function calcularTotal() {
        let total = 0;
        
        inputs.forEach(input => {
            const row = input.closest('tr');
            const cantidadText = row.querySelector('td:nth-child(2) span').textContent;
            const cantidad = parseFloat(cantidadText) || 1;
            const precioUnitario = parseFloat(input.value) || 0;
            const subtotal = cantidad * precioUnitario;
            
            // Actualizar subtotal de la fila
            const subtotalElement = row.querySelector('td:nth-child(4) span');
            subtotalElement.textContent = '$' + subtotal.toLocaleString('es-AR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            
            total += subtotal;
        });
        
        // Actualizar monto total
        if (montoTotalElement) {
            montoTotalElement.textContent = '$' + total.toLocaleString('es-AR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            
            // Cambiar color según si tiene valor
            if (total > 0) {
                montoTotalElement.classList.remove('text-gray-400');
                montoTotalElement.classList.add('text-green-600');
            } else {
                montoTotalElement.classList.remove('text-green-600');
                montoTotalElement.classList.add('text-gray-400');
            }
        }
    }
    
    inputs.forEach(input => {
        input.addEventListener('input', calcularTotal);
    });
    
    // Calcular total inicial
    calcularTotal();
});
</script>

@endsection
