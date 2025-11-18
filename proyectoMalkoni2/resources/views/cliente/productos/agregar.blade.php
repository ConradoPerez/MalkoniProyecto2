@extends('layouts.app')

@section('title', 'Agregar Productos')

@section('content')
<div class="min-h-screen bg-gray-50 text-gray-900">
    <div class="flex">
        @include('cliente.components.sidebar')

        <main class="flex-1 overflow-y-auto md:ml-64 transition-all duration-300">
            
            <div class="p-4 lg:p-8">
                @include('cliente.components.header')

                <nav class="flex mb-6 mt-4" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li>
                            <a href="{{ route('cliente.cotizacion.ver', $cotizacion->id) }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-[#D88429]">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                                Volver al Detalle
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                                <span class="ml-1 text-sm font-medium text-gray-400">Catálogo de Productos</span>
                            </div>
                        </li>
                    </ol>
                </nav>

                <div class="flex flex-col md:flex-row justify-between items-end mb-8 gap-4">
                    <div>
                        <h2 class="text-2xl font-syncopate font-bold text-gray-800">Selección de Productos</h2>
                        <p class="text-sm text-gray-500 mt-1">Agrega items a la cotización <span class="font-semibold text-[#D88429]">#{{ $cotizacion->numero }}</span></p>
                    </div>
                    
                    <div class="w-full md:w-1/3 relative">
                        <input type="text" placeholder="Buscar productos..." class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-200 focus:border-[#D88429] focus:ring-2 focus:ring-[#D88429]/20 outline-none transition-all shadow-sm text-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <form action="{{ route('cliente.cotizacion.guardar_productos', ['id' => $cotizacion->id]) }}" method="POST" id="formProductos">
                    @csrf
                    
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                        
                        <div class="lg:col-span-2">
                            
                            <div class="flex gap-2 overflow-x-auto pb-4 mb-4 no-scrollbar">
                                <button type="button" class="px-4 py-2 bg-gray-800 text-white rounded-full text-sm font-medium whitespace-nowrap shadow-md">Todos</button>
                                @foreach($categorias as $cat)
                                    <button type="button" class="px-4 py-2 bg-white text-gray-600 border border-gray-200 rounded-full text-sm font-medium whitespace-nowrap hover:bg-gray-50 hover:border-gray-300 transition-colors">{{ $cat->nombre }}</button>
                                @endforeach
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                                @forelse($productos as $index => $producto)
                                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden group flex flex-col h-full">
                                        
                                        <div class="relative h-48 bg-gray-100 overflow-hidden">
                                            @if($producto->foto)
                                                <img src="{{ asset($producto->foto) }}" class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-500">
                                            @else
                                                <div class="flex items-center justify-center h-full text-gray-300">
                                                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                </div>
                                            @endif
                                            
                                            @if($producto->descuento > 0)
                                                <div class="absolute top-3 right-3 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded shadow-sm">
                                                    -{{ $producto->descuento }}%
                                                </div>
                                            @endif
                                        </div>

                                        <div class="p-5 flex-1 flex flex-col">
                                            <div class="mb-auto">
                                                <h3 class="font-bold text-gray-900 text-base mb-1 leading-tight">{{ $producto->nombre }}</h3>
                                                <p class="text-xs text-gray-500 line-clamp-2 mb-3">{{ $producto->descripcion }}</p>
                                            </div>

                                            <div class="mt-4 pt-4 border-t border-gray-50">
                                                <div class="flex justify-between items-end mb-3">
                                                    <div class="flex flex-col">
                                                        @if($producto->descuento > 0)
                                                            <span class="text-xs text-gray-400 line-through">${{ number_format($producto->precio_base / 100, 2, ',', '.') }}</span>
                                                        @endif
                                                        <span class="text-lg font-bold text-gray-900">${{ number_format($producto->precio_final / 100, 2, ',', '.') }}</span>
                                                    </div>
                                                </div>

                                                <div class="flex items-center bg-gray-50 rounded-lg border border-gray-200 p-1">
                                                    <button type="button" onclick="ajustarCantidad('{{ $producto->id_producto }}', -1)" class="w-8 h-8 flex items-center justify-center text-gray-500 hover:text-red-500 hover:bg-white rounded-md transition-colors">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                                                    </button>
                                                    
                                                    <input type="hidden" name="productos[{{ $index }}][id_producto]" value="{{ $producto->id_producto }}">
                                                    <input type="number" 
                                                           id="input-{{ $producto->id_producto }}"
                                                           name="productos[{{ $index }}][cantidad]" 
                                                           value="0" 
                                                           min="0"
                                                           class="flex-1 w-full text-center bg-transparent border-none focus:ring-0 text-gray-900 font-semibold p-0 cantidad-input"
                                                           data-precio="{{ $producto->precio_final }}"
                                                           data-nombre="{{ $producto->nombre }}"
                                                           readonly>
                                                           
                                                    <button type="button" onclick="ajustarCantidad('{{ $producto->id_producto }}', 1)" class="w-8 h-8 flex items-center justify-center text-gray-500 hover:text-green-600 hover:bg-white rounded-md transition-colors">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-span-full text-center py-12">
                                        <p class="text-gray-500">No se encontraron productos.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <div class="lg:col-span-1">
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 sticky top-24 overflow-hidden">
                                <div class="p-5 border-b border-gray-100 bg-gray-50/50">
                                    <h3 class="font-bold text-gray-800">Resumen del Pedido</h3>
                                </div>

                                <div class="p-5 max-h-[400px] overflow-y-auto space-y-3" id="lista-seleccionados">
                                    <p class="text-sm text-gray-400 text-center italic py-4" id="mensaje-vacio">
                                        Aún no has seleccionado productos.
                                    </p>
                                    </div>

                                <div class="bg-gray-900 p-5 text-white mt-auto">
                                    <div class="flex justify-between text-sm text-gray-400 mb-2">
                                        <span>Acumulado Anterior</span>
                                        <span>${{ number_format($cotizacion->precio_total / 100, 2, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm text-gray-300 mb-4 pb-4 border-b border-gray-700">
                                        <span>Nuevos Productos</span>
                                        <span id="subtotal-nuevos">$0,00</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="font-bold text-lg">Total Estimado</span>
                                        <span class="font-bold text-2xl text-[#D88429]" id="total-final">
                                            ${{ number_format($cotizacion->precio_total / 100, 2, ',', '.') }}
                                        </span>
                                    </div>
                                    
                                    <button type="submit" class="w-full mt-6 py-3 bg-[#D88429] hover:bg-[#c7731f] text-white font-bold rounded-lg shadow-lg transition-all transform hover:-translate-y-1 flex justify-center items-center disabled:opacity-50 disabled:cursor-not-allowed" id="btn-confirmar">
                                        Confirmar Agregado
                                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    </button>
                                </div>
                            </div>

                            <div class="mt-4 text-center">
                                <a href="{{ route('cliente.cotizacion.ver', $cotizacion->id) }}" class="text-sm text-gray-500 hover:text-gray-800 underline">Cancelar y volver</a>
                            </div>
                        </div>

                    </div>
                </form>
            </div>
        </main>
    </div>
</div>

<script>
    const precioAnterior = {{ $cotizacion->precio_total }};
    
    // Función auxiliar para botones +/-
    function ajustarCantidad(id, cambio) {
        const input = document.getElementById('input-' + id);
        let val = parseInt(input.value) || 0;
        val += cambio;
        if (val < 0) val = 0;
        input.value = val;
        // Disparar evento para recalcular
        input.dispatchEvent(new Event('input'));
    }

    document.addEventListener('DOMContentLoaded', function() {
        const inputs = document.querySelectorAll('.cantidad-input');
        const listaSeleccionados = document.getElementById('lista-seleccionados');
        const mensajeVacio = document.getElementById('mensaje-vacio');
        const subtotalEl = document.getElementById('subtotal-nuevos');
        const totalEl = document.getElementById('total-final');
        const btnConfirmar = document.getElementById('btn-confirmar');

        function actualizarEstado() {
            let subtotalNuevos = 0;
            let itemsHtml = '';
            let hayItems = false;

            inputs.forEach(input => {
                const cantidad = parseInt(input.value) || 0;
                
                if (cantidad > 0) {
                    hayItems = true;
                    const precio = parseFloat(input.dataset.precio);
                    const nombre = input.dataset.nombre;
                    const totalItem = cantidad * precio;
                    subtotalNuevos += totalItem;

                    // Crear mini item para el resumen
                    itemsHtml += `
                        <div class="flex justify-between items-start text-sm animate-fade-in-up">
                            <div class="flex-1 pr-2">
                                <p class="font-medium text-gray-700">${nombre}</p>
                                <p class="text-xs text-gray-500">x${cantidad} un.</p>
                            </div>
                            <span class="font-semibold text-gray-900">$${(totalItem/100).toLocaleString('es-AR', {minimumFractionDigits: 2})}</span>
                        </div>
                    `;
                }
            });

            // Actualizar HTML
            if (hayItems) {
                listaSeleccionados.innerHTML = itemsHtml;
                mensajeVacio.style.display = 'none';
            } else {
                listaSeleccionados.innerHTML = '';
                listaSeleccionados.appendChild(mensajeVacio);
                mensajeVacio.style.display = 'block';
            }

            // Actualizar montos
            const totalFinal = subtotalNuevos + precioAnterior;
            
            subtotalEl.innerText = '$' + (subtotalNuevos/100).toLocaleString('es-AR', {minimumFractionDigits: 2});
            totalEl.innerText = '$' + (totalFinal/100).toLocaleString('es-AR', {minimumFractionDigits: 2});
        }

        // Listeners
        inputs.forEach(input => {
            input.addEventListener('input', actualizarEstado);
        });

        // Prevenir envío vacío
        document.getElementById('formProductos').addEventListener('submit', function(e) {
            let hayItems = false;
            inputs.forEach(i => { if(i.value > 0) hayItems = true; });
            
            if(!hayItems) {
                e.preventDefault();
                alert('Por favor selecciona al menos un producto.');
            }
        });
    });
</script>

<style>
    /* Ocultar scrollbar en tabs pero permitir scroll */
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
    /* Animación simple para nuevos items */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(5px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in-up {
        animation: fadeInUp 0.3s ease-out forwards;
    }
</style>
@endsection
